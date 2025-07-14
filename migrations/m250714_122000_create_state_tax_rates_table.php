<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%state_tax_rates}}`.
 */
class m250714_122000_create_state_tax_rates_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('state_tax_rates', [
            'id' => $this->primaryKey(),
            'state_code' => $this->string(2)->notNull()->comment('Two-letter state code'),
            'country_code' => $this->string(2)->notNull()->defaultValue('US')->comment('Two-letter country code'),
            'base_rate' => $this->decimal(5,4)->notNull()->defaultValue(0)->comment('Base state tax rate (percentage)'),
            'has_local_tax' => $this->boolean()->notNull()->defaultValue(false)->comment('Whether state has local tax jurisdictions'),
            'average_total_rate' => $this->decimal(5,4)->notNull()->defaultValue(0)->comment('Average total rate including local taxes'),
            'revenue_threshold' => $this->decimal(12,2)->null()->comment('Economic nexus revenue threshold'),
            'transaction_threshold' => $this->integer()->null()->comment('Economic nexus transaction threshold'),
            'is_active' => $this->boolean()->notNull()->defaultValue(true)->comment('Whether tax rate is active'),
            'effective_date' => $this->date()->notNull()->comment('Date when this rate becomes effective'),
            'notes' => $this->text()->null()->comment('Additional notes about tax rate'),
            'created_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        // Add indexes
        $this->createIndex(
            'idx-state_tax_rates-state_code',
            'state_tax_rates',
            'state_code'
        );

        $this->createIndex(
            'idx-state_tax_rates-country_code',
            'state_tax_rates',
            'country_code'
        );

        $this->createIndex(
            'idx-state_tax_rates-state_country',
            'state_tax_rates',
            ['state_code', 'country_code']
        );

        $this->createIndex(
            'idx-state_tax_rates-is_active',
            'state_tax_rates',
            'is_active'
        );

        $this->createIndex(
            'idx-state_tax_rates-effective_date',
            'state_tax_rates',
            'effective_date'
        );

        // Add unique constraint
        $this->createIndex(
            'unique-state_tax_rates-state_country_effective',
            'state_tax_rates',
            ['state_code', 'country_code', 'effective_date'],
            true
        );

        // Foreign key constraint to states table
        $this->addForeignKey(
            'fk-state_tax_rates-state_code',
            'state_tax_rates',
            ['state_code', 'country_code'],
            'states',
            ['state_code', 'country_code'],
            'CASCADE',
            'CASCADE'
        );

        // Insert initial US state tax rates data
        $this->insertInitialData();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Drop foreign key first
        $this->dropForeignKey(
            'fk-state_tax_rates-state_code',
            'state_tax_rates'
        );

        $this->dropTable('state_tax_rates');
    }

    /**
     * Insert initial US state tax rates data
     */
    private function insertInitialData()
    {
        $effectiveDate = '2025-01-01';
        
        // US State Tax Rates (2025)
        $taxRates = [
            ['AL', 4.00, true, 9.24, 250000, null],        // Alabama
            ['AK', 0.00, true, 1.76, 100000, null],        // Alaska
            ['AZ', 5.60, true, 8.40, 100000, null],        // Arizona  
            ['AR', 6.50, true, 9.47, 100000, 200],         // Arkansas
            ['CA', 6.00, true, 8.85, 500000, null],        // California
            ['CO', 2.90, true, 7.86, 100000, null],        // Colorado
            ['CT', 6.35, false, 6.35, 100000, 200],        // Connecticut
            ['DE', 0.00, false, 0.00, null, null],         // Delaware - No sales tax
            ['FL', 6.00, true, 7.02, 100000, null],        // Florida
            ['GA', 4.00, true, 7.31, 100000, 200],         // Georgia
            ['HI', 4.00, true, 4.44, 100000, 200],         // Hawaii
            ['ID', 6.00, true, 6.03, 100000, null],        // Idaho
            ['IL', 6.25, true, 8.64, 100000, 200],         // Illinois
            ['IN', 7.00, false, 7.00, 100000, 200],        // Indiana
            ['IA', 6.00, true, 6.94, 100000, 200],         // Iowa
            ['KS', 6.50, true, 8.68, 100000, null],        // Kansas
            ['KY', 6.00, false, 6.00, 100000, 200],        // Kentucky
            ['LA', 4.45, true, 9.56, 100000, 200],         // Louisiana
            ['ME', 5.50, false, 5.50, 100000, 200],        // Maine
            ['MD', 6.00, false, 6.00, 100000, 200],        // Maryland
            ['MA', 6.25, false, 6.25, 100000, null],       // Massachusetts
            ['MI', 6.00, false, 6.00, 100000, 200],        // Michigan
            ['MN', 6.88, true, 7.46, 100000, 200],         // Minnesota
            ['MS', 7.00, true, 7.07, 250000, null],        // Mississippi
            ['MO', 4.23, true, 8.30, 100000, null],        // Missouri
            ['MT', 0.00, false, 0.00, null, null],         // Montana - No sales tax
            ['NE', 5.50, true, 6.94, 100000, 200],         // Nebraska
            ['NV', 4.60, true, 8.23, 100000, 200],         // Nevada
            ['NH', 0.00, false, 0.00, null, null],         // New Hampshire - No sales tax
            ['NJ', 6.63, false, 6.63, 100000, 200],        // New Jersey
            ['NM', 5.13, true, 7.69, 100000, null],        // New Mexico
            ['NY', 4.00, true, 8.54, 500000, 100],         // New York
            ['NC', 4.75, true, 6.98, 100000, 200],         // North Carolina
            ['ND', 5.00, true, 6.86, 100000, null],        // North Dakota
            ['OH', 5.75, true, 7.26, 100000, 200],         // Ohio
            ['OK', 4.50, true, 9.05, 100000, null],        // Oklahoma
            ['OR', 0.00, false, 0.00, null, null],         // Oregon - No sales tax
            ['PA', 6.00, true, 6.34, 100000, null],        // Pennsylvania
            ['RI', 7.00, false, 7.00, 100000, 200],        // Rhode Island
            ['SC', 6.00, true, 7.46, 100000, null],        // South Carolina
            ['SD', 4.20, true, 6.40, 100000, 200],         // South Dakota
            ['TN', 7.00, true, 9.55, 100000, null],        // Tennessee
            ['TX', 6.25, true, 8.20, 500000, null],        // Texas
            ['UT', 4.85, true, 7.10, 100000, 200],         // Utah
            ['VT', 6.00, true, 6.24, 100000, 200],         // Vermont
            ['VA', 4.30, true, 5.75, 100000, 200],         // Virginia
            ['WA', 6.50, true, 9.23, 100000, null],        // Washington
            ['WV', 6.00, true, 6.48, 100000, 200],         // West Virginia
            ['WI', 5.00, true, 5.44, 100000, null],        // Wisconsin
            ['WY', 4.00, true, 5.36, 100000, 200],         // Wyoming
        ];

        foreach ($taxRates as $rate) {
            $this->insert('state_tax_rates', [
                'state_code' => $rate[0],
                'country_code' => 'US',
                'base_rate' => $rate[1],
                'has_local_tax' => $rate[2] ? 1 : 0,
                'average_total_rate' => $rate[3],
                'revenue_threshold' => $rate[4],
                'transaction_threshold' => $rate[5],
                'is_active' => 1,
                'effective_date' => $effectiveDate,
                'notes' => 'Initial 2025 US state tax rates',
            ]);
        }
    }
}