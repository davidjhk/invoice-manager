<?php

namespace app\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * User model
 *
 * @property integer $id
 * @property string $username
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $email
 * @property string $auth_key
 * @property string $created_at
 * @property string $updated_at
 * @property string $password write-only password
 * @property string $full_name
 * @property string $login_type
 * @property boolean $is_active
 * @property boolean $email_verified
 * @property integer $max_companies
 * @property string $role
 * @property integer $parent_user_id
 * @property integer $company_id
 * @property string $google_id
 * @property string $avatar_url
 */
class User extends ActiveRecord implements IdentityInterface
{
    const LOGIN_TYPE_LOCAL = 'local';
    const LOGIN_TYPE_GOOGLE = 'google';

    /**
     * @var string password field for validation
     */
    public $password;
    
    /**
     * @var string password repeat field for validation
     */
    public $password_repeat;

    public function init()
    {
        parent::init();
        if ($this->isNewRecord) {
            $this->is_active = true;
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'jdosa_users';
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
            [['email'], 'required'],
            [['username'], 'required', 'when' => function($model) {
                return $model->login_type === self::LOGIN_TYPE_LOCAL;
            }],
            [['password'], 'required', 'when' => function($model) {
                return ($model->login_type === self::LOGIN_TYPE_LOCAL && $model->isNewRecord) || 
                       $model->scenario === 'create';
            }],
            [['password'], 'string', 'min' => 6],
            [['password_repeat'], 'required', 'on' => 'create'],
            [['password_repeat'], 'compare', 'compareAttribute' => 'password', 'on' => 'create'],
            [['is_active', 'email_verified'], 'boolean'],
            [['max_companies'], 'integer', 'min' => 1, 'max' => 100],
            [['max_companies'], 'default', 'value' => 1],
            [['role'], 'string'],
            [['role'], 'in', 'range' => ['admin', 'user', 'demo', 'subuser']],
            [['role'], 'default', 'value' => 'user'],
            [['created_at', 'updated_at'], 'safe'],
            [['username'], 'string', 'max' => 50],
            [['username'], 'validateUniqueUsername'],
            [['email'], 'string', 'max' => 255],
            [['email'], 'email'],
            [['email'], 'validateUniqueEmail'],
            [['password_hash', 'password_reset_token'], 'string', 'max' => 255],
            [['full_name'], 'string', 'max' => 100],
            [['google_id'], 'string', 'max' => 100],
            [['google_id'], 'unique'],
            [['avatar_url'], 'string', 'max' => 500],
            [['login_type'], 'in', 'range' => [self::LOGIN_TYPE_LOCAL, self::LOGIN_TYPE_GOOGLE]],
            [['login_type'], 'default', 'value' => self::LOGIN_TYPE_LOCAL],
            [['auth_key'], 'string', 'max' => 32],
            [['parent_user_id', 'company_id'], 'integer'],
            [['parent_user_id'], 'exist', 'targetClass' => User::class, 'targetAttribute' => 'id'],
            [['company_id'], 'exist', 'targetClass' => Company::class, 'targetAttribute' => 'id'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios['create'] = ['username', 'email', 'password', 'password_repeat', 'full_name', 'role', 'max_companies', 'is_active'];
        $scenarios['update'] = ['username', 'email', 'password', 'full_name', 'role', 'max_companies', 'is_active'];
        $scenarios['create_subuser'] = ['username', 'email', 'password', 'password_repeat', 'full_name', 'parent_user_id', 'company_id', 'is_active'];
        return $scenarios;
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Username',
            'email' => 'Email',
            'password' => 'Password',
            'password_repeat' => 'Confirm Password',
            'password_hash' => 'Password Hash',
            'role' => 'Role',
            'full_name' => 'Full Name',
            'google_id' => 'Google ID',
            'avatar_url' => 'Avatar URL',
            'login_type' => 'Login Type',
            'is_active' => 'Active',
            'email_verified' => 'Email Verified',
            'auth_key' => 'Auth Key',
            'password_reset_token' => 'Password Reset Token',
            'max_companies' => 'Maximum Companies',
            'parent_user_id' => 'Parent User',
            'company_id' => 'Default Company',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[Companies]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCompanies()
    {
        return $this->hasMany(Company::class, ['user_id' => 'id']);
    }

    /**
     * Gets query for [[Subscriptions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSubscriptions()
    {
        return $this->hasMany(UserSubscription::class, ['user_id' => 'id']);
    }

    /**
     * Gets query for [[ParentUser]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getParentUser()
    {
        return $this->hasOne(User::class, ['id' => 'parent_user_id']);
    }

    /**
     * Gets query for [[Subusers]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSubusers()
    {
        return $this->hasMany(User::class, ['parent_user_id' => 'id']);
    }

    /**
     * Gets query for [[DefaultCompany]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDefaultCompanyRelation()
    {
        return $this->hasOne(Company::class, ['id' => 'company_id']);
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'is_active' => true]);
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     * Finds user by username (case-insensitive)
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::find()
            ->where(['and', 
                ['LOWER(username)' => strtolower($username)], 
                ['is_active' => true]
            ])
            ->one();
    }

    /**
     * Finds user by email (case-insensitive)
     *
     * @param string $email
     * @return static|null
     */
    public static function findByEmail($email)
    {
        return static::find()
            ->where(['and', 
                ['LOWER(email)' => strtolower($email)], 
                ['is_active' => true]
            ])
            ->one();
    }

    /**
     * Finds user by Google ID
     *
     * @param string $googleId
     * @return static|null
     */
    public static function findByGoogleId($googleId)
    {
        return static::findOne(['google_id' => $googleId, 'is_active' => true]);
    }

    /**
     * Finds user by password reset token
     *
     * @param string $token
     * @return static|null
     */
    public static function findByPasswordResetToken($token)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::findOne([
            'password_reset_token' => $token,
            'is_active' => true,
        ]);
    }

    /**
     * Finds out if password reset token is valid
     *
     * @param string $token
     * @return bool
     */
    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }

        $timestamp = (int) substr($token, strrpos($token, '_') + 1);
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        return $timestamp + $expire >= time();
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
        return $this->auth_key === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }

