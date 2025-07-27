<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use app\components\UsSalesTaxCalculator;
use app\models\User;

/**
 * This is the model class for table "jdosa_estimates".
 *
 * @property int $id
 * @property string $estimate_number
 * @property int $company_id
 * @property int $customer_id
 * @property int|null $user_id
 * @property string|null $bill_to_address
 * @property string|null $bill_to_city
 * @property string|null $bill_to_state
 * @property string|null $bill_to_zip_code
 * @property string|null $bill_to_country
 * @property string|null $cc_email
 * @property string $estimate_date
 * @property string|null $expiry_date
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
 * @property bool $converted_to_invoice
 * @property int|null $invoice_id
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Company $company
 * @property Customer $customer
 * @property Invoice $invoice
 * @property EstimateItem[] $estimateItems
 */
class Estimate extends ActiveRecord
{
    const STATUS_DRAFT = 'draft';
    const STATUS_PRINTED = 'printed';
    const STATUS_SENT = 'sent';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_DECLINED = 'declined';
    const STATUS_EXPIRED = 'expired';
    const STATUS_VOID = 'void';
    
    // Tax calculation modes
    const TAX_MODE_AUTOMATIC = 'automatic';
    const TAX_MODE_MANUAL = 'manual';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'jdosa_estimates';
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
            [['estimate_number', 'company_id', 'customer_id', 'estimate_date'], 'required'],
            [['company_id', 'customer_id', 'invoice_id', 'user_id'], 'integer'],
            [['estimate_date', 'expiry_date', 'shipping_date'], 'date', 'format' => 'php:Y-m-d'],
            [['subtotal', 'tax_rate', 'tax_amount', 'total_amount', 'discount_value', 'discount_amount', 'shipping_fee'], 'number', 'min' => 0],
            [['notes', 'ship_to_address', 'ship_from_address', 'payment_instructions', 'customer_notes', 'memo'], 'string'],
            [['bill_to_city', 'ship_to_city'], 'string', 'max' => 100],
            [['bill_to_state', 'ship_to_state'], 'string', 'max' => 50],
            [['bill_to_zip_code', 'ship_to_zip_code'], 'string', 'max' => 20],
            [['bill_to_country', 'ship_to_country'], 'string', 'max' => 2],
            [['bill_to_country', 'ship_to_country'], 'default', 'value' => 'US'],
            [['converted_to_invoice'], 'boolean'],
            [['created_at', 'updated_at'], 'safe'],
            [['estimate_number'], 'string', 'max' => 100],
            [['status'], 'string', 'max' => 20],
            [['currency'], 'string', 'max' => 10],
            [['tracking_number', 'shipping_method', 'terms'], 'string', 'max' => 100],
            [['estimate_number'], 'unique', 'targetAttribute' => ['estimate_number', 'company_id']],
            [['status'], 'in', 'range' => [self::STATUS_DRAFT, self::STATUS_PRINTED, self::STATUS_SENT, self::STATUS_ACCEPTED, self::STATUS_DECLINED, self::STATUS_EXPIRED, self::STATUS_VOID]],
            [['currency'], 'in', 'range' => ['USD', 'EUR', 'GBP', 'KRW']],
            [['discount_type'], 'in', 'range' => ['percentage', 'fixed']],
            [['company_id'], 'exist', 'skipOnError' => true, 'targetClass' => Company::class, 'targetAttribute' => ['company_id' => 'id']],
            [['customer_id'], 'exist', 'skipOnError' => true, 'targetClass' => Customer::class, 'targetAttribute' => ['customer_id' => 'id']],
            [['invoice_id'], 'exist', 'skipOnError' => true, 'targetClass' => Invoice::class, 'targetAttribute' => ['invoice_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
        
        // Add rules for new tax fields only if they exist in the table
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
            'estimate_number' => 'Estimate Number',
            'company_id' => 'Company',
            'customer_id' => 'Customer',
            'estimate_date' => 'Estimate Date',
            'expiry_date' => 'Expiry Date',
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
            'converted_to_invoice' => 'Converted to Invoice',
            'invoice_id' => 'Invoice',
            'user_id' => 'User',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
        
        // Add labels for new tax fields only if they exist in the table
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
     * Gets query for [[Invoice]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getInvoice()
    {
        return $this->hasOne(Invoice::class, ['id' => 'invoice_id']);
    }

    /**
     * Gets query for [[EstimateItems]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEstimateItems()
    {
        return $this->hasMany(EstimateItem::class, ['estimate_id' => 'id'])
            ->orderBy(['sort_order' => SORT_ASC]);
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
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
            self::STATUS_ACCEPTED => 'Accepted',
            self::STATUS_DECLINED => 'Declined',
            self::STATUS_EXPIRED => 'Expired',
            self::STATUS_VOID => 'Void',
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
            self::STATUS_ACCEPTED => 'success',
            self::STATUS_DECLINED => 'danger',
            self::STATUS_EXPIRED => 'warning',
            self::STATUS_VOID => 'dark',
        ];
        return $classes[$this->status] ?? 'secondary';
    }

    /**
     * Calculate totals based on estimate items
     */
    public function calculateTotals()
    {
        $subtotal = 0;
        $taxAmount = 0;
        $taxableSubtotal = 0;

        foreach ($this->estimateItems as $item) {
            $itemAmount = is_numeric($item->amount) ? (float) $item->amount : 0;
            $itemTaxAmount = is_numeric($item->tax_amount) ? (float) $item->tax_amount : 0;
            
            $subtotal += $itemAmount;
            if ($item->is_taxable) {
                $taxableSubtotal += $itemAmount;
                $taxAmount += $itemTaxAmount;
            }
        }

        // Calculate discount
        $discountAmount = 0;
        $discountValue = is_numeric($this->discount_value) ? (float) $this->discount_value : 0;
        
        if ($this->discount_type == 'percentage') {
            $discountAmount = $subtotal * ($discountValue / 100);
        } elseif ($this->discount_type == 'fixed') {
            $discountAmount = $discountValue;
        }

        // If no item-level tax amounts are calculated, use estimate-level tax calculation
        if ($taxAmount == 0 && $this->tax_rate > 0 && $taxableSubtotal > 0) {
            // Calculate tax on taxable subtotal after discount (similar to Invoice model)
            $afterDiscountTaxable = $taxableSubtotal;
            if ($subtotal > 0) {
                $afterDiscountTaxable = $taxableSubtotal - ($discountAmount * ($taxableSubtotal / $subtotal));
            } else {
                $afterDiscountTaxable = $taxableSubtotal - $discountAmount;
            }
            
            // Ensure after discount taxable is not negative
            $afterDiscountTaxable = max(0, $afterDiscountTaxable);
            
            $taxAmount = $afterDiscountTaxable * ($this->tax_rate / 100);
        }

        $this->subtotal = $subtotal;
        $this->discount_amount = $discountAmount;
        $this->tax_amount = $taxAmount;
        
        // Include shipping fee in total amount calculation
        $shippingFee = $this->shipping_fee ?? 0;
        $this->total_amount = $subtotal - $discountAmount + $taxAmount + $shippingFee;
    }

    /**
     * Format amount with currency symbol
     *
     * @param float $amount
     * @return string
     */
    public function formatAmount($amount)
    {
        if ($this->company) {
            return $this->company->formatAmount($amount);
        }
        return '$' . number_format($amount, 2);
    }

    /**
     * Get default expiry date (30 days from estimate date)
     *
     * @return string
     */
    public function getDefaultExpiryDate()
    {
        return date('Y-m-d', strtotime($this->estimate_date . ' +30 days'));
    }

    /**
     * Check if estimate is expired
     *
     * @return bool
     */
    public function isExpired()
    {
        return $this->expiry_date && $this->expiry_date < date('Y-m-d') && $this->status !== self::STATUS_ACCEPTED;
    }


    /**
     * Convert estimate to invoice
     *
     * @return Invoice|null
     */
    public function convertToInvoice()
    {
        if (!$this->canConvertToInvoice() || $this->converted_to_invoice) {
            return null;
        }

        $transaction = Yii::$app->db->beginTransaction();
        
        try {
            // Create new invoice
            $invoice = new Invoice();
            $invoice->attributes = [
                'invoice_number' => $this->company->generateInvoiceNumber(),
                'company_id' => $this->company_id,
                'customer_id' => $this->customer_id,
                'invoice_date' => date('Y-m-d'),
                'due_date' => $this->company->getDefaultDueDate(),
                'subtotal' => $this->subtotal,
                'tax_rate' => $this->tax_rate,
                'tax_amount' => $this->tax_amount,
                'total_amount' => $this->total_amount,
                'notes' => $this->notes,
                'status' => Invoice::STATUS_DRAFT,
                'currency' => $this->currency,
                'bill_to_address' => $this->bill_to_address,
                'bill_to_city' => $this->bill_to_city,
                'bill_to_state' => $this->bill_to_state,
                'bill_to_zip_code' => $this->bill_to_zip_code,
                'bill_to_country' => $this->bill_to_country,
                'ship_to_address' => $this->ship_to_address,
                'ship_to_city' => $this->ship_to_city,
                'ship_to_state' => $this->ship_to_state,
                'ship_to_zip_code' => $this->ship_to_zip_code,
                'ship_to_country' => $this->ship_to_country,
                'ship_from_address' => $this->ship_from_address,
                'shipping_date' => $this->shipping_date,
                'tracking_number' => $this->tracking_number,
                'shipping_method' => $this->shipping_method,
                'terms' => $this->terms,
                'payment_instructions' => $this->payment_instructions,
                'customer_notes' => $this->customer_notes,
                'memo' => $this->memo,
                'discount_type' => $this->discount_type,
                'discount_value' => $this->discount_value,
                'discount_amount' => $this->discount_amount,
                'shipping_fee' => $this->shipping_fee,
            ];

            if (!$invoice->save()) {
                throw new \Exception('Failed to create invoice: ' . json_encode($invoice->errors));
            }

            // Copy estimate items to invoice items
            foreach ($this->estimateItems as $estimateItem) {
                $invoiceItem = new InvoiceItem();
                $invoiceItem->attributes = [
                    'invoice_id' => $invoice->id,
                    'product_service_name' => $estimateItem->product_service_name,
                    'description' => $estimateItem->description,
                    'quantity' => $estimateItem->quantity,
                    'rate' => $estimateItem->rate,
                    'amount' => $estimateItem->amount,
                    'tax_rate' => $estimateItem->tax_rate,
                    'tax_amount' => $estimateItem->tax_amount,
                    'is_taxable' => $estimateItem->is_taxable,
                    'sort_order' => $estimateItem->sort_order,
                ];

                if (!$invoiceItem->save()) {
                    throw new \Exception('Failed to create invoice item: ' . json_encode($invoiceItem->errors));
                }
            }

            // Update estimate
            $this->converted_to_invoice = true;
            $this->invoice_id = $invoice->id;
            if (!$this->save()) {
                throw new \Exception('Failed to update estimate: ' . json_encode($this->errors));
            }

            $transaction->commit();
            return $invoice;

        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error('Failed to convert estimate to invoice: ' . $e->getMessage());
            return null;
        }
    }


    /**
     * Get estimates that are about to expire (within 7 days)
     *
     * @param int $companyId
     * @return \yii\db\ActiveQuery
     */
    /**
     * Get active (non-void) estimates query
     *
     * @return \yii\db\ActiveQuery
     */
    public static function findActive()
    {
        return static::find()->where(['!=', 'status', self::STATUS_VOID]);
    }

    public static function findExpiringSoon($companyId)
    {
        return static::find()
            ->where(['company_id' => $companyId])
            ->andWhere(['!=', 'status', self::STATUS_VOID])
            ->andWhere(['in', 'status', [self::STATUS_SENT]])
            ->andWhere(['>=', 'expiry_date', date('Y-m-d')])
            ->andWhere(['<=', 'expiry_date', date('Y-m-d', strtotime('+7 days'))]);
    }

    /**
     * Before save event
     *
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        // Set user_id if not set and user is logged in
        if ($insert && empty($this->user_id) && !Yii::$app->user->isGuest) {
            $this->user_id = Yii::$app->user->id;
        }

        // Auto-expire estimates past expiry date
        if ($this->expiry_date && $this->expiry_date < date('Y-m-d') && $this->status === self::STATUS_SENT) {
            $this->status = self::STATUS_EXPIRED;
        }

        // Set default expiry date if not provided or if estimate_date has changed
        if ($insert || $this->isAttributeChanged('estimate_date')) {
            if (empty($this->expiry_date)) {
                $company = Company::findOne($this->company_id);
                if ($company) {
                    $this->expiry_date = $company->getDefaultExpiryDate();
                }
            }
        }

        // Apply tax calculation based on mode
        if (!empty($this->tax_calculation_mode)) {
            $this->applyTaxCalculation();
        }

        return parent::beforeSave($insert);
    }

    /**
     * Mark estimate as printed
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
     * Mark estimate as accepted
     * 
     * @return bool
     */
    public function markAsAccepted()
    {
        if (in_array($this->status, [self::STATUS_DRAFT, self::STATUS_PRINTED, self::STATUS_SENT])) {
            $this->status = self::STATUS_ACCEPTED;
            return $this->save(false);
        }
        return true;
    }

    /**
     * Mark estimate as void (soft delete)
     * 
     * @return bool
     */
    public function markAsVoid()
    {
        if ($this->status !== self::STATUS_VOID) {
            $this->status = self::STATUS_VOID;
            return $this->save(false);
        }
        return true;
    }

    /**
     * Check if estimate can be edited
     * 
     * @return bool
     */
    public function canEdit()
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_PRINTED]);
    }

    /**
     * Check if estimate can be converted to invoice
     * 
     * @return bool
     */
    public function canConvertToInvoice()
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_PRINTED, self::STATUS_SENT, self::STATUS_ACCEPTED]) && !$this->converted_to_invoice;
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
                return strtoupper($state);
            }
        }

        // Get state names from database for name matching
        $stateData = State::find()
            ->select(['state_code', 'state_name'])
            ->where(['country_code' => 'US'])
            ->asArray()
            ->all();

        // Try to match state names in address
        foreach ($stateData as $state) {
            $stateName = strtolower($state['state_name']);
            if (preg_match('/\b' . preg_quote($stateName, '/') . '\b/i', $address)) {
                return strtoupper($state['state_code']);
            }
        }
        
        return null;
    }

    /**
     * Apply tax calculation based on mode
     */
    public function applyTaxCalculation()
    {
        if (!$this->hasAttribute('tax_calculation_mode')) {
            return; // Skip if field doesn't exist
        }
        
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