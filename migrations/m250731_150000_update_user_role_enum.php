<?php

use yii\db\Migration;

/**
 * Updates user role column to support 'subuser' role
 */
class m250731_150000_update_user_role_enum extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Check if role column is ENUM or VARCHAR
        $tableSchema = $this->db->getTableSchema('{{%jdosa_users}}');
        $roleColumn = $tableSchema->getColumn('role');
        
        if ($roleColumn) {
            // If it's an ENUM, we need to modify it to include 'subuser'
            // If it's VARCHAR, we might need to increase the length
            
            // First, let's try to modify the ENUM to include 'subuser'
            $this->execute("ALTER TABLE {{%jdosa_users}} MODIFY COLUMN `role` ENUM('admin', 'user', 'demo', 'subuser') DEFAULT 'user'");
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Remove 'subuser' from ENUM (this will fail if there are existing subuser records)
        $this->execute("ALTER TABLE {{%jdosa_users}} MODIFY COLUMN `role` ENUM('admin', 'user', 'demo') DEFAULT 'user'");
    }
}