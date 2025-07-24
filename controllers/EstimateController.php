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
        $company = Company::getCurrent();
        if (!$company) {
            return $this->redirect(['company/select']);
        }

        $searchTerm = Yii::$app->request->get('search', '');
        $statusFilter = Yii::$app->request->get('status', '');

        $query = Estimate::find()
            ->where(['company_id' => $company->id])
            ->with(['customer', 'estimateItems'])
            ->orderBy(['estimate_number' => SORT_DESC]);

        // Exclude void status by default unless specifically filtering for void
        if ($statusFilter !== 'void') {
            $query->andWhere(['!=', 'status', Estimate::STATUS_VOID]);
        }

        // Apply search filter
        if (!empty($searchTerm)) {
            $query->joinWith(['customer'])
                ->andWhere(['or',
                    ['like', 'estimate_number', $searchTerm],
                    ['like', 'jdosa_customers.customer_name', $searchTerm],
                    ['like', 'notes', $searchTerm],
                ]);
        }

        // Apply status filter
        if (!empty($statusFilter)) {
            if ($statusFilter === 'expired') {
                $query->andWhere(['status' => 'sent'])
                    ->andWhere(['<', 'expiry_date', date('Y-m-d')]);
            } else {
                $query->andWhere(['status' => $statusFilter]);
            }
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'company' => $company,
            'searchTerm' => $searchTerm,
            'statusFilter' => $statusFilter,
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
        $company = Company::getCurrent();
        if (!$company) {
            return $this->redirect(['company/select']);
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
                    return $this->redirect(['preview', 'id' => $model->id]);
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
                    return $this->redirect(['preview', 'id' => $model->id]);
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
        
        if ($model->status === \app\models\Estimate::STATUS_VOID) {
            Yii::$app->session->setFlash('error', 'This estimate is already void.');
            return $this->redirect(['index']);
        }
        
        if ($model->converted_to_invoice) {
            Yii::$app->session->setFlash('error', 'Cannot void estimate that has been converted to invoice.');
        } else {
            if ($model->markAsVoid()) {
                Yii::$app->session->setFlash('success', 'Estimate marked as void successfully.');
            } else {
                Yii::$app->session->setFlash('error', 'Failed to mark estimate as void.');
            }
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
            return $this->redirect(['/invoice/preview', 'id' => $invoice->id]);
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
        
        // Mark as printed if in draft status
        $model->markAsPrinted();
        
        // Generate PDF using PdfGenerator
        return PdfGenerator::generateEstimatePdf($model, 'D');
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
        
        // Mark as printed if in draft status
        $model->markAsPrinted();
        
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
        
        $senderEmail = $model->company->sender_email ?: $model->company->company_email;
        $senderName = $model->company->sender_name ?: $model->company->company_name;
        
        $message = $mailer->compose()
            ->setFrom([$senderEmail => $senderName])
            ->setTo($emailData['to'])
            ->setSubject($emailData['subject'])
            ->setHtmlBody($emailData['message']);
        
        // Add CC if provided
        if (!empty($emailData['cc'])) {
            $message->setCc($emailData['cc']);
        }
        
        // Add configured BCC email if available, plus any additional BCC addresses
        $bccAddresses = [];
        if (!empty($model->company->bcc_email)) {
            $bccAddresses[] = $model->company->bcc_email;
        }
        if (!empty($emailData['bcc'])) {
            $bccAddresses = array_merge($bccAddresses, (array)$emailData['bcc']);
        }
        if (!empty($bccAddresses)) {
            $message->setBcc($bccAddresses);
        }
        
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
        $company = Company::getCurrent();
        if (!$company) {
            return $this->redirect(['company/select']);
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
     * Also ensures the estimate belongs to the current company.
     *
     * @param int $id ID
     * @return Estimate the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        $company = Company::getCurrent();
        if (!$company) {
            throw new NotFoundHttpException('No company selected.');
        }
        
        $model = Estimate::findOne(['id' => $id, 'company_id' => $company->id]);
        if ($model !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested estimate does not exist.');
    }

    /**
     * Calculate automatic tax rate for estimate
     *
     * @return array
     */
    public function actionCalculateTax()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        if (!Yii::$app->request->isPost) {
            return ['success' => false, 'message' => 'Only POST requests allowed'];
        }
        
        $data = Json::decode(Yii::$app->request->rawBody);
        $customerId = $data['customer_id'] ?? null;
        $companyId = $data['company_id'] ?? null;
        
        if (!$customerId || !$companyId) {
            return [
                'success' => false, 
                'message' => Yii::t('app/invoice', 'Customer and company are required')
            ];
        }
        
        try {
            $customer = Customer::findOne($customerId);
            $company = Company::findOne($companyId);
            
            if (!$customer || !$company) {
                return [
                    'success' => false, 
                    'message' => Yii::t('app/invoice', 'Customer or company not found')
                ];
            }
            
            // Create a temporary estimate to calculate tax
            $estimate = new Estimate();
            $estimate->customer_id = $customerId;
            $estimate->company_id = $companyId;
            if ($estimate->hasAttribute('tax_calculation_mode')) {
                $estimate->tax_calculation_mode = Estimate::TAX_MODE_AUTOMATIC;
            }
            
            // Calculate automatic tax rate
            $taxRate = $estimate->calculateAutomaticTaxRate($customer, $company);
            
            if ($taxRate !== null) {
                $details = $estimate->hasAttribute('tax_calculation_details') ? Json::decode($estimate->tax_calculation_details) : null;
                $message = Yii::t('app/invoice', 'Tax rate calculated automatically based on customer address');
                $messageType = 'success';
                
                // Check if fallback was used and modify message accordingly
                if (!empty($details['used_fallback']) && $details['used_fallback'] === true) {
                    $fallbackReason = $details['fallback_reason'] ?? 'unknown';
                    
                    if ($fallbackReason === 'no_data_in_table') {
                        $message = Yii::t('app/invoice', 'Tax rate calculated using fallback rates (tax rate database is empty)');
                        $messageType = 'warning';
                    } elseif ($fallbackReason === 'database_error') {
                        $message = Yii::t('app/invoice', 'Tax rate calculated using fallback rates (database connection error)');
                        $messageType = 'warning';
                    } else {
                        $message = Yii::t('app/invoice', 'Tax rate calculated using fallback rates');
                        $messageType = 'warning';
                    }
                }
                
                return [
                    'success' => true,
                    'tax_rate' => $taxRate,
                    'message' => $message,
                    'message_type' => $messageType,
                    'details' => $details
                ];
            } else {
                // Fallback to company default
                return [
                    'success' => true,
                    'tax_rate' => $company->tax_rate ?? 0,
                    'message' => Yii::t('app/invoice', 'Using company default tax rate (address not found or invalid)'),
                    'message_type' => 'info'
                ];
            }
            
        } catch (\Exception $e) {
            Yii::error("Tax calculation controller error: " . $e->getMessage() . 
                      " | Customer ID: " . $customerId . 
                      " | Company ID: " . $companyId);
            return [
                'success' => false,
                'message' => Yii::t('app/invoice', 'Error calculating tax rate'),
                'error' => YII_DEBUG ? $e->getMessage() : null,
                'fallback_rate' => isset($company) ? ($company->tax_rate ?? 0) : 0
            ];
        }
    }
}