    /**
     * Create user from Google profile
     *
     * @param array $profile Google profile data
     * @return User|null
     */
    public static function createFromGoogle($profile)
    {
        $user = new static();
        
        // Generate unique username from email
        $baseUsername = explode('@', $profile['email'])[0];
        $username = $baseUsername;
        $counter = 1;
        
        // Ensure username is unique (case-insensitive)
        while (static::find()->where(['LOWER(username)' => strtolower($username)])->exists()) {
            $username = $baseUsername . $counter;
            $counter++;
        }
        
        $user->username = strtolower($username);
        $user->email = strtolower($profile['email']);
        $user->full_name = $profile['name'] ?? null;
        $user->google_id = $profile['id'];
        $user->avatar_url = $profile['picture'] ?? null;
        $user->login_type = self::LOGIN_TYPE_GOOGLE;
        $user->is_active = true;
        $user->email_verified = true; // Google accounts are pre-verified
        $user->generateAuthKey();
        
        if ($user->save()) {
            return $user;
        }
        
        // Log validation errors for debugging
        if (!empty($user->errors)) {
            Yii::error('User creation failed: ' . json_encode($user->errors), 'app');
        }
        
        return null;
    }

    /**
     * Update user from Google profile
     *
     * @param array $profile Google profile data
     * @return bool
     */
    public function updateFromGoogle($profile)
    {
        $this->full_name = $profile['name'] ?? $this->full_name;
        $this->avatar_url = $profile['picture'] ?? $this->avatar_url;
        $this->email_verified = true;
        
        return $this->save();
    }

    /**
     * Get user's default company
     *
     * @return Company|null
     */
    public function getDefaultCompany()
    {
        return $this->getCompanies()->where(['is_active' => true])->one();
    }

    /**
     * Get current company ID (from session or default)
     *
     * @return int|null
     */
    public function getCompanyId()
    {
        // First try to get from session
        $companyId = Yii::$app->session->get('current_company_id');
        if ($companyId) {
            // Verify this company belongs to the user
            $company = $this->getCompanies()->where(['id' => $companyId, 'is_active' => true])->one();
            if ($company) {
                return $companyId;
            }
        }
        
        // Fall back to default company
        $defaultCompany = $this->getDefaultCompany();
        return $defaultCompany ? $defaultCompany->id : null;
    }

    /**
     * Get user's display name
     *
     * @return string
     */
    public function getDisplayName()
    {
        return $this->full_name ?: $this->username ?: $this->email;
    }

