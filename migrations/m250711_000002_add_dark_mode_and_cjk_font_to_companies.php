<?php

use yii\db\Migration;

/**
 * Class m250711_000002_add_dark_mode_and_cjk_font_to_companies
 */
class m250711_000002_add_dark_mode_and_cjk_font_to_companies extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%jdosa_companies}}', 'dark_mode', $this->boolean()->defaultValue(false)->comment('Enable dark mode for company'));
        $this->addColumn('{{%jdosa_companies}}', 'use_cjk_font', $this->boolean()->defaultValue(false)->comment('Use CJK fonts for PDF generation'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%jdosa_companies}}', 'dark_mode');
        $this->dropColumn('{{%jdosa_companies}}', 'use_cjk_font');
    }
}