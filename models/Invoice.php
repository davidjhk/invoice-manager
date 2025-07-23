<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use app\components\UsSalesTaxCalculator;
use app\models\State;

/**
 * This is the model class for table "jdosa_invoices".
 *
 * @property int $id
 * @property string $invoice_number
 * @property int $company_id
 * @property int $customer_id
 * @property string|null $bill_to_address
 * @property string|null $bill_to_city
 * @property string|null $bill_to_state
 * @property string|null $bill_to_zip_code
 * @property string|null $bill_to_country
 * @property string|null $cc_email
 * @property string $invoice_date
 * @property string|null $due_date
 * @property float $subtotal
 * @property float $tax_rate
 * @property float $tax_amount
 * @property float $total_amount
 * @property string|null $notes
 * @property string $status
 * @property string $currency
 * @property string|null $ship_to_address
 * @property string|null $ship_to_city
 * @property string|null $ship_to_state
 * @property string|null $ship_to_zip_code
 * @property string|null $ship_to_country
 * @property string|null $ship_from_address
 * @property string|null $shipping_date
 * @property string|null $tracking_number
 * @property string|null $shipping_method
 * @property string|null $terms
 * @property string|null $payment_instructions
 * @property string|null $customer_notes
 * @property string|null $memo
 * @property string|null $discount_type
 * @property float $discount_value
 * @property float $discount_amount
 * @property float $shipping_fee
 * @property float $deposit_amount
 * @property string $tax_calculation_mode
 * @property float|null $auto_calculated_tax_rate
 * @property int|null $tax_jurisdiction_id
 * @property string|null $tax_calculation_details
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Company $company
 * @property Customer $customer
 * @property InvoiceItem[] $invoiceItems
 * @property TaxJurisdiction $taxJurisdiction
 */
class Invoice extends ActiveRecord
{
    const STATUS_DRAFT = 'draft';
    const STATUS_PRINTED = 'printed';
    const STATUS_SENT = 'sent';
    const STATUS_PARTIAL = 'partial';
    const STATUS_PAID = 'paid';
    const STATUS_CANCELLED = 'cancelled';
    
