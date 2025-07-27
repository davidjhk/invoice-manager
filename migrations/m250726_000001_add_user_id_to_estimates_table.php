<?php

use yii\db\Migration;

/**
 * Add user_id column to estimates table for multi-user support
 */
class m250726_000001_add_user_id_to_estimates_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Add user_id column to estimates table (if not exists)
        $estimatesTable = $this->db->schema->getTableSchema('{{%jdosa_estimates}}', true);
        if ($estimatesTable && !isset($estimatesTable->columns['user_id'])) {
            $this->addColumn('{{%jdosa_estimates}}', 'user_id', $this->integer()->null()->comment('Owner of the estimate'));
            
            // Create index for user_id column
            $this->createIndex('idx-jdosa_estimates-user_id', '{{%jdosa_estimates}}', 'user_id');
            
            // Add foreign key constraint
            $this->addForeignKey(
                'fk-jdosa_estimates-user_id',
                '{{%jdosa_estimates}}',
                'user_id',
                '{{%jdosa_users}}',
                'id',
                'CASCADE'
            );
        }

        // Update existing estimates to belong to admin user (if user_id is null)
        // First get the admin user ID
        $adminUser = (new \yii\db\Query())
            ->select(['id'])
            ->from('{{%jdosa_users}}')
            ->where(['username' => 'admin'])
            ->one();
        
        if ($adminUser) {
            $this->update('{{%jdosa_estimates}}', ['user_id' => $adminUser['id']], ['user_id' => null]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Drop foreign key constraint
        $this->dropForeignKey('fk-jdosa_estimates-user_id', '{{%jdosa_estimates}}');
        
        // Drop index
        $this->dropIndex('idx-jdosa_estimates-user_id', '{{%jdosa_estimates}}');
        
        // Drop user_id column from estimates table
        $this->dropColumn('{{%jdosa_estimates}}', 'user_id');
    }
}