    /**
     * Get current company count for this user
     *
     * @return int
     */
    public function getCompanyCount()
    {
        return (int) $this->getCompanies()->where(['is_active' => true])->count();
    }

    /**
     * Check if user can create more companies
     *
     * @return bool
     */
    public function canCreateMoreCompanies()
    {
        // Admin users have unlimited access
        if ($this->isAdmin()) {
            return true;
        }
        
        // For subusers, check parent user's limits
        if ($this->isSubuser()) {
            $parentUser = $this->getParentUser()->one();
            if ($parentUser) {
                return $parentUser->canCreateMoreCompanies();
            }
            return false; // No parent user found
        }
        
        return $this->getCompanyCount() < $this->max_companies;
    }

    /**
     * Get remaining company slots
     *
     * @return int
     */
    public function getRemainingCompanySlots()
    {
        // Admin users have unlimited access
        if ($this->isAdmin()) {
//            return PHP_INT_MAX; // Unlimited
        }
        
        // For subusers, check parent user's limits
        if ($this->isSubuser()) {
            $parentUser = $this->getParentUser()->one();
            if ($parentUser) {
                return $parentUser->getRemainingCompanySlots();
            }
            return 0; // No parent user found
        }
        
        return max(0, $this->max_companies - $this->getCompanyCount());
    }

    /**
     * Check if user is admin
     *
     * @return bool
     */
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    /**
     * Get user's current subscription
     *
     * @return \app\models\UserSubscription|null
     */
    public function getCurrentSubscription()
    {
        return UserSubscription::getActiveSubscription($this->id);
    }

    /**
     * Get monthly invoice count for current month
     *
     * @return int
     */
    public function getMonthlyInvoiceCount()
    {
        $startOfMonth = date('Y-m-01 00:00:00');
        $endOfMonth = date('Y-m-t 23:59:59');

        return Invoice::find()
            ->where(['user_id' => $this->id])
            ->andWhere(['>=', 'created_at', $startOfMonth])
            ->andWhere(['<=', 'created_at', $endOfMonth])
            ->count();
    }

    /**
     * Get monthly estimate count for current month
     *
     * @return int
     */
    public function getMonthlyEstimateCount()
    {
        $startOfMonth = date('Y-m-01 00:00:00');
        $endOfMonth = date('Y-m-t 23:59:59');

        // Check if user_id column exists in estimates table
        $estimateModel = new Estimate();
        if ($estimateModel->hasAttribute('user_id')) {
            return Estimate::find()
                ->where(['user_id' => $this->id])
                ->andWhere(['>=', 'created_at', $startOfMonth])
                ->andWhere(['<=', 'created_at', $endOfMonth])
                ->count();
        } else {
            // Fallback: count estimates through company ownership
            // Get companies owned by this user
            $companyIds = Company::find()
                ->select('id')
                ->where(['user_id' => $this->id])
                ->column();
            
            if (empty($companyIds)) {
                return 0;
            }
            
            return Estimate::find()
                ->where(['in', 'company_id', $companyIds])
                ->andWhere(['>=', 'created_at', $startOfMonth])
                ->andWhere(['<=', 'created_at', $endOfMonth])
                ->count();
        }
    }

    /**
     * Check if user can create more invoices this month
     *
     * @return bool
     */
    public function canCreateInvoice()
    {
        // Admin users have unlimited access
        if ($this->isAdmin()) {
            return true;
        }
        
        // Check if subscription is cancelled or expired
        if ($this->hasCancelledOrExpiredSubscription()) {
            return false;
        }
        
        $plan = $this->getCurrentPlan();
        
        // No subscription means free tier with limitations
        if (!$plan) {
            $freeLimit = Yii::$app->params['freeUserMonthlyLimit'] ?? 10;
            return $this->getMonthlyInvoiceCount() < $freeLimit; // Free users get limited invoices
        }

        $monthlyLimit = $plan->getMonthlyEstimateLimit();
        
        // Unlimited
        if ($monthlyLimit === null) {
            return true;
        }

        return $this->getMonthlyInvoiceCount() < $monthlyLimit;
    }

