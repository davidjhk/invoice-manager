<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "jdosa_update_logs".
 *
 * @property int $id
 * @property string $entity_type
 * @property int $entity_id
 * @property string $action
 * @property int $user_id
 * @property string $user_name
 * @property string|null $details
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property string $created_at
 */
class UpdateLog extends ActiveRecord
{
    // Entity types
    const ENTITY_INVOICE = 'invoice';
    const ENTITY_ESTIMATE = 'estimate';
    const ENTITY_CUSTOMER = 'customer';
    const ENTITY_PRODUCT = 'product';
    
    // Actions
    const ACTION_CREATE = 'create';
    const ACTION_UPDATE = 'update';
    const ACTION_DELETE = 'delete';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'jdosa_update_logs';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['entity_type', 'entity_id', 'action', 'user_id', 'user_name'], 'required'],
            [['entity_id', 'user_id'], 'integer'],
            [['details', 'user_agent'], 'string'],
            [['created_at'], 'safe'],
            [['entity_type'], 'string', 'max' => 50],
            [['action'], 'string', 'max' => 20],
            [['user_name'], 'string', 'max' => 255],
            [['ip_address'], 'string', 'max' => 45],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'entity_type' => 'Entity Type',
            'entity_id' => 'Entity ID',
            'action' => 'Action',
            'user_id' => 'User ID',
            'user_name' => 'User Name',
            'details' => 'Details',
            'ip_address' => 'IP Address',
            'user_agent' => 'User Agent',
            'created_at' => 'Created At',
        ];
    }

    /**
     * Get the latest update log for a specific entity
     * 
     * @param string $entityType
     * @param int $entityId
     * @return UpdateLog|null
     */
    public static function getLatestUpdate($entityType, $entityId)
    {
        return static::find()
            ->where(['entity_type' => $entityType, 'entity_id' => $entityId])
            ->orderBy(['created_at' => SORT_DESC])
            ->one();
    }

    /**
     * Log an entity update
     * 
     * @param string $entityType
     * @param int $entityId
     * @param string $action
     * @param string|null $details
     * @return bool
     */
    public static function logUpdate($entityType, $entityId, $action, $details = null)
    {
        $user = Yii::$app->user->identity;
        
        if (!$user) {
            return false;
        }
        
        $log = new static();
        $log->entity_type = $entityType;
        $log->entity_id = $entityId;
        $log->action = $action;
        $log->user_id = $user->id;
        $log->user_name = $user->username;
        $log->details = $details;
        $log->ip_address = Yii::$app->request->userIP;
        $log->user_agent = Yii::$app->request->userAgent;
        
        return $log->save();
    }

    /**
     * Get formatted action label
     * 
     * @return string
     */
    public function getActionLabel()
    {
        switch ($this->action) {
            case self::ACTION_CREATE:
                return Yii::t('app', 'Created');
            case self::ACTION_UPDATE:
                return Yii::t('app', 'Updated');
            case self::ACTION_DELETE:
                return Yii::t('app', 'Deleted');
            default:
                return $this->action;
        }
    }
}