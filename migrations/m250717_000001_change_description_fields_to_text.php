<?php

use yii\db\Migration;

/**
 * Changes description fields from varchar(500) to text for better content storage
 * Affects jdosa_invoice_items and jdosa_estimate_items tables
 */
class m250717_000001_change_description_fields_to_text extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Change description field in jdosa_invoice_items table from varchar(500) to text
        $this->alterColumn('{{%jdosa_invoice_items}}', 'description', $this->text()->notNull());
        
        // Change description field in jdosa_estimate_items table from varchar(500) to text
        $this->alterColumn('{{%jdosa_estimate_items}}', 'description', $this->text()->notNull());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Revert back to varchar(500) - note: data may be truncated if text is longer than 500 characters
        $this->alterColumn('{{%jdosa_invoice_items}}', 'description', $this->string(500)->notNull());
        $this->alterColumn('{{%jdosa_estimate_items}}', 'description', $this->string(500)->notNull());
    }
}