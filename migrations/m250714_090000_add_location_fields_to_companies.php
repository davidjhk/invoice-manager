<?php

use yii\db\Migration;

/**
 * Add location fields to companies table for better tax calculation
 */
class m250714_090000_add_location_fields_to_companies extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('jdosa_companies', 'city', "VARCHAR(100) DEFAULT NULL COMMENT 'Company city'");
        $this->addColumn('jdosa_companies', 'state', "VARCHAR(2) DEFAULT NULL COMMENT 'Company state (2-letter code)'");
        $this->addColumn('jdosa_companies', 'zip_code', "VARCHAR(10) DEFAULT NULL COMMENT 'Company ZIP code'");
        $this->addColumn('jdosa_companies', 'country', "VARCHAR(2) DEFAULT 'US' COMMENT 'Company country (2-letter code)'");
        
        // Add indexes for better performance
        $this->createIndex('idx-companies-state', 'jdosa_companies', 'state');
        $this->createIndex('idx-companies-zip_code', 'jdosa_companies', 'zip_code');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Drop indexes first
        $this->dropIndex('idx-companies-zip_code', 'jdosa_companies');
        $this->dropIndex('idx-companies-state', 'jdosa_companies');
        
        // Drop columns
        $this->dropColumn('jdosa_companies', 'country');
        $this->dropColumn('jdosa_companies', 'zip_code');
        $this->dropColumn('jdosa_companies', 'state');
        $this->dropColumn('jdosa_companies', 'city');
    }
}