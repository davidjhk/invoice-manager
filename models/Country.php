<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "countries".
 *
 * @property int $id
 * @property string $country_code
 * @property string $country_name
 * @property string $created_at
 * @property string $updated_at
 */
class Country extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'countries';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['country_code', 'country_name'], 'required'],
            [['created_at', 'updated_at'], 'safe'],
            [['country_code'], 'string', 'max' => 2],
            [['country_name'], 'string', 'max' => 100],
            [['country_code'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'country_code' => Yii::t('app', 'Country Code'),
            'country_name' => Yii::t('app', 'Country Name'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    /**
     * Get all countries as array for dropdown
     * @return array
     */
    public static function getCountryList()
    {
        return static::find()
            ->select(['country_code', 'country_name'])
            ->orderBy('country_name')
            ->indexBy('country_code')
            ->column();
    }

    /**
     * Get country name by code
     * @param string $code
     * @return string|null
     */
    public static function getCountryName($code)
    {
        $country = static::findOne(['country_code' => $code]);
        return $country ? $country->country_name : null;
    }

    /**
     * Get commonly used countries at the top
     * @return array
     */
    public static function getCountryListWithCommon()
    {
        $commonCountries = ['US', 'KR', 'JP', 'CN', 'GB', 'DE', 'FR', 'CA', 'AU'];
        
        $common = static::find()
            ->select(['country_code', 'country_name'])
            ->where(['country_code' => $commonCountries])
            ->orderBy('country_name')
            ->indexBy('country_code')
            ->column();
            
        $others = static::find()
            ->select(['country_code', 'country_name'])
            ->where(['not in', 'country_code', $commonCountries])
            ->orderBy('country_name')
            ->indexBy('country_code')
            ->column();
            
        $result = [];
        if (!empty($common)) {
            $result[Yii::t('app', 'Common Countries')] = $common;
        }
        if (!empty($others)) {
            $result[Yii::t('app', 'Other Countries')] = $others;
        }
        
        return $result;
    }
}