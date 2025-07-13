<?php

use yii\db\Migration;

/**
 * Class m250713_000001_add_compact_mode_to_companies
 */
class m250713_000001_add_compact_mode_to_companies extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%jdosa_companies}}', 'compact_mode', $this->boolean()->defaultValue(false)->comment('Enable compact mode for menu display'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%jdosa_companies}}', 'compact_mode');
    }
}