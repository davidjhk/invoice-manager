<?php

use yii\db\Migration;

/**
 * Adds Free Plan to the plans table
 */
class m250727_105000_add_free_plan extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Insert Free Plan
        $this->insert('{{%jdosa_plans}}', [
            'name' => 'Free',
            'description' => 'Free plan with basic features',
            'price' => 0.00,
            'stripe_plan_id' => null,
            'paypal_plan_id' => null,
            'features' => json_encode([
                'invoices' => '10 per month',
                'estimates' => '10 per month',
                'customers' => 'Unlimited',
                'products' => 'Unlimited',
                'companies' => 1,
                'storage' => '20MB',
                'support' => 'Community support',
                'import' => 'No',
                'api_access' => 'No',
                'custom_templates' => 'No'
            ]),
            'is_active' => true,
            'sort_order' => 0 // Display first
        ]);

        // Update sort order for existing plans
        $this->update('{{%jdosa_plans}}', ['sort_order' => 1], ['name' => 'Standard']);
        $this->update('{{%jdosa_plans}}', ['sort_order' => 2], ['name' => 'Pro']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->delete('{{%jdosa_plans}}', ['name' => 'Free']);
        
        // Restore original sort order
        $this->update('{{%jdosa_plans}}', ['sort_order' => 1], ['name' => 'Standard']);
        $this->update('{{%jdosa_plans}}', ['sort_order' => 2], ['name' => 'Pro']);
    }
}