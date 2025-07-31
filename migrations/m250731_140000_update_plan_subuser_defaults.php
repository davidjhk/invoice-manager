<?php

use yii\db\Migration;

/**
 * Updates default subuser limits for existing plans
 */
class m250731_140000_update_plan_subuser_defaults extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Update Pro plan to allow 3 subusers
        $this->update('{{%jdosa_plans}}', ['max_subusers' => 3], ['name' => 'Pro']);
        
        // Update Admin plan to allow unlimited subusers (-1)
        $this->update('{{%jdosa_plans}}', ['max_subusers' => -1], ['name' => 'Admin']);
        
        // Ensure Free and other plans have 0 subusers (should be default but let's be explicit)
        $this->update('{{%jdosa_plans}}', ['max_subusers' => 0], ['and', ['!=', 'name', 'Pro'], ['!=', 'name', 'Admin']]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Reset all plans to 0 subusers
        $this->update('{{%jdosa_plans}}', ['max_subusers' => 0]);
    }
}