<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "states".
 *
 * @property int $id
 * @property string $state_code
 * @property string $state_name
 * @property string $country_code
 * @property string $created_at
 * @property string $updated_at
 */
class State extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'states';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['state_code', 'state_name'], 'required'],
            [['created_at', 'updated_at'], 'safe'],
            [['state_code'], 'string', 'max' => 2],
            [['state_name'], 'string', 'max' => 50],
            [['country_code'], 'string', 'max' => 2],
            [['state_code', 'country_code'], 'unique', 'targetAttribute' => ['state_code', 'country_code']],
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
            'state_name' => Yii::t('app', 'State Name'),
            'country_code' => Yii::t('app', 'Country Code'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    /**
     * Get relation to Country model
     * @return \yii\db\ActiveQuery
     */
    public function getCountry()
    {
        return $this->hasOne(Country::class, ['country_code' => 'country_code']);
    }

    /**
     * Get all US states as array for dropdown
     * @return array
     */
    public static function getUsStateList()
    {
        return static::find()
            ->select(['state_code', 'state_name'])
            ->where(['country_code' => 'US'])
            ->orderBy('state_name')
            ->indexBy('state_code')
            ->column();
    }

    /**
     * Get states by country code
     * @param string $countryCode
     * @return array
     */
    public static function getStateListByCountry($countryCode = 'US')
    {
        return static::find()
            ->select(['state_code', 'state_name'])
            ->where(['country_code' => $countryCode])
            ->orderBy('state_name')
            ->indexBy('state_code')
            ->column();
    }

    /**
     * Get state name by code and country
     * @param string $stateCode
     * @param string $countryCode
     * @return string|null
     */
    public static function getStateName($stateCode, $countryCode = 'US')
    {
        $state = static::findOne(['state_code' => $stateCode, 'country_code' => $countryCode]);
        return $state ? $state->state_name : null;
    }

    /**
     * Get all states with country info
     * @return array
     */
    public static function getAllStatesGrouped()
    {
        $states = static::find()
            ->with('country')
            ->orderBy(['country_code' => SORT_ASC, 'state_name' => SORT_ASC])
            ->all();
            
        $grouped = [];
        foreach ($states as $state) {
            $countryName = $state->country ? $state->country->country_name : $state->country_code;
            $grouped[$countryName][$state->state_code] = $state->state_name;
        }
        
        return $grouped;
    }

    /**
     * Check if state exists for country
     * @param string $stateCode
     * @param string $countryCode
     * @return bool
     */
    public static function existsForCountry($stateCode, $countryCode)
    {
        return static::find()
            ->where(['state_code' => $stateCode, 'country_code' => $countryCode])
            ->exists();
    }
}