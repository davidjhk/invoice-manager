<?php

use yii\db\Migration;

/**
 * Handles the creation of table `jdosa_update_logs`.
 * This table stores update logs for Invoice, Estimate, Customer, and Product entities.
 */
class m250801_100000_create_update_log_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%jdosa_update_logs}}', [
            'id' => $this->primaryKey(),
            'entity_type' => $this->string(50)->notNull()->comment('Entity type: invoice, estimate, customer, product'),
            'entity_id' => $this->integer()->notNull()->comment('ID of the entity'),
            'action' => $this->string(20)->notNull()->comment('Action: create, update, delete'),
            'user_id' => $this->integer()->notNull()->comment('ID of the user who made the change'),
            'user_name' => $this->string(255)->notNull()->comment('Name of the user who made the change'),
            'details' => $this->text()->comment('Additional details about the change'),
            'ip_address' => $this->string(45)->comment('IP address of the user'),
            'user_agent' => $this->text()->comment('User agent of the request'),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP')->comment('Timestamp of the action'),
        ]);
        
        // Add indexes for better query performance
        $this->createIndex('idx_update_logs_entity', '{{%jdosa_update_logs}}', ['entity_type', 'entity_id']);
        $this->createIndex('idx_update_logs_user', '{{%jdosa_update_logs}}', ['user_id']);
        $this->createIndex('idx_update_logs_action', '{{%jdosa_update_logs}}', ['action']);
        $this->createIndex('idx_update_logs_created_at', '{{%jdosa_update_logs}}', ['created_at']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%jdosa_update_logs}}');
    }
}