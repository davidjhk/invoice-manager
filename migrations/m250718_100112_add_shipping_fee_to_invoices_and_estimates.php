<?php

use yii\db\Migration;

/**
 * Class m250718_100112_add_shipping_fee_to_invoices_and_estimates
 */
class m250718_100112_add_shipping_fee_to_invoices_and_estimates extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Add shipping_fee column to invoices table
        $this->addColumn('{{%jdosa_invoices}}', 'shipping_fee', $this->decimal(10, 2)->defaultValue(0)->after('discount_amount'));
        
        // Add shipping_fee column to estimates table
        $this->addColumn('{{%jdosa_estimates}}', 'shipping_fee', $this->decimal(10, 2)->defaultValue(0)->after('discount_amount'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Remove shipping_fee column from invoices table
        $this->dropColumn('{{%jdosa_invoices}}', 'shipping_fee');
        
        // Remove shipping_fee column from estimates table
        $this->dropColumn('{{%jdosa_estimates}}', 'shipping_fee');
    }
}