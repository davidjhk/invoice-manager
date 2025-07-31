<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "jdosa_subuser_company_access".
 *
 * @property int $id
 * @property int $subuser_id
 * @property int $company_id
 * @property string $created_at
 * @property int $created_by
 *
 * @property User $subuser
 * @property Company $company
 * @property User $createdBy
 */
class SubuserCompanyAccess extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%jdosa_subuser_company_access}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['subuser_id', 'company_id', 'created_by'], 'required'],
            [['subuser_id', 'company_id', 'created_by'], 'integer'],
            [['created_at'], 'safe'],
            [['subuser_id', 'company_id'], 'unique', 'targetAttribute' => ['subuser_id', 'company_id']],
            [['subuser_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['subuser_id' => 'id']],
            [['company_id'], 'exist', 'skipOnError' => true, 'targetClass' => Company::class, 'targetAttribute' => ['company_id' => 'id']],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['created_by' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'subuser_id' => 'Subuser ID',
            'company_id' => 'Company ID',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
        ];
    }

    /**
     * Gets query for [[Subuser]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSubuser()
    {
        return $this->hasOne(User::class, ['id' => 'subuser_id']);
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
     * Gets query for [[CreatedBy]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }

    /**
     * Grant access to a company for a subuser
     *
     * @param int $subuserId
     * @param int $companyId
     * @param int $createdBy
     * @return bool
     */
    public static function grantAccess($subuserId, $companyId, $createdBy)
    {
        // Check if access already exists
        $existing = static::find()
            ->where(['subuser_id' => $subuserId, 'company_id' => $companyId])
            ->one();

        if ($existing) {
            return true; // Already has access
        }

        $access = new static();
        $access->subuser_id = $subuserId;
        $access->company_id = $companyId;
        $access->created_by = $createdBy;

        return $access->save();
    }

    /**
     * Revoke access to a company for a subuser
     *
     * @param int $subuserId
     * @param int $companyId
     * @return bool
     */
    public static function revokeAccess($subuserId, $companyId)
    {
        $access = static::find()
            ->where(['subuser_id' => $subuserId, 'company_id' => $companyId])
            ->one();

        if ($access) {
            return $access->delete() !== false;
        }

        return true; // Nothing to revoke
    }

    /**
     * Check if a subuser has access to a company
     *
     * @param int $subuserId
     * @param int $companyId
     * @return bool
     */
    public static function hasAccess($subuserId, $companyId)
    {
        return static::find()
            ->where(['subuser_id' => $subuserId, 'company_id' => $companyId])
            ->exists();
    }

    /**
     * Get all companies a subuser has access to
     *
     * @param int $subuserId
     * @return \yii\db\ActiveQuery
     */
    public static function getAccessibleCompanies($subuserId)
    {
        return Company::find()
            ->alias('c')
            ->innerJoin('{{%jdosa_subuser_company_access}} sca', 'c.id = sca.company_id')
            ->where(['sca.subuser_id' => $subuserId])
            ->andWhere(['c.is_active' => true]);
    }

    /**
     * Get all subusers who have access to a company
     *
     * @param int $companyId
     * @return \yii\db\ActiveQuery
     */
    public static function getAuthorizedSubusers($companyId)
    {
        return User::find()
            ->alias('u')
            ->innerJoin('{{%jdosa_subuser_company_access}} sca', 'u.id = sca.subuser_id')
            ->where(['sca.company_id' => $companyId])
            ->andWhere(['u.is_active' => true, 'u.role' => 'subuser']);
    }
}