<?php

use yii\db\Migration;

/**
 * Handles adding role column to users table.
 */
class m250711_000004_add_role_to_users_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Check if role column already exists
        $tableSchema = $this->db->getTableSchema('{{%jdosa_users}}');
        if (!isset($tableSchema->columns['role'])) {
            // Add role column to users table
            $this->addColumn('{{%jdosa_users}}', 'role', "ENUM('admin', 'user', 'demo') DEFAULT 'user' COMMENT 'User role (admin, user, or demo)'");
            
            // Create index for role column
            $this->createIndex('idx-jdosa_users-role', '{{%jdosa_users}}', 'role');
            
            // Update existing users - set first user as admin
            $this->update('{{%jdosa_users}}', ['role' => 'admin'], ['id' => 1]);
        }
        
        // Check if demo user already exists
        $demoUser = $this->db->createCommand('SELECT id FROM {{%jdosa_users}} WHERE username = :username', [':username' => 'demo'])->queryScalar();
        if (!$demoUser) {
            // Create a demo user for testing
            $this->insert('{{%jdosa_users}}', [
                'username' => 'demo',
                'email' => 'demo@example.com',
                'password_hash' => '$2y$13$nJ1WDlBaGcbCdbNC9wYL5.bEFPPRxnI8r/jkBMfJl8iWlEGlz1l4K', // password: admin123
                'full_name' => 'Demo User',
                'login_type' => 'local',
                'is_active' => true,
                'email_verified' => true,
                'auth_key' => 'demo_auth_key_' . time(),
                'role' => 'demo',
                'max_companies' => 1,
            ]);
        }
        
        // All other users remain as 'user' (default value)
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Drop the role column
        $this->dropColumn('{{%jdosa_users}}', 'role');
    }
}