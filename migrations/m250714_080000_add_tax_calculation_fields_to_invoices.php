<?php

use yii\db\Migration;

/**
 * Add tax calculation mode fields to invoices table
 */
class m250714_080000_add_tax_calculation_fields_to_invoices extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('jdosa_invoices', 'tax_calculation_mode', "ENUM('automatic', 'manual') DEFAULT 'manual' COMMENT 'Tax calculation mode'");
        $this->addColumn('jdosa_invoices', 'auto_calculated_tax_rate', "DECIMAL(7,4) DEFAULT NULL COMMENT 'Automatically calculated tax rate'");
        $this->addColumn('jdosa_invoices', 'tax_jurisdiction_id', "INT(11) DEFAULT NULL COMMENT 'Reference to tax jurisdiction used for calculation'");
        $this->addColumn('jdosa_invoices', 'tax_calculation_details', "TEXT DEFAULT NULL COMMENT 'JSON details of tax calculation breakdown'");
        
        // Add index for tax_jurisdiction_id
        $this->createIndex(
            'idx-invoices-tax_jurisdiction_id',
            'jdosa_invoices',
            'tax_jurisdiction_id'
        );
        
        // Add foreign key constraint to tax_jurisdictions table if it exists
        if ($this->db->schema->getTableSchema('jdosa_tax_jurisdictions') !== null) {
            $this->addForeignKey(
                'fk-invoices-tax_jurisdiction_id',
                'jdosa_invoices',
                'tax_jurisdiction_id',
                'jdosa_tax_jurisdictions',
                'id',
                'SET NULL',
                'CASCADE'
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Drop foreign key constraint first
        if ($this->db->schema->getTableSchema('jdosa_tax_jurisdictions') !== null) {
            $this->dropForeignKey('fk-invoices-tax_jurisdiction_id', 'jdosa_invoices');
        }
        
        // Drop index
        $this->dropIndex('idx-invoices-tax_jurisdiction_id', 'jdosa_invoices');
        
        // Drop columns
        $this->dropColumn('jdosa_invoices', 'tax_calculation_details');
        $this->dropColumn('jdosa_invoices', 'tax_jurisdiction_id');
        $this->dropColumn('jdosa_invoices', 'auto_calculated_tax_rate');
        $this->dropColumn('jdosa_invoices', 'tax_calculation_mode');
    }
}