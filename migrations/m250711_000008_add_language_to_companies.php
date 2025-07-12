<?php

use yii\db\Migration;

/**
 * Class m250711_000008_add_language_to_companies
 */
class m250711_000008_add_language_to_companies extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Add language column to companies table
        $this->addColumn('{{%jdosa_companies}}', 'language', $this->string(10)->notNull()->defaultValue('en-US'));
        
        // Create index for better performance
        $this->createIndex(
            'idx-companies-language',
            '{{%jdosa_companies}}',
            'language'
        );
        
        // Add comment
        $this->addCommentOnColumn('{{%jdosa_companies}}', 'language', 'Company interface language preference');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Drop index
        $this->dropIndex('idx-companies-language', '{{%jdosa_companies}}');
        
        // Drop column
        $this->dropColumn('{{%jdosa_companies}}', 'language');
    }
}