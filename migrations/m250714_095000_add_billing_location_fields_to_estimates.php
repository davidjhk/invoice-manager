<?php

use yii\db\Migration;

/**
 * Add structured billing location fields to estimates table
 */
class m250714_095000_add_billing_location_fields_to_estimates extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Add billing location fields to estimates table
        $this->addColumn('{{%jdosa_estimates}}', 'bill_to_city', $this->string(100)->null()->comment('Billing city'));
        $this->addColumn('{{%jdosa_estimates}}', 'bill_to_state', $this->string(50)->null()->comment('Billing state/province'));
        $this->addColumn('{{%jdosa_estimates}}', 'bill_to_zip_code', $this->string(20)->null()->comment('Billing ZIP/postal code'));
        $this->addColumn('{{%jdosa_estimates}}', 'bill_to_country', $this->string(2)->defaultValue('US')->comment('Billing country code'));

        // Add indexes for better query performance
        $this->createIndex('idx_estimates_bill_to_state', '{{%jdosa_estimates}}', 'bill_to_state');
        $this->createIndex('idx_estimates_bill_to_zip_code', '{{%jdosa_estimates}}', 'bill_to_zip_code');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Drop indexes first
        $this->dropIndex('idx_estimates_bill_to_zip_code', '{{%jdosa_estimates}}');
        $this->dropIndex('idx_estimates_bill_to_state', '{{%jdosa_estimates}}');
        
        // Drop columns
        $this->dropColumn('{{%jdosa_estimates}}', 'bill_to_country');
        $this->dropColumn('{{%jdosa_estimates}}', 'bill_to_zip_code');
        $this->dropColumn('{{%jdosa_estimates}}', 'bill_to_state');
        $this->dropColumn('{{%jdosa_estimates}}', 'bill_to_city');
    }
}