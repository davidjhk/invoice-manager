<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "jdosa_estimate_items".
 *
 * @property int $id
 * @property int $estimate_id
 * @property int|null $product_id
 * @property string|null $product_service_name
 * @property string $description
 * @property float $quantity
 * @property float $rate
 * @property float $amount
 * @property float $tax_rate
 * @property float $tax_amount
 * @property bool $is_taxable
 * @property int $sort_order
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Estimate $estimate
 * @property Product $product
 */
class EstimateItem extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'jdosa_estimate_items';
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
            [['estimate_id', 'description'], 'required'],
            [['estimate_id', 'sort_order'], 'integer'],
            [['quantity', 'rate', 'amount', 'tax_rate', 'tax_amount'], 'number', 'min' => 0],
            [['is_taxable'], 'boolean'],
            [['created_at', 'updated_at', 'description'], 'safe'],
            [['product_service_name'], 'string', 'max' => 255],
            [['quantity'], 'number', 'min' => 0.01],
            [['rate'], 'number', 'min' => 0],
            [['estimate_id'], 'exist', 'skipOnError' => true, 'targetClass' => Estimate::class, 'targetAttribute' => ['estimate_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'estimate_id' => 'Estimate ID',
            'product_service_name' => 'Product/Service',
            'description' => 'Description',
            'quantity' => 'Quantity',
            'rate' => 'Rate',
            'amount' => 'Amount',
            'tax_rate' => 'Tax Rate (%)',
            'tax_amount' => 'Tax Amount',
            'is_taxable' => 'Taxable',
            'sort_order' => 'Sort Order',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[Estimate]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEstimate()
    {
        return $this->hasOne(Estimate::class, ['id' => 'estimate_id']);
    }

    /**
     * Calculate amount based on quantity and rate
     */
    public function calculateAmount()
    {
        // Ensure numeric values
        $quantity = is_numeric($this->quantity) ? (float) $this->quantity : 0;
        $rate = is_numeric($this->rate) ? (float) $this->rate : 0;
        $taxRate = is_numeric($this->tax_rate) ? (float) $this->tax_rate : 0;
        
        $this->amount = $quantity * $rate;
        
        // Calculate tax amount if taxable
        if ($this->is_taxable && $taxRate > 0) {
            $this->tax_amount = $this->amount * ($taxRate / 100);
        } else {
            $this->tax_amount = 0;
        }
    }

    /**
     * Format amount with currency symbol
     *
     * @return string
     */
    public function getFormattedAmount()
    {
        if ($this->estimate) {
            return $this->estimate->formatAmount($this->amount);
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
        if ($this->estimate) {
            return $this->estimate->formatAmount($this->rate);
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
                ->where(['estimate_id' => $this->estimate_id])
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
        
        // Update estimate totals
        if ($this->estimate) {
            $this->estimate->calculateTotals();
            $this->estimate->save();
        }
    }

    /**
     * After delete event
     */
    public function afterDelete()
    {
        parent::afterDelete();
        
        // Update estimate totals after deletion
        if ($this->estimate) {
            $this->estimate->calculateTotals();
            $this->estimate->save();
        }
    }

    /**
     * Create multiple items from array data
     *
     * @param int $estimateId
     * @param array $itemsData
     * @return bool
     */
    public static function createMultiple($estimateId, $itemsData)
    {
        $transaction = Yii::$app->db->beginTransaction();
        
        try {
            $existingItemIds = [];
            
            // Create or update items
            foreach ($itemsData as $index => $itemData) {
                if (empty($itemData['description']) || 
                    !isset($itemData['quantity']) || $itemData['quantity'] === '' || 
                    !isset($itemData['rate']) || $itemData['rate'] === '') {
                    continue; // Skip empty items
                }
                
                // Check if this is an existing item (has ID)
                if (!empty($itemData['id'])) {
                    $item = static::findOne($itemData['id']);
                    if (!$item || $item->estimate_id != $estimateId) {
                        // Item doesn't exist or doesn't belong to this estimate, create new
                        $item = new static();
                        $item->estimate_id = $estimateId;
                    }
                    $existingItemIds[] = $item->id;
                } else {
                    // New item
                    $item = new static();
                    $item->estimate_id = $estimateId;
                }
                
                $item->product_service_name = $itemData['product_service_name'] ?? null;
                $item->description = $itemData['description'];
                $item->quantity = is_numeric($itemData['quantity']) ? (float) $itemData['quantity'] : 1;
                $item->rate = is_numeric($itemData['rate']) ? (float) $itemData['rate'] : 0;
                $item->tax_rate = is_numeric($itemData['tax_rate'] ?? 0) ? (float) ($itemData['tax_rate'] ?? 0) : 0;
                $item->is_taxable = isset($itemData['is_taxable']) ? (bool) $itemData['is_taxable'] : true;
                $item->sort_order = $index + 1;
                
                if (!$item->save()) {
                    throw new \Exception('Failed to save estimate item: ' . json_encode($item->errors));
                }
                
                if (!in_array($item->id, $existingItemIds)) {
                    $existingItemIds[] = $item->id;
                }
            }
            
            // Delete items that are no longer in the list
            if (!empty($existingItemIds)) {
                static::deleteAll([
                    'and',
                    ['estimate_id' => $estimateId],
                    ['not in', 'id', $existingItemIds]
                ]);
            } else {
                // If no items exist, delete all items for this estimate
                static::deleteAll(['estimate_id' => $estimateId]);
            }
            
            $transaction->commit();
            return true;
            
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error('Failed to create estimate items: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get items as array for form
     *
     * @param int $estimateId
     * @return array
     */
    public static function getItemsArray($estimateId)
    {
        $items = static::find()
            ->where(['estimate_id' => $estimateId])
            ->orderBy(['sort_order' => SORT_ASC])
            ->all();
        
        $result = [];
        foreach ($items as $item) {
            $result[] = [
                'id' => $item->id,
                'product_service_name' => $item->product_service_name,
                'description' => $item->description,
                'quantity' => $item->getFormattedQuantity(),
                'rate' => number_format($item->rate, 2),
                'amount' => number_format($item->amount, 2),
                'tax_rate' => $item->tax_rate,
                'is_taxable' => $item->is_taxable,
            ];
        }
        
        return $result;
    }
}