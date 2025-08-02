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
 * @property int|null $category_id
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
 * @property ProductCategory $productCategory
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
            [['category_id'], 'integer'],
            [['category_id'], 'exist', 'skipOnError' => true, 'targetClass' => ProductCategory::class, 'targetAttribute' => ['category_id' => 'id']],
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
            'id' => Yii::t('app/product', 'ID'),
            'company_id' => Yii::t('app/product', 'Company ID'),
            'name' => Yii::t('app/product', 'Product/Service Name'),
            'description' => Yii::t('app/product', 'Description'),
            'type' => Yii::t('app/product', 'Type'),
            'category' => Yii::t('app/product', 'Category (Legacy)'),
            'category_id' => Yii::t('app/product', 'Category'),
            'sku' => Yii::t('app/product', 'SKU'),
            'unit' => Yii::t('app/product', 'Unit'),
            'price' => Yii::t('app/product', 'Price'),
            'cost' => Yii::t('app/product', 'Cost'),
            'is_taxable' => Yii::t('app/product', 'Taxable'),
            'is_active' => Yii::t('app/product', 'Active'),
            'created_at' => Yii::t('app/product', 'Created At'),
            'updated_at' => Yii::t('app/product', 'Updated At'),
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
     * Gets query for [[ProductCategory]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProductCategory()
    {
        return $this->hasOne(ProductCategory::class, ['id' => 'category_id']);
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
     * Get category options for dropdown (dynamic from database)
     *
     * @param int $companyId
     * @return array
     */
    public static function getCategoryOptions($companyId = null)
    {
        if (!$companyId) {
            // Try to get from current user's company
            $user = Yii::$app->user->identity;
            if ($user && method_exists($user, 'getCompanyId')) {
                $companyId = $user->getCompanyId();
            }
        }
        
        if (!$companyId) {
            return [];
        }
        
        return ProductCategory::getCategoryOptions($companyId);
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
     * Get category label
     *
     * @return string
     */
    public function getCategoryLabel()
    {
        if ($this->productCategory) {
            return $this->productCategory->name;
        }
        
        // Fallback to legacy category field
        return $this->category ?: 'Uncategorized';
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
            ->joinWith('productCategory')
            ->where(['jdosa_products.company_id' => $companyId, 'jdosa_products.is_active' => true])
            ->andWhere(['or',
                ['like', 'jdosa_products.name', $term],
                ['like', 'jdosa_products.description', $term],
                ['like', 'jdosa_products.sku', $term],
                ['like', 'jdosa_products.category', $term], // Legacy category field
                ['like', 'jdosa_product_categories.name', $term], // New category table
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

    /**
     * {@inheritdoc}
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        
        // Log the update
        $action = $insert ? UpdateLog::ACTION_CREATE : UpdateLog::ACTION_UPDATE;
        UpdateLog::logUpdate(UpdateLog::ENTITY_PRODUCT, $this->id, $action);
    }

    /**
     * {@inheritdoc}
     */
    public function afterDelete()
    {
        parent::afterDelete();
        
        // Log the deletion
        UpdateLog::logUpdate(UpdateLog::ENTITY_PRODUCT, $this->id, UpdateLog::ACTION_DELETE);
    }
}