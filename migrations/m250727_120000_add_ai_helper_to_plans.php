<?php

use yii\db\Migration;

/**
 * Adds can_use_ai_helper column to jdosa_plans table
 */
class m250727_120000_add_ai_helper_to_plans extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Add AI Helper permission column
        $this->addColumn('{{%jdosa_plans}}', 'can_use_ai_helper', $this->boolean()->defaultValue(false)->comment('AI Helper feature permission'));

        // Update existing plans with their respective AI Helper permissions
        
        // Free Plan - No AI Helper
        $this->update('{{%jdosa_plans}}', [
            'can_use_ai_helper' => false,
        ], ['name' => 'Free']);

        // Standard Plan - AI Helper available
        $this->update('{{%jdosa_plans}}', [
            'can_use_ai_helper' => true,
        ], ['name' => 'Standard']);

        // Pro Plan - AI Helper available
        $this->update('{{%jdosa_plans}}', [
            'can_use_ai_helper' => true,
        ], ['name' => 'Pro']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%jdosa_plans}}', 'can_use_ai_helper');
    }
}