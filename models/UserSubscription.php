<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "jdosa_user_subscriptions".
 *
 * @property int $id
 * @property int $user_id
 * @property int $plan_id
 * @property string $status
 * @property string|null $stripe_subscription_id
 * @property string|null $paypal_subscription_id
 * @property string|null $payment_method
 * @property string $start_date
 * @property string|null $end_date
 * @property string|null $next_billing_date
 * @property string|null $cancel_date
 * @property string|null $trial_end_date
 * @property bool $is_recurring
 * @property int|null $scheduled_plan_id
 * @property string|null $scheduled_change_date
 * @property string $created_at
 * @property string $updated_at
 *
 * @property User $user
 * @property Plan $plan
 * @property Plan $scheduledPlan
 */
class UserSubscription extends ActiveRecord
{
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_EXPIRED = 'expired';

    const PAYMENT_METHOD_STRIPE = 'stripe';
    const PAYMENT_METHOD_PAYPAL = 'paypal';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'jdosa_user_subscriptions';
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
            [['user_id', 'plan_id', 'start_date'], 'required'],
            [['user_id', 'plan_id'], 'integer'],
            [['status'], 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_INACTIVE, self::STATUS_CANCELLED, self::STATUS_EXPIRED]],
            [['payment_method'], 'in', 'range' => [self::PAYMENT_METHOD_STRIPE, self::PAYMENT_METHOD_PAYPAL]],
            [['start_date', 'end_date', 'next_billing_date', 'cancel_date', 'trial_end_date', 'scheduled_change_date'], 'date', 'format' => 'yyyy-MM-dd'],
            [['scheduled_plan_id'], 'integer'],
            [['is_recurring'], 'boolean'],
            [['created_at', 'updated_at'], 'safe'],
            [['stripe_subscription_id', 'paypal_subscription_id'], 'string', 'max' => 100],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
            [['plan_id'], 'exist', 'skipOnError' => true, 'targetClass' => Plan::class, 'targetAttribute' => ['plan_id' => 'id']],
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
            'plan_id' => 'Plan ID',
            'status' => 'Status',
            'stripe_subscription_id' => 'Stripe Subscription ID',
            'paypal_subscription_id' => 'PayPal Subscription ID',
            'payment_method' => 'Payment Method',
            'start_date' => 'Start Date',
            'end_date' => 'End Date',
            'next_billing_date' => 'Next Billing Date',
            'cancel_date' => 'Cancel Date',
            'trial_end_date' => 'Trial End Date',
            'is_recurring' => 'Is Recurring',
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
     * Gets query for [[Plan]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPlan()
    {
        return $this->hasOne(Plan::class, ['id' => 'plan_id']);
    }

    /**
     * Gets query for [[ScheduledPlan]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getScheduledPlan()
    {
        return $this->hasOne(Plan::class, ['id' => 'scheduled_plan_id']);
    }

    /**
     * Get user's active subscription
     *
     * @param int $userId
     * @return static|null
     */
    public static function getActiveSubscription($userId)
    {
        return static::find()
            ->where(['user_id' => $userId, 'status' => self::STATUS_ACTIVE])
            ->one();
    }

    /**
     * Check if subscription is active
     *
     * @return bool
     */
    public function isActive()
    {
        return $this->status === self::STATUS_ACTIVE && 
               (empty($this->end_date) || strtotime($this->end_date) >= time());
    }

    /**
     * Check if subscription is cancelled
     *
     * @return bool
     */
    public function isCancelled()
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /**
     * Check if subscription is expired
     *
     * @return bool
     */
    public function isExpired()
    {
        return $this->status === self::STATUS_EXPIRED ||
               (!empty($this->end_date) && strtotime($this->end_date) < time());
    }

    /**
     * Check if subscription is in trial period
     *
     * @return bool
     */
    public function isInTrial()
    {
        return !empty($this->trial_end_date) && strtotime($this->trial_end_date) >= time();
    }

    /**
     * Get days remaining in subscription
     *
     * @return int
     */
    public function getDaysRemaining()
    {
        if (empty($this->end_date)) {
            return -1; // Unlimited/recurring
        }
        
        $endTime = strtotime($this->end_date);
        $currentTime = time();
        
        if ($endTime <= $currentTime) {
            return 0;
        }
        
        return ceil(($endTime - $currentTime) / (24 * 60 * 60));
    }

    /**
     * Get status label
     *
     * @return string
     */
    public function getStatusLabel()
    {
        $labels = [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_INACTIVE => 'Inactive',
            self::STATUS_CANCELLED => 'Cancelled',
            self::STATUS_EXPIRED => 'Expired',
        ];
        
        return $labels[$this->status] ?? 'Unknown';
    }

    /**
     * Get payment method label
     *
     * @return string
     */
    public function getPaymentMethodLabel()
    {
        $labels = [
            self::PAYMENT_METHOD_STRIPE => 'Stripe',
            self::PAYMENT_METHOD_PAYPAL => 'PayPal',
        ];
        
        return $labels[$this->payment_method] ?? 'Unknown';
    }

    /**
     * Cancel subscription
     *
     * @param string|null $cancelDate
     * @return bool
     */
    public function cancel($cancelDate = null)
    {
        $this->status = self::STATUS_CANCELLED;
        $this->cancel_date = $cancelDate ?: date('Y-m-d');
        $this->is_recurring = false;
        
        return $this->save();
    }

    /**
     * Expire subscription
     *
     * @return bool
     */
    public function expire()
    {
        $this->status = self::STATUS_EXPIRED;
        if (empty($this->end_date)) {
            $this->end_date = date('Y-m-d');
        }
        
        return $this->save();
    }

    /**
     * Activate subscription
     *
     * @return bool
     */
    public function activate()
    {
        $this->status = self::STATUS_ACTIVE;
        return $this->save();
    }

    /**
     * Get subscription external ID based on payment method
     *
     * @return string|null
     */
    public function getExternalSubscriptionId()
    {
        if ($this->payment_method === self::PAYMENT_METHOD_STRIPE) {
            return $this->stripe_subscription_id;
        } elseif ($this->payment_method === self::PAYMENT_METHOD_PAYPAL) {
            return $this->paypal_subscription_id;
        }
        
        return null;
    }

    /**
     * Set subscription external ID based on payment method
     *
     * @param string $subscriptionId
     * @param string $paymentMethod
     */
    public function setExternalSubscriptionId($subscriptionId, $paymentMethod)
    {
        $this->payment_method = $paymentMethod;
        
        if ($paymentMethod === self::PAYMENT_METHOD_STRIPE) {
            $this->stripe_subscription_id = $subscriptionId;
        } elseif ($paymentMethod === self::PAYMENT_METHOD_PAYPAL) {
            $this->paypal_subscription_id = $subscriptionId;
        }
    }

    /**
     * Check if subscription can be cancelled
     *
     * @return bool
     */
    public function canBeCancelled()
    {
        return $this->isActive() && !$this->isCancelled();
    }

    /**
     * Check if subscription can be upgraded
     *
     * @return bool
     */
    public function canBeUpgraded()
    {
        return $this->isActive() && !$this->isCancelled();
    }

    /**
     * Get next billing date formatted
     *
     * @return string|null
     */
    public function getFormattedNextBillingDate()
    {
        if (empty($this->next_billing_date)) {
            return null;
        }
        
        return date('M j, Y', strtotime($this->next_billing_date));
    }

    /**
     * Update next billing date (typically after successful payment)
     *
     * @param string $nextBillingDate
     * @return bool
     */
    public function updateNextBillingDate($nextBillingDate)
    {
        $this->next_billing_date = $nextBillingDate;
        return $this->save();
    }
}