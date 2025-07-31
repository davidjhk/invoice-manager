<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "jdosa_user_company_access".
 *
 * @property int $id
 * @property int $user_id
 * @property int $company_id
 * @property int $granted_by_user_id
 * @property string $created_at
 * @property string $updated_at
 *
 * @property User $user
 * @property Company $company
 * @property User $grantedByUser
 */
class UserCompanyAccess extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'jdosa_user_company_access';
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
            [['user_id', 'company_id', 'granted_by_user_id'], 'required'],
            [['user_id', 'company_id', 'granted_by_user_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['user_id', 'company_id'], 'unique', 'targetAttribute' => ['user_id', 'company_id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
            [['company_id'], 'exist', 'skipOnError' => true, 'targetClass' => Company::class, 'targetAttribute' => ['company_id' => 'id']],
            [['granted_by_user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['granted_by_user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'company_id' => 'Company ID',
            'granted_by_user_id' => 'Granted By User ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
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
     * Gets query for [[GrantedByUser]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGrantedByUser()
    {
        return $this->hasOne(User::class, ['id' => 'granted_by_user_id']);
    }

    /**
     * Grant access to a company for a subuser
     *
     * @param int $userId
     * @param int $companyId
     * @param int $grantedByUserId
     * @return bool
     */
    public static function grantAccess($userId, $companyId, $grantedByUserId)
    {
        // Check if access already exists
        $existing = self::find()
            ->where(['user_id' => $userId, 'company_id' => $companyId])
            ->one();
            
        if ($existing) {
            return true; // Already has access
        }
        
        $access = new self();
        $access->user_id = $userId;
        $access->company_id = $companyId;
        $access->granted_by_user_id = $grantedByUserId;
        
        return $access->save();
    }

    /**
     * Revoke access to a company for a subuser
     *
     * @param int $userId
     * @param int $companyId
     * @return bool
     */
    public static function revokeAccess($userId, $companyId)
    {
        $access = self::find()
            ->where(['user_id' => $userId, 'company_id' => $companyId])
            ->one();
            
        if ($access) {
            return $access->delete() !== false;
        }
        
        return true; // Already doesn't have access
    }

    /**
     * Check if user has access to company
     *
     * @param int $userId
     * @param int $companyId
     * @return bool
     */
    public static function hasAccess($userId, $companyId)
    {
        return self::find()
            ->where(['user_id' => $userId, 'company_id' => $companyId])
            ->exists();
    }
}