    /**
     * Check if user can create more estimates this month
     *
     * @return bool
     */
    public function canCreateEstimate()
    {
        // Admin users have unlimited access
        if ($this->isAdmin()) {
            return true;
        }
        
        // Check if subscription is cancelled or expired
        if ($this->hasCancelledOrExpiredSubscription()) {
            return false;
        }
        
        $plan = $this->getCurrentPlan();
        
        // No subscription means free tier with limitations
        if (!$plan) {
            $freeLimit = Yii::$app->params['freeUserMonthlyLimit'] ?? 10;
            return $this->getMonthlyEstimateCount() < $freeLimit; // Free users get limited estimates
        }

        $monthlyLimit = $plan->getMonthlyEstimateLimit();
        
        // Unlimited
        if ($monthlyLimit === null) {
            return true;
        }

        return $this->getMonthlyEstimateCount() < $monthlyLimit;
    }

    /**
     * Get remaining estimates for current month
     *
     * @return int|null Null means unlimited
     */
    public function getRemainingEstimates()
    {
        // Admin users have unlimited access
        if ($this->isAdmin()) {
            return null; // Unlimited
        }
        
        // Check if subscription is cancelled or expired
        if ($this->hasCancelledOrExpiredSubscription()) {
            return 0;
        }
        
        $plan = $this->getCurrentPlan();
        
        // No subscription means free tier with limitations
        if (!$plan) {
            $freeLimit = Yii::$app->params['freeUserMonthlyLimit'] ?? 10;
            return max(0, $freeLimit - $this->getMonthlyEstimateCount()); // Free users get limited estimates
        }

        $monthlyLimit = $plan->getMonthlyEstimateLimit();
        
        if ($monthlyLimit === null) {
            return null; // Unlimited
        }

        return max(0, $monthlyLimit - $this->getMonthlyEstimateCount());
    }

    /**
     * Get estimate usage percentage for current month
     *
     * @return float|null Null means unlimited
     */
    public function getEstimateUsagePercentage()
    {
        // Admin users have unlimited access
        if ($this->isAdmin()) {
            return null; // Unlimited
        }
        
        // Check if subscription is cancelled or expired
        if ($this->hasCancelledOrExpiredSubscription()) {
            return 100; // Show as 100% used when subscription is cancelled or expired
        }
        
        $plan = $this->getCurrentPlan();
        
        if (!$plan) {
            $freeLimit = Yii::$app->params['freeUserMonthlyLimit'] ?? 10;
            return ($this->getMonthlyEstimateCount() / $freeLimit) * 100;
        }

        $monthlyLimit = $plan->getMonthlyEstimateLimit();
        
        if ($monthlyLimit === null) {
            return null; // Unlimited
        }

        return ($this->getMonthlyEstimateCount() / $monthlyLimit) * 100;
    }

    /**
     * Get remaining invoices for current month
     *
     * @return int|null Null means unlimited
     */
    public function getRemainingInvoices()
    {
        // Admin users have unlimited access
        if ($this->isAdmin()) {
            return null; // Unlimited
        }
        
        // Check if subscription is cancelled or expired
        if ($this->hasCancelledOrExpiredSubscription()) {
            return 0;
        }
        
        $plan = $this->getCurrentPlan();
        
        // No subscription means free tier with limitations
        if (!$plan) {
            $freeLimit = Yii::$app->params['freeUserMonthlyLimit'] ?? 10;
            return max(0, $freeLimit - $this->getMonthlyInvoiceCount()); // Free users get limited invoices
        }

        $monthlyLimit = $plan->getMonthlyEstimateLimit();
        
        if ($monthlyLimit === null) {
            return null; // Unlimited
        }

        return max(0, $monthlyLimit - $this->getMonthlyInvoiceCount());
    }

    /**
     * Get invoice usage percentage for current month
     *
     * @return float|null Null means unlimited
     */
    public function getInvoiceUsagePercentage()
    {
        // Admin users have unlimited access
        if ($this->isAdmin()) {
            return null; // Unlimited
        }
        
        // Check if subscription is cancelled or expired
        if ($this->hasCancelledOrExpiredSubscription()) {
            return 100; // Show as 100% used when subscription is cancelled or expired
        }
        
        $plan = $this->getCurrentPlan();
        
        if (!$plan) {
            $freeLimit = Yii::$app->params['freeUserMonthlyLimit'] ?? 10;
            return ($this->getMonthlyInvoiceCount() / $freeLimit) * 100;
        }

        $monthlyLimit = $plan->getMonthlyEstimateLimit();
        
        if ($monthlyLimit === null) {
            return null; // Unlimited
        }

        return ($this->getMonthlyInvoiceCount() / $monthlyLimit) * 100;
    }

