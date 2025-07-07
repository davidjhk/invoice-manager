<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "jdosa_invoice_items".
 *
 * @property int $id
 * @property int $invoice_id
 * @property int|null $product_id
 * @property string $description
 * @property float $quantity
 * @property float $rate
 * @property float $amount
 * @property int $sort_order
 * @property string|null $product_service_name
 * @property float $tax_rate
 * @property float $tax_amount
 * @property bool $is_taxable
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Invoice $invoice
 * @property Product $product
 */
class InvoiceItem extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'jdosa_invoice_items';
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
            [['invoice_id', 'description'], 'required'],
            [['invoice_id', 'product_id', 'sort_order'], 'integer'],
            [['quantity', 'rate', 'amount', 'tax_rate', 'tax_amount'], 'number', 'min' => 0],
            [['product_service_name'], 'string', 'max' => 255],
            [['is_taxable'], 'boolean'],
            [['created_at', 'updated_at', 'description'], 'safe'],
            [['quantity'], 'number', 'min' => 0.01],
            [['rate'], 'number', 'min' => 0],
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
            'invoice_id' => 'Invoice ID',
            'description' => 'Description',
            'quantity' => 'Quantity',
            'rate' => 'Rate',
            'amount' => 'Amount',
            'sort_order' => 'Sort Order',
            'product_service_name' => 'Product/Service',
            'tax_rate' => 'Tax Rate (%)',
            'tax_amount' => 'Tax Amount',
            'is_taxable' => 'Taxable',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
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
     * Gets query for [[Product]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(Product::class, ['id' => 'product_id']);
    }

    /**
     * Calculate amount based on quantity and rate
     */
    public function calculateAmount()
    {
        $this->amount = $this->quantity * $this->rate;
    }

    /**
     * Format amount with currency symbol
     *
     * @return string
     */
    public function getFormattedAmount()
    {
        if ($this->invoice) {
            return $this->invoice->formatAmount($this->amount);
        }
        return '$' . number_format($this->amount, 2);
    }

    /**
     * Format rate with currency symbol
     *
     * @return string
     */
    public function getFormattedRate()
    {
        if ($this->invoice) {
            return $this->invoice->formatAmount($this->rate);
        }
        return '$' . number_format($this->rate, 2);
    }

    /**
     * Get formatted quantity
     *
     * @return string
     */
    public function getFormattedQuantity()
    {
        // Remove trailing zeros and decimal point if not needed
        return rtrim(rtrim(number_format($this->quantity, 2), '0'), '.');
    }

    /**
     * Before save event
     *
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        // Calculate amount before saving
        $this->calculateAmount();
        
        // Set sort order if not provided
        if ($insert && empty($this->sort_order)) {
            $maxOrder = static::find()
                ->where(['invoice_id' => $this->invoice_id])
                ->max('sort_order');
            $this->sort_order = ($maxOrder ?: 0) + 1;
        }
        
        return parent::beforeSave($insert);
    }

    /**
     * After save event
     *
     * @param bool $insert
     * @param array $changedAttributes
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        
        // Update invoice totals
        if ($this->invoice) {
            $this->invoice->calculateTotals();
            $this->invoice->save();
        }
    }

    /**
     * After delete event
     */
    public function afterDelete()
    {
        parent::afterDelete();
        
        // Update invoice totals after deletion
        if ($this->invoice) {
            $this->invoice->calculateTotals();
            $this->invoice->save();
        }
    }

    /**
     * Create multiple items from array data
     *
     * @param int $invoiceId
     * @param array $itemsData
     * @return bool
     */
    public static function createMultiple($invoiceId, $itemsData)
    {
        $transaction = Yii::$app->db->beginTransaction();
        
        try {
            $existingItemIds = [];
            
            // Create or update items
            foreach ($itemsData as $index => $itemData) {
                if (empty($itemData['description']) || empty($itemData['quantity']) || !isset($itemData['rate'])) {
                    continue; // Skip empty items
                }
                
                // Check if this is an existing item (has ID)
                if (!empty($itemData['id'])) {
                    $item = static::findOne($itemData['id']);
                    if (!$item || $item->invoice_id != $invoiceId) {
                        // Item doesn't exist or doesn't belong to this invoice, create new
                        $item = new static();
                        $item->invoice_id = $invoiceId;
                    }
                    $existingItemIds[] = $item->id;
                } else {
                    // New item
                    $item = new static();
                    $item->invoice_id = $invoiceId;
                }
                
                $item->description = $itemData['description'];
                $item->quantity = (float) $itemData['quantity'];
                $item->rate = (float) $itemData['rate'];
                $item->sort_order = $index + 1;
                
                if (!$item->save()) {
                    throw new \Exception('Failed to save invoice item: ' . json_encode($item->errors));
                }
                
                if (!in_array($item->id, $existingItemIds)) {
                    $existingItemIds[] = $item->id;
                }
            }
            
            // Delete items that are no longer in the list
            if (!empty($existingItemIds)) {
                static::deleteAll([
                    'and',
                    ['invoice_id' => $invoiceId],
                    ['not in', 'id', $existingItemIds]
                ]);
            } else {
                // If no items exist, delete all items for this invoice
                static::deleteAll(['invoice_id' => $invoiceId]);
            }
            
            $transaction->commit();
            return true;
            
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error('Failed to create invoice items: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get items as array for form
     *
     * @param int $invoiceId
     * @return array
     */
    public static function getItemsArray($invoiceId)
    {
        $items = static::find()
            ->where(['invoice_id' => $invoiceId])
            ->orderBy(['sort_order' => SORT_ASC])
            ->all();
        
        $result = [];
        foreach ($items as $item) {
            $result[] = [
                'id' => $item->id,
                'description' => $item->description,
                'quantity' => $item->getFormattedQuantity(),
                'rate' => number_format($item->rate, 2),
                'amount' => number_format($item->amount, 2),
            ];
        }
        
        return $result;
    }
}