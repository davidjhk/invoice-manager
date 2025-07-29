<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "admin_settings".
 *
 * @property int $id
 * @property string $setting_key
 * @property string|null $setting_value
 * @property string|null $description
 * @property string $created_at
 * @property string $updated_at
 */
class AdminSettings extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%admin_settings}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['setting_key'], 'required'],
            [['setting_value', 'description'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['setting_key'], 'string', 'max' => 100],
            [['setting_key'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'setting_key' => 'Setting Key',
            'setting_value' => 'Setting Value',
            'description' => 'Description',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Get a setting value by key
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function getSetting($key, $default = null)
    {
        $setting = static::findOne(['setting_key' => $key]);
        return $setting ? $setting->setting_value : $default;
    }

    /**
     * Set a setting value by key
     * @param string $key
     * @param mixed $value
     * @param string $description
     * @return bool
     */
    public static function setSetting($key, $value, $description = null)
    {
        $setting = static::findOne(['setting_key' => $key]);
        if (!$setting) {
            $setting = new static();
            $setting->setting_key = $key;
        }
        $setting->setting_value = $value;
        if ($description !== null) {
            $setting->description = $description;
        }
        return $setting->save();
    }

    /**
     * Check if signup is allowed
     * @return bool
     */
    public static function isSignupAllowed()
    {
        $value = static::getSetting('allow_signup', '0');
        return $value === '1' || $value === 1 || $value === true;
    }

    /**
     * Check if site is in maintenance mode
     * @return bool
     */
    public static function isMaintenanceMode()
    {
        return (bool) static::getSetting('site_maintenance', false);
    }

    /**
     * Get the current AI model for OpenRouter
     * @return string
     */
    public static function getAiModel()
    {
        $defaultModel = Yii::$app->params['defaultAiModel'] ?? 'anthropic/claude-3.5-sonnet';
        return static::getSetting('ai_model', $defaultModel);
    }

    /**
     * Set the AI model for OpenRouter
     * @param string $model
     * @return bool
     */
    public static function setAiModel($model)
    {
        return static::setSetting('ai_model', $model, 'Selected AI model for OpenRouter API');
    }

    /**
     * Get available AI models from params
     * @return array
     */
    public static function getAvailableAiModels()
    {
        return Yii::$app->params['openRouterModels'] ?? [];
    }
}