    // Tax calculation modes
    const TAX_MODE_AUTOMATIC = 'automatic';
    const TAX_MODE_MANUAL = 'manual';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'jdosa_invoices';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => function() {
                    return date('Y-m-d H:i:s');
                },
            ],
        ];
    }


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        $rules = [
            [['invoice_number', 'company_id', 'customer_id', 'invoice_date'], 'required'],
            [['company_id', 'customer_id'], 'integer'],
            [['invoice_date', 'due_date'], 'date', 'format' => 'php:Y-m-d'],
            [['subtotal', 'tax_rate', 'tax_amount', 'total_amount', 'discount_value', 'discount_amount', 'shipping_fee', 'deposit_amount'], 'number', 'min' => 0],
            [['notes', 'bill_to_address', 'cc_email', 'ship_to_address', 'ship_from_address', 'payment_instructions', 'customer_notes', 'memo'], 'string'],
            [['bill_to_city', 'ship_to_city'], 'string', 'max' => 100],
            [['bill_to_state', 'ship_to_state'], 'string', 'max' => 50],
            [['bill_to_zip_code', 'ship_to_zip_code'], 'string', 'max' => 20],
            [['bill_to_country', 'ship_to_country'], 'string', 'max' => 2],
            [['bill_to_country', 'ship_to_country'], 'default', 'value' => 'US'],
            [['shipping_date'], 'date', 'format' => 'php:Y-m-d'],
            [['tracking_number', 'shipping_method', 'terms'], 'string', 'max' => 100],
            [['discount_type'], 'in', 'range' => ['percentage', 'fixed']],
            [['created_at', 'updated_at'], 'safe'],
            [['invoice_number'], 'string', 'max' => 100],
            [['status'], 'string', 'max' => 20],
            [['currency'], 'string', 'max' => 10],
            [['invoice_number'], 'unique'],
            [['status'], 'in', 'range' => [self::STATUS_DRAFT, self::STATUS_PRINTED, self::STATUS_SENT, self::STATUS_PAID, self::STATUS_CANCELLED]],
            [['currency'], 'in', 'range' => ['USD', 'EUR', 'GBP', 'KRW']],
            [['company_id'], 'exist', 'skipOnError' => true, 'targetClass' => Company::class, 'targetAttribute' => ['company_id' => 'id']],
            [['customer_id'], 'exist', 'skipOnError' => true, 'targetClass' => Customer::class, 'targetAttribute' => ['customer_id' => 'id']],
        ];
        
        // Add rules for new fields only if they exist in the table
        if ($this->hasAttribute('auto_calculated_tax_rate')) {
            $rules[] = [['auto_calculated_tax_rate'], 'number', 'min' => 0];
        }
        
        if ($this->hasAttribute('tax_calculation_details')) {
            $rules[] = [['tax_calculation_details'], 'string'];
        }
        
        if ($this->hasAttribute('tax_calculation_mode')) {
            $rules[] = [['tax_calculation_mode'], 'in', 'range' => [self::TAX_MODE_AUTOMATIC, self::TAX_MODE_MANUAL]];
            $rules[] = [['tax_calculation_mode'], 'default', 'value' => self::TAX_MODE_MANUAL];
        }
        
        if ($this->hasAttribute('tax_jurisdiction_id')) {
            $rules[] = [['tax_jurisdiction_id'], 'integer'];
            // Note: TaxJurisdiction class validation will be skipped if class doesn't exist
            if (class_exists('\app\models\TaxJurisdiction')) {
                $rules[] = [['tax_jurisdiction_id'], 'exist', 'skipOnError' => true, 'targetClass' => TaxJurisdiction::class, 'targetAttribute' => ['tax_jurisdiction_id' => 'id']];
            }
        }
        
        return $rules;
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        $labels = [
            'id' => 'ID',
            'invoice_number' => 'Invoice Number',
            'company_id' => 'Company ID',
            'customer_id' => 'Customer ID',
            'invoice_date' => 'Invoice Date',
            'due_date' => 'Due Date',
            'subtotal' => 'Subtotal',
            'tax_rate' => 'Tax Rate (%)',
            'tax_amount' => 'Tax Amount',
            'total_amount' => 'Total Amount',
            'notes' => 'Notes',
            'status' => 'Status',
            'currency' => 'Currency',
            'bill_to_address' => 'Bill To Address',
            'bill_to_city' => 'Bill To City',
            'bill_to_state' => 'Bill To State',
            'bill_to_zip_code' => 'Bill To ZIP Code',
            'bill_to_country' => 'Bill To Country',
            'ship_to_address' => 'Ship To Address',
            'ship_to_city' => 'Ship To City',
            'ship_to_state' => 'Ship To State',
            'ship_to_zip_code' => 'Ship To ZIP Code',
            'ship_to_country' => 'Ship To Country',
            'ship_from_address' => 'Ship From Address',
            'shipping_date' => 'Shipping Date',
            'tracking_number' => 'Tracking Number',
            'shipping_method' => 'Shipping Method',
            'terms' => 'Terms',
            'payment_instructions' => 'Payment Instructions',
            'customer_notes' => 'Customer Notes',
            'memo' => 'Internal Memo',
            'discount_type' => 'Discount Type',
            'discount_value' => 'Discount Value',
            'discount_amount' => 'Discount Amount',
            'shipping_fee' => 'Shipping Fee',
            'deposit_amount' => 'Deposit Amount',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
        
        // Add labels for new fields only if they exist in the table
        if ($this->hasAttribute('tax_calculation_mode')) {
            $labels['tax_calculation_mode'] = 'Tax Calculation Mode';
        }
        if ($this->hasAttribute('auto_calculated_tax_rate')) {
            $labels['auto_calculated_tax_rate'] = 'Auto Calculated Tax Rate (%)';
        }
        if ($this->hasAttribute('tax_jurisdiction_id')) {
            $labels['tax_jurisdiction_id'] = 'Tax Jurisdiction';
        }
        if ($this->hasAttribute('tax_calculation_details')) {
            $labels['tax_calculation_details'] = 'Tax Calculation Details';
        }
        
        return $labels;
    }

    /**
     * Gets query for [[Company]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCompany()
    {
        return $this->hasOne(Company::class, ['id' => 'company_id']);
    }

    /**
     * Gets query for [[Customer]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCustomer()
    {
        return $this->hasOne(Customer::class, ['id' => 'customer_id']);
    }

    /**
     * Gets query for [[InvoiceItems]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getInvoiceItems()
    {
        return $this->hasMany(InvoiceItem::class, ['invoice_id' => 'id'])
            ->orderBy(['sort_order' => SORT_ASC]);
    }

    /**
     * Gets query for [[TaxJurisdiction]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTaxJurisdiction()
    {
        return $this->hasOne(TaxJurisdiction::class, ['id' => 'tax_jurisdiction_id']);
    }

    /**
     * Gets query for [[Payments]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPayments()
    {
        return $this->hasMany(Payment::class, ['invoice_id' => 'id'])
            ->orderBy(['payment_date' => SORT_DESC]);
    }

    /**
     * Get status options
     *
     * @return array
     */
    public static function getStatusOptions()
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_PRINTED => 'Printed',
            self::STATUS_SENT => 'Sent',
            self::STATUS_PARTIAL => 'Partially Paid',
            self::STATUS_PAID => 'Paid',
            self::STATUS_CANCELLED => 'Cancelled',
        ];
    }

    /**
     * Get status label
     *
     * @return string
     */
    public function getStatusLabel()
    {
        $options = self::getStatusOptions();
        return $options[$this->status] ?? $this->status;
    }

    /**
     * Get status CSS class
     *
     * @return string
     */
    public function getStatusClass()
    {
        $classes = [
            self::STATUS_DRAFT => 'secondary',
            self::STATUS_PRINTED => 'primary',
            self::STATUS_SENT => 'info',
            self::STATUS_PARTIAL => 'warning',
            self::STATUS_PAID => 'success',
            self::STATUS_CANCELLED => 'danger',
        ];
        return $classes[$this->status] ?? 'secondary';
    }

    /**
     * Calculate totals based on invoice items
     */
    public function calculateTotals()
    {
        $subtotal = 0;
        $taxableSubtotal = 0;
        
        // Refresh invoice items from database only if we have an ID
        if (!$this->isNewRecord) {
            $this->refresh();
        }
        
        foreach ($this->invoiceItems as $item) {
            $subtotal += $item->amount;
            if ($item->is_taxable) {
                $taxableSubtotal += $item->amount;
            }
        }
        
        $this->subtotal = $subtotal;
        
        // Calculate discount
        $discountAmount = 0;
        if ($this->discount_value > 0) {
            if ($this->discount_type === 'percentage') {
                $discountAmount = $subtotal * ($this->discount_value / 100);
            } else {
                $discountAmount = $this->discount_value;
            }
        }
        $this->discount_amount = $discountAmount;
        
        // Calculate tax on taxable subtotal after discount
        $afterDiscountTaxable = $taxableSubtotal;
        if ($subtotal > 0) {
            $afterDiscountTaxable = $taxableSubtotal - ($discountAmount * ($taxableSubtotal / $subtotal));
        } else {
            $afterDiscountTaxable = $taxableSubtotal - $discountAmount;
        }
        
        // Ensure after discount taxable is not negative
        $afterDiscountTaxable = max(0, $afterDiscountTaxable);
        
        $this->tax_amount = $afterDiscountTaxable * (($this->tax_rate ?? 0) / 100);
        
        // Include shipping fee in total amount calculation
        $shippingFee = $this->shipping_fee ?? 0;
        $this->total_amount = $subtotal - $discountAmount + $this->tax_amount + $shippingFee;
    }

    /**
     * Get currency symbol
     *
     * @return string
     */
    public function getCurrencySymbol()
    {
        $symbols = [
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'KRW' => '₩',
        ];
        return $symbols[$this->currency] ?? '$';
    }

    /**
     * Format amount with currency symbol
     *
     * @param float $amount
     * @return string
     */
    public function formatAmount($amount)
    {
        return $this->getCurrencySymbol() . number_format($amount, 2);
    }

    /**
     * Check if invoice is editable
     *
     * @return bool
     */
    public function isEditable()
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_PRINTED, self::STATUS_SENT]);
    }

    /**
     * Check if invoice can be sent
     *
     * @return bool
     */
    public function canBeSent()
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_SENT, self::STATUS_PARTIAL, self::STATUS_PRINTED]) && 
               !empty($this->customer->customer_email);
    }

    /**
     * Search invoices
     *
     * @param string $term
     * @param int $companyId
     * @return \yii\db\ActiveQuery
     */
    public static function search($term, $companyId)
    {
        return static::find()
            ->joinWith(['customer'])
            ->where(['jdosa_invoices.company_id' => $companyId])
            ->andWhere(['or',
                ['like', 'invoice_number', $term],
                ['like', 'jdosa_customers.customer_name', $term],
                ['like', 'notes', $term],
            ])
            ->orderBy(['created_at' => SORT_DESC]);
    }

    /**
     * Get invoices by status
     *
     * @param string $status
     * @param int $companyId
     * @return \yii\db\ActiveQuery
     */
    public static function findByStatus($status, $companyId)
    {
        return static::find()
            ->where(['status' => $status, 'company_id' => $companyId])
            ->orderBy(['created_at' => SORT_DESC]);
    }

    /**
     * Get overdue invoices
     *
     * @param int $companyId
     * @return \yii\db\ActiveQuery
     */
    public static function findOverdue($companyId)
    {
        return static::find()
            ->where(['company_id' => $companyId])
            ->andWhere(['!=', 'status', self::STATUS_PAID])
            ->andWhere(['!=', 'status', self::STATUS_CANCELLED])
            ->andWhere(['<', 'due_date', date('Y-m-d')])
            ->orderBy(['due_date' => SORT_ASC]);
    }

    /**
     * Get total amount for company
     *
     * @param int $companyId
     * @param string|null $status
     * @return float
     */
    public static function getTotalAmount($companyId, $status = null)
    {
        $query = static::find()->where(['company_id' => $companyId]);
        
        if ($status) {
            $query->andWhere(['status' => $status]);
        }
        
        return $query->sum('total_amount') ?: 0;
    }

    /**
     * Before save event
     *
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if ($insert && empty($this->invoice_number)) {
            $company = Company::findOne($this->company_id);
            if ($company) {
                $this->invoice_number = $company->generateInvoiceNumber();
            }
        }
        
        if ($insert && empty($this->due_date) && !empty($this->company_id)) {
            $company = Company::findOne($this->company_id);
            if ($company) {
                $this->due_date = $company->getDefaultDueDate();
            }
        }
        
        // Apply tax calculation based on mode
        if (!empty($this->tax_calculation_mode)) {
            $this->applyTaxCalculation();
        }
        
        return parent::beforeSave($insert);
    }

    /**
     * Get total paid amount
     *
     * @return float
     */
    public function getTotalPaidAmount()
    {
        return $this->getPayments()->sum('amount') ?: 0;
    }

    /**
     * Get paid amount (alias for getTotalPaidAmount)
     *
     * @return float
     */
    public function getPaidAmount()
    {
        return $this->getTotalPaidAmount();
    }

    /**
     * Get balance (alias for getRemainingBalance)
     *
     * @return float
     */
    public function getBalance()
    {
        return $this->getRemainingBalance();
    }

    /**
     * Get remaining balance
     *
     * @return float
     */
    public function getRemainingBalance()
    {
        return $this->total_amount - $this->getTotalPaidAmount();
    }

    /**
     * Check if invoice is fully paid
     *
     * @return bool
     */
    public function isFullyPaid()
    {
        return $this->getRemainingBalance() <= 0;
    }

    /**
     * Check if invoice is partially paid
     *
     * @return bool
     */
    public function isPartiallyPaid()
    {
        $paidAmount = $this->getTotalPaidAmount();
        return $paidAmount > 0 && $paidAmount < $this->total_amount;
    }

    /**
     * Update payment status based on payments
     */
    public function updatePaymentStatus()
    {
        if ($this->isFullyPaid()) {
            $this->status = self::STATUS_PAID;
        } elseif ($this->isPartiallyPaid()) {
            $this->status = self::STATUS_PARTIAL;
        }
        
        $this->save(false);
    }

    /**
     * Check if payment can be received
     *
     * @return bool
     */
    public function canReceivePayment()
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_PRINTED, self::STATUS_SENT, self::STATUS_PARTIAL]) && 
               $this->getRemainingBalance() > 0;
    }

    /**
     * Duplicate invoice
     *
     * @return Invoice|null
     */
    public function duplicate()
    {
        $transaction = Yii::$app->db->beginTransaction();
        
        try {
            // Create new invoice
            $newInvoice = new self();
            $newInvoice->attributes = $this->attributes;
            
            // Reset specific fields
            $newInvoice->id = null;
            $newInvoice->invoice_date = date('Y-m-d');
            $newInvoice->status = self::STATUS_DRAFT;
            $newInvoice->subtotal = 0;
            $newInvoice->tax_amount = 0;
            $newInvoice->total_amount = 0;
            $newInvoice->created_at = null;
            $newInvoice->updated_at = null;
            
            // Generate new invoice number manually to ensure it's available
            if ($newInvoice->company_id) {
                $company = Company::findOne($newInvoice->company_id);
                if ($company) {
                    $newInvoice->invoice_number = $company->generateInvoiceNumber();
                    $newInvoice->due_date = $company->getDefaultDueDate();
                }
            }
            
            // If still no invoice number, set a temporary one
            if (empty($newInvoice->invoice_number)) {
                $newInvoice->invoice_number = 'COPY-' . $this->invoice_number . '-' . date('YmdHis');
            }
            
            if (!$newInvoice->save()) {
                throw new \Exception('Failed to duplicate invoice: ' . json_encode($newInvoice->errors));
            }
            
            // Duplicate invoice items
            foreach ($this->invoiceItems as $item) {
                $newItem = new InvoiceItem();
                $newItem->attributes = $item->attributes;
                $newItem->id = null;
                $newItem->invoice_id = $newInvoice->id;
                $newItem->created_at = null;
                $newItem->updated_at = null;
                
                if (!$newItem->save()) {
                    throw new \Exception('Failed to duplicate invoice item: ' . json_encode($newItem->errors));
                }
            }
            
            // Recalculate totals
            $newInvoice->calculateTotals();
            $newInvoice->save();
            
            $transaction->commit();
            return $newInvoice;
            
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error('Failed to duplicate invoice: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Mark invoice as printed
     * 
     * @return bool
     */
    public function markAsPrinted()
    {
        if ($this->status === self::STATUS_DRAFT) {
            $this->status = self::STATUS_PRINTED;
            return $this->save(false);
        }
        return true;
    }

    /**
     * Check if invoice can be edited
     * 
     * @return bool
     */
    public function canEdit()
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_PRINTED]);
    }

    /**
     * Get tax calculation mode options
     *
     * @return array
     */
    public static function getTaxCalculationModeOptions()
    {
        return [
            self::TAX_MODE_MANUAL => Yii::t('app/invoice', 'Manual Input'),
            self::TAX_MODE_AUTOMATIC => Yii::t('app/invoice', 'Automatic Calculation'),
        ];
    }

    /**
     * Get tax calculation mode label
     *
     * @return string
     */
    public function getTaxCalculationModeLabel()
    {
        $options = self::getTaxCalculationModeOptions();
        return $options[$this->tax_calculation_mode] ?? $this->tax_calculation_mode;
    }

    /**
     * Calculate tax rate automatically based on customer address
     *
     * @param Customer|null $customer Optional customer object to use instead of relation
     * @param Company|null $company Optional company object to use instead of relation
     * @return float|null
     */
    public function calculateAutomaticTaxRate($customer = null, $company = null)
    {
        $customer = $customer ?: $this->customer;
        $company = $company ?: $this->company;
        
        if (!$customer || !$company) {
            return null;
        }

        try {
            // Use UsSalesTaxCalculator component
            $calculator = new UsSalesTaxCalculator();
            
            // Get customer ZIP code - prefer structured field over extracted
            $zipCode = $customer->zip_code;
            if (!$zipCode) {
                // Fallback to extracting from address
                $zipCode = $this->extractZipFromAddress($customer->customer_address);
            }
            
            if (!$zipCode) {
                // No ZIP code available, fallback to company tax rate
                return $company->tax_rate ?? 0;
            }

            // Get company state - prefer structured field over extracted
            $companyState = $company->state;
            if (!$companyState) {
                // Fallback to extracting from address
                $companyState = $this->extractStateFromAddress($company->company_address);
            }
            
            if (!$companyState) {
                // If no state found, fallback to company tax rate
                return $company->tax_rate ?? 0;
            }

            // Calculate tax rate based on jurisdiction
            $taxRate = $calculator->calculateTaxRate($companyState, true, $zipCode);
            
            // Store calculation details (only if fields exist)
            try {
                if ($this->hasAttribute('auto_calculated_tax_rate')) {
                    $this->auto_calculated_tax_rate = $taxRate;
                }
                if ($this->hasAttribute('tax_calculation_details')) {
                    $this->tax_calculation_details = json_encode([
                        'method' => 'automatic',
                        'customer_zip_code' => $zipCode,
                        'customer_state' => $customer->state,
                        'customer_city' => $customer->city,
                        'company_state' => $companyState,
                        'calculated_rate' => $taxRate,
                        'calculated_at' => date('Y-m-d H:i:s'),
                        'used_fallback' => $calculator->lastCalculationUsedFallback,
                        'fallback_reason' => $calculator->fallbackReason,
                    ]);
                }
            } catch (\Exception $fieldError) {
                // Ignore field assignment errors if columns don't exist
                Yii::info("Tax calculation field assignment skipped: " . $fieldError->getMessage());
            }

            return $taxRate;
        } catch (\Exception $e) {
            // Log detailed error information and fallback to company tax rate
            Yii::error("Tax calculation error in calculateAutomaticTaxRate: " . $e->getMessage() . 
                      " | Customer ID: " . ($customer->id ?? 'null') . 
                      " | Company ID: " . ($company->id ?? 'null') . 
                      " | ZIP: " . ($zipCode ?? 'null') . 
                      " | State: " . ($companyState ?? 'null'));
            
            // Store error details for debugging (only if field exists)
            try {
                if ($this->hasAttribute('tax_calculation_details')) {
                    $this->tax_calculation_details = json_encode([
                        'method' => 'automatic',
                        'error' => $e->getMessage(),
                        'fallback_rate' => $company->tax_rate ?? 0,
                        'calculated_at' => date('Y-m-d H:i:s'),
                    ]);
                }
            } catch (\Exception $fieldError) {
                // Ignore field assignment errors if columns don't exist
                Yii::info("Tax calculation error field assignment skipped: " . $fieldError->getMessage());
            }
            
            return $company->tax_rate ?? 0;
        }
    }

    /**
     * Extract ZIP code from address string
     *
     * @param string $address
     * @return string|null
     */
    private function extractZipFromAddress($address)
    {
        if (empty($address)) {
            return null;
        }

        // Match 5-digit ZIP code or ZIP+4 format
        if (preg_match('/\b(\d{5})(?:-\d{4})?\b/', $address, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Extract state from address string
     *
     * @param string $address
     * @return string|null
     */
    private function extractStateFromAddress($address)
    {
        if (empty($address)) {
            return null;
        }

        // Get US state abbreviations from database
        $states = State::find()
            ->select(['state_code'])
            ->where(['country_code' => 'US'])
            ->column();

        // Try to match state abbreviation in address
        foreach ($states as $state) {
            if (preg_match('/\b' . $state . '\b/i', $address)) {
                return $state;
            }
        }

        // Get state names from database for name matching
        $stateData = State::find()
            ->select(['state_code', 'state_name'])
            ->where(['country_code' => 'US'])
            ->asArray()
            ->all();

        // Create name to code mapping
        $stateNames = [];
        foreach ($stateData as $state) {
            $stateNames[strtolower($state['state_name'])] = $state['state_code'];
        }

        // Try to match state names in address
        foreach ($stateNames as $name => $code) {
            if (preg_match('/\b' . preg_quote($name, '/') . '\b/i', $address)) {
                return $code;
            }
        }

        return null;
    }

    /**
     * Apply tax calculation based on mode
     */
    public function applyTaxCalculation()
    {
        if ($this->tax_calculation_mode === self::TAX_MODE_AUTOMATIC) {
            $automaticRate = $this->calculateAutomaticTaxRate();
            if ($automaticRate !== null) {
                $this->tax_rate = $automaticRate;
            }
        } else {
            // Manual mode - use company tax rate or keep current value
            if ($this->company && $this->tax_rate === null) {
                $this->tax_rate = $this->company->tax_rate ?? 0;
            }
            
            // Clear automatic calculation data (only if fields exist)
            try {
                if ($this->hasAttribute('auto_calculated_tax_rate')) {
                    $this->auto_calculated_tax_rate = null;
                }
                if ($this->hasAttribute('tax_jurisdiction_id')) {
                    $this->tax_jurisdiction_id = null;
                }
                if ($this->hasAttribute('tax_calculation_details')) {
                    $this->tax_calculation_details = json_encode([
                        'method' => 'manual',
                        'applied_at' => date('Y-m-d H:i:s'),
                    ]);
                }
            } catch (\Exception $fieldError) {
                // Ignore field assignment errors if columns don't exist
                Yii::info("Manual tax mode field assignment skipped: " . $fieldError->getMessage());
            }
        }
    }
}