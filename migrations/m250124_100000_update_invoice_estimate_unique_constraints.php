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
        // Drop existing unique constraints
        try {
            $this->dropIndex('invoice_number', 'jdosa_invoices');
        } catch (Exception $e) {
            // Index might not exist or have different name
            echo "Note: Could not drop invoice_number index: " . $e->getMessage() . "\n";
        }
        
        try {
            $this->dropIndex('estimate_number', 'jdosa_estimates');
        } catch (Exception $e) {
            // Index might not exist or have different name
            echo "Note: Could not drop estimate_number index: " . $e->getMessage() . "\n";
        }
        
        // Create composite unique constraints
        $this->createIndex(
            'idx_invoice_number_company_unique',
            'jdosa_invoices',
            ['invoice_number', 'company_id'],
            true // unique
        );
        
        $this->createIndex(
            'idx_estimate_number_company_unique',
            'jdosa_estimates',
            ['estimate_number', 'company_id'],
            true // unique
        );
        
        echo "Updated unique constraints for invoice_number and estimate_number to be unique per company.\n";
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Drop composite unique constraints
        $this->dropIndex('idx_invoice_number_company_unique', 'jdosa_invoices');
        $this->dropIndex('idx_estimate_number_company_unique', 'jdosa_estimates');
        
        // Restore original unique constraints (if needed)
        try {
            $this->createIndex('invoice_number', 'jdosa_invoices', 'invoice_number', true);
        } catch (Exception $e) {
            echo "Note: Could not restore original invoice_number unique constraint: " . $e->getMessage() . "\n";
        }
        
        try {
            $this->createIndex('estimate_number', 'jdosa_estimates', 'estimate_number', true);
        } catch (Exception $e) {
            echo "Note: Could not restore original estimate_number unique constraint: " . $e->getMessage() . "\n";
        }
        
        echo "Reverted unique constraints for invoice_number and estimate_number to global unique.\n";
    }
}