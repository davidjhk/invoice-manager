<?php

use yii\db\Migration;

/**
 * Add structured billing location fields to invoices table
 */
class m250714_096000_add_billing_location_fields_to_invoices extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Add billing location fields to invoices table
        $this->addColumn('{{%jdosa_invoices}}', 'bill_to_city', $this->string(100)->null()->comment('Billing city'));
        $this->addColumn('{{%jdosa_invoices}}', 'bill_to_state', $this->string(50)->null()->comment('Billing state/province'));
        $this->addColumn('{{%jdosa_invoices}}', 'bill_to_zip_code', $this->string(20)->null()->comment('Billing ZIP/postal code'));
        $this->addColumn('{{%jdosa_invoices}}', 'bill_to_country', $this->string(2)->defaultValue('US')->comment('Billing country code'));

        // Add indexes for better query performance
        $this->createIndex('idx_invoices_bill_to_state', '{{%jdosa_invoices}}', 'bill_to_state');
        $this->createIndex('idx_invoices_bill_to_zip_code', '{{%jdosa_invoices}}', 'bill_to_zip_code');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Drop indexes first
        $this->dropIndex('idx_invoices_bill_to_zip_code', '{{%jdosa_invoices}}');
        $this->dropIndex('idx_invoices_bill_to_state', '{{%jdosa_invoices}}');
        
        // Drop columns
        $this->dropColumn('{{%jdosa_invoices}}', 'bill_to_country');
        $this->dropColumn('{{%jdosa_invoices}}', 'bill_to_zip_code');
        $this->dropColumn('{{%jdosa_invoices}}', 'bill_to_state');
        $this->dropColumn('{{%jdosa_invoices}}', 'bill_to_city');
    }
}