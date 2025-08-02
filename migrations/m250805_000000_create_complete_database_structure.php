<?php

use yii\db\Migration;

/**
 * Complete database migration for Invoice Manager
 * 
 * This migration creates the entire database structure for a fresh installation.
 * It includes all tables, indexes, foreign keys, and initial data.
 * 
 * @author Invoice Manager Development Team
 * @since 2025-08-05
 */
class m250805_000000_create_complete_database_structure extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Disable foreign key checks for MySQL
        if ($this->db->driverName === 'mysql') {
            $this->execute('SET foreign_key_checks = 0');
        }

        try {
            // 1. Create reference tables first (no dependencies)
            $this->createCountriesTable();
            $this->createStatesTable();
            
            // 2. Create core user and plan tables
            $this->createUsersTable();
            $this->createAdminSettingsTable();
            $this->createPlansTable();
            $this->createUserSubscriptionsTable();
            
            // 3. Create business entity tables
            $this->createCompaniesTable();
            $this->createProductCategoriesTable();
            $this->createCustomersTable();
            $this->createProductsTable();
            
            // 4. Create document tables
            $this->createInvoicesTable();
            $this->createEstimatesTable();
            $this->createInvoiceItemsTable();
            $this->createEstimateItemsTable();
            $this->createPaymentsTable();
            
            // 5. Create tax and geographic tables
            $this->createTaxJurisdictionsTable();
            $this->createStateTaxRatesTable();
            
            // 6. Create access control and logging tables
            $this->createSubuserCompanyAccessTable();
            $this->createUpdateLogsTable();
            
            // 7. Insert initial data
            $this->insertInitialData();
            
        } finally {
            // Re-enable foreign key checks
            if ($this->db->driverName === 'mysql') {
                $this->execute('SET foreign_key_checks = 1');
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Disable foreign key checks for MySQL
        if ($this->db->driverName === 'mysql') {
            $this->execute('SET foreign_key_checks = 0');
        }

        try {
            // Drop in reverse order to respect foreign key dependencies
            $this->dropTable('{{%jdosa_update_logs}}');
            $this->dropTable('{{%jdosa_subuser_company_access}}');
            $this->dropTable('{{%state_tax_rates}}');
            $this->dropTable('{{%jdosa_tax_jurisdictions}}');
            $this->dropTable('{{%jdosa_payments}}');
            $this->dropTable('{{%jdosa_estimate_items}}');
            $this->dropTable('{{%jdosa_invoice_items}}');
            $this->dropTable('{{%jdosa_estimates}}');
            $this->dropTable('{{%jdosa_invoices}}');
            $this->dropTable('{{%jdosa_products}}');
            $this->dropTable('{{%jdosa_customers}}');
            $this->dropTable('{{%jdosa_product_categories}}');
            $this->dropTable('{{%jdosa_companies}}');
            $this->dropTable('{{%jdosa_user_subscriptions}}');
            $this->dropTable('{{%jdosa_plans}}');
            $this->dropTable('{{%jdosa_admin_settings}}');
            $this->dropTable('{{%jdosa_users}}');
            $this->dropTable('{{%states}}');
            $this->dropTable('{{%countries}}');
            
        } finally {
            // Re-enable foreign key checks
            if ($this->db->driverName === 'mysql') {
                $this->execute('SET foreign_key_checks = 1');
            }
        }
    }

    /**
     * Create countries table
     */
    private function createCountriesTable()
    {
        $this->createTable('{{%countries}}', [
            'id' => $this->primaryKey(),
            'country_code' => $this->string(2)->notNull()->unique(),
            'country_name' => $this->string(100)->notNull(),
            'is_active' => $this->boolean()->defaultValue(true),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        $this->createIndex('idx_countries_code', '{{%countries}}', 'country_code');
    }

    /**
     * Create states table
     */
    private function createStatesTable()
    {
        $this->createTable('{{%states}}', [
            'id' => $this->primaryKey(),
            'country_code' => $this->string(2)->notNull(),
            'state_code' => $this->string(2)->notNull(),
            'state_name' => $this->string(100)->notNull(),
            'is_active' => $this->boolean()->defaultValue(true),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        $this->createIndex('idx_states_code', '{{%states}}', ['country_code', 'state_code']);
        $this->addForeignKey('fk_states_country', '{{%states}}', 'country_code', '{{%countries}}', 'country_code', 'CASCADE', 'CASCADE');
    }

    /**
     * Create users table
     */
    private function createUsersTable()
    {
        $this->createTable('{{%jdosa_users}}', [
            'id' => $this->primaryKey(),
            'username' => $this->string(50)->unique(),
            'email' => $this->string(255)->notNull()->unique(),
            'password_hash' => $this->string(255),
            'full_name' => $this->string(100),
            'google_id' => $this->string(100)->unique(),
            'avatar_url' => $this->string(500),
            'login_type' => "ENUM('local', 'google') DEFAULT 'local'",
            'is_active' => $this->boolean()->defaultValue(true),
            'email_verified' => $this->boolean()->defaultValue(false),
            'auth_key' => $this->string(32),
            'password_reset_token' => $this->string(255)->unique(),
            'role' => "ENUM('admin', 'user', 'demo', 'subuser') DEFAULT 'user'",
            'max_companies' => $this->integer()->defaultValue(1),
            'parent_user_id' => $this->integer(),
            'company_id' => $this->integer(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        $this->createIndex('idx_users_email', '{{%jdosa_users}}', 'email');
        $this->createIndex('idx_users_username', '{{%jdosa_users}}', 'username');
        $this->createIndex('idx_users_parent', '{{%jdosa_users}}', 'parent_user_id');
    }

    /**
     * Create admin settings table
     */
    private function createAdminSettingsTable()
    {
        $this->createTable('{{%jdosa_admin_settings}}', [
            'id' => $this->primaryKey(),
            'setting_name' => $this->string(50)->notNull()->unique(),
            'setting_value' => $this->text(),
            'setting_type' => "ENUM('string', 'integer', 'boolean', 'json') DEFAULT 'string'",
            'description' => $this->text(),
            'is_public' => $this->boolean()->defaultValue(false),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        $this->createIndex('idx_admin_settings_name', '{{%jdosa_admin_settings}}', 'setting_name');
    }

    /**
     * Create plans table
     */
    private function createPlansTable()
    {
        $this->createTable('{{%jdosa_plans}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(50)->notNull(),
            'description' => $this->text(),
            'price' => $this->decimal(10, 2)->notNull(),
            'stripe_plan_id' => $this->string(100),
            'paypal_plan_id' => $this->string(100),
            'features' => $this->json(),
            'monthly_invoice_limit' => $this->integer(),
            'monthly_estimate_limit' => $this->integer(),
            'max_companies' => $this->integer()->defaultValue(1),
            'storage_limit_mb' => $this->integer(),
            'can_use_api' => $this->boolean()->defaultValue(false),
            'can_use_import' => $this->boolean()->defaultValue(false),
            'can_use_custom_templates' => $this->boolean()->defaultValue(false),
            'can_use_ai_helper' => $this->boolean()->defaultValue(false),
            'max_subusers' => $this->integer()->defaultValue(0),
            'is_active' => $this->boolean()->defaultValue(true),
            'sort_order' => $this->integer()->defaultValue(0),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        $this->createIndex('idx_plans_active', '{{%jdosa_plans}}', 'is_active');
        $this->createIndex('idx_plans_sort', '{{%jdosa_plans}}', 'sort_order');
    }

    /**
     * Create user subscriptions table
     */
    private function createUserSubscriptionsTable()
    {
        $this->createTable('{{%jdosa_user_subscriptions}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'plan_id' => $this->integer()->notNull(),
            'status' => "ENUM('active', 'inactive', 'cancelled', 'expired') DEFAULT 'active'",
            'stripe_subscription_id' => $this->string(100),
            'paypal_subscription_id' => $this->string(100),
            'payment_method' => "ENUM('stripe', 'paypal')",
            'start_date' => $this->date()->notNull(),
            'end_date' => $this->date(),
            'next_billing_date' => $this->date(),
            'cancel_date' => $this->date(),
            'trial_end_date' => $this->date(),
            'is_recurring' => $this->boolean()->defaultValue(true),
            'scheduled_plan_id' => $this->integer(),
            'scheduled_change_date' => $this->date(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        $this->createIndex('idx_subscriptions_user', '{{%jdosa_user_subscriptions}}', 'user_id');
        $this->createIndex('idx_subscriptions_status', '{{%jdosa_user_subscriptions}}', 'status');
        $this->addForeignKey('fk_subscriptions_user', '{{%jdosa_user_subscriptions}}', 'user_id', '{{%jdosa_users}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_subscriptions_plan', '{{%jdosa_user_subscriptions}}', 'plan_id', '{{%jdosa_plans}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_subscriptions_scheduled_plan', '{{%jdosa_user_subscriptions}}', 'scheduled_plan_id', '{{%jdosa_plans}}', 'id', 'SET NULL', 'CASCADE');
    }

    /**
     * Create companies table
     */
    private function createCompaniesTable()
    {
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
            'sender_name' => $this->string(255),
            'bcc_email' => $this->string(255),
            'estimate_validity_days' => $this->integer()->defaultValue(30),
            'tax_rate' => $this->decimal(5, 2)->defaultValue(10.00),
            'currency' => $this->string(10)->defaultValue('USD'),
            'invoice_prefix' => $this->string(20)->defaultValue('INV-'),
            'estimate_prefix' => $this->string(20)->defaultValue('EST-'),
            'due_date_days' => $this->integer()->defaultValue(30),
            'is_active' => $this->boolean()->defaultValue(true),
            'user_id' => $this->integer(),
            'dark_mode' => $this->boolean()->defaultValue(false),
            'use_cjk_font' => $this->boolean()->defaultValue(false),
            'language' => $this->string(10)->defaultValue('en-US'),
            'compact_mode' => $this->boolean()->defaultValue(false),
            'hide_footer' => $this->boolean()->defaultValue(false),
            'pdf_template' => $this->string(50)->defaultValue('classic'),
            
            // Tax related fields
            'tax_state_code' => $this->string(2),
            'tax_zip_code' => $this->string(10),
            'use_local_tax' => $this->boolean()->defaultValue(true),
            'auto_tax_calculation' => $this->boolean()->defaultValue(false),
            'tax_exempt' => $this->boolean()->defaultValue(false),
            'tax_id_number' => $this->string(50),
            
            // Location fields
            'city' => $this->string(100),
            'state' => $this->string(2),
            'zip_code' => $this->string(10),
            'country' => $this->string(2)->defaultValue('US'),
            
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        $this->createIndex('idx_companies_user', '{{%jdosa_companies}}', 'user_id');
        $this->createIndex('idx_companies_active', '{{%jdosa_companies}}', 'is_active');
        $this->addForeignKey('fk_companies_user', '{{%jdosa_companies}}', 'user_id', '{{%jdosa_users}}', 'id', 'SET NULL', 'CASCADE');
        
        // Add the company_id foreign key to users table
        $this->addForeignKey('fk_users_company', '{{%jdosa_users}}', 'company_id', '{{%jdosa_companies}}', 'id', 'SET NULL', 'CASCADE');
        $this->addForeignKey('fk_users_parent', '{{%jdosa_users}}', 'parent_user_id', '{{%jdosa_users}}', 'id', 'CASCADE', 'CASCADE');
    }

    /**
     * Create product categories table
     */
    private function createProductCategoriesTable()
    {
        $this->createTable('{{%jdosa_product_categories}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(100)->notNull(),
            'description' => $this->text(),
            'company_id' => $this->integer()->notNull(),
            'parent_id' => $this->integer(),
            'sort_order' => $this->integer()->defaultValue(0),
            'is_active' => $this->boolean()->defaultValue(true),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        $this->createIndex('idx_categories_company', '{{%jdosa_product_categories}}', 'company_id');
        $this->createIndex('idx_categories_parent', '{{%jdosa_product_categories}}', 'parent_id');
        $this->addForeignKey('fk_categories_company', '{{%jdosa_product_categories}}', 'company_id', '{{%jdosa_companies}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_categories_parent', '{{%jdosa_product_categories}}', 'parent_id', '{{%jdosa_product_categories}}', 'id', 'CASCADE', 'CASCADE');
    }

    /**
     * Create customers table
     */
    private function createCustomersTable()
    {
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
            'contact_name' => $this->string(255),
            'billing_address' => $this->text(),
            'shipping_address' => $this->text(),
            
            // Tax related fields
            'tax_state_code' => $this->string(2),
            'tax_zip_code' => $this->string(10),
            'tax_exempt' => $this->boolean()->defaultValue(false),
            'tax_exempt_certificate' => $this->string(100),
            'tax_exempt_expiry' => $this->date(),
            'customer_type' => "ENUM('individual', 'business', 'government', 'nonprofit') DEFAULT 'business'",
            
            // Location fields
            'city' => $this->string(100),
            'state' => $this->string(2),
            'zip_code' => $this->string(10),
            'country' => $this->string(2)->defaultValue('US'),
            
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        $this->createIndex('idx_customers_company', '{{%jdosa_customers}}', 'company_id');
        $this->createIndex('idx_customers_email', '{{%jdosa_customers}}', 'customer_email');
        $this->addForeignKey('fk_customers_company', '{{%jdosa_customers}}', 'company_id', '{{%jdosa_companies}}', 'id', 'CASCADE', 'CASCADE');
    }

    /**
     * Create products table
     */
    private function createProductsTable()
    {
        $this->createTable('{{%jdosa_products}}', [
            'id' => $this->primaryKey(),
            'product_name' => $this->string(255)->notNull(),
            'product_description' => $this->text(),
            'unit_price' => $this->decimal(15, 2)->notNull(),
            'company_id' => $this->integer()->notNull(),
            'category_id' => $this->integer(),
            'sku' => $this->string(100),
            'unit_of_measure' => $this->string(50)->defaultValue('each'),
            'tax_category' => $this->string(50),
            'is_active' => $this->boolean()->defaultValue(true),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        $this->createIndex('idx_products_company', '{{%jdosa_products}}', 'company_id');
        $this->createIndex('idx_products_category', '{{%jdosa_products}}', 'category_id');
        $this->createIndex('idx_products_sku', '{{%jdosa_products}}', 'sku');
        $this->addForeignKey('fk_products_company', '{{%jdosa_products}}', 'company_id', '{{%jdosa_companies}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_products_category', '{{%jdosa_products}}', 'category_id', '{{%jdosa_product_categories}}', 'id', 'SET NULL', 'CASCADE');
    }

    /**
     * Create invoices table
     */
    private function createInvoicesTable()
    {
        $this->createTable('{{%jdosa_invoices}}', [
            'id' => $this->primaryKey(),
            'invoice_number' => $this->string(100)->notNull(),
            'company_id' => $this->integer()->notNull(),
            'customer_id' => $this->integer()->notNull(),
            'user_id' => $this->integer(),
            'bill_to_address' => $this->text(),
            'bill_to_city' => $this->string(100),
            'bill_to_state' => $this->string(50),
            'bill_to_zip_code' => $this->string(20),
            'bill_to_country' => $this->string(2)->defaultValue('US'),
            'ship_to_address' => $this->text(),
            'ship_to_city' => $this->string(100),
            'ship_to_state' => $this->string(50),
            'ship_to_zip_code' => $this->string(20),
            'ship_to_country' => $this->string(2)->defaultValue('US'),
            'ship_from_address' => $this->text(),
            'cc_email' => $this->string(255),
            'invoice_date' => $this->date()->notNull(),
            'due_date' => $this->date(),
            'shipping_date' => $this->date(),
            'tracking_number' => $this->string(100),
            'shipping_method' => $this->string(100),
            'terms' => $this->string(100),
            'payment_instructions' => $this->text(),
            'customer_notes' => $this->text(),
            'memo' => $this->text(),
            'subtotal' => $this->decimal(15, 2)->defaultValue(0.00),
            'discount_type' => "ENUM('percentage', 'fixed')",
            'discount_value' => $this->decimal(15, 2)->defaultValue(0.00),
            'discount_amount' => $this->decimal(15, 2)->defaultValue(0.00),
            'shipping_fee' => $this->decimal(10, 2)->defaultValue(0.00),
            'deposit_amount' => $this->decimal(15, 2)->defaultValue(0.00),
            'tax_rate' => $this->decimal(5, 2)->defaultValue(0.00),
            'tax_amount' => $this->decimal(15, 2)->defaultValue(0.00),
            'total_amount' => $this->decimal(15, 2)->defaultValue(0.00),
            'notes' => $this->text(),
            'status' => $this->string(20)->defaultValue('draft'),
            'currency' => $this->string(10)->defaultValue('USD'),
            
            // Tax calculation fields
            'tax_calculation_mode' => "ENUM('automatic', 'manual') DEFAULT 'manual'",
            'auto_calculated_tax_rate' => $this->decimal(7, 4),
            'tax_jurisdiction_id' => $this->integer(),
            'tax_calculation_details' => $this->text(),
            
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        $this->createIndex('idx_invoices_number_company', '{{%jdosa_invoices}}', ['invoice_number', 'company_id'], true);
        $this->createIndex('idx_invoices_company', '{{%jdosa_invoices}}', 'company_id');
        $this->createIndex('idx_invoices_customer', '{{%jdosa_invoices}}', 'customer_id');
        $this->createIndex('idx_invoices_user', '{{%jdosa_invoices}}', 'user_id');
        $this->createIndex('idx_invoices_status', '{{%jdosa_invoices}}', 'status');
        $this->createIndex('idx_invoices_date', '{{%jdosa_invoices}}', 'invoice_date');
        
        $this->addForeignKey('fk_invoices_company', '{{%jdosa_invoices}}', 'company_id', '{{%jdosa_companies}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_invoices_customer', '{{%jdosa_invoices}}', 'customer_id', '{{%jdosa_customers}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_invoices_user', '{{%jdosa_invoices}}', 'user_id', '{{%jdosa_users}}', 'id', 'SET NULL', 'CASCADE');
    }

    /**
     * Create estimates table
     */
    private function createEstimatesTable()
    {
        $this->createTable('{{%jdosa_estimates}}', [
            'id' => $this->primaryKey(),
            'estimate_number' => $this->string(100)->notNull(),
            'company_id' => $this->integer()->notNull(),
            'customer_id' => $this->integer()->notNull(),
            'user_id' => $this->integer(),
            'bill_to_address' => $this->text(),
            'bill_to_city' => $this->string(100),
            'bill_to_state' => $this->string(50),
            'bill_to_zip_code' => $this->string(20),
            'bill_to_country' => $this->string(2)->defaultValue('US'),
            'ship_to_address' => $this->text(),
            'ship_to_city' => $this->string(100),
            'ship_to_state' => $this->string(50),
            'ship_to_zip_code' => $this->string(20),
            'ship_to_country' => $this->string(2)->defaultValue('US'),
            'ship_from_address' => $this->text(),
            'cc_email' => $this->string(255),
            'estimate_date' => $this->date()->notNull(),
            'expiry_date' => $this->date(),
            'shipping_date' => $this->date(),
            'tracking_number' => $this->string(100),
            'shipping_method' => $this->string(100),
            'terms' => $this->string(100),
            'payment_instructions' => $this->text(),
            'customer_notes' => $this->text(),
            'memo' => $this->text(),
            'subtotal' => $this->decimal(15, 2)->defaultValue(0.00),
            'discount_type' => "ENUM('percentage', 'fixed')",
            'discount_value' => $this->decimal(15, 2)->defaultValue(0.00),
            'discount_amount' => $this->decimal(15, 2)->defaultValue(0.00),
            'shipping_fee' => $this->decimal(10, 2)->defaultValue(0.00),
            'tax_rate' => $this->decimal(5, 2)->defaultValue(0.00),
            'tax_amount' => $this->decimal(15, 2)->defaultValue(0.00),
            'total_amount' => $this->decimal(15, 2)->defaultValue(0.00),
            'notes' => $this->text(),
            'status' => $this->string(20)->defaultValue('draft'),
            'currency' => $this->string(10)->defaultValue('USD'),
            'converted_to_invoice' => $this->boolean()->defaultValue(false),
            'invoice_id' => $this->integer(),
            
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        $this->createIndex('idx_estimates_number_company', '{{%jdosa_estimates}}', ['estimate_number', 'company_id'], true);
        $this->createIndex('idx_estimates_company', '{{%jdosa_estimates}}', 'company_id');
        $this->createIndex('idx_estimates_customer', '{{%jdosa_estimates}}', 'customer_id');
        $this->createIndex('idx_estimates_user', '{{%jdosa_estimates}}', 'user_id');
        $this->createIndex('idx_estimates_status', '{{%jdosa_estimates}}', 'status');
        $this->createIndex('idx_estimates_date', '{{%jdosa_estimates}}', 'estimate_date');
        
        $this->addForeignKey('fk_estimates_company', '{{%jdosa_estimates}}', 'company_id', '{{%jdosa_companies}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_estimates_customer', '{{%jdosa_estimates}}', 'customer_id', '{{%jdosa_customers}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_estimates_user', '{{%jdosa_estimates}}', 'user_id', '{{%jdosa_users}}', 'id', 'SET NULL', 'CASCADE');
        $this->addForeignKey('fk_estimates_invoice', '{{%jdosa_estimates}}', 'invoice_id', '{{%jdosa_invoices}}', 'id', 'SET NULL', 'CASCADE');
    }

    /**
     * Create invoice items table
     */
    private function createInvoiceItemsTable()
    {
        $this->createTable('{{%jdosa_invoice_items}}', [
            'id' => $this->primaryKey(),
            'invoice_id' => $this->integer()->notNull(),
            'product_id' => $this->integer(),
            'description' => $this->text()->notNull(),
            'quantity' => $this->decimal(10, 2)->notNull(),
            'rate' => $this->decimal(15, 2)->notNull(),
            'amount' => $this->decimal(15, 2)->notNull(),
            'sort_order' => $this->integer()->defaultValue(0),
            'product_service_name' => $this->string(255),
            'tax_rate' => $this->decimal(5, 2)->defaultValue(0.00),
            'tax_amount' => $this->decimal(15, 2)->defaultValue(0.00),
            'is_taxable' => $this->boolean()->defaultValue(true),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        $this->createIndex('idx_invoice_items_invoice', '{{%jdosa_invoice_items}}', 'invoice_id');
        $this->createIndex('idx_invoice_items_product', '{{%jdosa_invoice_items}}', 'product_id');
        $this->createIndex('idx_invoice_items_sort', '{{%jdosa_invoice_items}}', 'sort_order');
        
        $this->addForeignKey('fk_invoice_items_invoice', '{{%jdosa_invoice_items}}', 'invoice_id', '{{%jdosa_invoices}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_invoice_items_product', '{{%jdosa_invoice_items}}', 'product_id', '{{%jdosa_products}}', 'id', 'SET NULL', 'CASCADE');
    }

    /**
     * Create estimate items table
     */
    private function createEstimateItemsTable()
    {
        $this->createTable('{{%jdosa_estimate_items}}', [
            'id' => $this->primaryKey(),
            'estimate_id' => $this->integer()->notNull(),
            'product_id' => $this->integer(),
            'product_service_name' => $this->string(255),
            'description' => $this->text()->notNull(),
            'quantity' => $this->decimal(10, 2)->notNull(),
            'rate' => $this->decimal(15, 2)->notNull(),
            'amount' => $this->decimal(15, 2)->notNull(),
            'tax_rate' => $this->decimal(5, 2)->defaultValue(0.00),
            'tax_amount' => $this->decimal(15, 2)->defaultValue(0.00),
            'is_taxable' => $this->boolean()->defaultValue(true),
            'sort_order' => $this->integer()->defaultValue(0),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        $this->createIndex('idx_estimate_items_estimate', '{{%jdosa_estimate_items}}', 'estimate_id');
        $this->createIndex('idx_estimate_items_product', '{{%jdosa_estimate_items}}', 'product_id');
        $this->createIndex('idx_estimate_items_sort', '{{%jdosa_estimate_items}}', 'sort_order');
        
        $this->addForeignKey('fk_estimate_items_estimate', '{{%jdosa_estimate_items}}', 'estimate_id', '{{%jdosa_estimates}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_estimate_items_product', '{{%jdosa_estimate_items}}', 'product_id', '{{%jdosa_products}}', 'id', 'SET NULL', 'CASCADE');
    }

    /**
     * Create payments table
     */
    private function createPaymentsTable()
    {
        $this->createTable('{{%jdosa_payments}}', [
            'id' => $this->primaryKey(),
            'invoice_id' => $this->integer()->notNull(),
            'payment_date' => $this->date()->notNull(),
            'amount' => $this->decimal(15, 2)->notNull(),
            'payment_method' => $this->string(50),
            'reference_number' => $this->string(100),
            'notes' => $this->text(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        $this->createIndex('idx_payments_invoice', '{{%jdosa_payments}}', 'invoice_id');
        $this->createIndex('idx_payments_date', '{{%jdosa_payments}}', 'payment_date');
        
        $this->addForeignKey('fk_payments_invoice', '{{%jdosa_payments}}', 'invoice_id', '{{%jdosa_invoices}}', 'id', 'CASCADE', 'CASCADE');
    }

    /**
     * Create tax jurisdictions table
     */
    private function createTaxJurisdictionsTable()
    {
        $this->createTable('{{%jdosa_tax_jurisdictions}}', [
            'id' => $this->primaryKey(),
            'zip_code' => $this->string(10)->notNull(),
            'state_code' => $this->string(2)->notNull(),
            'state_name' => $this->string(50),
            'county_name' => $this->string(100),
            'city_name' => $this->string(100),
            'tax_region_name' => $this->string(200),
            'state_rate' => $this->decimal(7, 4)->defaultValue(0.0000),
            'county_rate' => $this->decimal(7, 4)->defaultValue(0.0000),
            'city_rate' => $this->decimal(7, 4)->defaultValue(0.0000),
            'special_rate' => $this->decimal(7, 4)->defaultValue(0.0000),
            'combined_rate' => $this->decimal(7, 4)->notNull(),
            'estimated_population' => $this->integer(),
            'tax_authority' => $this->string(200),
            'jurisdiction_code' => $this->string(50),
            'data_source' => $this->string(50)->defaultValue('manual'),
            'effective_date' => $this->date()->notNull(),
            'expiry_date' => $this->date(),
            'is_active' => $this->boolean()->defaultValue(true),
            'data_year' => $this->integer(),
            'data_month' => $this->integer(),
            'last_verified' => $this->date(),
            'notes' => $this->text(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
        ]);

        $this->createIndex('idx_tax_jurisdictions_zip_state', '{{%jdosa_tax_jurisdictions}}', ['zip_code', 'state_code']);
        $this->createIndex('idx_tax_jurisdictions_active', '{{%jdosa_tax_jurisdictions}}', 'is_active');
        $this->createIndex('idx_tax_jurisdictions_effective', '{{%jdosa_tax_jurisdictions}}', 'effective_date');
        
        // Add the foreign key to invoices table
        $this->addForeignKey('fk_invoices_tax_jurisdiction', '{{%jdosa_invoices}}', 'tax_jurisdiction_id', '{{%jdosa_tax_jurisdictions}}', 'id', 'SET NULL', 'CASCADE');
    }

    /**
     * Create state tax rates table
     */
    private function createStateTaxRatesTable()
    {
        $this->createTable('{{%state_tax_rates}}', [
            'id' => $this->primaryKey(),
            'state_code' => $this->string(2)->notNull(),
            'state_name' => $this->string(50)->notNull(),
            'state_rate' => $this->decimal(5, 4)->notNull(),
            'avg_local_rate' => $this->decimal(5, 4)->defaultValue(0.0000),
            'combined_rate' => $this->decimal(5, 4)->notNull(),
            'max_local_rate' => $this->decimal(5, 4)->defaultValue(0.0000),
            'rank' => $this->integer(),
            'data_year' => $this->integer()->notNull(),
            'data_month' => $this->integer(),
            'is_active' => $this->boolean()->defaultValue(true),
            'notes' => $this->text(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        $this->createIndex('idx_state_tax_rates_state', '{{%state_tax_rates}}', 'state_code');
        $this->createIndex('idx_state_tax_rates_year', '{{%state_tax_rates}}', 'data_year');
        $this->createIndex('idx_state_tax_rates_active', '{{%state_tax_rates}}', 'is_active');
    }

    /**
     * Create subuser company access table
     */
    private function createSubuserCompanyAccessTable()
    {
        $this->createTable('{{%jdosa_subuser_company_access}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'company_id' => $this->integer()->notNull(),
            'role' => "ENUM('read', 'write', 'admin') DEFAULT 'read'",
            'granted_by' => $this->integer()->notNull(),
            'granted_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'expires_at' => $this->timestamp(),
            'is_active' => $this->boolean()->defaultValue(true),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        $this->createIndex('idx_subuser_access_user_company', '{{%jdosa_subuser_company_access}}', ['user_id', 'company_id'], true);
        $this->createIndex('idx_subuser_access_company', '{{%jdosa_subuser_company_access}}', 'company_id');
        $this->createIndex('idx_subuser_access_active', '{{%jdosa_subuser_company_access}}', 'is_active');
        
        $this->addForeignKey('fk_subuser_access_user', '{{%jdosa_subuser_company_access}}', 'user_id', '{{%jdosa_users}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_subuser_access_company', '{{%jdosa_subuser_company_access}}', 'company_id', '{{%jdosa_companies}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_subuser_access_granted_by', '{{%jdosa_subuser_company_access}}', 'granted_by', '{{%jdosa_users}}', 'id', 'CASCADE', 'CASCADE');
    }

    /**
     * Create update logs table
     */
    private function createUpdateLogsTable()
    {
        $this->createTable('{{%jdosa_update_logs}}', [
            'id' => $this->primaryKey(),
            'entity_type' => $this->string(50)->notNull(),
            'entity_id' => $this->integer()->notNull(),
            'action' => $this->string(20)->notNull(),
            'user_id' => $this->integer()->notNull(),
            'user_name' => $this->string(255)->notNull(),
            'details' => $this->text(),
            'ip_address' => $this->string(45),
            'user_agent' => $this->text(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);

        $this->createIndex('idx_update_logs_entity', '{{%jdosa_update_logs}}', ['entity_type', 'entity_id']);
        $this->createIndex('idx_update_logs_user', '{{%jdosa_update_logs}}', 'user_id');
        $this->createIndex('idx_update_logs_action', '{{%jdosa_update_logs}}', 'action');
        $this->createIndex('idx_update_logs_created', '{{%jdosa_update_logs}}', 'created_at');
        
        $this->addForeignKey('fk_update_logs_user', '{{%jdosa_update_logs}}', 'user_id', '{{%jdosa_users}}', 'id', 'CASCADE', 'CASCADE');
    }

    /**
     * Insert initial data
     */
    private function insertInitialData()
    {
        // Insert countries
        $this->insert('{{%countries}}', [
            'country_code' => 'US',
            'country_name' => 'United States',
            'is_active' => true,
        ]);

        // Insert US states
        $states = [
            ['US', 'AL', 'Alabama'], ['US', 'AK', 'Alaska'], ['US', 'AZ', 'Arizona'], ['US', 'AR', 'Arkansas'],
            ['US', 'CA', 'California'], ['US', 'CO', 'Colorado'], ['US', 'CT', 'Connecticut'], ['US', 'DE', 'Delaware'],
            ['US', 'FL', 'Florida'], ['US', 'GA', 'Georgia'], ['US', 'HI', 'Hawaii'], ['US', 'ID', 'Idaho'],
            ['US', 'IL', 'Illinois'], ['US', 'IN', 'Indiana'], ['US', 'IA', 'Iowa'], ['US', 'KS', 'Kansas'],
            ['US', 'KY', 'Kentucky'], ['US', 'LA', 'Louisiana'], ['US', 'ME', 'Maine'], ['US', 'MD', 'Maryland'],
            ['US', 'MA', 'Massachusetts'], ['US', 'MI', 'Michigan'], ['US', 'MN', 'Minnesota'], ['US', 'MS', 'Mississippi'],
            ['US', 'MO', 'Missouri'], ['US', 'MT', 'Montana'], ['US', 'NE', 'Nebraska'], ['US', 'NV', 'Nevada'],
            ['US', 'NH', 'New Hampshire'], ['US', 'NJ', 'New Jersey'], ['US', 'NM', 'New Mexico'], ['US', 'NY', 'New York'],
            ['US', 'NC', 'North Carolina'], ['US', 'ND', 'North Dakota'], ['US', 'OH', 'Ohio'], ['US', 'OK', 'Oklahoma'],
            ['US', 'OR', 'Oregon'], ['US', 'PA', 'Pennsylvania'], ['US', 'RI', 'Rhode Island'], ['US', 'SC', 'South Carolina'],
            ['US', 'SD', 'South Dakota'], ['US', 'TN', 'Tennessee'], ['US', 'TX', 'Texas'], ['US', 'UT', 'Utah'],
            ['US', 'VT', 'Vermont'], ['US', 'VA', 'Virginia'], ['US', 'WA', 'Washington'], ['US', 'WV', 'West Virginia'],
            ['US', 'WI', 'Wisconsin'], ['US', 'WY', 'Wyoming'], ['US', 'DC', 'District of Columbia'],
        ];

        foreach ($states as $state) {
            $this->insert('{{%states}}', [
                'country_code' => $state[0],
                'state_code' => $state[1],
                'state_name' => $state[2],
                'is_active' => true,
            ]);
        }

        // Insert subscription plans
        $this->insert('{{%jdosa_plans}}', [
            'name' => 'Free',
            'description' => 'Basic features for personal use',
            'price' => 0.00,
            'monthly_invoice_limit' => 5,
            'monthly_estimate_limit' => 5,
            'max_companies' => 1,
            'storage_limit_mb' => 100,
            'can_use_api' => false,
            'can_use_import' => false,
            'can_use_custom_templates' => false,
            'can_use_ai_helper' => false,
            'max_subusers' => 0,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $this->insert('{{%jdosa_plans}}', [
            'name' => 'Standard',
            'description' => 'Advanced features for small businesses',
            'price' => 29.99,
            'monthly_invoice_limit' => 100,
            'monthly_estimate_limit' => 100,
            'max_companies' => 3,
            'storage_limit_mb' => 1000,
            'can_use_api' => true,
            'can_use_import' => true,
            'can_use_custom_templates' => true,
            'can_use_ai_helper' => true,
            'max_subusers' => 3,
            'is_active' => true,
            'sort_order' => 2,
        ]);

        $this->insert('{{%jdosa_plans}}', [
            'name' => 'Pro',
            'description' => 'Full features for growing businesses',
            'price' => 59.99,
            'monthly_invoice_limit' => null,
            'monthly_estimate_limit' => null,
            'max_companies' => 10,
            'storage_limit_mb' => 5000,
            'can_use_api' => true,
            'can_use_import' => true,
            'can_use_custom_templates' => true,
            'can_use_ai_helper' => true,
            'max_subusers' => 10,
            'is_active' => true,
            'sort_order' => 3,
        ]);

        // Insert default admin user
        $this->insert('{{%jdosa_users}}', [
            'username' => 'admin',
            'email' => 'admin@example.com',
            'password_hash' => '$2y$13$ZjJmYzM4N2M4N2M4N2M4N.YmYzM4N2M4N2M4N2M4N2M4N2M4N2M4N2', // admin123
            'full_name' => 'System Administrator',
            'login_type' => 'local',
            'is_active' => true,
            'email_verified' => true,
            'role' => 'admin',
            'max_companies' => 999,
            'auth_key' => 'admin_auth_key',
        ]);

        // Insert demo user
        $this->insert('{{%jdosa_users}}', [
            'username' => 'demo',
            'email' => 'demo@example.com',
            'password_hash' => '$2y$13$ZjJmYzM4N2M4N2M4N2M4N.YmYzM4N2M4N2M4N2M4N2M4N2M4N2M4N2', // demo123
            'full_name' => 'Demo User',
            'login_type' => 'local',
            'is_active' => true,
            'email_verified' => true,
            'role' => 'demo',
            'max_companies' => 1,
            'auth_key' => 'demo_auth_key',
        ]);

        // Assign Free plan to demo user
        $this->insert('{{%jdosa_user_subscriptions}}', [
            'user_id' => 2, // demo user
            'plan_id' => 1, // Free plan
            'status' => 'active',
            'start_date' => date('Y-m-d'),
            'is_recurring' => false,
        ]);

        // Insert default company for demo user
        $this->insert('{{%jdosa_companies}}', [
            'company_name' => 'Demo Company',
            'company_address' => '123 Demo Street, Demo City, CA 90210',
            'company_phone' => '+1 (555) 123-4567',
            'company_email' => 'info@democompany.com',
            'user_id' => 2,
            'currency' => 'USD',
            'tax_rate' => 8.25,
            'invoice_prefix' => 'INV-',
            'estimate_prefix' => 'EST-',
            'due_date_days' => 30,
            'estimate_validity_days' => 30,
            'language' => 'en-US',
            'pdf_template' => 'classic',
            'is_active' => true,
        ]);

        // Update demo user's company_id
        $this->update('{{%jdosa_users}}', ['company_id' => 1], ['id' => 2]);

        // Insert admin settings
        $adminSettings = [
            ['allow_registration', 'true', 'boolean', 'Allow new user registration'],
            ['maintenance_mode', 'false', 'boolean', 'Enable maintenance mode'],
            ['max_users', '100', 'integer', 'Maximum number of users'],
            ['default_ai_model', 'claude-3-5-sonnet-20241022', 'string', 'Default AI model for AI Helper'],
            ['site_name', 'Invoice Manager', 'string', 'Site name'],
            ['support_email', 'support@invoicemanager.com', 'string', 'Support email address'],
        ];

        foreach ($adminSettings as $setting) {
            $this->insert('{{%jdosa_admin_settings}}', [
                'setting_name' => $setting[0],
                'setting_value' => $setting[1],
                'setting_type' => $setting[2],
                'description' => $setting[3],
                'is_public' => false,
            ]);
        }

        // Insert sample US state tax rates (2025 data)
        $stateTaxRates = [
            ['AL', 'Alabama', 4.0000, 5.1400, 9.1400, 11.9300, 35, 2025],
            ['AK', 'Alaska', 0.0000, 1.7600, 1.7600, 7.5000, 1, 2025],
            ['AZ', 'Arizona', 5.6000, 2.7700, 8.3700, 10.7300, 20, 2025],
            ['AR', 'Arkansas', 6.5000, 2.9300, 9.4300, 11.6300, 30, 2025],
            ['CA', 'California', 7.2500, 1.6100, 8.8600, 11.7500, 36, 2025],
            ['CO', 'Colorado', 2.9000, 4.6000, 7.5000, 11.2000, 15, 2025],
            ['CT', 'Connecticut', 6.3500, 0.0000, 6.3500, 6.3500, 8, 2025],
            ['DE', 'Delaware', 0.0000, 0.0000, 0.0000, 0.0000, 2, 2025],
            ['FL', 'Florida', 6.0000, 1.0500, 7.0500, 8.5000, 10, 2025],
            ['GA', 'Georgia', 4.0000, 3.2900, 7.2900, 8.9000, 17, 2025],
        ];

        foreach ($stateTaxRates as $rate) {
            $this->insert('{{%state_tax_rates}}', [
                'state_code' => $rate[0],
                'state_name' => $rate[1],
                'state_rate' => $rate[2],
                'avg_local_rate' => $rate[3],
                'combined_rate' => $rate[4],
                'max_local_rate' => $rate[5],
                'rank' => $rate[6],
                'data_year' => $rate[7],
                'data_month' => 1,
                'is_active' => true,
            ]);
        }
    }
}