    /**
     * Check if user can use import functionality
     *
     * @return bool
     */
    public function canUseImport()
    {
        // Admin users have unlimited access
        if ($this->isAdmin()) {
            return true;
        }
        
        // Check if subscription is cancelled or expired
        if ($this->hasCancelledOrExpiredSubscription()) {
            return false;
        }
        
        $plan = $this->getCurrentPlan();
        
        // No subscription means free tier - no import allowed
        if (!$plan) {
            return false;
        }

        return $plan->canUseImport();
    }

    /**
     * Check if user can use API functionality
     *
     * @return bool
     */
    public function canUseApi()
    {
        // Admin users have unlimited access
        if ($this->isAdmin()) {
            return true;
        }
        
        // Check if subscription is cancelled or expired
        if ($this->hasCancelledOrExpiredSubscription()) {
            return false;
        }
        
        $plan = $this->getCurrentPlan();
        
        // No subscription means free tier - no API access
        if (!$plan) {
            return false;
        }

        return $plan->canUseApi();
    }

    /**
     * Check if user can use custom templates
     *
     * @return bool
     */
    public function canUseCustomTemplates()
    {
        // Admin users have unlimited access
        if ($this->isAdmin()) {
            return true;
        }
        
        // Check if subscription is cancelled or expired
        if ($this->hasCancelledOrExpiredSubscription()) {
            return false;
        }
        
        $plan = $this->getCurrentPlan();
        
        // No subscription means free tier - no custom templates
        if (!$plan) {
            return false;
        }

        return $plan->canUseCustomTemplates();
    }

    /**
     * Check if user can use AI Helper functionality
     *
     * @return bool
     */
    public function canUseAiHelper()
    {
        // Admin users have unlimited access
        if ($this->isAdmin()) {
            return true;
        }
        
        // Check if subscription is cancelled or expired
        if ($this->hasCancelledOrExpiredSubscription()) {
            return false;
        }
        
        $plan = $this->getCurrentPlan();
        
        // Check AI Helper permission from plan
        if (!$plan) {
            return false; // No plan = no AI Helper
        }
        
        return $plan->canUseAiHelper();
    }

    /**
     * Get storage limit in MB
     *
     * @return int|null Null means unlimited
     */
    public function getStorageLimit()
    {
        // Admin users have unlimited storage
        if ($this->isAdmin()) {
            return null;
        }
        
        $plan = $this->getCurrentPlan();
        
        // No subscription means free tier storage limit
        if (!$plan) {
            return 100; // 100MB for free tier
        }

        return $plan->getStorageLimit();
    }

    /**
     * Get maximum companies allowed
     *
     * @return int|null Null means unlimited
     */
    public function getMaxCompaniesLimit()
    {
        // Admin users have unlimited companies
        if ($this->isAdmin()) {
            return null;
        }
        
        $plan = $this->getCurrentPlan();
        
        // No subscription means free tier company limit
        if (!$plan) {
            return 1; // 1 company for free tier
        }

        return $plan->getMaxCompanies();
    }

    /**
     * Get plan name for display
     *
     * @return string
     */
    public function getPlanName()
    {
        $plan = $this->getCurrentPlan();
        return $plan ? $plan->name : 'Free';
    }

    /**
     * Check if user is on free tier (no active subscription)
     *
     * @return bool
     */
    public function isFreeTier()
    {
        return !$this->hasActiveSubscription() && !$this->isAdmin();
    }

    /**
     * Check if user is demo
     *
     * @return bool
     */
    public function isDemo()
    {
        return $this->role === 'demo';
    }

    /**
     * Check if user is regular user
     *
     * @return bool
     */
    public function isUser()
    {
        return $this->role === 'user';
    }

    /**
     * Check if user is subuser
     *
     * @return bool
     */
    public function isSubuser()
    {
        return $this->role === 'subuser';
    }

    /**
     * Get role options for dropdown
     *
     * @return array
     */
    public static function getRoleOptions()
    {
        return [
            'user' => 'User',
            'admin' => 'Admin',
            'demo' => 'Demo',
            'subuser' => 'Subuser',
        ];
    }

    /**
     * Get role label
     *
     * @return string
     */
    public function getRoleLabel()
    {
        $options = self::getRoleOptions();
        return $options[$this->role] ?? 'Unknown';
    }

