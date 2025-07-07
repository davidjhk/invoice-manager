<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "jdosa_products".
 *
 * @property int $id
 * @property int $company_id
 * @property string $name
 * @property string|null $description
 * @property string $type
 * @property string|null $category
 * @property string|null $sku
 * @property string $unit
 * @property float $price
 * @property float $cost
 * @property bool $is_taxable
 * @property bool $is_active
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Company $company
 * @property InvoiceItem[] $invoiceItems
 * @property EstimateItem[] $estimateItems
 */
class Product extends ActiveRecord
{
    const TYPE_SERVICE = 'service';
    const TYPE_PRODUCT = 'product';
    const TYPE_NON_INVENTORY = 'non_inventory';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'jdosa_products';
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
            [['company_id', 'name'], 'required'],
            [['company_id'], 'integer'],
            [['description'], 'string'],
            [['price', 'cost'], 'number', 'min' => 0],
            [['is_taxable', 'is_active'], 'boolean'],
            [['created_at', 'updated_at'], 'safe'],
            [['name'], 'string', 'max' => 255],
            [['type'], 'string', 'max' => 50],
            [['type'], 'in', 'range' => [self::TYPE_SERVICE, self::TYPE_PRODUCT, self::TYPE_NON_INVENTORY]],
            [['category'], 'string', 'max' => 100],
            [['sku'], 'string', 'max' => 100],
            [['unit'], 'string', 'max' => 50],
            [['sku'], 'unique', 'targetAttribute' => ['company_id', 'sku'], 'message' => 'SKU must be unique within the company.'],
            [['company_id'], 'exist', 'skipOnError' => true, 'targetClass' => Company::class, 'targetAttribute' => ['company_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'company_id' => 'Company ID',
            'name' => 'Product/Service Name',
            'description' => 'Description',
            'type' => 'Type',
            'category' => 'Category',
            'sku' => 'SKU',
            'unit' => 'Unit',
            'price' => 'Price',
            'cost' => 'Cost',
            'is_taxable' => 'Taxable',
            'is_active' => 'Active',
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
     * Gets query for [[InvoiceItems]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getInvoiceItems()
    {
        return $this->hasMany(InvoiceItem::class, ['product_id' => 'id']);
    }

    /**
     * Gets query for [[EstimateItems]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEstimateItems()
    {
        return $this->hasMany(EstimateItem::class, ['product_id' => 'id']);
    }

    /**
     * Get type options for dropdown
     *
     * @return array
     */
    public static function getTypeOptions()
    {
        return [
            self::TYPE_SERVICE => 'Service',
            self::TYPE_PRODUCT => 'Product',
            self::TYPE_NON_INVENTORY => 'Non-inventory Item',
        ];
    }

    /**
     * Get unit options for dropdown
     *
     * @return array
     */
    public static function getUnitOptions()
    {
        return [
            'each' => 'Each',
            'hour' => 'Hour',
            'piece' => 'Piece',
            'box' => 'Box',
            'kg' => 'Kilogram',
            'lb' => 'Pound',
            'meter' => 'Meter',
            'foot' => 'Foot',
            'sqft' => 'Square Foot',
            'sqm' => 'Square Meter',
            'month' => 'Month',
            'year' => 'Year',
        ];
    }

    /**
     * Get category options
     *
     * @return array
     */
    public static function getCategoryOptions()
    {
        return [
            'General' => 'General',
            'Consulting' => 'Consulting',
            'Software' => 'Software',
            'Hardware' => 'Hardware',
			'Installation' => 'Installation',
			'Development' => 'Development',
            'Maintenance' => 'Maintenance',
			'Website' => 'Website',
			'Hosting' => 'Hosting',
            'Training' => 'Training',
            'Support' => 'Support',
            'Other' => 'Other',
        ];
    }

    /**
     * Get type label
     *
     * @return string
     */
    public function getTypeLabel()
    {
        $types = self::getTypeOptions();
        return isset($types[$this->type]) ? $types[$this->type] : $this->type;
    }

    /**
     * Get unit label
     *
     * @return string
     */
    public function getUnitLabel()
    {
        $units = self::getUnitOptions();
        return isset($units[$this->unit]) ? $units[$this->unit] : $this->unit;
    }

    /**
     * Format price for display
     *
     * @return string
     */
    public function getFormattedPrice()
    {
        return '$' . number_format($this->price, 2);
    }

    /**
     * Format cost for display
     *
     * @return string
     */
    public function getFormattedCost()
    {
        return '$' . number_format($this->cost, 2);
    }

    /**
     * Get profit margin
     *
     * @return float
     */
    public function getProfitMargin()
    {
        if ($this->price == 0) {
            return 0;
        }
        return (($this->price - $this->cost) / $this->price) * 100;
    }

    /**
     * Find active products by company
     *
     * @param int $companyId
     * @return \yii\db\ActiveQuery
     */
    public static function findActiveByCompany($companyId)
    {
        return static::find()
            ->where(['company_id' => $companyId, 'is_active' => true]);
    }

    /**
     * Search products
     *
     * @param string $term
     * @param int $companyId
     * @return \yii\db\ActiveQuery
     */
    public static function search($term, $companyId)
    {
        return static::find()
            ->where(['company_id' => $companyId, 'is_active' => true])
            ->andWhere(['or',
                ['like', 'name', $term],
                ['like', 'description', $term],
                ['like', 'sku', $term],
                ['like', 'category', $term],
            ]);
    }

    /**
     * Get display name for dropdowns
     *
     * @return string
     */
    public function getDisplayName()
    {
        $name = $this->name;
        if ($this->sku) {
            $name .= ' (' . $this->sku . ')';
        }
        return $name;
    }

    /**
     * Get full description including price
     *
     * @return string
     */
    public function getFullDescription()
    {
        $desc = $this->name;
        if ($this->description) {
            $desc .= ' - ' . $this->description;
        }
        $desc .= ' (' . $this->getFormattedPrice() . ' per ' . $this->getUnitLabel() . ')';
        return $desc;
    }
}