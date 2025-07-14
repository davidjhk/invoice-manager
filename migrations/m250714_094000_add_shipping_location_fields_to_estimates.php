<?php

use yii\db\Migration;

/**
 * Add structured shipping location fields to estimates table
 */
class m250714_094000_add_shipping_location_fields_to_estimates extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Add shipping location fields to estimates table
        $this->addColumn('{{%jdosa_estimates}}', 'ship_to_city', $this->string(100)->null()->comment('Shipping city'));
        $this->addColumn('{{%jdosa_estimates}}', 'ship_to_state', $this->string(50)->null()->comment('Shipping state/province'));
        $this->addColumn('{{%jdosa_estimates}}', 'ship_to_zip_code', $this->string(20)->null()->comment('Shipping ZIP/postal code'));
        $this->addColumn('{{%jdosa_estimates}}', 'ship_to_country', $this->string(2)->defaultValue('US')->comment('Shipping country code'));

        // Add indexes for better query performance
        $this->createIndex('idx_estimates_ship_to_state', '{{%jdosa_estimates}}', 'ship_to_state');
        $this->createIndex('idx_estimates_ship_to_zip_code', '{{%jdosa_estimates}}', 'ship_to_zip_code');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Drop indexes first
        $this->dropIndex('idx_estimates_ship_to_zip_code', '{{%jdosa_estimates}}');
        $this->dropIndex('idx_estimates_ship_to_state', '{{%jdosa_estimates}}');
        
        // Drop columns
        $this->dropColumn('{{%jdosa_estimates}}', 'ship_to_country');
        $this->dropColumn('{{%jdosa_estimates}}', 'ship_to_zip_code');
        $this->dropColumn('{{%jdosa_estimates}}', 'ship_to_state');
        $this->dropColumn('{{%jdosa_estimates}}', 'ship_to_city');
    }
}