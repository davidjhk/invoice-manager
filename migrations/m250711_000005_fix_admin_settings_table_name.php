<?php

use yii\db\Migration;

/**
 * Handles fixing the admin_settings table name to jdosa_admin_settings.
 */
class m250711_000005_fix_admin_settings_table_name extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Check if admin_settings table exists (without prefix)
        $adminSettingsExists = $this->db->schema->getTableSchema('admin_settings', true);
        $jdosaAdminSettingsExists = $this->db->schema->getTableSchema('jdosa_admin_settings', true);
        
        if ($adminSettingsExists && !$jdosaAdminSettingsExists) {
            // Rename the table to include the jdosa_ prefix
            $this->renameTable('admin_settings', 'jdosa_admin_settings');
            echo "    > Renamed admin_settings table to jdosa_admin_settings\n";
        } elseif (!$adminSettingsExists && !$jdosaAdminSettingsExists) {
            // Create the table with proper name and data
            $this->createTable('jdosa_admin_settings', [
                'id' => $this->primaryKey(),
                'setting_key' => $this->string(100)->notNull()->unique(),
                'setting_value' => $this->text(),
                'description' => $this->text(),
                'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
                'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
            ]);
            
            // Insert default settings
            $this->batchInsert('jdosa_admin_settings', ['setting_key', 'setting_value', 'description'], [
                ['allow_signup', '0', 'Allow new user registration (1 = enabled, 0 = disabled)'],
                ['max_users', '100', 'Maximum number of users allowed'],
                ['site_maintenance', '0', 'Site maintenance mode (1 = enabled, 0 = disabled)'],
                ['password_min_length', '6', 'Minimum password length requirement'],
                ['session_timeout', '3600', 'Session timeout in seconds'],
                ['email_notifications', '1', 'Enable email notifications'],
                ['backup_enabled', '1', 'Enable automatic backups'],
                ['max_companies_per_user', '5', 'Maximum companies per user'],
            ]);
            
            echo "    > Created jdosa_admin_settings table with default data\n";
        } else {
            echo "    > jdosa_admin_settings table already exists\n";
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('jdosa_admin_settings');
    }
}