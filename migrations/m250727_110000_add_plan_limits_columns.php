<?php

use yii\db\Migration;

/**
 * Adds plan limit columns to jdosa_plans table
 */
class m250727_110000_add_plan_limits_columns extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Add plan limit columns
        $this->addColumn('{{%jdosa_plans}}', 'monthly_invoice_limit', $this->integer()->null()->comment('Monthly invoice limit (null = unlimited)'));
        $this->addColumn('{{%jdosa_plans}}', 'monthly_estimate_limit', $this->integer()->null()->comment('Monthly estimate limit (null = unlimited)'));
        $this->addColumn('{{%jdosa_plans}}', 'max_companies', $this->integer()->defaultValue(1)->comment('Maximum number of companies'));
        $this->addColumn('{{%jdosa_plans}}', 'storage_limit_mb', $this->integer()->null()->comment('Storage limit in MB (null = unlimited)'));
        $this->addColumn('{{%jdosa_plans}}', 'can_use_api', $this->boolean()->defaultValue(false)->comment('API access permission'));
        $this->addColumn('{{%jdosa_plans}}', 'can_use_import', $this->boolean()->defaultValue(false)->comment('Import feature permission'));
        $this->addColumn('{{%jdosa_plans}}', 'can_use_custom_templates', $this->boolean()->defaultValue(false)->comment('Custom templates permission'));

        // Update existing plans with their respective limits
        
        // Free Plan
        $this->update('{{%jdosa_plans}}', [
            'monthly_invoice_limit' => 10,
            'monthly_estimate_limit' => 10,
            'max_companies' => 1,
            'storage_limit_mb' => 100,
            'can_use_api' => false,
            'can_use_import' => false,
            'can_use_custom_templates' => false,
        ], ['name' => 'Free']);

        // Standard Plan
        $this->update('{{%jdosa_plans}}', [
            'monthly_invoice_limit' => 100,
            'monthly_estimate_limit' => 100,
            'max_companies' => 3,
            'storage_limit_mb' => 1000, // 1GB
            'can_use_api' => false,
            'can_use_import' => false,
            'can_use_custom_templates' => false,
        ], ['name' => 'Standard']);

        // Pro Plan
        $this->update('{{%jdosa_plans}}', [
            'monthly_invoice_limit' => null, // Unlimited
            'monthly_estimate_limit' => null, // Unlimited
            'max_companies' => null, // Unlimited
            'storage_limit_mb' => 10000, // 10GB
            'can_use_api' => true,
            'can_use_import' => true,
            'can_use_custom_templates' => true,
        ], ['name' => 'Pro']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%jdosa_plans}}', 'monthly_invoice_limit');
        $this->dropColumn('{{%jdosa_plans}}', 'monthly_estimate_limit');
        $this->dropColumn('{{%jdosa_plans}}', 'max_companies');
        $this->dropColumn('{{%jdosa_plans}}', 'storage_limit_mb');
        $this->dropColumn('{{%jdosa_plans}}', 'can_use_api');
        $this->dropColumn('{{%jdosa_plans}}', 'can_use_import');
        $this->dropColumn('{{%jdosa_plans}}', 'can_use_custom_templates');
    }
}