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
            'setting_value' => '1',
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
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%admin_settings}}');
    }
}