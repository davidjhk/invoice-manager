<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "jdosa_plans".
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property float $price
 * @property string|null $stripe_plan_id
 * @property string|null $paypal_plan_id
 * @property array|null $features
 * @property bool $is_active
 * @property int $sort_order
 * @property string $created_at
 * @property string $updated_at
 * @property int $max_subusers
 *
 * @property UserSubscription[] $userSubscriptions
 */
class Plan extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'jdosa_plans';
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
            [['name', 'price'], 'required'],
            [['description'], 'string'],
            [['price'], 'number', 'min' => 0],
            [['features'], 'safe'],
            [['is_active'], 'boolean'],
            [['sort_order'], 'integer', 'min' => 0],
            [['max_subusers'], 'integer', 'min' => -1],
            [['created_at', 'updated_at'], 'safe'],
            [['name'], 'string', 'max' => 50],
            [['stripe_plan_id', 'paypal_plan_id'], 'string', 'max' => 100],
            [['name'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Plan Name',
            'description' => 'Description',
            'price' => 'Monthly Price',
            'stripe_plan_id' => 'Stripe Plan ID',
            'paypal_plan_id' => 'PayPal Plan ID',
            'features' => 'Features',
            'is_active' => 'Active',
            'sort_order' => 'Sort Order',
            'max_subusers' => 'Maximum Subusers',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[UserSubscriptions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserSubscriptions()
    {
        return $this->hasMany(UserSubscription::class, ['plan_id' => 'id']);
    }

    /**
     * Get active plans ordered by sort order
     *
     * @return \yii\db\ActiveQuery
     */
    public static function getActivePlans()
    {
        return static::find()
            ->where(['is_active' => true])
            ->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC]);
    }

    /**
     * Get plan features as array
     *
     * @return array
     */
    public function getFeaturesArray()
    {
        if (is_string($this->features)) {
            return json_decode($this->features, true) ?: [];
        }
        return is_array($this->features) ? $this->features : [];
    }

    /**
     * Get plan features for display (alias for getFeaturesArray)
     *
     * @return array
     */
    public function getFeatures()
    {
        return $this->getFeaturesArray();
    }

    /**
     * Set plan features from array
     *
     * @param array $features
     */
    public function setFeaturesArray($features)
    {
        $this->features = json_encode($features);
    }

    /**
     * Get formatted price
     *
     * @return string
     */
    public function getFormattedPrice()
    {
        if ($this->price == 0) {
            return Yii::t('app', 'Free');
        }
        return '$' . number_format($this->price, 2);
    }

    /**
     * Check if plan has a specific feature
     *
     * @param string $featureKey
     * @return bool
     */
    public function hasFeature($featureKey)
    {
        $features = $this->getFeaturesArray();
        return isset($features[$featureKey]);
    }

    /**
     * Get feature value
     *
     * @param string $featureKey
     * @param mixed $default
     * @return mixed
     */
    public function getFeature($featureKey, $default = null)
    {
        $features = $this->getFeaturesArray();
        return $features[$featureKey] ?? $default;
    }

    /**
     * Get plan by name
     *
     * @param string $name
     * @return static|null
     */
    public static function findByName($name)
    {
        return static::findOne(['name' => $name, 'is_active' => true]);
    }

    /**
     * Get monthly invoice limit for this plan
     *
     * @return int|null Null means unlimited
     */
    public function getMonthlyInvoiceLimit()
    {
        return $this->monthly_invoice_limit;
    }

    /**
     * Check if plan has unlimited invoices
     *
     * @return bool
     */
    public function hasUnlimitedInvoices()
    {
        return $this->getMonthlyInvoiceLimit() === null;
    }

    /**
     * Get feature limits for this plan
     *
     * @return array
     */
    public function getFeatureLimits()
    {
        return [
            'monthly_invoices' => $this->monthly_invoice_limit,
            'monthly_estimates' => $this->monthly_estimate_limit,
            'companies' => $this->max_companies,
            'storage_mb' => $this->storage_limit_mb,
            'api_access' => $this->can_use_api,
            'import' => $this->can_use_import,
            'custom_templates' => $this->can_use_custom_templates,
            'ai_helper' => $this->can_use_ai_helper,
            'max_subusers' => $this->max_subusers,
        ];
    }

    /**
     * Get specific feature limit
     *
     * @param string $feature
     * @return mixed
     */
    public function getFeatureLimit($feature)
    {
        $limits = $this->getFeatureLimits();
        return $limits[$feature] ?? null;
    }

    /**
     * Check if plan allows import functionality
     *
     * @return bool
     */
    public function canUseImport()
    {
        return (bool) $this->can_use_import;
    }

    /**
     * Check if plan allows API access
     *
     * @return bool
     */
    public function canUseApi()
    {
        return (bool) $this->can_use_api;
    }

    /**
     * Check if plan allows custom templates
     *
     * @return bool
     */
    public function canUseCustomTemplates()
    {
        return (bool) $this->can_use_custom_templates;
    }

    /**
     * Get storage limit in MB
     *
     * @return int|null Null means unlimited
     */
    public function getStorageLimit()
    {
        return $this->storage_limit_mb;
    }

    /**
     * Get maximum companies allowed
     *
     * @return int|null Null means unlimited
     */
    public function getMaxCompanies()
    {
        return $this->max_companies;
    }

    /**
     * Get monthly estimate limit for this plan
     *
     * @return int|null Null means unlimited
     */
    public function getMonthlyEstimateLimit()
    {
        return $this->monthly_estimate_limit;
    }

    /**
     * Check if plan has unlimited estimates
     *
     * @return bool
     */
    public function hasUnlimitedEstimates()
    {
        return $this->getMonthlyEstimateLimit() === null;
    }

    /**
     * Check if plan allows AI Helper functionality
     *
     * @return bool
     */
    public function canUseAiHelper()
    {
        return (bool) $this->can_use_ai_helper;
    }

    /**
     * Get maximum subusers allowed
     *
     * @return int|null Null means unlimited, 0 means not allowed
     */
    public function getMaxSubusers()
    {
        return $this->max_subusers;
    }

    /**
     * Check if plan has unlimited subusers
     *
     * @return bool
     */
    public function hasUnlimitedSubusers()
    {
        return $this->getMaxSubusers() === -1;
    }

    /**
     * Check if plan allows subuser functionality
     *
     * @return bool
     */
    public function canUseSubusers()
    {
        return $this->getMaxSubusers() > 0 || $this->hasUnlimitedSubusers();
    }

    /**
     * Before save event
     *
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            // Ensure features is properly encoded
            if (is_array($this->features)) {
                $this->features = json_encode($this->features);
            }
            return true;
        }
        return false;
    }

    /**
     * After find event
     */
    public function afterFind()
    {
        parent::afterFind();
        // Decode features JSON if it's a string
        if (is_string($this->features) && !empty($this->features)) {
            $decoded = json_decode($this->features, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $this->features = $decoded;
            }
        }
    }
}