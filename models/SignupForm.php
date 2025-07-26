<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * Signup form
 */
class SignupForm extends Model
{
    public $username;
    public $email;
    public $password;
    public $password_repeat;
    public $full_name;
    public $plan_id;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['username', 'email', 'password', 'password_repeat'], 'required'],
            [['plan_id'], 'integer'],
            [['plan_id'], 'exist', 'targetClass' => Plan::class, 'targetAttribute' => 'id', 'filter' => ['is_active' => true]],
            [['username'], 'string', 'min' => 3, 'max' => 50],
            [['username'], 'unique', 'targetClass' => User::class, 'message' => 'This username has already been taken.'],
            [['username'], 'validateNotReserved'],
            [['email'], 'email'],
            [['email'], 'unique', 'targetClass' => User::class, 'message' => 'This email address has already been taken.'],
            [['password'], 'string', 'min' => 6],
            [['password_repeat'], 'string'],
            [['password_repeat'], 'compare', 'compareAttribute' => 'password', 'message' => 'Passwords do not match.'],
            [['full_name'], 'string', 'max' => 100],
        ];
    }

    /**
     * Validates that username is not a reserved/hardcoded username
     */
    public function validateNotReserved($attribute, $params)
    {
        $reservedUsernames = ['admin', 'demo2', 'test', 'root', 'administrator'];
        if (in_array(strtolower($this->$attribute), $reservedUsernames)) {
            $this->addError($attribute, 'This username is reserved. Please choose a different username.');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'username' => 'Username',
            'email' => 'Email',
            'password' => 'Password',
            'password_repeat' => 'Confirm Password',
            'full_name' => 'Full Name',
            'plan_id' => 'Subscription Plan',
        ];
    }

    /**
     * Signs user up.
     *
     * @return User|false whether the creating new account was successful
     */
    public function signup()
    {
        Yii::info('Starting signup process for: ' . $this->username, 'app');
        
        if (!$this->validate()) {
            Yii::error('SignupForm validation failed: ' . json_encode($this->errors), 'app');
            return false;
        }
        
        Yii::info('SignupForm validation passed', 'app');
        
        $user = new User();
        $user->username = $this->username;
        $user->email = $this->email;
        $user->password = $this->password; // This will be hashed in beforeSave()
        $user->full_name = $this->full_name;
        $user->login_type = User::LOGIN_TYPE_LOCAL;
        $user->is_active = true;
        $user->email_verified = false; // Will be verified later
        // Note: password_repeat is not set on User model as it's not a database field
        
        if ($user->save()) {
            // Create a default company for the user
            $company = new Company();
            $company->company_name = ($this->full_name ?: $this->username) . "'s Company";
            $company->company_email = $this->email;
            $company->sender_email = $this->email;
            $company->sender_name = $this->full_name ?: $this->username;
            $company->user_id = $user->id;
            
            if (!$company->save()) {
                Yii::error('Company creation failed: ' . json_encode($company->errors), 'app');
            }
            
            // Create subscription if plan_id is provided
            if ($this->plan_id) {
                $plan = Plan::findOne($this->plan_id);
                if ($plan && $plan->is_active) {
                    $subscription = new UserSubscription();
                    $subscription->user_id = $user->id;
                    $subscription->plan_id = $plan->id;
                    $subscription->status = UserSubscription::STATUS_INACTIVE; // Will be activated after payment
                    $subscription->start_date = date('Y-m-d');
                    $subscription->is_recurring = true;
                    
                    if (!$subscription->save()) {
                        Yii::error('Subscription creation failed: ' . json_encode($subscription->errors), 'app');
                    }
                }
            }
            
            return $user;
        } else {
            // Copy user validation errors to signup form
            foreach ($user->errors as $attribute => $errors) {
                foreach ($errors as $error) {
                    $this->addError($attribute, $error);
                }
            }
            Yii::error('User creation failed: ' . json_encode($user->errors), 'app');
        }

        return false;
    }
}