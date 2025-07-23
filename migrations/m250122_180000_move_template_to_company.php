<?php

use yii\db\Migration;

/**
 * Move template selection from invoices/estimates to companies table
 */
class m250122_180000_move_template_to_company extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Add template column to companies table
        $this->addColumn('{{%jdosa_companies}}', 'pdf_template', $this->string(50)->defaultValue('classic')->after('use_cjk_font'));
        
        // Create index for performance
        $this->createIndex('idx-jdosa_companies-pdf_template', '{{%jdosa_companies}}', 'pdf_template');
        
        // Set default template for existing companies
        $this->update('{{%jdosa_companies}}', ['pdf_template' => 'classic'], ['pdf_template' => null]);
        
        // Remove template columns from invoices and estimates tables
        $this->dropIndex('idx-jdosa_invoices-pdf_template', '{{%jdosa_invoices}}');
        $this->dropColumn('{{%jdosa_invoices}}', 'pdf_template');
        
        $this->dropIndex('idx-jdosa_estimates-pdf_template', '{{%jdosa_estimates}}');
        $this->dropColumn('{{%jdosa_estimates}}', 'pdf_template');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Add template columns back to invoices and estimates tables
        $this->addColumn('{{%jdosa_invoices}}', 'pdf_template', $this->string(50)->defaultValue('classic')->after('shipping_fee'));
        $this->addColumn('{{%jdosa_estimates}}', 'pdf_template', $this->string(50)->defaultValue('classic')->after('shipping_fee'));
        
        // Create indexes
        $this->createIndex('idx-jdosa_invoices-pdf_template', '{{%jdosa_invoices}}', 'pdf_template');
        $this->createIndex('idx-jdosa_estimates-pdf_template', '{{%jdosa_estimates}}', 'pdf_template');
        
        // Set default values
        $this->update('{{%jdosa_invoices}}', ['pdf_template' => 'classic'], ['pdf_template' => null]);
        $this->update('{{%jdosa_estimates}}', ['pdf_template' => 'classic'], ['pdf_template' => null]);
        
        // Remove template column from companies table
        $this->dropIndex('idx-jdosa_companies-pdf_template', '{{%jdosa_companies}}');
        $this->dropColumn('{{%jdosa_companies}}', 'pdf_template');
    }
}