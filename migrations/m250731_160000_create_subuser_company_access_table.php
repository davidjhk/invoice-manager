<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%subuser_company_access}}`.
 * This table manages which companies a subuser can access (Many-to-Many relationship).
 */
class m250731_160000_create_subuser_company_access_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%jdosa_subuser_company_access}}', [
            'id' => $this->primaryKey(),
            'subuser_id' => $this->integer()->notNull(),
            'company_id' => $this->integer()->notNull(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'created_by' => $this->integer()->notNull(),
        ]);

        // Add foreign key constraints
        $this->addForeignKey(
            'fk-jdosa_subuser_company_access-subuser_id',
            '{{%jdosa_subuser_company_access}}',
            'subuser_id',
            '{{%jdosa_users}}',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-jdosa_subuser_company_access-company_id',
            '{{%jdosa_subuser_company_access}}',
            'company_id',
            '{{%jdosa_companies}}',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-jdosa_subuser_company_access-created_by',
            '{{%jdosa_subuser_company_access}}',
            'created_by',
            '{{%jdosa_users}}',
            'id',
            'CASCADE'
        );

        // Add unique constraint to prevent duplicate access grants
        $this->createIndex(
            'idx-jdosa_subuser_company_access-unique',
            '{{%jdosa_subuser_company_access}}',
            ['subuser_id', 'company_id'],
            true
        );

        // Add index for better performance
        $this->createIndex('idx-jdosa_subuser_company_access-subuser_id', '{{%jdosa_subuser_company_access}}', 'subuser_id');
        $this->createIndex('idx-jdosa_subuser_company_access-company_id', '{{%jdosa_subuser_company_access}}', 'company_id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Drop foreign keys
        $this->dropForeignKey('fk-jdosa_subuser_company_access-subuser_id', '{{%jdosa_subuser_company_access}}');
        $this->dropForeignKey('fk-jdosa_subuser_company_access-company_id', '{{%jdosa_subuser_company_access}}');
        $this->dropForeignKey('fk-jdosa_subuser_company_access-created_by', '{{%jdosa_subuser_company_access}}');

        // Drop indexes
        $this->dropIndex('idx-jdosa_subuser_company_access-unique', '{{%jdosa_subuser_company_access}}');
        $this->dropIndex('idx-jdosa_subuser_company_access-subuser_id', '{{%jdosa_subuser_company_access}}');
        $this->dropIndex('idx-jdosa_subuser_company_access-company_id', '{{%jdosa_subuser_company_access}}');

        // Drop table
        $this->dropTable('{{%jdosa_subuser_company_access}}');
    }
}