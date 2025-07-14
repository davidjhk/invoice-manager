<?php

use yii\db\Migration;

/**
 * Migration to add US Sales Tax automation fields
 * 
 * Adds necessary fields for:
 * - Automatic tax calculation based on state/jurisdiction
 * - Tax jurisdiction tracking
 * - Economic nexus compliance
 * - Enhanced tax reporting
 */
class m250714_000001_add_us_sales_tax_fields extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Add US Sales Tax fields to companies table
        $this->addCompanyTaxFields();
        
        // Add tax jurisdiction fields to customers table
        $this->addCustomerTaxFields();
        
        // Add enhanced tax fields to invoices table
        $this->addInvoiceTaxFields();
        
        // Add enhanced tax fields to invoice items table
        $this->addInvoiceItemTaxFields();
        
        // Add enhanced tax fields to estimates table
        $this->addEstimateTaxFields();
        
        // Add enhanced tax fields to estimate items table
        $this->addEstimateItemTaxFields();
        
        // Create tax rates history table for audit trail
        $this->createTaxRatesHistoryTable();
        
        // Create indexes for better performance
        $this->createTaxIndexes();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Drop the tax rates history table
        $this->dropTable('{{%jdosa_tax_rates_history}}');
        
        // Remove fields from estimate items table
        $this->removeEstimateItemTaxFields();
        
        // Remove fields from estimates table
        $this->removeEstimateTaxFields();
        
        // Remove fields from invoice items table
        $this->removeInvoiceItemTaxFields();
        
        // Remove fields from invoices table
        $this->removeInvoiceTaxFields();
        
        // Remove fields from customers table
        $this->removeCustomerTaxFields();
        
        // Remove fields from companies table
        $this->removeCompanyTaxFields();
    }

    /**
     * Add US Sales Tax fields to companies table
     */
    private function addCompanyTaxFields()
    {
        $table = '{{%jdosa_companies}}';
        $schema = $this->db->schema->getTableSchema($table, true);
        
        if ($schema) {
            // Default tax state for company's base location
            if (!isset($schema->columns['tax_state_code'])) {
                $this->addColumn($table, 'tax_state_code', $this->string(2)
                    ->comment('Default state code for tax calculation (e.g., CA, NY)'));
            }
            
            // ZIP code for more precise local tax calculation
            if (!isset($schema->columns['tax_zip_code'])) {
                $this->addColumn($table, 'tax_zip_code', $this->string(10)
                    ->comment('ZIP code for local tax calculation'));
            }
            
            // Whether to use local tax rates or just state rates
            if (!isset($schema->columns['use_local_tax'])) {
                $this->addColumn($table, 'use_local_tax', $this->boolean()
                    ->defaultValue(true)
                    ->comment('Whether to include local tax rates in calculation'));
            }
            
            // Automatic tax calculation enabled
            if (!isset($schema->columns['auto_tax_calculation'])) {
                $this->addColumn($table, 'auto_tax_calculation', $this->boolean()
                    ->defaultValue(false)
                    ->comment('Enable automatic US sales tax calculation'));
            }
            
            // Tax exemption certificate on file
            if (!isset($schema->columns['tax_exempt'])) {
                $this->addColumn($table, 'tax_exempt', $this->boolean()
                    ->defaultValue(false)
                    ->comment('Company is tax exempt'));
            }
            
            // Tax ID number for exemption
            if (!isset($schema->columns['tax_id_number'])) {
                $this->addColumn($table, 'tax_id_number', $this->string(50)
                    ->comment('Tax ID/EIN for exemption purposes'));
            }
        }
    }

    /**
     * Add tax jurisdiction fields to customers table
     */
    private function addCustomerTaxFields()
    {
        $table = '{{%jdosa_customers}}';
        $schema = $this->db->schema->getTableSchema($table, true);
        
        if ($schema) {
            // Customer's tax state (billing address state)
            if (!isset($schema->columns['tax_state_code'])) {
                $this->addColumn($table, 'tax_state_code', $this->string(2)
                    ->comment('Customer billing state for tax calculation'));
            }
            
            // Customer's ZIP code for local tax
            if (!isset($schema->columns['tax_zip_code'])) {
                $this->addColumn($table, 'tax_zip_code', $this->string(10)
                    ->comment('Customer ZIP code for local tax calculation'));
            }
            
            // Tax exemption status
            if (!isset($schema->columns['tax_exempt'])) {
                $this->addColumn($table, 'tax_exempt', $this->boolean()
                    ->defaultValue(false)
                    ->comment('Customer is tax exempt'));
            }
            
            // Tax exemption certificate number
            if (!isset($schema->columns['tax_exempt_certificate'])) {
                $this->addColumn($table, 'tax_exempt_certificate', $this->string(100)
                    ->comment('Tax exemption certificate number'));
            }
            
            // Tax exemption expiry date
            if (!isset($schema->columns['tax_exempt_expiry'])) {
                $this->addColumn($table, 'tax_exempt_expiry', $this->date()
                    ->comment('Tax exemption certificate expiry date'));
            }
            
            // Customer type for tax purposes (business, individual, government, etc.)
            if (!isset($schema->columns['customer_type'])) {
                $this->addColumn($table, 'customer_type', "ENUM('individual', 'business', 'government', 'nonprofit') DEFAULT 'business' COMMENT 'Customer type for tax classification'");
            }
        }
    }

    /**
     * Add enhanced tax fields to invoices table
     */
    private function addInvoiceTaxFields()
    {
        $table = '{{%jdosa_invoices}}';
        $schema = $this->db->schema->getTableSchema($table, true);
        
        if ($schema) {
            // Tax jurisdiction used for calculation
            if (!isset($schema->columns['tax_state_code'])) {
                $this->addColumn($table, 'tax_state_code', $this->string(2)
                    ->comment('State code used for tax calculation'));
            }
            
            // ZIP code used for local tax
            if (!isset($schema->columns['tax_zip_code'])) {
                $this->addColumn($table, 'tax_zip_code', $this->string(10)
                    ->comment('ZIP code used for local tax calculation'));
            }
            
            // Base state tax rate applied
            if (!isset($schema->columns['tax_rate_state'])) {
                $this->addColumn($table, 'tax_rate_state', $this->decimal(5, 4)
                    ->defaultValue(0.0000)
                    ->comment('State tax rate applied (e.g., 6.0000 for 6%)'));
            }
            
            // Local tax rate applied
            if (!isset($schema->columns['tax_rate_local'])) {
                $this->addColumn($table, 'tax_rate_local', $this->decimal(5, 4)
                    ->defaultValue(0.0000)
                    ->comment('Local tax rate applied (e.g., 2.5000 for 2.5%)'));
            }
            
            // Total combined tax rate
            if (!isset($schema->columns['tax_rate_total'])) {
                $this->addColumn($table, 'tax_rate_total', $this->decimal(5, 4)
                    ->defaultValue(0.0000)
                    ->comment('Total combined tax rate (state + local)'));
            }
            
            // Tax calculation method used
            if (!isset($schema->columns['tax_calculation_method'])) {
                $this->addColumn($table, 'tax_calculation_method', "ENUM('manual', 'automatic', 'api') DEFAULT 'manual' COMMENT 'Method used for tax calculation'");
            }
            
            // Economic nexus status at time of invoice
            if (!isset($schema->columns['nexus_confirmed'])) {
                $this->addColumn($table, 'nexus_confirmed', $this->boolean()
                    ->defaultValue(false)
                    ->comment('Economic nexus confirmed for this jurisdiction'));
            }
            
            // Tax exemption applied
            if (!isset($schema->columns['tax_exemption_applied'])) {
                $this->addColumn($table, 'tax_exemption_applied', $this->boolean()
                    ->defaultValue(false)
                    ->comment('Tax exemption was applied to this invoice'));
            }
            
            // Tax exemption reason/certificate
            if (!isset($schema->columns['tax_exemption_reason'])) {
                $this->addColumn($table, 'tax_exemption_reason', $this->string(255)
                    ->comment('Reason or certificate number for tax exemption'));
            }
        }
    }

    /**
     * Add enhanced tax fields to invoice items table
     */
    private function addInvoiceItemTaxFields()
    {
        $table = '{{%jdosa_invoice_items}}';
        $schema = $this->db->schema->getTableSchema($table, true);
        
        if ($schema) {
            // Tax category for item (e.g., goods, services, digital, exempt)
            if (!isset($schema->columns['tax_category'])) {
                $this->addColumn($table, 'tax_category', "ENUM('goods', 'services', 'digital', 'exempt') DEFAULT 'goods' COMMENT 'Tax category for item classification'");
            }
            
            // Whether item qualifies for specific exemptions
            if (!isset($schema->columns['tax_exempt_reason'])) {
                $this->addColumn($table, 'tax_exempt_reason', $this->string(255)
                    ->comment('Reason for tax exemption if applicable'));
            }
        }
    }

    /**
     * Add enhanced tax fields to estimates table
     */
    private function addEstimateTaxFields()
    {
        $table = '{{%jdosa_estimates}}';
        $schema = $this->db->schema->getTableSchema($table, true);
        
        if ($schema) {
            // Tax jurisdiction for estimate
            if (!isset($schema->columns['tax_state_code'])) {
                $this->addColumn($table, 'tax_state_code', $this->string(2)
                    ->comment('State code for tax calculation'));
            }
            
            // ZIP code for local tax
            if (!isset($schema->columns['tax_zip_code'])) {
                $this->addColumn($table, 'tax_zip_code', $this->string(10)
                    ->comment('ZIP code for local tax calculation'));
            }
            
            // Base state tax rate
            if (!isset($schema->columns['tax_rate_state'])) {
                $this->addColumn($table, 'tax_rate_state', $this->decimal(5, 4)
                    ->defaultValue(0.0000)
                    ->comment('State tax rate applied'));
            }
            
            // Local tax rate
            if (!isset($schema->columns['tax_rate_local'])) {
                $this->addColumn($table, 'tax_rate_local', $this->decimal(5, 4)
                    ->defaultValue(0.0000)
                    ->comment('Local tax rate applied'));
            }
            
            // Total combined tax rate
            if (!isset($schema->columns['tax_rate_total'])) {
                $this->addColumn($table, 'tax_rate_total', $this->decimal(5, 4)
                    ->defaultValue(0.0000)
                    ->comment('Total combined tax rate'));
            }
            
            // Tax calculation method
            if (!isset($schema->columns['tax_calculation_method'])) {
                $this->addColumn($table, 'tax_calculation_method', "ENUM('manual', 'automatic', 'api') DEFAULT 'manual' COMMENT 'Method used for tax calculation'");
            }
        }
    }

    /**
     * Add enhanced tax fields to estimate items table
     */
    private function addEstimateItemTaxFields()
    {
        $table = '{{%jdosa_estimate_items}}';
        $schema = $this->db->schema->getTableSchema($table, true);
        
        if ($schema) {
            // Tax category for item
            if (!isset($schema->columns['tax_category'])) {
                $this->addColumn($table, 'tax_category', "ENUM('goods', 'services', 'digital', 'exempt') DEFAULT 'goods' COMMENT 'Tax category for item classification'");
            }
            
            // Tax exemption reason
            if (!isset($schema->columns['tax_exempt_reason'])) {
                $this->addColumn($table, 'tax_exempt_reason', $this->string(255)
                    ->comment('Reason for tax exemption if applicable'));
            }
        }
    }

    /**
     * Create tax rates history table for audit trail
     */
    private function createTaxRatesHistoryTable()
    {
        if (!$this->db->schema->getTableSchema('{{%jdosa_tax_rates_history}}', true)) {
            $this->createTable('{{%jdosa_tax_rates_history}}', [
                'id' => $this->primaryKey(),
                'state_code' => $this->string(2)->notNull()->comment('State code'),
                'zip_code' => $this->string(10)->comment('ZIP code if applicable'),
                'tax_rate_state' => $this->decimal(5, 4)->notNull()->comment('State tax rate'),
                'tax_rate_local' => $this->decimal(5, 4)->defaultValue(0.0000)->comment('Local tax rate'),
                'tax_rate_total' => $this->decimal(5, 4)->notNull()->comment('Total combined rate'),
                'effective_date' => $this->date()->notNull()->comment('When this rate became effective'),
                'source' => $this->string(50)->defaultValue('manual')->comment('Source of rate data (manual, api, import)'),
                'notes' => $this->text()->comment('Additional notes about rate change'),
                'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
                'created_by' => $this->integer()->comment('User who created this record'),
            ]);
            
            // Create indexes for tax rates history
            $this->createIndex('idx-tax_rates_history-state_code', '{{%jdosa_tax_rates_history}}', 'state_code');
            $this->createIndex('idx-tax_rates_history-zip_code', '{{%jdosa_tax_rates_history}}', 'zip_code');
            $this->createIndex('idx-tax_rates_history-effective_date', '{{%jdosa_tax_rates_history}}', 'effective_date');
            $this->createIndex('idx-tax_rates_history-state_zip', '{{%jdosa_tax_rates_history}}', ['state_code', 'zip_code']);
        }
    }

    /**
     * Create indexes for better tax-related query performance
     */
    private function createTaxIndexes()
    {
        // Helper method to safely create index
        $createIndexSafely = function($name, $table, $columns) {
            try {
                $this->createIndex($name, $table, $columns);
            } catch (\Exception $e) {
                // Index already exists, skip
                if (strpos($e->getMessage(), 'Duplicate key name') === false) {
                    throw $e;
                }
            }
        };

        // Indexes for companies table
        $createIndexSafely('idx-companies-tax_state_code', '{{%jdosa_companies}}', 'tax_state_code');
        $createIndexSafely('idx-companies-auto_tax', '{{%jdosa_companies}}', 'auto_tax_calculation');
        
        // Indexes for customers table
        $createIndexSafely('idx-customers-tax_state_code', '{{%jdosa_customers}}', 'tax_state_code');
        $createIndexSafely('idx-customers-tax_exempt', '{{%jdosa_customers}}', 'tax_exempt');
        $createIndexSafely('idx-customers-customer_type', '{{%jdosa_customers}}', 'customer_type');
        
        // Indexes for invoices table
        $createIndexSafely('idx-invoices-tax_state_code', '{{%jdosa_invoices}}', 'tax_state_code');
        $createIndexSafely('idx-invoices-tax_calculation_method', '{{%jdosa_invoices}}', 'tax_calculation_method');
        $createIndexSafely('idx-invoices-nexus_confirmed', '{{%jdosa_invoices}}', 'nexus_confirmed');
        
        // Indexes for estimates table
        $createIndexSafely('idx-estimates-tax_state_code', '{{%jdosa_estimates}}', 'tax_state_code');
        $createIndexSafely('idx-estimates-tax_calculation_method', '{{%jdosa_estimates}}', 'tax_calculation_method');
        
        // Indexes for invoice items table
        $createIndexSafely('idx-invoice_items-tax_category', '{{%jdosa_invoice_items}}', 'tax_category');
        
        // Indexes for estimate items table
        $createIndexSafely('idx-estimate_items-tax_category', '{{%jdosa_estimate_items}}', 'tax_category');
    }

    /**
     * Remove fields from companies table
     */
    private function removeCompanyTaxFields()
    {
        $table = '{{%jdosa_companies}}';
        $columns = ['tax_state_code', 'tax_zip_code', 'use_local_tax', 'auto_tax_calculation', 'tax_exempt', 'tax_id_number'];
        
        foreach ($columns as $column) {
            if ($this->db->schema->getTableSchema($table, true)->getColumn($column)) {
                $this->dropColumn($table, $column);
            }
        }
    }

    /**
     * Remove fields from customers table
     */
    private function removeCustomerTaxFields()
    {
        $table = '{{%jdosa_customers}}';
        $columns = ['tax_state_code', 'tax_zip_code', 'tax_exempt', 'tax_exempt_certificate', 'tax_exempt_expiry', 'customer_type'];
        
        foreach ($columns as $column) {
            if ($this->db->schema->getTableSchema($table, true)->getColumn($column)) {
                $this->dropColumn($table, $column);
            }
        }
    }

    /**
     * Remove fields from invoices table
     */
    private function removeInvoiceTaxFields()
    {
        $table = '{{%jdosa_invoices}}';
        $columns = ['tax_state_code', 'tax_zip_code', 'tax_rate_state', 'tax_rate_local', 'tax_rate_total', 
                   'tax_calculation_method', 'nexus_confirmed', 'tax_exemption_applied', 'tax_exemption_reason'];
        
        foreach ($columns as $column) {
            if ($this->db->schema->getTableSchema($table, true)->getColumn($column)) {
                $this->dropColumn($table, $column);
            }
        }
    }

    /**
     * Remove fields from invoice items table
     */
    private function removeInvoiceItemTaxFields()
    {
        $table = '{{%jdosa_invoice_items}}';
        $columns = ['tax_category', 'tax_exempt_reason'];
        
        foreach ($columns as $column) {
            if ($this->db->schema->getTableSchema($table, true)->getColumn($column)) {
                $this->dropColumn($table, $column);
            }
        }
    }

    /**
     * Remove fields from estimates table
     */
    private function removeEstimateTaxFields()
    {
        $table = '{{%jdosa_estimates}}';
        $columns = ['tax_state_code', 'tax_zip_code', 'tax_rate_state', 'tax_rate_local', 'tax_rate_total', 'tax_calculation_method'];
        
        foreach ($columns as $column) {
            if ($this->db->schema->getTableSchema($table, true)->getColumn($column)) {
                $this->dropColumn($table, $column);
            }
        }
    }

    /**
     * Remove fields from estimate items table
     */
    private function removeEstimateItemTaxFields()
    {
        $table = '{{%jdosa_estimate_items}}';
        $columns = ['tax_category', 'tax_exempt_reason'];
        
        foreach ($columns as $column) {
            if ($this->db->schema->getTableSchema($table, true)->getColumn($column)) {
                $this->dropColumn($table, $column);
            }
        }
    }
}