    /**
     * Get user's active subscription
     *
     * @return UserSubscription|null
     */
    public function getActiveSubscription()
    {
        return UserSubscription::getActiveSubscription($this->id);
    }

    /**
     * Check if user has active subscription
     *
     * @return bool
     */
    public function hasActiveSubscription()
    {
        $subscription = $this->getActiveSubscription();
        return $subscription && $subscription->isActive();
    }

    /**
     * Check if user has valid subscription (active or within grace period)
     *
     * @return bool
     */
    public function hasValidSubscription()
    {
        // Admin users always have valid subscription
        if ($this->isAdmin()) {
            return true;
        }
        
        $subscription = $this->getActiveSubscription();
        return $subscription && ($subscription->isActive() || $subscription->isCancelled() || $subscription->isExpired());
    }

    /**
     * Check if user's subscription is cancelled or expired
     *
     * @return bool
     */
    public function hasCancelledOrExpiredSubscription()
    {
        // Admin users never have cancelled or expired subscription
        if ($this->isAdmin()) {
            return false;
        }
        
        $subscription = $this->getActiveSubscription();
        return $subscription && ($subscription->isCancelled() || $subscription->isExpired());
    }

    /**
     * Get user's current plan
     *
     * @return Plan|null
     */
    public function getCurrentPlan()
    {
        $subscription = $this->getActiveSubscription();
        return $subscription ? $subscription->plan : null;
    }

    /**
     * Get status label
     *
     * @return string
     */
    public function getStatusLabel()
    {
        return $this->is_active ? 'Active' : 'Inactive';
    }

    /**
     * Validates username uniqueness (case-insensitive)
     *
     * @param string $attribute
     * @param array $params
     */
    public function validateUniqueUsername($attribute, $params)
    {
        if (!empty($this->$attribute)) {
            $query = static::find()
                ->where(['LOWER(username)' => strtolower($this->$attribute)]);
            
            if (!$this->isNewRecord) {
                $query->andWhere(['!=', 'id', $this->id]);
            }
            
            if ($query->exists()) {
                $this->addError($attribute, 'This username has already been taken.');
            }
        }
    }

