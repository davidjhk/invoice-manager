<?php

use yii\db\Migration;

/**
 * Handles adding subuser fields to table `{{%users}}`.
 */
class m250731_130000_add_subuser_fields_to_users extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Add parent_user_id for subuser relationship
        $this->addColumn('{{%jdosa_users}}', 'parent_user_id', $this->integer()->null()->after('role'));
        
        // Add company_id for subuser default company assignment
        $this->addColumn('{{%jdosa_users}}', 'company_id', $this->integer()->null()->after('parent_user_id'));
        
        // Add foreign key constraints
        $this->addForeignKey(
            'fk-jdosa_users-parent_user_id',
            '{{%jdosa_users}}',
            'parent_user_id',
            '{{%jdosa_users}}',
            'id',
            'CASCADE'
        );
        
        $this->addForeignKey(
            'fk-jdosa_users-company_id',
            '{{%jdosa_users}}',
            'company_id',
            '{{%jdosa_companies}}',
            'id',
            'SET NULL'
        );
        
        // Add index for better performance
        $this->createIndex('idx-jdosa_users-parent_user_id', '{{%jdosa_users}}', 'parent_user_id');
        $this->createIndex('idx-jdosa_users-company_id', '{{%jdosa_users}}', 'company_id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Drop foreign keys
        $this->dropForeignKey('fk-jdosa_users-parent_user_id', '{{%jdosa_users}}');
        $this->dropForeignKey('fk-jdosa_users-company_id', '{{%jdosa_users}}');
        
        // Drop indexes
        $this->dropIndex('idx-jdosa_users-parent_user_id', '{{%jdosa_users}}');
        $this->dropIndex('idx-jdosa_users-company_id', '{{%jdosa_users}}');
        
        // Drop columns
        $this->dropColumn('{{%jdosa_users}}', 'parent_user_id');
        $this->dropColumn('{{%jdosa_users}}', 'company_id');
    }
}