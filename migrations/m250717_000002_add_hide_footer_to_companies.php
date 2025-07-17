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
        // Check if column already exists to avoid error
        $tableSchema = $this->db->getTableSchema('{{%companies}}');
        if ($tableSchema && !isset($tableSchema->columns['hide_footer'])) {
            $this->addColumn('{{%companies}}', 'hide_footer', $this->boolean()->defaultValue(false)->comment('Hide footer text in PDF documents'));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Check if column exists before dropping it
        $tableSchema = $this->db->getTableSchema('{{%companies}}');
        if ($tableSchema && isset($tableSchema->columns['hide_footer'])) {
            $this->dropColumn('{{%companies}}', 'hide_footer');
        }
    }
}