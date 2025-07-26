<?php

use yii\db\Migration;

/**
 * Add scheduled plan fields to user subscriptions table for handling downgrades
 */
class m250126_130000_add_scheduled_plan_fields_to_subscriptions extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%jdosa_user_subscriptions}}', 'scheduled_plan_id', $this->integer()->null()->comment('Plan ID to change to at scheduled date'));
        $this->addColumn('{{%jdosa_user_subscriptions}}', 'scheduled_change_date', $this->date()->null()->comment('Date when the plan change should take effect'));
        
        // Add foreign key constraint for scheduled_plan_id
        $this->addForeignKey(
            'fk-user_subscriptions-scheduled_plan_id',
            '{{%jdosa_user_subscriptions}}',
            'scheduled_plan_id',
            '{{%jdosa_plans}}',
            'id',
            'SET NULL'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Drop foreign key first
        $this->dropForeignKey('fk-user_subscriptions-scheduled_plan_id', '{{%jdosa_user_subscriptions}}');
        
        // Drop columns
        $this->dropColumn('{{%jdosa_user_subscriptions}}', 'scheduled_change_date');
        $this->dropColumn('{{%jdosa_user_subscriptions}}', 'scheduled_plan_id');
    }
}