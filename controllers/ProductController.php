<?php

namespace app\controllers;

use Yii;
use app\models\Product;
use app\models\Company;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\Response;

/**
 * ProductController implements the CRUD actions for Product model.
 */
class ProductController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'], // Only authenticated users
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Product models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $company = Company::getCurrent();
        if (!$company) {
            return $this->redirect(['company/select']);
        }

        $searchTerm = Yii::$app->request->get('search', '');
        $typeFilter = Yii::$app->request->get('type', '');
        $categoryFilter = Yii::$app->request->get('category', '');
        
        $query = Product::find()->where(['company_id' => $company->id]);

        if (!empty($searchTerm)) {
            $query->joinWith('productCategory');
            $query->andWhere(['or',
                ['like', 'name', $searchTerm],
                ['like', 'description', $searchTerm],
                ['like', 'sku', $searchTerm],
                ['like', 'category', $searchTerm], // Keep old category for backward compatibility
                ['like', 'product_categories.name', $searchTerm], // New category search
            ]);
        }

        if (!empty($typeFilter)) {
            $query->andWhere(['type' => $typeFilter]);
        }

        if (!empty($categoryFilter)) {
            // Support both old category field and new category_id
            if (is_numeric($categoryFilter)) {
                $query->andWhere(['category_id' => $categoryFilter]);
            } else {
                $query->andWhere(['category' => $categoryFilter]);
            }
        }

        $products = $query->orderBy(['name' => SORT_ASC])->all();

        return $this->render('index', [
            'products' => $products,
            'searchTerm' => $searchTerm,
            'typeFilter' => $typeFilter,
            'categoryFilter' => $categoryFilter,
            'company' => $company,
        ]);
    }

    /**
     * Displays a single Product model.
     *
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        
        return $this->render('view', [
            'model' => $model,
        ]);
    }

    /**
     * Creates a new Product model.
     *
     * @return string|Response
     */
    public function actionCreate()
    {
        $company = Company::getCurrent();
        if (!$company) {
            return $this->redirect(['company/select']);
        }

        $model = new Product();
        $model->company_id = $company->id;
        $model->type = Product::TYPE_SERVICE;
        $model->unit = 'each';
        $model->price = 0.00;
        $model->cost = 0.00;
        $model->is_taxable = true;
        $model->is_active = true;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Product created successfully.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
            'company' => $company,
        ]);
    }

    /**
     * Updates an existing Product model.
     *
     * @param int $id ID
     * @return string|Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Product updated successfully.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
            'company' => $model->company,
        ]);
    }

    /**
     * Deletes an existing Product model.
     *
     * @param int $id ID
     * @return Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        
        $model->delete();
        Yii::$app->session->setFlash('success', 'Product deleted successfully.');

        return $this->redirect(['index']);
    }

    /**
     * Toggle product active status
     *
     * @param int $id
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionToggleStatus($id)
    {
        $model = $this->findModel($id);
        
        $model->is_active = !$model->is_active;
        
        if ($model->save()) {
            $status = $model->is_active ? 'activated' : 'deactivated';
            Yii::$app->session->setFlash('success', "Product {$status} successfully.");
        } else {
            Yii::$app->session->setFlash('error', 'Failed to update product status.');
        }

        return $this->redirect(['view', 'id' => $model->id]);
    }

    /**
     * Search products via AJAX
     *
     * @return Response
     */
    public function actionSearch()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        $term = Yii::$app->request->get('term', '');
        $company = Company::getCurrent();
        
        if (!$company || empty($term)) {
            return [];
        }
        
        $products = Product::search($term, $company->id)->limit(20)->all();
        
        $result = [];
        foreach ($products as $product) {
            $result[] = [
                'id' => $product->id,
                'text' => $product->getDisplayName(),
                'name' => $product->name,
                'description' => $product->description,
                'sku' => $product->sku,
                'unit' => $product->unit,
                'price' => $product->price,
                'cost' => $product->cost,
                'is_taxable' => $product->is_taxable,
                'type' => $product->type,
                'category' => $product->getCategoryLabel(), // Use new category system
                'category_id' => $product->category_id, // Provide category_id for new system
                'formatted_price' => $product->getFormattedPrice(),
                'full_description' => $product->getFullDescription(),
            ];
        }
        
        return $result;
    }

    /**
     * Get product data via AJAX
     *
     * @param int $id
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionGetData($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        $model = $this->findModel($id);
        
        return [
            'success' => true,
            'product' => [
                'id' => $model->id,
                'name' => $model->name,
                'description' => $model->description,
                'sku' => $model->sku,
                'unit' => $model->unit,
                'price' => $model->price,
                'cost' => $model->cost,
                'is_taxable' => $model->is_taxable,
                'type' => $model->type,
                'category' => $model->getCategoryLabel(), // Use new category system
                'category_id' => $model->category_id, // Provide category_id for new system
                'formatted_price' => $model->getFormattedPrice(),
                'full_description' => $model->getFullDescription(),
                'display_name' => $model->getDisplayName(),
            ]
        ];
    }

    /**
     * Create product via AJAX
     *
     * @return Response
     */
    public function actionCreateAjax()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        $company = Company::getCurrent();
        if (!$company) {
            return ['success' => false, 'message' => 'Default company not found.'];
        }

        $model = new Product();
        $model->company_id = $company->id;
        $model->type = Product::TYPE_SERVICE;
        $model->unit = 'each';
        $model->price = 0.00;
        $model->cost = 0.00;
        $model->is_taxable = true;
        $model->is_active = true;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return [
                'success' => true,
                'product' => [
                    'id' => $model->id,
                    'name' => $model->name,
                    'description' => $model->description,
                    'sku' => $model->sku,
                    'unit' => $model->unit,
                    'price' => $model->price,
                    'cost' => $model->cost,
                    'is_taxable' => $model->is_taxable,
                    'type' => $model->type,
                    'category' => $model->getCategoryLabel(), // Use new category system
                    'category_id' => $model->category_id, // Provide category_id for new system
                    'display_name' => $model->getDisplayName(),
                    'formatted_price' => $model->getFormattedPrice(),
                ],
                'message' => 'Product created successfully.',
            ];
        }

        return [
            'success' => false,
            'errors' => $model->errors,
            'message' => 'Failed to create product.',
        ];
    }

    /**
     * Export products to CSV
     *
     * @return Response
     */
    public function actionExport()
    {
        $company = Company::getCurrent();
        if (!$company) {
            return $this->redirect(['company/select']);
        }

        $products = Product::findActiveByCompany($company->id)->all();
        
        $filename = 'products_' . date('Y-m-d_H-i-s') . '.csv';
        
        Yii::$app->response->format = Response::FORMAT_RAW;
        Yii::$app->response->headers->add('Content-Type', 'text/csv');
        Yii::$app->response->headers->add('Content-Disposition', 'attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        // CSV header
        fputcsv($output, [
            'ID',
            'Name',
            'Description',
            'Type',
            'Category',
            'SKU',
            'Unit',
            'Price',
            'Cost',
            'Taxable',
            'Status',
            'Created At',
        ]);
        
        // CSV data
        foreach ($products as $product) {
            fputcsv($output, [
                $product->id,
                $product->name,
                $product->description,
                $product->getTypeLabel(),
                $product->getCategoryLabel(),
                $product->sku,
                $product->getUnitLabel(),
                number_format($product->price, 2),
                number_format($product->cost, 2),
                $product->is_taxable ? 'Yes' : 'No',
                $product->is_active ? 'Active' : 'Inactive',
                $product->created_at,
            ]);
        }
        
        fclose($output);
        
        return Yii::$app->response;
    }

    /**
     * Finds the Product model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * Also ensures the product belongs to the current company.
     *
     * @param int $id ID
     * @return Product the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        $company = Company::getCurrent();
        if (!$company) {
            throw new NotFoundHttpException('No company selected.');
        }
        
        $model = Product::findOne(['id' => $id, 'company_id' => $company->id]);
        if ($model !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}