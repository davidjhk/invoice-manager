<?php

use yii\db\Migration;

/**
 * Add hide_footer column to jdosa_companies table
 */
class m250717_000002_add_hide_footer_to_companies extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%companies}}', 'hide_footer', $this->boolean()->defaultValue(false)->comment('Hide footer text in PDF documents'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%companies}}', 'hide_footer');
    }
}