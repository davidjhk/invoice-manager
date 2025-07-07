<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "jdosa_invoices".
 *
 * @property int $id
 * @property string $invoice_number
 * @property int $company_id
 * @property int $customer_id
 * @property string|null $bill_to_address
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
 * @property float $deposit_amount
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Company $company
 * @property Customer $customer
 * @property InvoiceItem[] $invoiceItems
 */
class Invoice extends ActiveRecord
{
    const STATUS_DRAFT = 'draft';
    const STATUS_SENT = 'sent';
    const STATUS_PARTIAL = 'partial';
    const STATUS_PAID = 'paid';
    const STATUS_CANCELLED = 'cancelled';

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
        return [
            [['invoice_number', 'company_id', 'customer_id', 'invoice_date'], 'required'],
            [['company_id', 'customer_id'], 'integer'],
            [['invoice_date', 'due_date'], 'date', 'format' => 'php:Y-m-d'],
            [['subtotal', 'tax_rate', 'tax_amount', 'total_amount', 'discount_value', 'discount_amount', 'deposit_amount'], 'number', 'min' => 0],
            [['notes', 'bill_to_address', 'cc_email', 'ship_to_address', 'ship_from_address', 'payment_instructions', 'customer_notes', 'memo'], 'string'],
            [['shipping_date'], 'date', 'format' => 'php:Y-m-d'],
            [['tracking_number', 'shipping_method', 'terms'], 'string', 'max' => 100],
            [['discount_type'], 'in', 'range' => ['percentage', 'fixed']],
            [['created_at', 'updated_at'], 'safe'],
            [['invoice_number'], 'string', 'max' => 100],
            [['status'], 'string', 'max' => 20],
            [['currency'], 'string', 'max' => 10],
            [['invoice_number'], 'unique'],
            [['status'], 'in', 'range' => [self::STATUS_DRAFT, self::STATUS_SENT, self::STATUS_PAID, self::STATUS_CANCELLED]],
            [['currency'], 'in', 'range' => ['USD', 'EUR', 'GBP', 'KRW']],
            [['company_id'], 'exist', 'skipOnError' => true, 'targetClass' => Company::class, 'targetAttribute' => ['company_id' => 'id']],
            [['customer_id'], 'exist', 'skipOnError' => true, 'targetClass' => Customer::class, 'targetAttribute' => ['customer_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
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
            'ship_to_address' => 'Ship To Address',
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
            'deposit_amount' => 'Deposit Amount',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
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
        
        $this->total_amount = $subtotal - $discountAmount + $this->tax_amount;
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
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_SENT]);
    }

    /**
     * Check if invoice can be sent
     *
     * @return bool
     */
    public function canBeSent()
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_SENT, self::STATUS_PARTIAL]) && 
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
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_SENT, self::STATUS_PARTIAL]) && 
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
}