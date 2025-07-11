<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%product_categories}}`.
 */
class m250711_000006_create_product_categories_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%jdosa_product_categories}}', [
            'id' => $this->primaryKey(),
            'company_id' => $this->integer()->notNull(),
            'name' => $this->string(100)->notNull(),
            'description' => $this->text(),
            'is_active' => $this->boolean()->defaultValue(true),
            'sort_order' => $this->integer()->defaultValue(0),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        // Add foreign key for company_id
        $this->addForeignKey(
            'fk-product_categories-company_id',
            '{{%jdosa_product_categories}}',
            'company_id',
            '{{%jdosa_companies}}',
            'id',
            'CASCADE'
        );

        // Add unique index for name per company
        $this->createIndex(
            'idx-product_categories-company_id-name',
            '{{%jdosa_product_categories}}',
            ['company_id', 'name'],
            true
        );

        // Add index for sort_order
        $this->createIndex(
            'idx-product_categories-sort_order',
            '{{%jdosa_product_categories}}',
            'sort_order'
        );

        // Insert default categories for existing companies
        $this->insertDefaultCategories();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Drop foreign key first
        $this->dropForeignKey(
            'fk-product_categories-company_id',
            '{{%jdosa_product_categories}}'
        );

        $this->dropTable('{{%jdosa_product_categories}}');
    }

    /**
     * Insert default categories for existing companies
     */
    private function insertDefaultCategories()
    {
        // Get all existing companies
        $companies = $this->db->createCommand('SELECT id FROM {{%jdosa_companies}}')->queryAll();

        $defaultCategories = [
            'General',
            'Consulting',
            'Software',
            'Hardware',
            'Installation',
            'Development',
            'Maintenance',
            'Website',
            'Hosting',
            'Training',
            'Support',
            'Other',
        ];

        foreach ($companies as $company) {
            $sortOrder = 1;
            foreach ($defaultCategories as $category) {
                $this->insert('{{%jdosa_product_categories}}', [
                    'company_id' => $company['id'],
                    'name' => $category,
                    'description' => null,
                    'is_active' => 1,
                    'sort_order' => $sortOrder++,
                ]);
            }
        }
    }
}