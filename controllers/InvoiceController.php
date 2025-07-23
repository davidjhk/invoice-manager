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
use yii\filters\AccessControl;
use yii\web\Response;
use yii\helpers\Json;
use yii\data\ActiveDataProvider;
use app\components\PdfGenerator;
use app\components\EmailSender;

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
                ],
            ],
        ];
    }

    /**
     * Lists all Invoice models.
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
        
        $query = Invoice::find()
            ->joinWith(['customer'])
            ->where(['jdosa_invoices.company_id' => $company->id]);

        if (!empty($searchTerm)) {
            $query->andWhere(['or',
                ['like', 'invoice_number', $searchTerm],
                ['like', 'jdosa_customers.customer_name', $searchTerm],
                ['like', 'notes', $searchTerm],
            ]);
        }

        // Apply status filter
        if (!empty($statusFilter)) {
            switch ($statusFilter) {
                case 'draft':
                    $query->andWhere(['jdosa_invoices.status' => Invoice::STATUS_DRAFT]);
                    break;
                case 'sent':
                    $query->andWhere(['jdosa_invoices.status' => Invoice::STATUS_SENT]);
                    break;
                case 'paid':
                    $query->andWhere(['jdosa_invoices.status' => Invoice::STATUS_PAID]);
                    break;
                case 'overdue':
                    $query->andWhere(['and',
                        ['<', 'jdosa_invoices.due_date', date('Y-m-d')],
                        ['!=', 'jdosa_invoices.status', Invoice::STATUS_PAID]
                    ]);
                    break;
            }
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 50,
                'params' => array_merge(
                    Yii::$app->request->queryParams,
                    ['search' => $searchTerm, 'status' => $statusFilter]
                ),
            ],
            'sort' => [
                'defaultOrder' => ['invoice_number' => SORT_DESC],
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
     * Creates a new Invoice model.
     *
     * @return string|Response
     */
    public function actionCreate()
    {
        $company = Company::getCurrent();
        if (!$company) {
            return $this->redirect(['company/select']);
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

        if ($model->load(Yii::$app->request->post())) {
            $transaction = Yii::$app->db->beginTransaction();
            
            try {
                if ($model->save()) {
                    // Handle invoice items - check both possible data structures
                    $itemsData = Yii::$app->request->post('items', []);
                    if (empty($itemsData)) {
                        $itemsData = Yii::$app->request->post('InvoiceItem', []);
                    }
                    
                    if (!empty($itemsData)) {
                        $this->saveInvoiceItems($model->id, $itemsData);
                    }
                    
                    // Ensure required fields have default values before calculating totals
                    if ($model->discount_value === null) $model->discount_value = 0;
                    if ($model->discount_type === null) $model->discount_type = 'percentage';
                    if ($model->tax_rate === null) $model->tax_rate = 0;
                    
                    // Recalculate totals
                    $model->calculateTotals();
                    $model->save();
                    
                    $transaction->commit();
                    
                    Yii::$app->session->setFlash('success', 'Invoice created successfully.');
                    return $this->redirect(['preview', 'id' => $model->id]);
                } else {
                    $transaction->rollBack();
                    $errors = [];
                    foreach ($model->errors as $field => $fieldErrors) {
                        $errors[] = $field . ': ' . implode(', ', $fieldErrors);
                    }
                    Yii::$app->session->setFlash('error', 'Validation failed: ' . implode('; ', $errors));
                }
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::error('Failed to create invoice: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
                Yii::$app->session->setFlash('error', 'Failed to create invoice: ' . $e->getMessage());
            }
        }

        $customers = Customer::findActiveByCompany($company->id)->all();

        return $this->render('create', [
            'model' => $model,
            'company' => $company,
            'customers' => $customers,
        ]);
    }

    /**
     * Updates an existing Invoice model.
     *
     * @param int $id ID
     * @return string|Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        
        if (!$model->isEditable()) {
            Yii::$app->session->setFlash('error', 'This invoice cannot be edited.');
            return $this->redirect(['preview', 'id' => $model->id]);
        }

        if ($model->load(Yii::$app->request->post())) {
            $transaction = Yii::$app->db->beginTransaction();
            
            try {
                if ($model->save()) {
                    // Handle invoice items - check both possible data structures
                    $itemsData = Yii::$app->request->post('items', []);
                    if (empty($itemsData)) {
                        $itemsData = Yii::$app->request->post('InvoiceItem', []);
                    }
                    
                    if (!empty($itemsData)) {
                        $this->saveInvoiceItems($model->id, $itemsData);
                    }
                    
                    // Ensure required fields have default values before calculating totals
                    if ($model->discount_value === null) $model->discount_value = 0;
                    if ($model->discount_type === null) $model->discount_type = 'percentage';
                    if ($model->tax_rate === null) $model->tax_rate = 0;
                    
                    // Recalculate totals
                    $model->calculateTotals();
                    $model->save();
                    
                    $transaction->commit();
                    
                    Yii::$app->session->setFlash('success', 'Invoice updated successfully.');
                    return $this->redirect(['view', 'id' => $model->id]);
                }
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', 'Failed to update invoice: ' . $e->getMessage());
            }
        }

        $customers = Customer::findActiveByCompany($model->company_id)->all();

        return $this->render('update', [
            'model' => $model,
            'company' => $model->company,
            'customers' => $customers,
        ]);
    }

    /**
     * Deletes an existing Invoice model.
     *
     * @param int $id ID
     * @return Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        
        if (!$model->isEditable()) {
            Yii::$app->session->setFlash('error', 'This invoice cannot be deleted.');
            return $this->redirect(['index']);
        }

        $model->delete();
        Yii::$app->session->setFlash('success', 'Invoice deleted successfully.');

        return $this->redirect(['index']);
    }

    /**
     * Preview invoice
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
     * Download invoice as PDF
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
        
        // Generate and output PDF
        return PdfGenerator::generateInvoicePdf($model, 'D');
    }

    /**
     * Print invoice
     *
     * @param int $id
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionPrint($id)
    {
        $model = $this->findModel($id);
        
        // Mark as printed if in draft status
        $model->markAsPrinted();
        
        // Generate PDF using PdfGenerator
        return PdfGenerator::generateInvoicePdf($model, 'D');
    }

    /**
     * Send invoice email
     *
     * @param int $id
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionSendEmail($id)
    {
        $model = $this->findModel($id);
        
        if (!$model->canBeSent()) {
            Yii::$app->session->setFlash('error', 'Invoice cannot be sent.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        if (Yii::$app->request->isPost) {
            $emailData = Yii::$app->request->post();
            
            // TODO: Implement email sending logic
            // This would integrate with SMTP2GO API
            
            // Only update status to SENT if it's currently DRAFT
            if ($model->status === Invoice::STATUS_DRAFT) {
                $model->status = Invoice::STATUS_SENT;
                $model->save();
            }
            
            Yii::$app->session->setFlash('success', 'Invoice sent successfully.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('send-email', [
            'model' => $model,
        ]);
    }

    /**
     * Send invoice email via AJAX
     *
     * @param int $id
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionSendEmailAjax($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        $model = $this->findModel($id);
        
        if (!$model->canBeSent()) {
            return [
                'success' => false,
                'message' => 'Invoice cannot be sent.',
            ];
        }

        $recipientEmail = Yii::$app->request->post('recipient_email');
        $subject = Yii::$app->request->post('subject');
        $message = Yii::$app->request->post('message');
        $attachPdf = (bool) Yii::$app->request->post('attach_pdf', true);

        if (empty($recipientEmail) || empty($subject) || empty($message)) {
            return [
                'success' => false,
                'message' => 'All fields are required.',
            ];
        }

        // Send email
        $result = EmailSender::sendInvoiceEmail($model, $recipientEmail, $subject, $message, $attachPdf);
        
        if ($result['success']) {
            // Update invoice status only if it's currently DRAFT
            if ($model->status === Invoice::STATUS_DRAFT) {
                $model->status = Invoice::STATUS_SENT;
                $model->save();
            }
        }
        
        return $result;
    }

    /**
     * Get invoice data as JSON
     *
     * @param int $id
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionGetData($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        $model = $this->findModel($id);
        
        return [
            'invoice' => $model->toArray(),
            'customer' => $model->customer->toArray(),
            'company' => $model->company->toArray(),
            'items' => InvoiceItem::getItemsArray($model->id),
        ];
    }

    /**
     * Calculate invoice totals via AJAX
     *
     * @return Response
     */
    public function actionCalculateTotals()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        $itemsData = Json::decode(Yii::$app->request->post('items', '[]'));
        $taxRate = (float) Yii::$app->request->post('taxRate', 10);
        
        $subtotal = 0;
        foreach ($itemsData as $item) {
            if (!empty($item['quantity']) && !empty($item['rate'])) {
                $subtotal += (float) $item['quantity'] * (float) $item['rate'];
            }
        }
        
        $taxAmount = $subtotal * ($taxRate / 100);
        $total = $subtotal + $taxAmount;
        
        return [
            'subtotal' => number_format($subtotal, 2),
            'taxAmount' => number_format($taxAmount, 2),
            'total' => number_format($total, 2),
        ];
    }

    /**
     * Search customers via AJAX
     *
     * @return Response
     */
    public function actionSearchCustomers()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        $term = Yii::$app->request->get('term', '');
        $company = Company::getCurrent();
        
        if (!$company || empty($term)) {
            return [];
        }
        
        $customers = Customer::search($term, $company->id)->limit(10)->all();
        
        $result = [];
        foreach ($customers as $customer) {
            $result[] = [
                'id' => $customer->id,
                'text' => $customer->getDisplayName(),
                'name' => $customer->customer_name,
                'email' => $customer->customer_email,
                'phone' => $customer->customer_phone,
                'address' => $customer->customer_address,
            ];
        }
        
        return $result;
    }

    /**
     * Save invoice items
     *
     * @param int $invoiceId
     * @param array $itemsData
     */
    protected function saveInvoiceItems($invoiceId, $itemsData)
    {
        // Delete existing items
        InvoiceItem::deleteAll(['invoice_id' => $invoiceId]);
        
        // Log items data for debugging
        Yii::info('Saving invoice items: ' . print_r($itemsData, true));
        
        // Save new items
        foreach ($itemsData as $index => $itemData) {
            if (empty($itemData['product_service_name']) && empty($itemData['description'])) {
                continue; // Skip empty rows
            }
            
            $item = new InvoiceItem();
            $item->invoice_id = $invoiceId;
            $item->product_id = !empty($itemData['product_id']) ? $itemData['product_id'] : null;
            $item->product_service_name = $itemData['product_service_name'] ?? '';
            $item->description = $itemData['description'] ?? '';
            $item->quantity = !empty($itemData['quantity']) ? (float)$itemData['quantity'] : 1;
            $item->rate = !empty($itemData['rate']) ? (float)$itemData['rate'] : 0;
            $item->amount = $item->quantity * $item->rate;
            $item->is_taxable = isset($itemData['is_taxable']) ? (bool)$itemData['is_taxable'] : false;
            $item->sort_order = $index + 1;
            $item->tax_rate = 0; // Set default tax rate
            $item->tax_amount = 0; // Set default tax amount
            
            if (!$item->save()) {
                Yii::error('Failed to save invoice item: ' . print_r($item->errors, true));
                throw new \Exception('Failed to save invoice item: ' . implode(', ', array_map(function($errors) {
                    return implode(', ', $errors);
                }, $item->errors)));
            }
        }
    }

    /**
     * Finds the Invoice model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * Also ensures the invoice belongs to the current company.
     *
     * @param int $id ID
     * @return Invoice the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        $company = Company::getCurrent();
        if (!$company) {
            throw new NotFoundHttpException('No company selected.');
        }
        
        $model = Invoice::find()
            ->where(['id' => $id, 'company_id' => $company->id])
            ->with(['invoiceItems'])
            ->one();
            
        if ($model !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    /**
     * Duplicate an existing Invoice.
     *
     * @param int $id
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionDuplicate($id)
    {
        $model = $this->findModel($id);
        
        $newInvoice = $model->duplicate();
        
        if ($newInvoice) {
            Yii::$app->session->setFlash('success', 'Invoice duplicated successfully.');
            return $this->redirect(['update', 'id' => $newInvoice->id]);
        } else {
            Yii::$app->session->setFlash('error', 'Failed to duplicate invoice.');
            return $this->redirect(['view', 'id' => $model->id]);
        }
    }


    /**
     * View payments for an invoice
     *
     * @param int $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionPayments($id)
    {
        $invoice = $this->findModel($id);
        
        return $this->render('payments', [
            'invoice' => $invoice,
        ]);
    }

    /**
     * Receive payment for one or more invoices for a customer.
     *
     * @param int $id The ID of the initial invoice to start the payment from.
     * @return string|Response
     * @throws NotFoundHttpException
     */
    public function actionReceivePayment($id)
    {
        $startInvoice = $this->findModel($id);
        $customer = $startInvoice->customer;

        if (Yii::$app->request->isPost) {
            $postData = Yii::$app->request->post();
            
            // Debug logging
            Yii::info('Receive payment POST data: ' . print_r($postData, true), __METHOD__);
            
            $totalReceived = (float)($postData['total_amount_received'] ?? 0);
            $paymentDate = $postData['payment_date'] ?? date('Y-m-d');
            $paymentMethod = $postData['payment_method'] ?? 'Cash';
            $notes = $postData['notes'] ?? '';
            $payments = $postData['payments'] ?? [];
            
            Yii::info("Total received: $totalReceived, Payments: " . print_r($payments, true), __METHOD__);

            if ($totalReceived > 0 && !empty($payments)) {
                Yii::info("Starting payment processing...", __METHOD__);
                $transaction = Yii::$app->db->beginTransaction();
                try {
                    foreach ($payments as $invoiceId => $amount) {
                        $paymentAmount = (float)$amount;
                        Yii::info("Processing payment for invoice $invoiceId: $paymentAmount", __METHOD__);
                        
                        if ($paymentAmount > 0) {
                            $invoice = $this->findModel($invoiceId);
                            if ($invoice->customer_id !== $customer->id) {
                                throw new \Exception("Invoice {$invoice->invoice_number} does not belong to this customer.");
                            }

                            $payment = new Payment();
                            $payment->invoice_id = $invoiceId;
                            $payment->customer_id = $customer->id;
                            $payment->company_id = $customer->company_id;
                            $payment->amount = $paymentAmount;
                            $payment->payment_date = $paymentDate;
                            $payment->payment_method = $paymentMethod;
                            $payment->notes = $notes;
                            
                            Yii::info("Payment object created: " . print_r($payment->attributes, true), __METHOD__);
                            
                            if (!$payment->save()) {
                                Yii::error("Payment validation errors: " . print_r($payment->errors, true), __METHOD__);
                                throw new \Exception("Failed to save payment for invoice {$invoice->invoice_number}: " . implode(', ', array_map(function($errors) {
                                    return implode(', ', $errors);
                                }, $payment->errors)));
                            }
                            
                            Yii::info("Payment saved successfully for invoice $invoiceId", __METHOD__);
                        }
                    }
                    $transaction->commit();
                    Yii::info("All payments committed successfully", __METHOD__);
                    Yii::$app->session->setFlash('success', 'Payment(s) recorded successfully.');
                    return $this->redirect(['view', 'id' => $startInvoice->id]);
                } catch (\Exception $e) {
                    $transaction->rollBack();
                    Yii::error("Payment processing failed: " . $e->getMessage(), __METHOD__);
                    Yii::$app->session->setFlash('error', $e->getMessage());
                }
            } else {
                Yii::info("Payment not processed - totalReceived: $totalReceived, payments empty: " . (empty($payments) ? 'yes' : 'no'), __METHOD__);
                Yii::$app->session->setFlash('error', 'Please enter a payment amount and select at least one invoice.');
            }
        }

        $outstandingInvoices = $customer->getOutstandingInvoices()->all();

        return $this->render('receive-payment', [
            'customer' => $customer,
            'outstandingInvoices' => $outstandingInvoices,
            'startInvoice' => $startInvoice,
        ]);
    }

    /**
     * Export invoices to CSV
     *
     * @return Response
     */
    public function actionExport()
    {
        $company = Company::getCurrent();
        if (!$company) {
            return $this->redirect(['company/select']);
        }

        $invoices = Invoice::find()
            ->where(['company_id' => $company->id])
            ->with(['customer'])
            ->orderBy(['created_at' => SORT_DESC])
            ->all();

        // Set response headers for CSV download
        Yii::$app->response->format = Response::FORMAT_RAW;
        Yii::$app->response->headers->add('Content-Type', 'text/csv; charset=utf-8');
        Yii::$app->response->headers->add('Content-Disposition', 'attachment; filename="invoices.csv"');

        // Create CSV content
        $output = fopen('php://output', 'w');
        
        // Add BOM for UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Add headers
        fputcsv($output, [
            'Invoice Number',
            'Customer',
            'Invoice Date',
            'Due Date',
            'Status',
            'Total Amount',
            'Paid Amount',
            'Balance Due',
            'Currency',
            'Created At'
        ]);

        // Add data
        foreach ($invoices as $invoice) {
            fputcsv($output, [
                $invoice->invoice_number,
                $invoice->customer->customer_name,
                $invoice->invoice_date,
                $invoice->due_date,
                $invoice->getStatusLabel(),
                $invoice->total_amount,
                $invoice->getTotalPaidAmount(),
                $invoice->getRemainingBalance(),
                $invoice->currency,
                $invoice->created_at,
            ]);
        }

        fclose($output);
        return Yii::$app->response;
    }

    /**
     * Calculate automatic tax rate for invoice
     *
     * @return Response
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
            
            // Create a temporary invoice to calculate tax
            $invoice = new Invoice();
            $invoice->customer_id = $customerId;
            $invoice->company_id = $companyId;
            if ($invoice->hasAttribute('tax_calculation_mode')) {
                $invoice->tax_calculation_mode = Invoice::TAX_MODE_AUTOMATIC;
            }
            
            // Calculate automatic tax rate
            $taxRate = $invoice->calculateAutomaticTaxRate($customer, $company);
            
            if ($taxRate !== null) {
                $details = Json::decode($invoice->tax_calculation_details);
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