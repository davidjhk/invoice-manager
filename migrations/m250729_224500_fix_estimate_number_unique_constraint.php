<?php

use yii\db\Migration;

/**
 * Class m250729_224500_fix_estimate_number_unique_constraint
 */
class m250729_224500_fix_estimate_number_unique_constraint extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Drop the old, incorrect unique index on just estimate_number
        try {
            $this->dropIndex('idx-jdosa_estimates-estimate_number', 'jdosa_estimates');
        } catch (Exception $e) {
            // It's possible the index doesn't exist, so we catch the error and continue.
            echo "Notice: Could not drop index 'idx-jdosa_estimates-estimate_number'. It may not exist. " . $e->getMessage() . "\n";
        }

        // Add the new composite unique index on company_id and estimate_number
        $this->createIndex(
            'idx-jdosa_estimates-company_estimate_number',
            'jdosa_estimates',
            ['company_id', 'estimate_number'],
            true
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Drop the composite unique index
        $this->dropIndex('idx-jdosa_estimates-company_estimate_number', 'jdosa_estimates');

        // Restore the old unique index
        $this->createIndex(
            'idx-jdosa_estimates-estimate_number',
            'jdosa_estimates',
            'estimate_number',
            true
        );
    }
}
