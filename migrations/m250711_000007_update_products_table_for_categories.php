<?php

use yii\db\Migration;

/**
 * Handles updating the products table for category relationship.
 */
class m250711_000007_update_products_table_for_categories extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Add category_id column
        $this->addColumn('{{%jdosa_products}}', 'category_id', $this->integer()->after('type'));
        
        // Add foreign key for category_id
        $this->addForeignKey(
            'fk-products-category_id',
            '{{%jdosa_products}}',
            'category_id',
            '{{%jdosa_product_categories}}',
            'id',
            'SET NULL'
        );

        // Migrate existing category data
        $this->migrateExistingCategories();

        // Keep the old category column for now (we'll remove it later after verification)
        // $this->dropColumn('{{%jdosa_products}}', 'category');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Drop foreign key first
        $this->dropForeignKey(
            'fk-products-category_id',
            '{{%jdosa_products}}'
        );

        $this->dropColumn('{{%jdosa_products}}', 'category_id');
    }

    /**
     * Migrate existing category strings to category_id references
     */
    private function migrateExistingCategories()
    {
        // Get all products with categories
        $products = $this->db->createCommand('
            SELECT id, company_id, category 
            FROM {{%jdosa_products}} 
            WHERE category IS NOT NULL AND category != ""
        ')->queryAll();

        foreach ($products as $product) {
            // Find or create the category for this company
            $categoryId = $this->findOrCreateCategory($product['company_id'], $product['category']);
            
            if ($categoryId) {
                // Update the product with the category_id
                $this->update('{{%jdosa_products}}', 
                    ['category_id' => $categoryId], 
                    ['id' => $product['id']]
                );
            }
        }
    }

    /**
     * Find or create a category for the given company
     *
     * @param int $companyId
     * @param string $categoryName
     * @return int|null
     */
    private function findOrCreateCategory($companyId, $categoryName)
    {
        // Try to find existing category
        $existingCategory = $this->db->createCommand('
            SELECT id FROM {{%jdosa_product_categories}} 
            WHERE company_id = :company_id AND name = :name
        ', [
            ':company_id' => $companyId,
            ':name' => $categoryName
        ])->queryScalar();

        if ($existingCategory) {
            return $existingCategory;
        }

        // Create new category if it doesn't exist
        $nextSortOrder = $this->db->createCommand('
            SELECT COALESCE(MAX(sort_order), 0) + 1 
            FROM {{%jdosa_product_categories}} 
            WHERE company_id = :company_id
        ', [':company_id' => $companyId])->queryScalar();

        $this->insert('{{%jdosa_product_categories}}', [
            'company_id' => $companyId,
            'name' => $categoryName,
            'description' => null,
            'is_active' => 1,
            'sort_order' => $nextSortOrder,
        ]);

        return $this->db->getLastInsertID();
    }
}