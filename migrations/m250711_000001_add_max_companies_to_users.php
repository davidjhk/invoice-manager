<?php

use yii\db\Migration;

/**
 * Handles adding max_companies to table `jdosa_users`.
 */
class m250711_000001_add_max_companies_to_users extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('jdosa_users', 'max_companies', $this->integer()->defaultValue(1)->notNull()->comment('Maximum number of companies user can create'));
        
        // Set default value for existing users
        $this->update('jdosa_users', ['max_companies' => 1], '1=1');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('jdosa_users', 'max_companies');
    }
}