<?php

use yii\db\Migration;

/**
 * Handles the creation of initial database structure for invoice system
 * This migration creates all necessary tables for a new server installation
 */
class m250705_000001_create_initial_database_structure extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Create jdosa_companies table (only if it doesn't exist)
        if (!$this->db->schema->getTableSchema('{{%jdosa_companies}}', true)) {
            $this->createTable('{{%jdosa_companies}}', [
            'id' => $this->primaryKey(),
            'company_name' => $this->string(255)->notNull(),
            'company_address' => $this->text(),
            'company_phone' => $this->string(50),
            'company_email' => $this->string(255),
            'logo_path' => $this->string(500),
            'logo_filename' => $this->string(255),
            'smtp2go_api_key' => $this->string(255),
            'sender_email' => $this->string(255),
            'tax_rate' => $this->decimal(5, 2)->defaultValue(10.00),
            'currency' => $this->string(10)->defaultValue('USD'),
            'invoice_prefix' => $this->string(20)->defaultValue('INV-'),
            'estimate_prefix' => $this->string(20)->defaultValue('EST-'),
            'due_date_days' => $this->integer()->defaultValue(30),
            'is_active' => $this->boolean()->defaultValue(true),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);
        } else {
            // If table exists, add missing columns
            $this->addMissingColumns();
        }

        // Create jdosa_customers table (only if it doesn't exist)
        if (!$this->db->schema->getTableSchema('{{%jdosa_customers}}', true)) {
            $this->createTable('{{%jdosa_customers}}', [
            'id' => $this->primaryKey(),
            'customer_name' => $this->string(255)->notNull(),
            'customer_address' => $this->text(),
            'payment_terms' => $this->string(50)->defaultValue('Net 30'),
            'customer_phone' => $this->string(50),
            'customer_fax' => $this->string(50),
            'customer_mobile' => $this->string(50),
            'customer_email' => $this->string(255),
            'company_id' => $this->integer()->notNull(),
            'is_active' => $this->boolean()->defaultValue(true),
            'contact_name' => $this->string(255)->null()->comment('Contact person name'),
            'billing_address' => $this->text()->null()->comment('Billing address'),
            'shipping_address' => $this->text()->null()->comment('Shipping address'),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        // Create jdosa_products table
        $this->createTable('{{%jdosa_products}}', [
            'id' => $this->primaryKey(),
            'company_id' => $this->integer()->notNull(),
            'name' => $this->string(255)->notNull(),
            'description' => $this->text(),
            'type' => $this->string(50)->notNull()->defaultValue('service'),
            'category' => $this->string(100),
            'sku' => $this->string(100),
            'unit' => $this->string(50)->defaultValue('each'),
            'price' => $this->decimal(10, 2)->defaultValue(0.00),
            'cost' => $this->decimal(10, 2)->defaultValue(0.00),
            'is_taxable' => $this->boolean()->defaultValue(true),
            'is_active' => $this->boolean()->defaultValue(true),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        // Create jdosa_invoices table
        $this->createTable('{{%jdosa_invoices}}', [
            'id' => $this->primaryKey(),
            'invoice_number' => $this->string(100)->notNull()->unique(),
            'company_id' => $this->integer()->notNull(),
            'customer_id' => $this->integer()->notNull(),
            'bill_to_address' => $this->text()->null(),
            'cc_email' => $this->string(255)->null(),
            'invoice_date' => $this->date()->notNull(),
            'due_date' => $this->date(),
            'subtotal' => $this->decimal(15, 2)->defaultValue(0.00),
            'tax_rate' => $this->decimal(5, 2)->defaultValue(10.00),
            'tax_amount' => $this->decimal(15, 2)->defaultValue(0.00),
            'total_amount' => $this->decimal(15, 2)->defaultValue(0.00),
            'notes' => $this->text(),
            'status' => $this->string(20)->defaultValue('draft'),
            'currency' => $this->string(10)->defaultValue('USD'),
            'ship_to_address' => $this->text()->null()->comment('Shipping address'),
            'ship_from_address' => $this->text()->null()->comment('Shipping from address (hidden)'),
            'shipping_date' => $this->date()->null()->comment('Shipping date'),
            'tracking_number' => $this->string(100)->null()->comment('Tracking number'),
            'shipping_method' => $this->string(100)->null()->comment('Shipping method'),
            'terms' => $this->string(50)->null()->comment('Payment terms (Net 30, etc)'),
            'payment_instructions' => $this->text()->null()->comment('Customer payment options'),
            'customer_notes' => $this->text()->null()->comment('Notes to customer'),
            'memo' => $this->text()->null()->comment('Internal memo (hidden)'),
            'discount_type' => "ENUM('percentage', 'fixed') DEFAULT NULL",
            'discount_value' => $this->decimal(15,2)->defaultValue(0.00)->comment('Discount value'),
            'discount_amount' => $this->decimal(15,2)->defaultValue(0.00)->comment('Calculated discount amount'),
            'deposit_amount' => $this->decimal(15,2)->defaultValue(0.00)->comment('Deposit/prepayment amount'),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        // Create jdosa_invoice_items table
        $this->createTable('{{%jdosa_invoice_items}}', [
            'id' => $this->primaryKey(),
            'invoice_id' => $this->integer()->notNull(),
            'product_id' => $this->integer()->null(),
            'description' => $this->string(500)->notNull(),
            'quantity' => $this->decimal(10, 2)->notNull()->defaultValue(1.00),
            'rate' => $this->decimal(15, 2)->notNull()->defaultValue(0.00),
            'amount' => $this->decimal(15, 2)->notNull()->defaultValue(0.00),
            'sort_order' => $this->integer()->defaultValue(0),
            'product_service_name' => $this->string(255)->null()->comment('Product/Service name'),
            'tax_rate' => $this->decimal(5,2)->defaultValue(0.00)->comment('Individual item tax rate'),
            'tax_amount' => $this->decimal(15,2)->defaultValue(0.00)->comment('Individual item tax amount'),
            'is_taxable' => $this->boolean()->defaultValue(true)->comment('Whether item is taxable'),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        // Create jdosa_estimates table
        $this->createTable('{{%jdosa_estimates}}', [
            'id' => $this->primaryKey(),
            'estimate_number' => $this->string(100)->notNull()->unique(),
            'company_id' => $this->integer()->notNull(),
            'customer_id' => $this->integer()->notNull(),
            'bill_to_address' => $this->text()->null(),
            'cc_email' => $this->string(255)->null(),
            'estimate_date' => $this->date()->notNull(),
            'expiry_date' => $this->date(),
            'subtotal' => $this->decimal(15,2)->defaultValue(0.00),
            'tax_rate' => $this->decimal(5,2)->defaultValue(10.00),
            'tax_amount' => $this->decimal(15,2)->defaultValue(0.00),
            'total_amount' => $this->decimal(15,2)->defaultValue(0.00),
            'notes' => $this->text(),
            'status' => $this->string(20)->defaultValue('draft'),
            'currency' => $this->string(10)->defaultValue('USD'),
            'ship_to_address' => $this->text(),
            'ship_from_address' => $this->text(),
            'shipping_date' => $this->date(),
            'tracking_number' => $this->string(100),
            'shipping_method' => $this->string(100),
            'terms' => $this->string(50),
            'payment_instructions' => $this->text(),
            'customer_notes' => $this->text(),
            'memo' => $this->text(),
            'discount_type' => "ENUM('percentage', 'fixed') DEFAULT NULL",
            'discount_value' => $this->decimal(15,2)->defaultValue(0.00),
            'discount_amount' => $this->decimal(15,2)->defaultValue(0.00),
            'converted_to_invoice' => $this->boolean()->defaultValue(false),
            'invoice_id' => $this->integer(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        // Create jdosa_estimate_items table
        $this->createTable('{{%jdosa_estimate_items}}', [
            'id' => $this->primaryKey(),
            'estimate_id' => $this->integer()->notNull(),
            'product_id' => $this->integer()->null(),
            'product_service_name' => $this->string(255),
            'description' => $this->string(500)->notNull(),
            'quantity' => $this->decimal(10,2)->notNull()->defaultValue(1.00),
            'rate' => $this->decimal(15,2)->notNull()->defaultValue(0.00),
            'amount' => $this->decimal(15,2)->notNull()->defaultValue(0.00),
            'tax_rate' => $this->decimal(5,2)->defaultValue(0.00),
            'tax_amount' => $this->decimal(15,2)->defaultValue(0.00),
            'is_taxable' => $this->boolean()->defaultValue(true),
            'sort_order' => $this->integer()->defaultValue(0),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        // Create jdosa_payments table
        $this->createTable('{{%jdosa_payments}}', [
            'id' => $this->primaryKey(),
            'invoice_id' => $this->integer()->notNull(),
            'payment_date' => $this->date()->notNull(),
            'amount' => $this->decimal(10, 2)->notNull(),
            'payment_method' => $this->string(50)->notNull(),
            'reference_number' => $this->string(100)->null(),
            'notes' => $this->text()->null(),
            'created_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        // Create indexes for companies table
        $this->createIndex('idx-jdosa_companies-is_active', '{{%jdosa_companies}}', 'is_active');

        // Create indexes for customers table
        $this->createIndex('idx-jdosa_customers-company_id', '{{%jdosa_customers}}', 'company_id');
        $this->createIndex('idx-jdosa_customers-is_active', '{{%jdosa_customers}}', 'is_active');
        $this->createIndex('idx-jdosa_customers-customer_email', '{{%jdosa_customers}}', 'customer_email');
        $this->createIndex('idx-jdosa_customers-contact_name', '{{%jdosa_customers}}', 'contact_name');

        // Create indexes for products table
        $this->createIndex('idx-jdosa_products-company_id', '{{%jdosa_products}}', 'company_id');
        $this->createIndex('idx-jdosa_products-type', '{{%jdosa_products}}', 'type');
        $this->createIndex('idx-jdosa_products-sku', '{{%jdosa_products}}', ['company_id', 'sku']);
        $this->createIndex('idx-jdosa_products-name', '{{%jdosa_products}}', 'name');

        // Create indexes for invoices table
        $this->createIndex('idx-jdosa_invoices-invoice_number', '{{%jdosa_invoices}}', 'invoice_number', true);
        $this->createIndex('idx-jdosa_invoices-company_id', '{{%jdosa_invoices}}', 'company_id');
        $this->createIndex('idx-jdosa_invoices-customer_id', '{{%jdosa_invoices}}', 'customer_id');
        $this->createIndex('idx-jdosa_invoices-status', '{{%jdosa_invoices}}', 'status');
        $this->createIndex('idx-jdosa_invoices-invoice_date', '{{%jdosa_invoices}}', 'invoice_date');
        $this->createIndex('idx-jdosa_invoices-due_date', '{{%jdosa_invoices}}', 'due_date');
        $this->createIndex('idx-jdosa_invoices-shipping_date', '{{%jdosa_invoices}}', 'shipping_date');
        $this->createIndex('idx-jdosa_invoices-tracking_number', '{{%jdosa_invoices}}', 'tracking_number');
        $this->createIndex('idx-jdosa_invoices-terms', '{{%jdosa_invoices}}', 'terms');

        // Create indexes for invoice items table
        $this->createIndex('idx-jdosa_invoice_items-invoice_id', '{{%jdosa_invoice_items}}', 'invoice_id');
        $this->createIndex('idx-jdosa_invoice_items-product_id', '{{%jdosa_invoice_items}}', 'product_id');
        $this->createIndex('idx-jdosa_invoice_items-sort_order', '{{%jdosa_invoice_items}}', 'sort_order');
        $this->createIndex('idx-jdosa_invoice_items-product_service_name', '{{%jdosa_invoice_items}}', 'product_service_name');

        // Create indexes for estimates table
        $this->createIndex('idx-jdosa_estimates-estimate_number', '{{%jdosa_estimates}}', 'estimate_number', true);
        $this->createIndex('idx-jdosa_estimates-company_id', '{{%jdosa_estimates}}', 'company_id');
        $this->createIndex('idx-jdosa_estimates-customer_id', '{{%jdosa_estimates}}', 'customer_id');
        $this->createIndex('idx-jdosa_estimates-status', '{{%jdosa_estimates}}', 'status');
        $this->createIndex('idx-jdosa_estimates-estimate_date', '{{%jdosa_estimates}}', 'estimate_date');
        $this->createIndex('idx-jdosa_estimates-expiry_date', '{{%jdosa_estimates}}', 'expiry_date');
        $this->createIndex('idx-jdosa_estimates-converted', '{{%jdosa_estimates}}', 'converted_to_invoice');
        $this->createIndex('idx-jdosa_estimates-invoice_id', '{{%jdosa_estimates}}', 'invoice_id');

        // Create indexes for estimate items table
        $this->createIndex('idx-jdosa_estimate_items-estimate_id', '{{%jdosa_estimate_items}}', 'estimate_id');
        $this->createIndex('idx-jdosa_estimate_items-product_id', '{{%jdosa_estimate_items}}', 'product_id');
        $this->createIndex('idx-jdosa_estimate_items-sort_order', '{{%jdosa_estimate_items}}', 'sort_order');
        $this->createIndex('idx-jdosa_estimate_items-product_service_name', '{{%jdosa_estimate_items}}', 'product_service_name');

        // Create indexes for payments table
        $this->createIndex('idx-jdosa_payments-invoice_id', '{{%jdosa_payments}}', 'invoice_id');
        $this->createIndex('idx-jdosa_payments-payment_date', '{{%jdosa_payments}}', 'payment_date');

        // Add foreign key constraints
        $this->addForeignKey('fk-jdosa_customers-company_id', '{{%jdosa_customers}}', 'company_id', '{{%jdosa_companies}}', 'id', 'CASCADE');
        $this->addForeignKey('fk-jdosa_products-company_id', '{{%jdosa_products}}', 'company_id', '{{%jdosa_companies}}', 'id', 'CASCADE');
        $this->addForeignKey('fk-jdosa_invoices-company_id', '{{%jdosa_invoices}}', 'company_id', '{{%jdosa_companies}}', 'id', 'CASCADE');
        $this->addForeignKey('fk-jdosa_invoices-customer_id', '{{%jdosa_invoices}}', 'customer_id', '{{%jdosa_customers}}', 'id', 'CASCADE');
        $this->addForeignKey('fk-jdosa_invoice_items-invoice_id', '{{%jdosa_invoice_items}}', 'invoice_id', '{{%jdosa_invoices}}', 'id', 'CASCADE');
        $this->addForeignKey('fk-jdosa_invoice_items-product_id', '{{%jdosa_invoice_items}}', 'product_id', '{{%jdosa_products}}', 'id', 'SET NULL');
        $this->addForeignKey('fk-jdosa_estimates-company_id', '{{%jdosa_estimates}}', 'company_id', '{{%jdosa_companies}}', 'id', 'CASCADE');
        $this->addForeignKey('fk-jdosa_estimates-customer_id', '{{%jdosa_estimates}}', 'customer_id', '{{%jdosa_customers}}', 'id', 'CASCADE');
        $this->addForeignKey('fk-jdosa_estimates-invoice_id', '{{%jdosa_estimates}}', 'invoice_id', '{{%jdosa_invoices}}', 'id', 'SET NULL');
        $this->addForeignKey('fk-jdosa_estimate_items-estimate_id', '{{%jdosa_estimate_items}}', 'estimate_id', '{{%jdosa_estimates}}', 'id', 'CASCADE');
        $this->addForeignKey('fk-jdosa_estimate_items-product_id', '{{%jdosa_estimate_items}}', 'product_id', '{{%jdosa_products}}', 'id', 'SET NULL');
        $this->addForeignKey('fk-jdosa_payments-invoice_id', '{{%jdosa_payments}}', 'invoice_id', '{{%jdosa_invoices}}', 'id', 'CASCADE');

        // Insert default company data
        $this->insert('{{%jdosa_companies}}', [
            'company_name' => 'Company Name',
            'company_address' => 'Company Address',
            'company_phone' => 'Company Phone',
            'company_email' => 'example@example.com',
            'sender_email' => 'example@example.com',
            'tax_rate' => 10.00,
            'currency' => 'USD',
            'invoice_prefix' => 'INV',
            'due_date_days' => 30,
            'is_active' => true,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Drop foreign keys first
        $this->dropForeignKey('fk-jdosa_payments-invoice_id', '{{%jdosa_payments}}');
        $this->dropForeignKey('fk-jdosa_estimate_items-product_id', '{{%jdosa_estimate_items}}');
        $this->dropForeignKey('fk-jdosa_estimate_items-estimate_id', '{{%jdosa_estimate_items}}');
        $this->dropForeignKey('fk-jdosa_estimates-invoice_id', '{{%jdosa_estimates}}');
        $this->dropForeignKey('fk-jdosa_estimates-customer_id', '{{%jdosa_estimates}}');
        $this->dropForeignKey('fk-jdosa_estimates-company_id', '{{%jdosa_estimates}}');
        $this->dropForeignKey('fk-jdosa_invoice_items-product_id', '{{%jdosa_invoice_items}}');
        $this->dropForeignKey('fk-jdosa_invoice_items-invoice_id', '{{%jdosa_invoice_items}}');
        $this->dropForeignKey('fk-jdosa_invoices-customer_id', '{{%jdosa_invoices}}');
        $this->dropForeignKey('fk-jdosa_invoices-company_id', '{{%jdosa_invoices}}');
        $this->dropForeignKey('fk-jdosa_products-company_id', '{{%jdosa_products}}');
        $this->dropForeignKey('fk-jdosa_customers-company_id', '{{%jdosa_customers}}');

        // Drop tables
        $this->dropTable('{{%jdosa_payments}}');
        $this->dropTable('{{%jdosa_estimate_items}}');
        $this->dropTable('{{%jdosa_estimates}}');
        $this->dropTable('{{%jdosa_invoice_items}}');
        $this->dropTable('{{%jdosa_invoices}}');
        $this->dropTable('{{%jdosa_products}}');
        $this->dropTable('{{%jdosa_customers}}');
        $this->dropTable('{{%jdosa_companies}}');
    }

    /**
     * Add missing columns to existing tables
     */
    private function addMissingColumns()
    {
        $schema = $this->db->schema;
        
        // Check and add missing columns to companies table
        $companiesTable = $schema->getTableSchema('{{%jdosa_companies}}', true);
        if ($companiesTable) {
            if (!isset($companiesTable->columns['invoice_prefix'])) {
                $this->addColumn('{{%jdosa_companies}}', 'invoice_prefix', $this->string(20)->defaultValue('INV-'));
            }
            if (!isset($companiesTable->columns['estimate_prefix'])) {
                $this->addColumn('{{%jdosa_companies}}', 'estimate_prefix', $this->string(20)->defaultValue('EST-'));
            }
        }
    }
}
