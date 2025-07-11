<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%admin_settings}}`.
 */
class m250711_000003_create_admin_settings_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%admin_settings}}', [
            'id' => $this->primaryKey(),
            'setting_key' => $this->string(100)->notNull()->unique(),
            'setting_value' => $this->text(),
            'description' => $this->text(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        // Insert default settings
        $this->insert('{{%admin_settings}}', [
            'setting_key' => 'allow_signup',
            'setting_value' => '0',
            'description' => 'Allow new user registration (1 = enabled, 0 = disabled)',
        ]);

        $this->insert('{{%admin_settings}}', [
            'setting_key' => 'max_users',
            'setting_value' => '100',
            'description' => 'Maximum number of users allowed',
        ]);

        $this->insert('{{%admin_settings}}', [
            'setting_key' => 'site_maintenance',
            'setting_value' => '0',
            'description' => 'Site maintenance mode (1 = enabled, 0 = disabled)',
        ]);

        $this->insert('{{%admin_settings}}', [
            'setting_key' => 'password_min_length',
            'setting_value' => '6',
            'description' => 'Minimum password length requirement',
        ]);

        $this->insert('{{%admin_settings}}', [
            'setting_key' => 'session_timeout',
            'setting_value' => '3600',
            'description' => 'Session timeout in seconds',
        ]);

        $this->insert('{{%admin_settings}}', [
            'setting_key' => 'email_notifications',
            'setting_value' => '1',
            'description' => 'Enable email notifications',
        ]);

        $this->insert('{{%admin_settings}}', [
            'setting_key' => 'backup_enabled',
            'setting_value' => '1',
            'description' => 'Enable automatic backups',
        ]);

        $this->insert('{{%admin_settings}}', [
            'setting_key' => 'max_companies_per_user',
            'setting_value' => '5',
            'description' => 'Maximum companies per user',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%admin_settings}}');
    }
}