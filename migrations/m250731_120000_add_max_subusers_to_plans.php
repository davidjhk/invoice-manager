<?php

use yii\db\Migration;

/**
 * Handles adding max_subusers to table `{{%plans}}`.
 */
class m250731_120000_add_max_subusers_to_plans extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%jdosa_plans}}', 'max_subusers', $this->integer()->notNull()->defaultValue(0)->after('can_use_ai_helper'));
        
        // Update existing plans with appropriate max_subusers values
        $this->update('{{%jdosa_plans}}', ['max_subusers' => 3], ['name' => 'Pro']);
        $this->update('{{%jdosa_plans}}', ['max_subusers' => -1], ['name' => 'Admin']); // -1 means unlimited
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%jdosa_plans}}', 'max_subusers');
    }
}