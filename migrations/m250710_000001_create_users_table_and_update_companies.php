<?php

use yii\db\Migration;

/**
 * Handles the creation of users table and updating companies table for multi-user support
 * This migration adds Google SSO support and user ownership to companies
 */
class m250710_000001_create_users_table_and_update_companies extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Create jdosa_users table
        $this->createTable('{{%jdosa_users}}', [
            'id' => $this->primaryKey(),
            'username' => $this->string(50)->unique()->null()->comment('Username for local login (null for Google SSO)'),
            'email' => $this->string(255)->unique()->notNull()->comment('Email address (primary identifier)'),
            'password_hash' => $this->string(255)->null()->comment('Password hash for local login (null for Google SSO)'),
            'full_name' => $this->string(100)->comment('Full name of the user'),
            'google_id' => $this->string(100)->unique()->null()->comment('Google user ID for SSO'),
            'avatar_url' => $this->string(500)->null()->comment('Profile picture URL'),
            'login_type' => "ENUM('local', 'google') DEFAULT 'local'",
            'is_active' => $this->boolean()->defaultValue(true)->comment('Whether user account is active'),
            'email_verified' => $this->boolean()->defaultValue(false)->comment('Whether email is verified'),
            'auth_key' => $this->string(32)->comment('Authentication key for auto-login'),
            'password_reset_token' => $this->string(255)->unique()->null()->comment('Password reset token'),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        // Create indexes for users table
        $this->createIndex('idx-jdosa_users-email', '{{%jdosa_users}}', 'email');
        $this->createIndex('idx-jdosa_users-username', '{{%jdosa_users}}', 'username');
        $this->createIndex('idx-jdosa_users-google_id', '{{%jdosa_users}}', 'google_id');
        $this->createIndex('idx-jdosa_users-is_active', '{{%jdosa_users}}', 'is_active');
        $this->createIndex('idx-jdosa_users-login_type', '{{%jdosa_users}}', 'login_type');

        // Add user_id column to companies table (if not exists)
        $companiesTable = $this->db->schema->getTableSchema('{{%jdosa_companies}}', true);
        if ($companiesTable && !isset($companiesTable->columns['user_id'])) {
            $this->addColumn('{{%jdosa_companies}}', 'user_id', $this->integer()->null()->comment('Owner of the company'));
            $this->createIndex('idx-jdosa_companies-user_id', '{{%jdosa_companies}}', 'user_id');
            
            // Add foreign key constraint
            $this->addForeignKey(
                'fk-jdosa_companies-user_id',
                '{{%jdosa_companies}}',
                'user_id',
                '{{%jdosa_users}}',
                'id',
                'SET NULL'
            );
        }

        // Create a default admin user for testing
        $this->insert('{{%jdosa_users}}', [
            'username' => 'admin',
            'email' => 'admin@example.com',
            'password_hash' => '$2y$13$nJ1WDlBaGcbCdbNC9wYL5.bEFPPRxnI8r/jkBMfJl8iWlEGlz1l4K', // password: admin123
            'full_name' => 'System Administrator',
            'login_type' => 'local',
            'is_active' => true,
            'email_verified' => true,
            'auth_key' => 'admin_auth_key_' . time(),
        ]);

        // Get the admin user ID
        $adminUserId = $this->db->getLastInsertID();

        // Update existing companies to belong to admin user
        $this->update('{{%jdosa_companies}}', ['user_id' => $adminUserId], ['user_id' => null]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Remove foreign key constraint
        $this->dropForeignKey('fk-jdosa_companies-user_id', '{{%jdosa_companies}}');
        
        // Remove user_id column from companies table
        $this->dropColumn('{{%jdosa_companies}}', 'user_id');
        
        // Drop users table
        $this->dropTable('{{%jdosa_users}}');
    }
}