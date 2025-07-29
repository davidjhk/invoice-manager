<?php

namespace app\controllers;

use Yii;
use app\models\Estimate;
use app\models\EstimateItem;
use app\models\Company;
use app\models\Customer;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\data\ActiveDataProvider;
use yii\web\Response;
use yii\web\UploadedFile;
use yii\helpers\Url;
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
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                    'send-email' => ['POST'],
                    'download-pdf' => ['GET'],
                    'duplicate' => ['POST'],
                    'mark-as-printed' => ['POST'],
                    'mark-as-accepted' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Estimate models.
     * @return mixed
     */
    public function actionIndex()
    {
        $company = Company::getCurrent();
        if (!$company) {
            return $this->redirect(['company/select']);
        }

        $searchTerm = Yii::$app->request->get('search');
        $statusFilter = Yii::$app->request->get('status');

        $query = Estimate::find()
            ->where(['company_id' => $company->id])
            ->orderBy(['created_at' => SORT_DESC]);

        // Apply search filter
        if (!empty($searchTerm)) {
            $query->andWhere([
                'or',
                ['like', 'estimate_number', $searchTerm],
                ['like', 'LOWER(reference)', strtolower($searchTerm)],
            ]);
        }

        // Apply status filter
        if (!empty($statusFilter)) {
            $query->andWhere(['status' => $statusFilter]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchTerm' => $searchTerm,
            'statusFilter' => $statusFilter,
            'company' => $company,
        ]);
    }

    /**
     * Displays a single Estimate model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $company = Company::getCurrent();
        if (!$company) {
            return $this->redirect(['company/select']);
        }

        return $this->render('view', [
            'model' => $this->findModel($id, $company->id),
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

        // Check if user's subscription is cancelled or expired
        $user = Yii::$app->user->identity;
        if ($user->hasCancelledOrExpiredSubscription()) {
            Yii::$app->session->setFlash('error', Yii::t('app', 'Your subscription has been cancelled or expired. Please renew your subscription to create new estimates.'));
            return $this->redirect(['index']);
        }
        
        // Check if user can create more estimates this month
        if (!$user->canCreateEstimate()) {
            $plan = $user->getCurrentPlan();
            $planName = $plan ? $plan->name : 'Free';
            $limit = $plan ? $plan->getMonthlyInvoiceLimit() : (Yii::$app->params['freeUserMonthlyLimit'] ?? 5);
            
            Yii::$app->session->setFlash('error', Yii::t('estimate', 'You have reached your monthly estimate limit of {limit} for the {plan} plan. Please upgrade your plan to create more estimates.', [
                'limit' => $limit,
                'plan' => $planName
            ]));
            
            return $this->redirect(['index']);
        }

        $model = new Estimate();
        $model->company_id = $company->id;
        $model->user_id = Yii::$app->user->id;
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
                        foreach ($itemsData as $itemData) {
                            if (!empty($itemData['description']) || !empty($itemData['product_id'])) {
                                $item = new EstimateItem();
                                $item->estimate_id = $model->id;
                                $item->description = $itemData['description'] ?? '';
                                $item->quantity = $itemData['quantity'] ?? 1;
                                $item->rate = $itemData['rate'] ?? ($itemData['unit_price'] ?? 0);
                                $item->product_id = $itemData['product_id'] ?? null;
                                $item->save();
                            }
                        }
                    }
                    
                    $transaction->commit();
                    Yii::$app->session->setFlash('success', Yii::t('app', 'Estimate created successfully.'));
                    return $this->redirect(['preview', 'id' => $model->id]);
                }
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::error('Error creating estimate: ' . $e->getMessage(), 'app');
                Yii::$app->session->setFlash('error', Yii::t('app', 'An error occurred while creating the estimate. Please try again.'));
            }
        }

        return $this->render('create', [
            'model' => $model,
            'customers' => $customers,
            'company' => $company,
        ]);
    }

    /**
     * Updates an existing Estimate model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $company = Company::getCurrent();
        if (!$company) {
            return $this->redirect(['company/select']);
        }

        $model = $this->findModel($id, $company->id);
        
        // Prevent editing if estimate is not in draft status
        if ($model->status !== Estimate::STATUS_DRAFT) {
            Yii::$app->session->setFlash('error', Yii::t('app', 'Only draft estimates can be edited.'));
            return $this->redirect(['view', 'id' => $model->id]);
        }
        
        $customers = Customer::findActiveByCompany($company->id)->all();

        if ($model->load(Yii::$app->request->post())) {
            $transaction = Yii::$app->db->beginTransaction();
            
            try {
                // Delete existing items
                EstimateItem::deleteAll(['estimate_id' => $model->id]);
                
                // Handle estimate items
                $itemsData = Yii::$app->request->post('EstimateItem', []);
                if (!empty($itemsData)) {
                    foreach ($itemsData as $itemData) {
                        if (!empty($itemData['description']) || !empty($itemData['product_id'])) {
                            $item = new EstimateItem();
                            $item->estimate_id = $model->id;
                            $item->description = $itemData['description'] ?? '';
                            $item->quantity = $itemData['quantity'] ?? 1;
                            $item->rate = $itemData['rate'] ?? ($itemData['unit_price'] ?? 0);
                            $item->product_id = $itemData['product_id'] ?? null;
                            $item->save();
                        }
                    }
                }
                
                if ($model->save()) {
                    $transaction->commit();
                    Yii::$app->session->setFlash('success', Yii::t('app', 'Estimate updated successfully.'));
                    return $this->redirect(['preview', 'id' => $model->id]);
                }
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::error('Error updating estimate: ' . $e->getMessage(), 'app');
                Yii::$app->session->setFlash('error', Yii::t('app', 'An error occurred while updating the estimate. Please try again.'));
            }
        }

        return $this->render('update', [
            'model' => $model,
            'customers' => $customers,
            'company' => $company,
        ]);
    }

    /**
     * Deletes an existing Estimate model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $company = Company::getCurrent();
        if (!$company) {
            return $this->redirect(['company/select']);
        }

        $model = $this->findModel($id, $company->id);
        
        // Prevent deletion if estimate is not in draft status
        if ($model->status !== Estimate::STATUS_DRAFT) {
            Yii::$app->session->setFlash('error', Yii::t('app', 'Only draft estimates can be deleted.'));
            return $this->redirect(['index']);
        }
        
        // Delete associated items first
        EstimateItem::deleteAll(['estimate_id' => $model->id]);
        
        if ($model->delete()) {
            Yii::$app->session->setFlash('success', Yii::t('app', 'Estimate deleted successfully.'));
        } else {
            Yii::$app->session->setFlash('error', Yii::t('app', 'An error occurred while deleting the estimate. Please try again.'));
        }

        return $this->redirect(['index']);
    }

    /**
     * Finds the Estimate model based on its primary key value.
     * If the data model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @param integer $companyId
     * @return Estimate the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id, $companyId)
    {
        if (($model = Estimate::findOne(['id' => $id, 'company_id' => $companyId])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }

    /**
     * Preview the Estimate as PDF
     * @param integer $id
     * @return mixed
     */
    public function actionPreview($id)
    {
        $company = Company::getCurrent();
        if (!$company) {
            return $this->redirect(['company/select']);
        }

        $model = $this->findModel($id, $company->id);
        
        return $this->render('preview', [
            'model' => $model,
        ]);
    }
    
    /**
     * Send estimate via email
     * @param integer $id
     * @return mixed
     */
    public function actionSendEmail($id)
    {
        $company = Company::getCurrent();
        if (!$company) {
            return $this->redirect(['company/select']);
        }

        $model = $this->findModel($id, $company->id);
        
        // Only send emails for sent estimates
        if ($model->status !== Estimate::STATUS_SENT) {
            Yii::$app->session->setFlash('error', Yii::t('app', 'Only sent estimates can be emailed.'));
            return $this->redirect(['view', 'id' => $model->id]);
        }
        
        // Check if company has email configuration
        if (!$company->hasEmailConfiguration()) {
            Yii::$app->session->setFlash('error', Yii::t('app', 'Email configuration is required. Please configure SMTP2GO in Company Settings.'));
            return $this->redirect(['view', 'id' => $model->id]);
        }
        
        try {
            // Load email template
            $emailTemplate = $this->renderPartial('/estimate/email-template', [
                'model' => $model,
                'company' => $company,
            ]);
            
            // Send email
            $result = Yii::$app->emailSender->send(
                $model->customer->customer_email,
                Yii::t('app', 'Estimate #{estimateNumber}', ['estimateNumber' => $model->estimate_number]),
                $emailTemplate,
                $company
            );
            
            if ($result) {
                Yii::$app->session->setFlash('success', Yii::t('app', 'Estimate email sent successfully to {email}.', ['email' => $model->customer->customer_email]));
            } else {
                Yii::$app->session->setFlash('error', Yii::t('app', 'Failed to send estimate email. Please check your email configuration.'));
            }
        } catch (\Exception $e) {
            Yii::error('Error sending estimate email: ' . $e->getMessage(), 'app');
            Yii::$app->session->setFlash('error', Yii::t('app', 'An error occurred while sending the estimate email. Please try again.'));
        }

        return $this->redirect(['view', 'id' => $model->id]);
    }
    
    /**
     * Download estimate as PDF
     * @param integer $id
     * @return mixed
     */
    public function actionDownloadPdf($id)
    {
        $company = Company::getCurrent();
        if (!$company) {
            return $this->redirect(['company/select']);
        }

        $model = $this->findModel($id, $company->id);
        
        // Mark as printed if in draft status
        $model->markAsPrinted();
        
        // Generate and output PDF
        PdfGenerator::generateEstimatePdf($model, 'D');
    }
    
    /**
     * Mark estimate as printed
     * @param integer $id
     * @return mixed
     */
    public function actionMarkAsPrinted($id)
    {
        $company = Company::getCurrent();
        if (!$company) {
            return $this->redirect(['company/select']);
        }

        $model = $this->findModel($id, $company->id);
        
        // Mark as printed if in draft status
        $model->markAsPrinted();
        
        // Return JSON response for AJAX calls
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['success' => true, 'status' => $model->status];
        }
        
        return $this->redirect(['preview', 'id' => $model->id]);
    }

    /**
     * Mark estimate as accepted
     * @param integer $id
     * @return mixed
     */
    public function actionMarkAsAccepted($id)
    {
        $company = Company::getCurrent();
        if (!$company) {
            return $this->redirect(['company/select']);
        }

        $model = $this->findModel($id, $company->id);
        
        // Mark as accepted
        if ($model->markAsAccepted()) {
            Yii::$app->session->setFlash('success', Yii::t('app', 'Estimate marked as accepted successfully.'));
        } else {
            Yii::$app->session->setFlash('error', Yii::t('app', 'Failed to mark estimate as accepted.'));
        }
        
        // Return JSON response for AJAX calls
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['success' => true, 'status' => $model->status];
        }
        
        return $this->redirect(['view', 'id' => $model->id]);
    }

    /**
     * Convert estimate to invoice
     * @param integer $id
     * @return mixed
     */
    public function actionConvertToInvoice($id)
    {
        $company = Company::getCurrent();
        if (!$company) {
            return $this->redirect(['company/select']);
        }

        $estimate = $this->findModel($id, $company->id);
        
        // Check user's invoice creation permissions
        $user = Yii::$app->user->identity;
        if (!$user->canCreateInvoice()) {
            Yii::$app->session->setFlash('error', Yii::t('app', 'You have reached your monthly invoice limit. Please upgrade your plan to create more invoices.'));
            return $this->redirect(['preview', 'id' => $estimate->id]);
        }
        
        $transaction = Yii::$app->db->beginTransaction();
        
        try {
            // Create new invoice
            $invoice = new \app\models\Invoice();
            $invoice->company_id = $estimate->company_id;
            $invoice->customer_id = $estimate->customer_id;
            $invoice->user_id = Yii::$app->user->id;
            $invoice->invoice_date = date('Y-m-d');
            $invoice->due_date = date('Y-m-d', strtotime('+30 days'));
            $invoice->invoice_number = $company->generateInvoiceNumber();
            $invoice->memo = 'Converted from estimate: ' . $estimate->estimate_number;
            $invoice->currency = $estimate->currency;
            $invoice->tax_rate = $estimate->tax_rate;
            $invoice->status = \app\models\Invoice::STATUS_DRAFT;
            $invoice->subtotal = $estimate->subtotal;
            $invoice->tax_amount = $estimate->tax_amount;
            $invoice->total_amount = $estimate->total_amount;
            $invoice->discount_type = $estimate->discount_type;
            $invoice->discount_value = $estimate->discount_value;
            $invoice->discount_amount = $estimate->discount_amount;
            $invoice->shipping_fee = $estimate->shipping_fee ?? 0;
            $invoice->notes = $estimate->notes;
            $invoice->terms = $estimate->terms;
            $invoice->payment_instructions = $estimate->payment_instructions;
            $invoice->customer_notes = $estimate->customer_notes;
            
            // Copy billing address
            $invoice->bill_to_address = $estimate->bill_to_address;
            $invoice->bill_to_city = $estimate->bill_to_city;
            $invoice->bill_to_state = $estimate->bill_to_state;
            $invoice->bill_to_zip_code = $estimate->bill_to_zip_code;
            $invoice->bill_to_country = $estimate->bill_to_country;
            
            // Copy shipping address
            $invoice->ship_to_address = $estimate->ship_to_address;
            $invoice->ship_to_city = $estimate->ship_to_city;
            $invoice->ship_to_state = $estimate->ship_to_state;
            $invoice->ship_to_zip_code = $estimate->ship_to_zip_code;
            $invoice->ship_to_country = $estimate->ship_to_country;
            $invoice->ship_from_address = $estimate->ship_from_address;
            
            // Copy other fields
            $invoice->cc_email = $estimate->cc_email;
            $invoice->shipping_date = $estimate->shipping_date;
            $invoice->tracking_number = $estimate->tracking_number;
            $invoice->shipping_method = $estimate->shipping_method;
            
            if ($invoice->save()) {
                // Copy estimate items to invoice items
                foreach ($estimate->estimateItems as $estimateItem) {
                    $invoiceItem = new \app\models\InvoiceItem();
                    $invoiceItem->invoice_id = $invoice->id;
                    $invoiceItem->product_service_name = $estimateItem->product_service_name;
                    $invoiceItem->description = $estimateItem->description;
                    $invoiceItem->quantity = $estimateItem->quantity;
                    $invoiceItem->rate = $estimateItem->rate;
                    $invoiceItem->amount = $estimateItem->amount;
                    $invoiceItem->tax_rate = $estimateItem->tax_rate;
                    $invoiceItem->tax_amount = $estimateItem->tax_amount;
                    $invoiceItem->is_taxable = $estimateItem->is_taxable;
                    $invoiceItem->sort_order = $estimateItem->sort_order;
                    $invoiceItem->product_id = $estimateItem->product_id;
                    $invoiceItem->save();
                }
                
                // Update estimate to mark as converted
                $estimate->converted_to_invoice = true;
                $estimate->invoice_id = $invoice->id;
                $estimate->save(false);
                
                $transaction->commit();
                
                Yii::$app->session->setFlash('success', Yii::t('app', 'Estimate successfully converted to invoice #{invoiceNumber}.', ['invoiceNumber' => $invoice->invoice_number]));
                return $this->redirect(['/invoice/preview', 'id' => $invoice->id]);
            } else {
                throw new \Exception('Failed to create invoice');
            }
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error('Error converting estimate to invoice: ' . $e->getMessage(), 'app');
            Yii::$app->session->setFlash('error', Yii::t('app', 'An error occurred while converting the estimate to an invoice. Please try again.'));
        }
        
        return $this->redirect(['view', 'id' => $estimate->id]);
    }
    
    /**
     * Duplicate estimate
     * @param integer $id
     * @return mixed
     */
    public function actionDuplicate($id)
    {
        $company = Company::getCurrent();
        if (!$company) {
            return $this->redirect(['company/select']);
        }

        $originalEstimate = $this->findModel($id, $company->id);
        
        // Check if user's subscription is cancelled or expired
        $user = Yii::$app->user->identity;
        if ($user->hasCancelledOrExpiredSubscription()) {
            Yii::$app->session->setFlash('error', Yii::t('app', 'Your subscription has been cancelled or expired. Please renew your subscription to create new estimates.'));
            return $this->redirect(['index']);
        }
        
        // Check if user can create more estimates this month
        if (!$user->canCreateEstimate()) {
            $plan = $user->getCurrentPlan();
            $planName = $plan ? $plan->name : 'Free';
            $limit = $plan ? $plan->getMonthlyInvoiceLimit() : (Yii::$app->params['freeUserMonthlyLimit'] ?? 5);
            
            Yii::$app->session->setFlash('error', Yii::t('estimate', 'You have reached your monthly estimate limit of {limit} for the {plan} plan. Please upgrade your plan to create more estimates.', [
                'limit' => $limit,
                'plan' => $planName
            ]));
            
            return $this->redirect(['view', 'id' => $originalEstimate->id]);
        }
        
        $transaction = Yii::$app->db->beginTransaction();
        
        try {
            // Create new estimate
            $newEstimate = new Estimate();
            $newEstimate->company_id = $originalEstimate->company_id;
            $newEstimate->customer_id = $originalEstimate->customer_id;
            $newEstimate->user_id = Yii::$app->user->id;
            $newEstimate->estimate_date = date('Y-m-d');
            $newEstimate->estimate_number = $company->generateEstimateNumber();
            $newEstimate->reference = $originalEstimate->reference;
            $newEstimate->currency = $originalEstimate->currency;
            $newEstimate->tax_rate = $originalEstimate->tax_rate;
            $newEstimate->status = Estimate::STATUS_DRAFT;
            $newEstimate->subtotal = $originalEstimate->subtotal;
            $newEstimate->tax_amount = $originalEstimate->tax_amount;
            $newEstimate->total_amount = $originalEstimate->total_amount;
            $newEstimate->discount_type = $originalEstimate->discount_type;
            $newEstimate->discount_value = $originalEstimate->discount_value;
            $newEstimate->discount_amount = $originalEstimate->discount_amount;
            
            if ($newEstimate->save()) {
                // Copy estimate items
                foreach ($originalEstimate->estimateItems as $originalItem) {
                    $newItem = new EstimateItem();
                    $newItem->estimate_id = $newEstimate->id;
                    $newItem->description = $originalItem->description;
                    $newItem->quantity = $originalItem->quantity;
                    $newItem->rate = $originalItem->rate;
                    $newItem->product_id = $originalItem->product_id;
                    $newItem->save();
                }
                
                $transaction->commit();
                
                Yii::$app->session->setFlash('success', Yii::t('app', 'Estimate duplicated successfully.'));
                return $this->redirect(['update', 'id' => $newEstimate->id]);
            } else {
                throw new \Exception('Failed to duplicate estimate');
            }
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error('Error duplicating estimate: ' . $e->getMessage(), 'app');
            Yii::$app->session->setFlash('error', Yii::t('app', 'An error occurred while duplicating the estimate. Please try again.'));
        }
        
        return $this->redirect(['view', 'id' => $id]);
    }
}
