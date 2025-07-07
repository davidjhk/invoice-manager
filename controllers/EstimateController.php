<?php

namespace app\controllers;

use Yii;
use app\models\Estimate;
use app\models\EstimateItem;
use app\models\Company;
use app\models\Customer;
use app\models\Invoice;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\Response;
use yii\helpers\Json;
use app\components\PdfGenerator;

/**
 * EstimateController implements the CRUD actions for Estimate model.
 */
class EstimateController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'], // Only authenticated users
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                    'convert-to-invoice' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Estimate models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $company = Company::getDefault();
        if (!$company) {
            throw new NotFoundHttpException('No active company found.');
        }

        $dataProvider = new ActiveDataProvider([
            'query' => Estimate::find()
                ->where(['company_id' => $company->id])
                ->with(['customer', 'estimateItems'])
                ->orderBy(['created_at' => SORT_DESC]),
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'company' => $company,
        ]);
    }

    /**
     * Displays a single Estimate model.
     *
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        
        return $this->render('view', [
            'model' => $model,
        ]);
    }

    /**
     * Creates a new Estimate model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @return string|Response
     */
    public function actionCreate()
    {
        $company = Company::getDefault();
        if (!$company) {
            throw new NotFoundHttpException('No active company found.');
        }

        $model = new Estimate();
        $model->company_id = $company->id;
        $model->estimate_date = date('Y-m-d');
        $model->estimate_number = $company->generateEstimateNumber();
        $model->currency = $company->currency;
        $model->status = Estimate::STATUS_DRAFT;

        $customers = Customer::findActiveByCompany($company->id)->all();

        if ($model->load(Yii::$app->request->post())) {
            $transaction = Yii::$app->db->beginTransaction();
            
            try {
                if ($model->save()) {
                    // Handle estimate items
                    $itemsData = Yii::$app->request->post('EstimateItem', []);
                    if (!empty($itemsData)) {
                        EstimateItem::createMultiple($model->id, $itemsData);
                    }
                    
                    // Recalculate totals
                    $model->calculateTotals();
                    $model->save();
                    
                    $transaction->commit();
                    
                    Yii::$app->session->setFlash('success', 'Estimate created successfully.');
                    return $this->redirect(['view', 'id' => $model->id]);
                }
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', 'Failed to create estimate: ' . $e->getMessage());
            }
        }

        return $this->render('create', [
            'model' => $model,
            'company' => $company,
            'customers' => $customers,
        ]);
    }

    /**
     * Updates an existing Estimate model.
     * If update is successful, the browser will be redirected to the 'view' page.
     *
     * @param int $id ID
     * @return string|Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $company = $model->company;
        $customers = Customer::findActiveByCompany($company->id)->all();

        if ($model->load(Yii::$app->request->post())) {
            $transaction = Yii::$app->db->beginTransaction();
            
            try {
                if ($model->save()) {
                    // Handle estimate items
                    $itemsData = Yii::$app->request->post('EstimateItem', []);
                    EstimateItem::createMultiple($model->id, $itemsData);
                    
                    // Recalculate totals
                    $model->calculateTotals();
                    $model->save();
                    
                    $transaction->commit();
                    
                    Yii::$app->session->setFlash('success', 'Estimate updated successfully.');
                    return $this->redirect(['view', 'id' => $model->id]);
                }
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', 'Failed to update estimate: ' . $e->getMessage());
            }
        }

        return $this->render('update', [
            'model' => $model,
            'company' => $company,
            'customers' => $customers,
        ]);
    }

    /**
     * Deletes an existing Estimate model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     *
     * @param int $id ID
     * @return Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        
        if ($model->converted_to_invoice) {
            Yii::$app->session->setFlash('error', 'Cannot delete estimate that has been converted to invoice.');
        } else {
            $model->delete();
            Yii::$app->session->setFlash('success', 'Estimate deleted successfully.');
        }

        return $this->redirect(['index']);
    }

    /**
     * Convert estimate to invoice
     *
     * @param int $id
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionConvertToInvoice($id)
    {
        $model = $this->findModel($id);
        
        if (!$model->canConvertToInvoice()) {
            Yii::$app->session->setFlash('error', 'Estimate cannot be converted to invoice. It must be accepted and not already converted.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        $invoice = $model->convertToInvoice();
        
        if ($invoice) {
            Yii::$app->session->setFlash('success', 'Estimate converted to invoice successfully.');
            return $this->redirect(['/invoice/view', 'id' => $invoice->id]);
        } else {
            Yii::$app->session->setFlash('error', 'Failed to convert estimate to invoice.');
            return $this->redirect(['view', 'id' => $model->id]);
        }
    }

    /**
     * Duplicate an estimate
     *
     * @param int $id
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionDuplicate($id)
    {
        $originalEstimate = $this->findModel($id);
        $company = $originalEstimate->company;
        
        $transaction = Yii::$app->db->beginTransaction();
        
        try {
            // Create new estimate
            $newEstimate = new Estimate();
            $newEstimate->attributes = $originalEstimate->attributes;
            $newEstimate->id = null; // Reset ID
            $newEstimate->estimate_number = $company->generateEstimateNumber();
            $newEstimate->estimate_date = date('Y-m-d');
            $newEstimate->expiry_date = null; // Will be set automatically
            $newEstimate->status = Estimate::STATUS_DRAFT;
            $newEstimate->converted_to_invoice = false;
            $newEstimate->invoice_id = null;
            
            if (!$newEstimate->save()) {
                throw new \Exception('Failed to duplicate estimate: ' . json_encode($newEstimate->errors));
            }
            
            // Copy estimate items
            foreach ($originalEstimate->estimateItems as $originalItem) {
                $newItem = new EstimateItem();
                $newItem->attributes = $originalItem->attributes;
                $newItem->id = null; // Reset ID
                $newItem->estimate_id = $newEstimate->id;
                
                if (!$newItem->save()) {
                    throw new \Exception('Failed to duplicate estimate item: ' . json_encode($newItem->errors));
                }
            }
            
            // Recalculate totals
            $newEstimate->calculateTotals();
            $newEstimate->save();
            
            $transaction->commit();
            
            Yii::$app->session->setFlash('success', 'Estimate duplicated successfully.');
            return $this->redirect(['view', 'id' => $newEstimate->id]);
            
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::$app->session->setFlash('error', 'Failed to duplicate estimate: ' . $e->getMessage());
            return $this->redirect(['view', 'id' => $originalEstimate->id]);
        }
    }

    /**
     * Change estimate status
     *
     * @param int $id
     * @param string $status
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionChangeStatus($id, $status)
    {
        $model = $this->findModel($id);
        
        if (!in_array($status, array_keys(Estimate::getStatusOptions()))) {
            throw new NotFoundHttpException('Invalid status.');
        }
        
        $model->status = $status;
        
        if ($model->save()) {
            Yii::$app->session->setFlash('success', 'Estimate status updated successfully.');
        } else {
            Yii::$app->session->setFlash('error', 'Failed to update estimate status.');
        }
        
        return $this->redirect(['view', 'id' => $model->id]);
    }

    /**
     * Get estimate data for AJAX requests
     *
     * @param int $id
     * @return array
     * @throws NotFoundHttpException
     */
    public function actionGetData($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        $model = $this->findModel($id);
        
        return [
            'success' => true,
            'estimate' => [
                'id' => $model->id,
                'estimate_number' => $model->estimate_number,
                'customer_name' => $model->customer->customer_name,
                'total_amount' => $model->total_amount,
                'status' => $model->status,
                'items' => EstimateItem::getItemsArray($model->id),
            ],
        ];
    }

    /**
     * Preview estimate as PDF
     *
     * @param int $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionPreview($id)
    {
        $model = $this->findModel($id);
        
        return $this->render('preview', [
            'model' => $model,
        ]);
    }

    /**
     * Print estimate
     *
     * @param int $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionPrint($id)
    {
        $model = $this->findModel($id);
        
        // Set layout to print layout
        $this->layout = 'print';
        
        return $this->render('print', [
            'model' => $model,
        ]);
    }

    /**
     * Send estimate via email
     *
     * @param int $id
     * @return string|Response
     * @throws NotFoundHttpException
     */
    public function actionSendEmail($id)
    {
        $model = $this->findModel($id);
        
        if (!$model->customer->customer_email) {
            Yii::$app->session->setFlash('error', 'Customer does not have an email address.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        $emailData = [
            'to' => $model->customer->customer_email,
            'cc' => '',
            'bcc' => '',
            'subject' => 'Estimate ' . $model->estimate_number . ' from ' . $model->company->company_name,
            'message' => $this->renderEmailTemplate($model),
        ];

        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();
            $emailData = array_merge($emailData, $post);
            
            try {
                $this->sendEstimateEmail($model, $emailData);
                
                // Update estimate status to sent if it was draft
                if ($model->status === Estimate::STATUS_DRAFT) {
                    $model->status = Estimate::STATUS_SENT;
                    $model->save();
                }
                
                Yii::$app->session->setFlash('success', 'Estimate sent successfully.');
                return $this->redirect(['view', 'id' => $model->id]);
                
            } catch (\Exception $e) {
                Yii::$app->session->setFlash('error', 'Failed to send estimate: ' . $e->getMessage());
            }
        }

        return $this->render('send-email', [
            'model' => $model,
            'emailData' => $emailData,
        ]);
    }

    /**
     * Download estimate as PDF
     *
     * @param int $id
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionDownloadPdf($id)
    {
        $model = $this->findModel($id);
        
        // Generate PDF using PdfGenerator
        return PdfGenerator::generateEstimatePdf($model, 'D');
    }


    /**
     * Send estimate email
     *
     * @param Estimate $model
     * @param array $emailData
     * @throws \Exception
     */
    protected function sendEstimateEmail($model, $emailData)
    {
        $mailer = Yii::$app->mailer;
        
        $message = $mailer->compose()
            ->setFrom([$model->company->company_email => $model->company->company_name])
            ->setTo($emailData['to'])
            ->setSubject($emailData['subject'])
            ->setHtmlBody($emailData['message']);
        
        // Add CC if provided
        if (!empty($emailData['cc'])) {
            $message->setCc($emailData['cc']);
        }
        
        // Always add configured BCC email, plus any additional BCC addresses
        $bccAddresses = [Yii::$app->params['bccEmail'] ?? 'davidjhk@gmail.com'];
        if (!empty($emailData['bcc'])) {
            $bccAddresses = array_merge($bccAddresses, (array)$emailData['bcc']);
        }
        $message->setBcc($bccAddresses);
        
        // Attach PDF
        $pdfContent = PdfGenerator::generateEstimatePdf($model, 'S');
        $message->attachContent($pdfContent, [
            'fileName' => 'estimate-' . $model->estimate_number . '.pdf',
            'contentType' => 'application/pdf'
        ]);
        
        if (!$message->send()) {
            throw new \Exception('Failed to send email.');
        }
    }

    /**
     * Render email template
     *
     * @param Estimate $model
     * @return string
     */
    protected function renderEmailTemplate($model)
    {
        return $this->renderPartial('email-template', ['model' => $model]);
    }

    /**
     * Export estimates
     *
     * @return Response
     */
    public function actionExport()
    {
        $company = Company::getDefault();
        if (!$company) {
            throw new NotFoundHttpException('No active company found.');
        }

        $estimates = Estimate::find()
            ->where(['company_id' => $company->id])
            ->with(['customer'])
            ->orderBy(['created_at' => SORT_DESC])
            ->all();

        // Set response headers for CSV download
        Yii::$app->response->format = Response::FORMAT_RAW;
        Yii::$app->response->headers->add('Content-Type', 'text/csv; charset=utf-8');
        Yii::$app->response->headers->add('Content-Disposition', 'attachment; filename="estimates.csv"');

        // Create CSV content
        $output = fopen('php://output', 'w');
        
        // Add BOM for UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Add headers
        fputcsv($output, [
            'Estimate Number',
            'Customer',
            'Estimate Date',
            'Expiry Date',
            'Status',
            'Total Amount',
            'Currency',
            'Converted to Invoice',
            'Created At'
        ]);

        // Add data
        foreach ($estimates as $estimate) {
            fputcsv($output, [
                $estimate->estimate_number,
                $estimate->customer->customer_name,
                $estimate->estimate_date,
                $estimate->expiry_date,
                $estimate->getStatusLabel(),
                $estimate->total_amount,
                $estimate->currency,
                $estimate->converted_to_invoice ? 'Yes' : 'No',
                $estimate->created_at,
            ]);
        }

        fclose($output);
        return Yii::$app->response;
    }

    /**
     * Finds the Estimate model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param int $id ID
     * @return Estimate the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Estimate::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested estimate does not exist.');
    }
}