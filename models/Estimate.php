<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "jdosa_estimates".
 *
 * @property int $id
 * @property string $estimate_number
 * @property int $company_id
 * @property int $customer_id
 * @property string|null $bill_to_address
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
    const STATUS_REJECTED = 'rejected';
    const STATUS_EXPIRED = 'expired';

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
        return [
            [['estimate_number', 'company_id', 'customer_id', 'estimate_date'], 'required'],
            [['company_id', 'customer_id', 'invoice_id'], 'integer'],
            [['estimate_date', 'expiry_date', 'shipping_date'], 'date', 'format' => 'php:Y-m-d'],
            [['subtotal', 'tax_rate', 'tax_amount', 'total_amount', 'discount_value', 'discount_amount'], 'number', 'min' => 0],
            [['notes', 'ship_to_address', 'ship_from_address', 'payment_instructions', 'customer_notes', 'memo'], 'string'],
            [['converted_to_invoice'], 'boolean'],
            [['created_at', 'updated_at'], 'safe'],
            [['estimate_number'], 'string', 'max' => 100],
            [['status'], 'string', 'max' => 20],
            [['currency'], 'string', 'max' => 10],
            [['tracking_number', 'shipping_method', 'terms'], 'string', 'max' => 100],
            [['estimate_number'], 'unique'],
            [['status'], 'in', 'range' => [self::STATUS_DRAFT, self::STATUS_PRINTED, self::STATUS_SENT, self::STATUS_ACCEPTED, self::STATUS_REJECTED, self::STATUS_EXPIRED]],
            [['currency'], 'in', 'range' => ['USD', 'EUR', 'GBP', 'KRW']],
            [['discount_type'], 'in', 'range' => ['percentage', 'fixed']],
            [['company_id'], 'exist', 'skipOnError' => true, 'targetClass' => Company::class, 'targetAttribute' => ['company_id' => 'id']],
            [['customer_id'], 'exist', 'skipOnError' => true, 'targetClass' => Customer::class, 'targetAttribute' => ['customer_id' => 'id']],
            [['invoice_id'], 'exist', 'skipOnError' => true, 'targetClass' => Invoice::class, 'targetAttribute' => ['invoice_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
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
            'converted_to_invoice' => 'Converted to Invoice',
            'invoice_id' => 'Invoice',
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
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_EXPIRED => 'Expired',
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
            self::STATUS_REJECTED => 'danger',
            self::STATUS_EXPIRED => 'warning',
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

        foreach ($this->estimateItems as $item) {
            $itemAmount = is_numeric($item->amount) ? (float) $item->amount : 0;
            $itemTaxAmount = is_numeric($item->tax_amount) ? (float) $item->tax_amount : 0;
            
            $subtotal += $itemAmount;
            if ($item->is_taxable) {
                $taxAmount += $itemTaxAmount;
            }
        }

        // Apply discount
        $discountAmount = 0;
        $discountValue = is_numeric($this->discount_value) ? (float) $this->discount_value : 0;
        
        if ($this->discount_type == 'percentage') {
            $discountAmount = $subtotal * ($discountValue / 100);
        } elseif ($this->discount_type == 'fixed') {
            $discountAmount = $discountValue;
        }

        $this->subtotal = $subtotal;
        $this->discount_amount = $discountAmount;
        $this->tax_amount = $taxAmount;
        $this->total_amount = $subtotal - $discountAmount + $taxAmount;
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
                'ship_to_address' => $this->ship_to_address,
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
    public static function findExpiringSoon($companyId)
    {
        return static::find()
            ->where(['company_id' => $companyId])
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
        return in_array($this->status, [self::STATUS_PRINTED, self::STATUS_SENT, self::STATUS_ACCEPTED]);
    }
}