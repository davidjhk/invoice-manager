<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "jdosa_product_categories".
 *
 * @property int $id
 * @property int $company_id
 * @property string $name
 * @property string|null $description
 * @property bool $is_active
 * @property int $sort_order
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Company $company
 * @property Product[] $products
 */
class ProductCategory extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'jdosa_product_categories';
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
            [['company_id', 'sort_order'], 'integer'],
            [['description'], 'string'],
            [['is_active'], 'boolean'],
            [['created_at', 'updated_at'], 'safe'],
            [['name'], 'string', 'max' => 100],
            [['name'], 'unique', 'targetAttribute' => ['company_id', 'name'], 'message' => 'Category name must be unique within the company.'],
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
            'name' => 'Category Name',
            'description' => 'Description',
            'is_active' => 'Active',
            'sort_order' => 'Sort Order',
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
     * Gets query for [[Products]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProducts()
    {
        return $this->hasMany(Product::class, ['category_id' => 'id']);
    }

    /**
     * Find active categories by company
     *
     * @param int $companyId
     * @return \yii\db\ActiveQuery
     */
    public static function findActiveByCompany($companyId)
    {
        return static::find()
            ->where(['company_id' => $companyId, 'is_active' => true])
            ->orderBy('sort_order ASC, name ASC');
    }

    /**
     * Get category options for dropdown
     *
     * @param int $companyId
     * @return array
     */
    public static function getCategoryOptions($companyId)
    {
        $categories = static::findActiveByCompany($companyId)->all();
        $options = [];
        
        foreach ($categories as $category) {
            $options[$category->id] = $category->name;
        }
        
        return $options;
    }

    /**
     * Get category names for dropdown (for backward compatibility)
     *
     * @param int $companyId
     * @return array
     */
    public static function getCategoryNameOptions($companyId)
    {
        $categories = static::findActiveByCompany($companyId)->all();
        $options = [];
        
        foreach ($categories as $category) {
            $options[$category->name] = $category->name;
        }
        
        return $options;
    }

    /**
     * Get products count for this category
     *
     * @return int
     */
    public function getProductsCount()
    {
        return $this->getProducts()->count();
    }

    /**
     * Check if category can be deleted
     *
     * @return bool
     */
    public function canDelete()
    {
        return $this->getProductsCount() === 0;
    }

    /**
     * Before delete - check if category is in use
     *
     * @return bool
     */
    public function beforeDelete()
    {
        if (!$this->canDelete()) {
            $this->addError('name', 'Cannot delete category that is assigned to products.');
            return false;
        }
        
        return parent::beforeDelete();
    }

    /**
     * Get next sort order for new category
     *
     * @param int $companyId
     * @return int
     */
    public static function getNextSortOrder($companyId)
    {
        $maxOrder = static::find()
            ->where(['company_id' => $companyId])
            ->max('sort_order');
        
        return $maxOrder ? $maxOrder + 1 : 1;
    }

    /**
     * Before save - set sort order if not provided
     *
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if ($insert && !$this->sort_order) {
            $this->sort_order = static::getNextSortOrder($this->company_id);
        }
        
        return parent::beforeSave($insert);
    }
}