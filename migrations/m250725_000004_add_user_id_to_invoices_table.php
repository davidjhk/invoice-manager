<![CDATA[<?php

use yii\db\Migration;

/**
 * Add user_id column to invoices table for multi-user support
 */
class m250725_000004_add_user_id_to_invoices_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Add user_id column to invoices table (if not exists)
        $invoicesTable = $this->db->schema->getTableSchema('{{%jdosa_invoices}}', true);
        if ($invoicesTable && !isset($invoicesTable->columns['user_id'])) {
            $this->addColumn('{{%jdosa_invoices}}', 'user_id', $this->integer()->null()->comment('Owner of the invoice'));
            
            // Create index for user_id column
            $this->createIndex('idx-jdosa_invoices-user_id', '{{%jdosa_invoices}}', 'user_id');
            
            // Add foreign key constraint
            $this->addForeignKey(
                'fk-jdosa_invoices-user_id',
                '{{%jdosa_invoices}}',
                'user_id',
                '{{%jdosa_users}}',
                'id',
                'CASCADE'
            );
        }

        // Update existing invoices to belong to admin user (if user_id is null)
        // First get the admin user ID
        $adminUser = (new \yii\db\Query())
            ->select(['id'])
            ->from('{{%jdosa_users}}')
            ->where(['username' => 'admin'])
            ->one();
        
        if ($adminUser) {
            $this->update('{{%jdosa_invoices}}', ['user_id' => $adminUser['id']], ['user_id' => null]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Drop foreign key constraint
        $this->dropForeignKey('fk-jdosa_invoices-user_id', '{{%jdosa_invoices}}');
        
        // Drop index
        $this->dropIndex('idx-jdosa_invoices-user_id', '{{%jdosa_invoices}}');
        
        // Drop user_id column from invoices table
        $this->dropColumn('{{%jdosa_invoices}}', 'user_id');
    }
}
