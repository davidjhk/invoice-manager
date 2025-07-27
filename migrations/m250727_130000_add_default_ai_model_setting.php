<?php

use yii\db\Migration;

/**
 * Adds default AI model setting to admin settings
 */
class m250727_130000_add_default_ai_model_setting extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Check if the setting already exists
        $exists = $this->db->createCommand(
            "SELECT COUNT(*) FROM {{%jdosa_admin_settings}} WHERE setting_key = 'ai_model'"
        )->queryScalar();

        if ($exists == 0) {
            // Insert default AI model setting
            $this->insert('{{%jdosa_admin_settings}}', [
                'setting_key' => 'ai_model',
                'setting_value' => 'anthropic/claude-3.5-sonnet',
                'description' => 'AI model used for OpenRouter API requests',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->delete('{{%jdosa_admin_settings}}', ['setting_key' => 'ai_model']);
    }
}