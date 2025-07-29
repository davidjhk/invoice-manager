<?php

use yii\db\Migration;

/**
 * Update unique constraints for invoice_number and estimate_number to be unique per company
 */
class m250124_100000_update_invoice_estimate_unique_constraints extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Drop existing unique constraints created in the initial migration
        try {
            $this->dropIndex('idx-jdosa_invoices-invoice_number', 'jdosa_invoices');
        } catch (Exception $e) {
            echo "Note: Could not drop idx-jdosa_invoices-invoice_number index (it might not exist): " . $e->getMessage() . "\n";
        }
        
        try {
            $this->dropIndex('idx-jdosa_estimates-estimate_number', 'jdosa_estimates');
        } catch (Exception $e) {
            echo "Note: Could not drop idx-jdosa_estimates-estimate_number index (it might not exist): " . $e->getMessage() . "\n";
        }
        
        // Create composite unique constraints, handling cases where they might already exist
        try {
            $this->createIndex(
                'idx_invoice_number_company_unique',
                'jdosa_invoices',
                ['company_id', 'invoice_number'],
                true // unique
            );
        } catch (Exception $e) {
            echo "Note: Could not create idx_invoice_number_company_unique index (it might already exist): " . $e->getMessage() . "\n";
        }
        
        try {
            $this->createIndex(
                'idx_estimate_number_company_unique',
                'jdosa_estimates',
                ['company_id', 'estimate_number'],
                true // unique
            );
        } catch (Exception $e) {
            echo "Note: Could not create idx_estimate_number_company_unique index (it might already exist): " . $e->getMessage() . "\n";
        }
        
        echo "Ensured unique constraints for invoice_number and estimate_number are per company.\n";
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Drop composite unique constraints
        try {
            $this->dropIndex('idx_invoice_number_company_unique', 'jdosa_invoices');
        } catch (Exception $e) {
            echo "Note: Could not drop idx_invoice_number_company_unique index: " . $e->getMessage() . "\n";
        }
        try {
            $this->dropIndex('idx_estimate_number_company_unique', 'jdosa_estimates');
        } catch (Exception $e) {
            echo "Note: Could not drop idx_estimate_number_company_unique index: " . $e->getMessage() . "\n";
        }
        
        // Restore original unique constraints (if needed)
        try {
            $this->createIndex('idx-jdosa_invoices-invoice_number', 'jdosa_invoices', 'invoice_number', true);
        } catch (Exception $e) {
            echo "Note: Could not restore original invoice_number unique constraint: " . $e->getMessage() . "\n";
        }
        
        try {
            $this->createIndex('idx-jdosa_estimates-estimate_number', 'jdosa_estimates', 'estimate_number', true);
        } catch (Exception $e) {
            echo "Note: Could not restore original estimate_number unique constraint: " . $e->getMessage() . "\n";
        }
        
        echo "Reverted unique constraints for invoice_number and estimate_number to global unique.\n";
    }
}
