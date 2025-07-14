<?php

use yii\db\Migration;

/**
 * Migration to create tax jurisdictions table for detailed tax rate management
 * 
 * This table stores comprehensive tax rate data by ZIP code and jurisdiction
 * for accurate US sales tax calculation.
 */
class m250714_000002_create_tax_jurisdictions_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Create tax jurisdictions table
        $this->createTable('{{%jdosa_tax_jurisdictions}}', [
            'id' => $this->primaryKey(),
            'zip_code' => $this->string(10)->notNull()->comment('5 or 9 digit ZIP code'),
            'state_code' => $this->string(2)->notNull()->comment('2-letter state code'),
            'state_name' => $this->string(50)->comment('Full state name'),
            'county_name' => $this->string(100)->comment('County name'),
            'city_name' => $this->string(100)->comment('City name'),
            'tax_region_name' => $this->string(200)->comment('Tax region/jurisdiction name'),
            
            // Tax rates (stored as decimal percentages, e.g., 8.2500 for 8.25%)
            'state_rate' => $this->decimal(7, 4)->defaultValue(0.0000)->comment('State tax rate'),
            'county_rate' => $this->decimal(7, 4)->defaultValue(0.0000)->comment('County tax rate'),
            'city_rate' => $this->decimal(7, 4)->defaultValue(0.0000)->comment('City tax rate'),
            'special_rate' => $this->decimal(7, 4)->defaultValue(0.0000)->comment('Special district rate'),
            'combined_rate' => $this->decimal(7, 4)->notNull()->comment('Total combined tax rate'),
            
            // Additional jurisdiction info
            'estimated_population' => $this->integer()->comment('Estimated population for this jurisdiction'),
            'tax_authority' => $this->string(200)->comment('Tax collecting authority'),
            'jurisdiction_code' => $this->string(50)->comment('Internal jurisdiction code'),
            
            // Data source and validity
            'data_source' => $this->string(50)->defaultValue('manual')->comment('Source of data (manual, api, import)'),
            'effective_date' => $this->date()->notNull()->comment('When this rate became effective'),
            'expiry_date' => $this->date()->comment('When this rate expires (if applicable)'),
            'is_active' => $this->boolean()->defaultValue(true)->comment('Whether this rate is currently active'),
            
            // Metadata
            'data_year' => $this->integer()->comment('Year this data represents'),
            'data_month' => $this->integer()->comment('Month this data represents'),
            'last_verified' => $this->date()->comment('Last date this rate was verified'),
            'notes' => $this->text()->comment('Additional notes about this jurisdiction'),
            
            // Timestamps
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
            'created_by' => $this->integer()->comment('User who created this record'),
            'updated_by' => $this->integer()->comment('User who last updated this record'),
        ]);

        // Create indexes for optimal query performance
        $this->createIndex('idx-tax_jurisdictions-zip_code', '{{%jdosa_tax_jurisdictions}}', 'zip_code');
        $this->createIndex('idx-tax_jurisdictions-state_code', '{{%jdosa_tax_jurisdictions}}', 'state_code');
        $this->createIndex('idx-tax_jurisdictions-combined_rate', '{{%jdosa_tax_jurisdictions}}', 'combined_rate');
        $this->createIndex('idx-tax_jurisdictions-effective_date', '{{%jdosa_tax_jurisdictions}}', 'effective_date');
        $this->createIndex('idx-tax_jurisdictions-is_active', '{{%jdosa_tax_jurisdictions}}', 'is_active');
        $this->createIndex('idx-tax_jurisdictions-data_source', '{{%jdosa_tax_jurisdictions}}', 'data_source');
        
        // Composite indexes for common queries
        $this->createIndex('idx-tax_jurisdictions-zip_active', '{{%jdosa_tax_jurisdictions}}', ['zip_code', 'is_active']);
        $this->createIndex('idx-tax_jurisdictions-state_zip', '{{%jdosa_tax_jurisdictions}}', ['state_code', 'zip_code']);
        $this->createIndex('idx-tax_jurisdictions-effective_active', '{{%jdosa_tax_jurisdictions}}', ['effective_date', 'is_active']);
        $this->createIndex('idx-tax_jurisdictions-year_month', '{{%jdosa_tax_jurisdictions}}', ['data_year', 'data_month']);

        // Create unique constraint for ZIP + effective date to prevent duplicates
        $this->createIndex('idx-tax_jurisdictions-unique_zip_date', '{{%jdosa_tax_jurisdictions}}', 
            ['zip_code', 'effective_date', 'is_active'], true);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%jdosa_tax_jurisdictions}}');
    }
}