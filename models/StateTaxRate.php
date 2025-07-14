<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "jdosa_state_tax_rates".
 *
 * @property int $id
 * @property string $state_code
 * @property string $country_code
 * @property float $base_rate
 * @property bool $has_local_tax
 * @property float $average_total_rate
 * @property float|null $revenue_threshold
 * @property int|null $transaction_threshold
 * @property bool $is_active
 * @property string $effective_date
 * @property string|null $notes
 * @property string $created_at
 * @property string $updated_at
 *
 * @property State $state
 * @property Country $country
 */
class StateTaxRate extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'state_tax_rates';
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
            [['state_code', 'country_code', 'base_rate', 'average_total_rate', 'effective_date'], 'required'],
            [['base_rate', 'average_total_rate', 'revenue_threshold'], 'number', 'min' => 0],
            [['base_rate', 'average_total_rate'], 'number', 'max' => 99.9999],
            [['transaction_threshold'], 'integer', 'min' => 0],
            [['has_local_tax', 'is_active'], 'boolean'],
            [['effective_date'], 'date', 'format' => 'php:Y-m-d'],
            [['notes'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['state_code'], 'string', 'max' => 2],
            [['country_code'], 'string', 'max' => 2],
            [['state_code', 'country_code'], 'exist', 'skipOnError' => true, 'targetClass' => State::class, 'targetAttribute' => ['state_code' => 'state_code', 'country_code' => 'country_code']],
            [['state_code', 'country_code', 'effective_date'], 'unique', 'targetAttribute' => ['state_code', 'country_code', 'effective_date']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'state_code' => Yii::t('app', 'State Code'),
            'country_code' => Yii::t('app', 'Country Code'),
            'base_rate' => Yii::t('app', 'Base Tax Rate (%)'),
            'has_local_tax' => Yii::t('app', 'Has Local Tax'),
            'average_total_rate' => Yii::t('app', 'Average Total Rate (%)'),
            'revenue_threshold' => Yii::t('app', 'Revenue Threshold ($)'),
            'transaction_threshold' => Yii::t('app', 'Transaction Threshold'),
            'is_active' => Yii::t('app', 'Is Active'),
            'effective_date' => Yii::t('app', 'Effective Date'),
            'notes' => Yii::t('app', 'Notes'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    /**
     * Gets query for [[State]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getState()
    {
        return $this->hasOne(State::class, ['state_code' => 'state_code', 'country_code' => 'country_code']);
    }

    /**
     * Gets query for [[Country]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCountry()
    {
        return $this->hasOne(Country::class, ['country_code' => 'country_code']);
    }

    /**
     * Check if StateTaxRate table exists in database
     *
     * @return bool
     */
    public static function tableExists()
    {
        try {
            static::getDb()->createCommand("SHOW TABLES LIKE 'state_tax_rates'")->queryOne();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get current active tax rate for a state
     *
     * @param string $stateCode
     * @param string $countryCode
     * @param string|null $date Date to check (default: today)
     * @return static|null
     */
    public static function getCurrentRate($stateCode, $countryCode = 'US', $date = null)
    {
        // Check if table exists first
        if (!static::tableExists()) {
            return null;
        }

        if ($date === null) {
            $date = date('Y-m-d');
        }

        try {
            return static::find()
                ->where(['state_code' => $stateCode, 'country_code' => $countryCode, 'is_active' => true])
                ->andWhere(['<=', 'effective_date', $date])
                ->orderBy(['effective_date' => SORT_DESC])
                ->one();
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get tax rate for calculation
     *
     * @param string $stateCode
     * @param string $countryCode
     * @param bool $useLocalTax
     * @param string|null $date
     * @return float
     */
    public static function getTaxRate($stateCode, $countryCode = 'US', $useLocalTax = true, $date = null)
    {
        $taxRate = static::getCurrentRate($stateCode, $countryCode, $date);
        
        if (!$taxRate) {
            return 0.0;
        }

        if ($useLocalTax && $taxRate->has_local_tax) {
            return (float) $taxRate->average_total_rate;
        }

        return (float) $taxRate->base_rate;
    }

    /**
     * Get nexus information for a state
     *
     * @param string $stateCode
     * @param string $countryCode
     * @param string|null $date
     * @return array|null
     */
    public static function getNexusInfo($stateCode, $countryCode = 'US', $date = null)
    {
        $taxRate = static::getCurrentRate($stateCode, $countryCode, $date);
        
        if (!$taxRate) {
            return null;
        }

        return [
            'revenue_threshold' => $taxRate->revenue_threshold,
            'transaction_threshold' => $taxRate->transaction_threshold,
            'has_sales_tax' => $taxRate->base_rate > 0,
        ];
    }

    /**
     * Check if economic nexus threshold is met
     *
     * @param string $stateCode
     * @param string $countryCode
     * @param float $annualRevenue
     * @param int $annualTransactions
     * @param string|null $date
     * @return bool
     */
    public static function hasEconomicNexus($stateCode, $countryCode = 'US', $annualRevenue = 0, $annualTransactions = 0, $date = null)
    {
        $nexusInfo = static::getNexusInfo($stateCode, $countryCode, $date);
        
        if (!$nexusInfo) {
            return false;
        }

        // Check revenue threshold
        if ($nexusInfo['revenue_threshold'] && $annualRevenue >= $nexusInfo['revenue_threshold']) {
            return true;
        }

        // Check transaction threshold
        if ($nexusInfo['transaction_threshold'] && $annualTransactions >= $nexusInfo['transaction_threshold']) {
            return true;
        }

        return false;
    }

    /**
     * Get all states with no sales tax
     *
     * @param string $countryCode
     * @param string|null $date
     * @return array
     */
    public static function getNoSalesTaxStates($countryCode = 'US', $date = null)
    {
        // Check if table exists first
        if (!static::tableExists()) {
            return [];
        }

        if ($date === null) {
            $date = date('Y-m-d');
        }

        try {
            return static::find()
                ->select(['state_code'])
                ->where(['country_code' => $countryCode, 'is_active' => true, 'base_rate' => 0])
                ->andWhere(['<=', 'effective_date', $date])
                ->groupBy(['state_code'])
                ->column();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get detailed tax information for a state
     *
     * @param string $stateCode
     * @param string $countryCode
     * @param string|null $date
     * @return array|null
     */
    public static function getStateTaxInfo($stateCode, $countryCode = 'US', $date = null)
    {
        $taxRate = static::getCurrentRate($stateCode, $countryCode, $date);
        
        if (!$taxRate) {
            return null;
        }

        return [
            'state_code' => $taxRate->state_code,
            'country_code' => $taxRate->country_code,
            'base_rate' => (float) $taxRate->base_rate,
            'has_local_tax' => (bool) $taxRate->has_local_tax,
            'average_total_rate' => (float) $taxRate->average_total_rate,
            'revenue_threshold' => $taxRate->revenue_threshold ? (float) $taxRate->revenue_threshold : null,
            'transaction_threshold' => $taxRate->transaction_threshold ? (int) $taxRate->transaction_threshold : null,
            'effective_date' => $taxRate->effective_date,
            'notes' => $taxRate->notes,
        ];
    }

    /**
     * Get all active tax rates by country
     *
     * @param string $countryCode
     * @param string|null $date
     * @return array
     */
    public static function getActiveRatesByCountry($countryCode = 'US', $date = null)
    {
        // Check if table exists first
        if (!static::tableExists()) {
            return [];
        }

        if ($date === null) {
            $date = date('Y-m-d');
        }

        try {
            $rates = static::find()
                ->where(['country_code' => $countryCode, 'is_active' => true])
                ->andWhere(['<=', 'effective_date', $date])
                ->orderBy(['state_code' => SORT_ASC, 'effective_date' => SORT_DESC])
                ->all();

            $result = [];
            foreach ($rates as $rate) {
                if (!isset($result[$rate->state_code])) {
                    $result[$rate->state_code] = [
                        'state_code' => $rate->state_code,
                        'base_rate' => (float) $rate->base_rate,
                        'has_local_tax' => (bool) $rate->has_local_tax,
                        'average_total_rate' => (float) $rate->average_total_rate,
                        'revenue_threshold' => $rate->revenue_threshold ? (float) $rate->revenue_threshold : null,
                        'transaction_threshold' => $rate->transaction_threshold ? (int) $rate->transaction_threshold : null,
                    ];
                }
            }

            return $result;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Format rate as percentage string
     *
     * @param bool $useLocalTax
     * @param int $decimals
     * @return string
     */
    public function getFormattedRate($useLocalTax = true, $decimals = 2)
    {
        $rate = $useLocalTax && $this->has_local_tax ? $this->average_total_rate : $this->base_rate;
        return number_format($rate, $decimals) . '%';
    }

    /**
     * Get display name for the tax rate
     *
     * @return string
     */
    public function getDisplayName()
    {
        $stateName = $this->state ? $this->state->state_name : $this->state_code;
        return $stateName . ' (' . $this->getFormattedRate(false) . '%)';
    }
}