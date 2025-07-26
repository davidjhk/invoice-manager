<?php

namespace app\controllers;

use Yii;
use app\models\Invoice;
use app\models\InvoiceItem;
use app\models\Company;
use app\models\Customer;
use app\models\Payment;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\data\ActiveDataProvider;
use yii\web\Response;
use yii\helpers\ArrayHelper;

/**
 * InvoiceController implements the CRUD actions for Invoice model.
 */
class InvoiceController extends Controller
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
                    'mark-as-paid' => ['POST'],
                    'receive-payment' => ['GET', 'POST'],
                    'duplicate' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Invoice models.
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

        $query = Invoice::find()
            ->where(['company_id' => $company->id])
            ->orderBy(['created_at' => SORT_DESC]);

        // Apply search filter
        if (!empty($searchTerm)) {
            $query->andWhere([
                'or',
                ['like', 'invoice_number', $searchTerm],
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
     * Displays a single Invoice model.
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
     * Creates a new Invoice model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $company = Company::getCurrent();
        if (!$company) {
            return $this->redirect(['company/select']);
        }

        // Check if user can create more invoices this month
        $user = Yii::$app->user->identity;
        if (!$user->canCreateInvoice()) {
            $plan = $user->getCurrentPlan();
            $planName = $plan ? $plan->name : 'Free';
            $limit = $plan ? $plan->getMonthlyInvoiceLimit() : 5;
            
            Yii::$app->session->setFlash('error', Yii::t('invoice', 'You have reached your monthly invoice limit of {limit} for the {plan} plan. Please upgrade your plan to create more invoices.', [
                'limit' => $limit,
                'plan' => $planName
            ]));
            
            return $this->redirect(['index']);
        }

        // Check if user's subscription is cancelled or expired
        if ($user->hasCancelledOrExpiredSubscription()) {
            Yii::$app->session->setFlash('error', Yii::t('app', 'Your subscription has been cancelled or expired. Please renew your subscription to create new invoices.'));
            return $this->redirect(['index']);
        }

        $model = new Invoice();
        $model->invoice_number = $company->generateInvoiceNumber();
        $model->company_id = $company->id;
        $model->invoice_date = date('Y-m-d');
        $model->due_date = $company->getDefaultDueDate();
        $model->tax_rate = $company->tax_rate ?? 10.0;
        $model->currency = $company->currency ?? 'USD';
        $model->status = Invoice::STATUS_DRAFT;
        $model->subtotal = 0;
        $model->tax_amount = 0;
        $model->total_amount = 0;
        $model->discount_type = 'percentage';
        $model->discount_value = 0;
        $model->discount_amount = 0;
        $model->deposit_amount = 0;

        $customers = Customer::findActiveByCompany($company->id)->all();

        if ($model->load(Yii::$app->request->post())) {
            $transaction = Yii::$app->db->beginTransaction();
            
            try {
                if ($model->save()) {
                    // Handle invoice items
                    $itemsData = Yii::$app->request->post('InvoiceItem', []);
                    if (!empty($itemsData)) {
                        foreach ($itemsData as $itemData) {
                            if (!empty($itemData['description']) || !empty($itemData['product_id'])) {
                                $item = new InvoiceItem();
                                $item->invoice_id = $model->id;
                                $item->description = $itemData['description'] ?? '';
                                $item->quantity = $itemData['quantity'] ?? 1;
                                $item->unit_price = $itemData['unit_price'] ?? 0;
                                $item->product_id = $itemData['product_id'] ?? null;
                                $item->save();
                            }
                        }
                    }
                    
                    $transaction->commit();
                    Yii::$app->session->setFlash('success', Yii::t('app', 'Invoice created successfully.'));
                    return $this->redirect(['view', 'id' => $model->id]);
                }
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::error('Error creating invoice: ' . $e->getMessage(), 'app');
                Yii::$app->session->setFlash('error', Yii::t('app', 'An error occurred while creating the invoice. Please try again.'));
            }
        }

        return $this->render('create', [
            'model' => $model,
            'customers' => $customers,
        ]);
    }

    /**
     * Updates an existing Invoice model.
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
        
        // Prevent editing if invoice is not in draft status
        if ($model->status !== Invoice::STATUS_DRAFT) {
            Yii::$app->session->setFlash('error', Yii::t('app', 'Only draft invoices can be edited.'));
            return $this->redirect(['view', 'id' => $model->id]);
        }
        
        $customers = Customer::findActiveByCompany($company->id)->all();

        if ($model->load(Yii::$app->request->post())) {
            $transaction = Yii::$app->db->beginTransaction();
            
            try {
                // Delete existing items
                InvoiceItem::deleteAll(['invoice_id' => $model->id]);
                
                // Handle invoice items
                $itemsData = Yii::$app->request->post('InvoiceItem', []);
                if (!empty($itemsData)) {
                    foreach ($itemsData as $itemData) {
                        if (!empty($itemData['description']) || !empty($itemData['product_id'])) {
                            $item = new InvoiceItem();
                            $item->invoice_id = $model->id;
                            $item->description = $itemData['description'] ?? '';
                            $item->quantity = $itemData['quantity'] ?? 1;
                            $item->unit_price = $itemData['unit_price'] ?? 0;
                            $item->product_id = $itemData['product_id'] ?? null;
                            $item->save();
                        }
                    }
                }
                
                if ($model->save()) {
                    $transaction->commit();
                    Yii::$app->session->setFlash('success', Yii::t('app', 'Invoice updated successfully.'));
                    return $this->redirect(['view', 'id' => $model->id]);
                }
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::error('Error updating invoice: ' . $e->getMessage(), 'app');
                Yii::$app->session->setFlash('error', Yii::t('app', 'An error occurred while updating the invoice. Please try again.'));
            }
        }

        return $this->render('update', [
            'model' => $model,
            'customers' => $customers,
        ]);
    }

    /**
     * Deletes an existing Invoice model.
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
        
        // Prevent deletion if invoice is not in draft status
        if ($model->status !== Invoice::STATUS_DRAFT) {
            Yii::$app->session->setFlash('error', Yii::t('app', 'Only draft invoices can be deleted.'));
            return $this->redirect(['index']);
        }
        
        // Delete associated items and payments first
        InvoiceItem::deleteAll(['invoice_id' => $model->id]);
        Payment::deleteAll(['invoice_id' => $model->id]);
        
        if ($model->delete()) {
            Yii::$app->session->setFlash('success', Yii::t('app', 'Invoice deleted successfully.'));
        } else {
            Yii::$app->session->setFlash('error', Yii::t('app', 'An error occurred while deleting the invoice. Please try again.'));
        }

        return $this->redirect(['index']);
    }

    /**
     * Finds the Invoice model based on its primary key value.
     * If the data model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @param integer $companyId
     * @return Invoice the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id, $companyId)
    {
        if (($model = Invoice::findOne(['id' => $id, 'company_id' => $companyId])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }

    /**
     * Preview the Invoice as PDF
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
        
        // Set response format to HTML for PDF generation
        Yii::$app->response->format = Response::FORMAT_HTML;
        
        return $this->renderPartial('/invoice/print', [
            'model' => $model,
            'company' => $company,
            'isPreview' => true
        ]);
    }
    
    /**
     * Send invoice via email
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
        
        // Only send emails for draft/sent invoices
        if (!in_array($model->status, [Invoice::STATUS_DRAFT, Invoice::STATUS_SENT])) {
            Yii::$app->session->setFlash('error', Yii::t('app', 'Only draft or sent invoices can be emailed.'));
            return $this->redirect(['view', 'id' => $model->id]);
        }
        
        // Check if company has email configuration
        if (!$company->hasEmailConfiguration()) {
            Yii::$app->session->setFlash('error', Yii::t('app', 'Email configuration is required. Please configure SMTP2GO in Company Settings.'));
            return $this->redirect(['view', 'id' => $model->id]);
        }
        
        try {
            // Load email template
            $emailTemplate = $this->renderPartial('/invoice/email-template', [
                'model' => $model,
                'company' => $company,
            ]);
            
            // Send email
            $result = Yii::$app->emailSender->send(
                $model->customer->customer_email,
                Yii::t('app', 'Invoice #{invoiceNumber}', ['invoiceNumber' => $model->invoice_number]),
                $emailTemplate,
                $company
            );
            
            if ($result) {
                // Update status to sent if it was draft
                if ($model->status === Invoice::STATUS_DRAFT) {
                    $model->status = Invoice::STATUS_SENT;
                    $model->save();
                }
                
                Yii::$app->session->setFlash('success', Yii::t('app', 'Invoice email sent successfully to {email}.', ['email' => $model->customer->customer_email]));
            } else {
                Yii::$app->session->setFlash('error', Yii::t('app', 'Failed to send invoice email. Please check your email configuration.'));
            }
        } catch (\Exception $e) {
            Yii::error('Error sending invoice email: ' . $e->getMessage(), 'app');
            Yii::$app->session->setFlash('error', Yii::t('app', 'An error occurred while sending the invoice email. Please try again.'));
        }

        return $this->redirect(['view', 'id' => $model->id]);
    }
    
    /**
     * Download invoice as PDF
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
        
        // Generate PDF filename
        $filename = 'Invoice-' . $model->invoice_number . '.pdf';
        
        // Set response headers for PDF download
        Yii::$app->response->format = Response::FORMAT_RAW;
        Yii::$app->response->headers->add('Content-Type', 'application/pdf');
        Yii::$app->response->headers->add('Content-Disposition', 'attachment; filename="' . $filename . '"');
        
        // Render PDF content
        $content = $this->renderPartial('/invoice/print', [
            'model' => $model,
            'company' => $company,
            'isPreview' => false
        ]);
        
        // Generate PDF using TCPDF
        $pdf = Yii::$app->pdfGenerator->generate($content, $company);
        
        return $pdf->Output($filename, 'D'); // D = Download
    }
    
    /**
     * Mark invoice as paid
     * @param integer $id
     * @return mixed
     */
    public function actionMarkAsPaid($id)
    {
        $company = Company::getCurrent();
        if (!$company) {
            return $this->redirect(['company/select']);
        }

        $model = $this->findModel($id, $company->id);
        
        // Only mark as paid if invoice is not already paid
        if ($model->status !== Invoice::STATUS_PAID) {
            $model->status = Invoice::STATUS_PAID;
            if ($model->save()) {
                Yii::$app->session->setFlash('success', Yii::t('app', 'Invoice marked as paid successfully.'));
            } else {
                Yii::$app->session->setFlash('error', Yii::t('app', 'An error occurred while marking the invoice as paid. Please try again.'));
            }
        } else {
            Yii::$app->session->setFlash('info', Yii::t('app', 'Invoice is already marked as paid.'));
        }

        return $this->redirect(['view', 'id' => $model->id]);
    }
    
    /**
     * Export invoices to CSV
     * @return mixed
     */
    public function actionExport()
    {
        $company = Company::getCurrent();
        if (!$company) {
            return $this->redirect(['company/select']);
        }

        // Get all invoices for the company
        $invoices = Invoice::find()
            ->where(['company_id' => $company->id])
            ->with('customer')
            ->orderBy(['created_at' => SORT_DESC])
            ->all();
        
        // Set response headers for CSV download
        $filename = 'Invoices-' . date('Y-m-d') . '.csv';
        Yii::$app->response->format = Response::FORMAT_RAW;
        Yii::$app->response->headers->add('Content-Type', 'text/csv');
        Yii::$app->response->headers->add('Content-Disposition', 'attachment; filename="' . $filename . '"');
        
        // Create CSV content
        $output = fopen('php://output', 'w');
        
        // Add CSV headers
        fputcsv($output, [
            'Invoice Number',
            'Customer Name',
            'Invoice Date',
            'Due Date',
            'Amount',
            'Status',
            'Created At'
        ]);
        
        // Add invoice data
        foreach ($invoices as $invoice) {
            fputcsv($output, [
                $invoice->invoice_number,
                $invoice->customer ? $invoice->customer->customer_name : '',
                Yii::$app->formatter->asDate($invoice->invoice_date),
                Yii::$app->formatter->asDate($invoice->due_date),
                $invoice->formatAmount($invoice->total_amount),
                $invoice->getStatusLabel(),
                Yii::$app->formatter->asDatetime($invoice->created_at)
            ]);
        }
        
        fclose($output);
        exit;
    }
    
    /**
     * Receive payment for invoice
     * @param integer $id
     * @return mixed
     */
    public function actionReceivePayment($id)
    {
        $company = Company::getCurrent();
        if (!$company) {
            return $this->redirect(['company/select']);
        }

        $invoice = $this->findModel($id, $company->id);
        
        // Only receive payments for sent or overdue invoices
        if (!in_array($invoice->status, [Invoice::STATUS_SENT, Invoice::STATUS_OVERDUE])) {
            Yii::$app->session->setFlash('error', Yii::t('app/invoice', 'Payments can only be received for sent or overdue invoices.'));
            return $this->redirect(['view', 'id' => $invoice->id]);
        }
        
        $model = new Payment();
        $model->invoice_id = $invoice->id;
        $model->payment_date = date('Y-m-d');
        $model->amount = $invoice->getAmountDue();
        $model->currency = $invoice->currency;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            // Update invoice status based on payment
            $amountDue = $invoice->getAmountDue();
            if ($amountDue <= 0) {
                $invoice->status = Invoice::STATUS_PAID;
            } elseif ($amountDue < $invoice->total_amount) {
                $invoice->status = Invoice::STATUS_PARTIAL;
            }
            
            if ($invoice->save()) {
                Yii::$app->session->setFlash('success', Yii::t('app/invoice', 'Payment received successfully.'));
            } else {
                Yii::$app->session->setFlash('error', Yii::t('app/invoice', 'Payment received, but there was an error updating the invoice status.'));
            }
            
            return $this->redirect(['view', 'id' => $invoice->id]);
        }

        return $this->render('receive-payment', [
            'invoice' => $invoice,
            'model' => $model,
        ]);
    }
    
    /**
     * Duplicate invoice
     * @param integer $id
     * @return mixed
     */
    public function actionDuplicate($id)
    {
        $company = Company::getCurrent();
        if (!$company) {
            return $this->redirect(['company/select']);
        }

        $originalInvoice = $this->findModel($id, $company->id);
        
        // Check if user can create more invoices this month
        $user = Yii::$app->user->identity;
        if (!$user->canCreateInvoice()) {
            $plan = $user->getCurrentPlan();
            $planName = $plan ? $plan->name : 'Free';
            $limit = $plan ? $plan->getMonthlyInvoiceLimit() : 5;
            
            Yii::$app->session->setFlash('error', Yii::t('invoice', 'You have reached your monthly invoice limit of {limit} for the {plan} plan. Please upgrade your plan to create more invoices.', [
                'limit' => $limit,
                'plan' => $planName
            ]));
            
            return $this->redirect(['view', 'id' => $originalInvoice->id]);
        }
        
        // Check if user's subscription is cancelled or expired
        if ($user->hasCancelledOrExpiredSubscription()) {
            Yii::$app->session->setFlash('error', Yii::t('app', 'Your subscription has been cancelled or expired. Please renew your subscription to create new invoices.'));
            return $this->redirect(['view', 'id' => $originalInvoice->id]);
        }
        
        $transaction = Yii::$app->db->beginTransaction();
        
        try {
            // Create new invoice
            $newInvoice = new Invoice();
            $newInvoice->company_id = $originalInvoice->company_id;
            $newInvoice->customer_id = $originalInvoice->customer_id;
            $newInvoice->invoice_date = date('Y-m-d');
            $newInvoice->due_date = date('Y-m-d', strtotime('+30 days'));
            $newInvoice->invoice_number = $company->generateInvoiceNumber();
            $newInvoice->reference = $originalInvoice->reference;
            $newInvoice->currency = $originalInvoice->currency;
            $newInvoice->tax_rate = $originalInvoice->tax_rate;
            $newInvoice->status = Invoice::STATUS_DRAFT;
            $newInvoice->subtotal = $originalInvoice->subtotal;
            $newInvoice->tax_amount = $originalInvoice->tax_amount;
            $newInvoice->total_amount = $originalInvoice->total_amount;
            $newInvoice->discount_type = $originalInvoice->discount_type;
            $newInvoice->discount_value = $originalInvoice->discount_value;
            $newInvoice->discount_amount = $originalInvoice->discount_amount;
            $newInvoice->deposit_amount = $originalInvoice->deposit_amount;
            
            if ($newInvoice->save()) {
                // Copy invoice items
                foreach ($originalInvoice->invoiceItems as $originalItem) {
                    $newItem = new InvoiceItem();
                    $newItem->invoice_id = $newInvoice->id;
                    $newItem->description = $originalItem->description;
                    $newItem->quantity = $originalItem->quantity;
                    $newItem->unit_price = $originalItem->unit_price;
                    $newItem->product_id = $originalItem->product_id;
                    $newItem->save();
                }
                
                $transaction->commit();
                
                Yii::$app->session->setFlash('success', Yii::t('app', 'Invoice duplicated successfully.'));
                return $this->redirect(['update', 'id' => $newInvoice->id]);
            } else {
                throw new \Exception('Failed to duplicate invoice');
            }
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error('Error duplicating invoice: ' . $e->getMessage(), 'app');
            Yii::$app->session->setFlash('error', Yii::t('app', 'An error occurred while duplicating the invoice. Please try again.'));
        }
        
        return $this->redirect(['view', 'id' => $id]);
    }
}
