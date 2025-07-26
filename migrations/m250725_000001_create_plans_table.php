<?php

use yii\db\Migration;

/**
 * Handles the creation of plans table for subscription management
 */
class m250725_000001_create_plans_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Create jdosa_plans table
        $this->createTable('{{%jdosa_plans}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(50)->notNull()->comment('Plan name (e.g., Standard, Pro)'),
            'description' => $this->text()->comment('Plan description'),
            'price' => $this->decimal(10, 2)->notNull()->comment('Monthly price'),
            'stripe_plan_id' => $this->string(100)->null()->comment('Stripe plan ID'),
            'paypal_plan_id' => $this->string(100)->null()->comment('PayPal plan ID'),
            'features' => $this->json()->null()->comment('Plan features as JSON'),
            'is_active' => $this->boolean()->defaultValue(true)->comment('Whether plan is active'),
            'sort_order' => $this->integer()->defaultValue(0)->comment('Sort order for display'),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        // Create indexes
        $this->createIndex('idx-jdosa_plans-name', '{{%jdosa_plans}}', 'name');
        $this->createIndex('idx-jdosa_plans-is_active', '{{%jdosa_plans}}', 'is_active');
        $this->createIndex('idx-jdosa_plans-sort_order', '{{%jdosa_plans}}', 'sort_order');

        // Insert default plans
        $this->batchInsert('{{%jdosa_plans}}', [
            'name', 'description', 'price', 'stripe_plan_id', 'paypal_plan_id', 'features', 'is_active', 'sort_order'
        ], [
            [
                'Standard',
                'Basic plan with essential features',
                9.99,
                null,
                null,
                json_encode([
                    'invoices' => 'Unlimited',
                    'customers' => 'Unlimited',
                    'products' => 'Unlimited',
                    'users' => 1,
                    'support' => 'Email support',
                    'storage' => '1GB'
                ]),
                true,
                1
            ],
            [
                'Pro',
                'Advanced plan with premium features',
                29.99,
                null,
                null,
                json_encode([
                    'invoices' => 'Unlimited',
                    'customers' => 'Unlimited',
                    'products' => 'Unlimited',
                    'users' => 5,
                    'support' => 'Priority email support',
                    'storage' => '10GB',
                    'api_access' => 'Yes',
                    'custom_templates' => 'Yes'
                ]),
                true,
                2
            ]
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%jdosa_plans}}');
    }
}
