<?php

use yii\db\Migration;

/**
 * Add template selection to invoices and estimates tables
 */
class m250122_170000_add_template_to_invoices_and_estimates extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Add template column to invoices table (at the end to avoid column dependency issues)
        $this->addColumn('{{%jdosa_invoices}}', 'pdf_template', $this->string(50)->defaultValue('classic'));
        
        // Add template column to estimates table (at the end to avoid column dependency issues)
        $this->addColumn('{{%jdosa_estimates}}', 'pdf_template', $this->string(50)->defaultValue('classic'));
        
        // Create index for performance
        $this->createIndex('idx-jdosa_invoices-pdf_template', '{{%jdosa_invoices}}', 'pdf_template');
        $this->createIndex('idx-jdosa_estimates-pdf_template', '{{%jdosa_estimates}}', 'pdf_template');
        
        // Update existing records to use classic template
        $this->update('{{%jdosa_invoices}}', ['pdf_template' => 'classic'], ['pdf_template' => null]);
        $this->update('{{%jdosa_estimates}}', ['pdf_template' => 'classic'], ['pdf_template' => null]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Remove indexes
        $this->dropIndex('idx-jdosa_invoices-pdf_template', '{{%jdosa_invoices}}');
        $this->dropIndex('idx-jdosa_estimates-pdf_template', '{{%jdosa_estimates}}');
        
        // Remove columns
        $this->dropColumn('{{%jdosa_invoices}}', 'pdf_template');
        $this->dropColumn('{{%jdosa_estimates}}', 'pdf_template');
    }
}