    /**
     * Validates email uniqueness (case-insensitive)
     *
     * @param string $attribute
     * @param array $params
     */
    public function validateUniqueEmail($attribute, $params)
    {
        if (!empty($this->$attribute)) {
            $query = static::find()
                ->where(['LOWER(email)' => strtolower($this->$attribute)]);
            
            if (!$this->isNewRecord) {
                $query->andWhere(['!=', 'id', $this->id]);
            }
            
            if ($query->exists()) {
                $this->addError($attribute, 'This email address has already been taken.');
            }
        }
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
            if ($insert) {
                $this->generateAuthKey();
            }
            
            // Convert username and email to lowercase for consistency
            if (!empty($this->username)) {
                $this->username = strtolower($this->username);
            }
            if (!empty($this->email)) {
                $this->email = strtolower($this->email);
            }
            
            if (!empty($this->password)) {
                $this->setPassword($this->password);
            }
            
            return true;
        }
        return false;
    }

    /**
     * Get current subuser count for this user
     *
     * @return int
     */
    public function getSubuserCount()
    {
        return (int) $this->getSubusers()->where(['is_active' => true])->count();
    }

    /**
     * Check if user can create more subusers
     *
     * @return bool
     */
    public function canCreateMoreSubusers()
    {
        // Admin users have unlimited access
        if ($this->isAdmin()) {
            return true;
        }

		// Only user role can create subusers
        if (!$this->isUser()) {
            return false;
        }

        $plan = $this->getCurrentPlan();
        
        // No subscription means no subuser access
        if (!$plan) {
            return false;
        }

        $maxSubusers = $plan->max_subusers ?? 0;
        
        // Unlimited
        if ($maxSubusers === -1) {
            return true;
        }

        return $this->getSubuserCount() < $maxSubusers;
    }

    /**
     * Get remaining subuser slots
     *
     * @return int|null Null means unlimited
     */
    public function getRemainingSubuserSlots()
    {
        // Only user role can create subusers
        if (!$this->isUser()) {
            return 0;
        }

        // Admin users have unlimited access
        if ($this->isAdmin()) {
            return null; // Unlimited
        }

        $plan = $this->getCurrentPlan();
        
        // No subscription means no subuser access
        if (!$plan) {
            return 0;
        }

        $maxSubusers = $plan->max_subusers ?? 0;
        
        // Unlimited
        if ($maxSubusers === -1) {
            return null; // Unlimited
        }

        return max(0, $maxSubusers - $this->getSubuserCount());
    }

    /**
     * Get maximum subusers allowed
     *
     * @return int|null Null means unlimited
     */
    public function getMaxSubusersLimit()
    {
        // Only user role can create subusers
        if (!$this->isUser()) {
            return 0;
        }

        // Admin users have unlimited subusers
        if ($this->isAdmin()) {
            return null;
        }
        
        $plan = $this->getCurrentPlan();
        
        // No subscription means no subuser access
        if (!$plan) {
            return 0;
        }

        $maxSubusers = $plan->max_subusers ?? 0;
        
        // Unlimited
        if ($maxSubusers === -1) {
            return null;
        }

        return $maxSubusers;
    }

    /**
     * Create a new subuser
     *
     * @param array $attributes
     * @return User|null
     */
    public function createSubuser($attributes)
    {
        // Check if user can create more subusers
        if (!$this->canCreateMoreSubusers()) {
            return null;
        }

        $subuser = new User();
        $subuser->scenario = 'create_subuser';
        $subuser->attributes = $attributes;
        $subuser->role = 'subuser';
        $subuser->parent_user_id = $this->id;
        $subuser->max_companies = 0; // Subusers cannot create companies
        
        if ($subuser->save()) {
            return $subuser;
        }
        
        return null;
    }

    /**
     * Get parent user (for subusers)
     *
     * @return User|null
     */
    public function getParent()
    {
        return $this->parentUser;
    }

    /**
     * Get effective user (parent user for subusers, self for regular users)
     *
     * @return User
     */
    public function getEffectiveUser()
    {
        return $this->isSubuser() ? $this->getParent() : $this;
    }

    /**
     * Check if subuser can access a specific company
     *
     * @param int $companyId
     * @return bool
     */
    public function canAccessCompany($companyId)
    {
        if (!$this->isSubuser()) {
            // Regular users can access their own companies
            return $this->getCompanies()->where(['id' => $companyId])->exists();
        }

        // Subusers can access their parent's companies
        $parent = $this->getParent();
        if (!$parent) {
            return false;
        }

        return $parent->getCompanies()->where(['id' => $companyId])->exists();
    }

    /**
     * Get accessible companies (for subusers, returns companies through access table)
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAccessibleCompanies()
    {
        if (!$this->isSubuser()) {
            // Non-subusers get their own companies
            return $this->getCompanies()->where(['is_active' => true]);
        }

        // Subusers get companies through SubuserCompanyAccess
        return \app\models\SubuserCompanyAccess::getAccessibleCompanies($this->id);
    }

    /**
     * Check if user can manage subusers (create, edit, delete)
     *
     * @return bool
     */
    public function canManageSubusers()
    {
        // Admin users can always manage subusers
        if ($this->isAdmin()) {
            return true;
        }

        // Only user role with Pro plan or higher can manage subusers
        if (!$this->isUser()) {
            return false;
        }

        $plan = $this->getCurrentPlan();
        
        // Pro plan or higher required
        if (!$plan || $plan->max_subusers <= 0) {
            return false;
        }

        return true;
    }


    /**
     * Check if this user has access to a specific company
     *
     * @param int $companyId
     * @return bool
     */
    public function hasCompanyAccess($companyId)
    {
        if (!$this->isSubuser()) {
            // Non-subusers: check ownership
            return $this->getCompanies()->where(['id' => $companyId, 'is_active' => true])->exists();
        }

        // Subusers: check access table
        return \app\models\SubuserCompanyAccess::hasAccess($this->id, $companyId);
    }

    /**
     * Grant company access to this subuser
     *
     * @param int $companyId
     * @param int $grantedBy
     * @return bool
     */
    public function grantCompanyAccess($companyId, $grantedBy)
    {
        if (!$this->isSubuser()) {
            return false; // Only for subusers
        }

        return \app\models\SubuserCompanyAccess::grantAccess($this->id, $companyId, $grantedBy);
    }

    /**
     * Revoke company access from this subuser
     *
     * @param int $companyId
     * @return bool
     */
    public function revokeCompanyAccess($companyId)
    {
        if (!$this->isSubuser()) {
            return false; // Only for subusers
        }

        return \app\models\SubuserCompanyAccess::revokeAccess($this->id, $companyId);
    }
}