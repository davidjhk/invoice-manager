<?php

use yii\db\Migration;

/**
 * Handles the creation of user_subscriptions table for subscription management
 */
class m250725_000002_create_user_subscriptions_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Create jdosa_user_subscriptions table
        $this->createTable('{{%jdosa_user_subscriptions}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull()->comment('User ID'),
            'plan_id' => $this->integer()->notNull()->comment('Plan ID'),
            'status' => "ENUM('active', 'inactive', 'cancelled', 'expired') DEFAULT 'active' COMMENT 'Subscription status'",
            'stripe_subscription_id' => $this->string(100)->null()->comment('Stripe subscription ID'),
            'paypal_subscription_id' => $this->string(100)->null()->comment('PayPal subscription ID'),
            'payment_method' => "ENUM('stripe', 'paypal') COMMENT 'Payment method used'",
            'start_date' => $this->date()->notNull()->comment('Subscription start date'),
            'end_date' => $this->date()->null()->comment('Subscription end date'),
            'next_billing_date' => $this->date()->null()->comment('Next billing date'),
            'cancel_date' => $this->date()->null()->comment('Cancellation date'),
            'trial_end_date' => $this->date()->null()->comment('Trial end date'),
            'is_recurring' => $this->boolean()->defaultValue(true)->comment('Whether subscription is recurring'),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        // Create indexes
        $this->createIndex('idx-jdosa_user_subscriptions-user_id', '{{%jdosa_user_subscriptions}}', 'user_id');
        $this->createIndex('idx-jdosa_user_subscriptions-plan_id', '{{%jdosa_user_subscriptions}}', 'plan_id');
        $this->createIndex('idx-jdosa_user_subscriptions-status', '{{%jdosa_user_subscriptions}}', 'status');
        $this->createIndex('idx-jdosa_user_subscriptions-stripe_id', '{{%jdosa_user_subscriptions}}', 'stripe_subscription_id');
        $this->createIndex('idx-jdosa_user_subscriptions-paypal_id', '{{%jdosa_user_subscriptions}}', 'paypal_subscription_id');
        $this->createIndex('idx-jdosa_user_subscriptions-next_billing', '{{%jdosa_user_subscriptions}}', 'next_billing_date');

        // Add foreign key constraints
        $this->addForeignKey(
            'fk-jdosa_user_subscriptions-user_id',
            '{{%jdosa_user_subscriptions}}',
            'user_id',
            '{{%jdosa_users}}',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-jdosa_user_subscriptions-plan_id',
            '{{%jdosa_user_subscriptions}}',
            'plan_id',
            '{{%jdosa_plans}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Drop foreign key constraints
        $this->dropForeignKey('fk-jdosa_user_subscriptions-user_id', '{{%jdosa_user_subscriptions}}');
        $this->dropForeignKey('fk-jdosa_user_subscriptions-plan_id', '{{%jdosa_user_subscriptions}}');

        $this->dropTable('{{%jdosa_user_subscriptions}}');
    }
}
