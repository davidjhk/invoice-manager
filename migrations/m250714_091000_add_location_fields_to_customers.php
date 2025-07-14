<?php

use yii\db\Migration;

/**
 * Add location fields to customers table for better tax calculation
 */
class m250714_091000_add_location_fields_to_customers extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('jdosa_customers', 'city', "VARCHAR(100) DEFAULT NULL COMMENT 'Customer city'");
        $this->addColumn('jdosa_customers', 'state', "VARCHAR(2) DEFAULT NULL COMMENT 'Customer state (2-letter code)'");
        $this->addColumn('jdosa_customers', 'zip_code', "VARCHAR(10) DEFAULT NULL COMMENT 'Customer ZIP code'");
        $this->addColumn('jdosa_customers', 'country', "VARCHAR(2) DEFAULT 'US' COMMENT 'Customer country (2-letter code)'");
        
        // Add indexes for better performance
        $this->createIndex('idx-customers-state', 'jdosa_customers', 'state');
        $this->createIndex('idx-customers-zip_code', 'jdosa_customers', 'zip_code');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Drop indexes first
        $this->dropIndex('idx-customers-zip_code', 'jdosa_customers');
        $this->dropIndex('idx-customers-state', 'jdosa_customers');
        
        // Drop columns
        $this->dropColumn('jdosa_customers', 'country');
        $this->dropColumn('jdosa_customers', 'zip_code');
        $this->dropColumn('jdosa_customers', 'state');
        $this->dropColumn('jdosa_customers', 'city');
    }
}