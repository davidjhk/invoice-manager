<?php

namespace app\controllers;

use Yii;
use app\models\Product;
use app\models\ProductCategory;
use app\models\Company;
use yii\web\Controller;
use yii\web\UploadedFile;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\data\ArrayDataProvider;
use yii\helpers\Html;

/**
 * ProductImportController handles CSV import functionality for products.
 * Available only for Pro plan users.
 */
class ProductImportController extends Controller
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
                        'matchCallback' => function ($rule, $action) {
                            // Check if user can use import functionality
                            $user = Yii::$app->user->identity;
                            if (!$user || !$user->canUseImport()) {
                                return false;
                            }
                            return true;
                        }
                    ],
                ],
                'denyCallback' => function ($rule, $action) {
                    $user = Yii::$app->user->identity;
                    if (!$user) {
                        return $this->redirect(['site/login']);
                    }
                    
                    // Show upgrade message for non-Pro users
                    Yii::$app->session->setFlash('error', Yii::t('app', 'Import functionality is only available for Pro plan users. Please upgrade your plan to access this feature.'));
                    return $this->redirect(['/subscription/my-account']);
                }
            ],
        ];
    }

    /**
     * Import products from CSV
     *
     * @return string|\yii\web\Response
     */
    public function actionIndex()
    {
        $company = Company::getCurrent();
        if (!$company) {
            return $this->redirect(['company/select']);
        }

        $previewData = null;
        $errors = [];
        $step = 'upload'; // upload, preview, import

        if (Yii::$app->request->isPost) {
            $uploadedFile = UploadedFile::getInstanceByName('csvFile');
            
            if ($uploadedFile) {
                // Validate file
                if ($uploadedFile->extension !== 'csv') {
                    $errors[] = Yii::t('app', 'Please upload a CSV file.');
                } elseif ($uploadedFile->size > 5 * 1024 * 1024) { // 5MB limit
                    $errors[] = Yii::t('app', 'File size cannot exceed 5MB.');
                } else {
                    // Parse CSV for preview
                    $previewData = $this->parseCsvFile($uploadedFile->tempName);
                    if (empty($previewData)) {
                        $errors[] = Yii::t('app', 'The CSV file appears to be empty or invalid.');
                    } else {
                        $step = 'preview';
                        // Store file temporarily for import
                        $tempFile = Yii::getAlias('@runtime') . '/product_import_' . Yii::$app->user->id . '_' . time() . '.csv';
                        move_uploaded_file($uploadedFile->tempName, $tempFile);
                        Yii::$app->session->set('product_import_temp_file', $tempFile);
                    }
                }
            } elseif (Yii::$app->request->post('confirm_import')) {
                // Process import
                $tempFile = Yii::$app->session->get('product_import_temp_file');
                if ($tempFile && file_exists($tempFile)) {
                    $result = $this->processImport($tempFile, $company->id);
                    
                    // Clean up temp file
                    unlink($tempFile);
                    Yii::$app->session->remove('product_import_temp_file');
                    
                    if ($result['success']) {
                        Yii::$app->session->setFlash('success', Yii::t('app', 'Successfully imported {count} products.', ['count' => $result['imported']]));
                        return $this->redirect(['product/index']);
                    } else {
                        $errors = $result['errors'];
                        $step = 'upload';
                    }
                } else {
                    $errors[] = Yii::t('app', 'Import session expired. Please try again.');
                    $step = 'upload';
                }
            }
        }

        return $this->render('index', [
            'step' => $step,
            'previewData' => $previewData,
            'errors' => $errors,
            'company' => $company,
        ]);
    }

    /**
     * Download CSV template
     *
     * @return \yii\web\Response
     */
    public function actionDownloadTemplate()
    {
        $filename = 'product_import_template.csv';
        
        // Create CSV content with headers and sample data
        $headers = [
            'name',
            'description',
            'type',
            'category',
            'sku',
            'unit',
            'price',
            'cost',
            'is_taxable',
        ];
        
        $sampleData = [
            [
                'Web Development Service',
                'Custom website development and design',
                'service',
                'Development',
                'WEB-001',
                'hour',
                '75.00',
                '50.00',
                '1',
            ],
            [
                'Premium Widget',
                'High-quality widget with advanced features',
                'product',
                'Hardware',
                'WID-PREM-001',
                'each',
                '299.99',
                '150.00',
                '1',
            ],
            [
                'Consultation',
                'Business consultation and strategy planning',
                'service',
                'Consulting',
                'CONS-001',
                'hour',
                '120.00',
                '80.00',
                '0',
            ],
        ];

        // Set response headers for CSV download
        Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
        Yii::$app->response->headers->add('Content-Type', 'text/csv; charset=utf-8');
        Yii::$app->response->headers->add('Content-Disposition', 'attachment; filename="' . $filename . '"');

        // Create CSV content
        $output = fopen('php://output', 'w');
        
        // Add BOM for UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Add headers
        fputcsv($output, $headers);
        
        // Add sample data
        foreach ($sampleData as $row) {
            fputcsv($output, $row);
        }

        fclose($output);
        return Yii::$app->response;
    }

    /**
     * Parse CSV file and return preview data
     *
     * @param string $filePath
     * @return array
     */
    private function parseCsvFile($filePath)
    {
        $data = [];
        $headers = [];
        
        if (($handle = fopen($filePath, "r")) !== FALSE) {
            $rowIndex = 0;
            while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
                if ($rowIndex === 0) {
                    // First row as headers
                    $headers = array_map('trim', $row);
                } else {
                    // Data rows
                    $rowData = [];
                    foreach ($row as $index => $value) {
                        $header = isset($headers[$index]) ? $headers[$index] : 'column_' . $index;
                        $rowData[$header] = trim($value);
                    }
                    $data[] = $rowData;
                    
                    // Limit preview to first 10 rows
                    if (count($data) >= 10) {
                        break;
                    }
                }
                $rowIndex++;
            }
            fclose($handle);
        }
        
        return [
            'headers' => $headers,
            'data' => $data,
            'total_rows' => $rowIndex - 1, // Exclude header row
        ];
    }

    /**
     * Process the actual import
     *
     * @param string $filePath
     * @param int $companyId
     * @return array
     */
    private function processImport($filePath, $companyId)
    {
        $imported = 0;
        $errors = [];
        $headers = [];
        $categories = [];
        
        if (($handle = fopen($filePath, "r")) !== FALSE) {
            $transaction = Yii::$app->db->beginTransaction();
            
            try {
                $rowIndex = 0;
                while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    if ($rowIndex === 0) {
                        // First row as headers
                        $headers = array_map('trim', $row);
                    } else {
                        // Data rows
                        $rowData = [];
                        foreach ($row as $index => $value) {
                            $header = isset($headers[$index]) ? $headers[$index] : 'column_' . $index;
                            $rowData[$header] = trim($value);
                        }
                        
                        // Skip empty rows
                        if (empty($rowData['name'])) {
                            continue;
                        }
                        
                        // Handle category - create if not exists
                        $categoryId = null;
                        if (!empty($rowData['category'])) {
                            $categoryName = $rowData['category'];
                            if (!isset($categories[$categoryName])) {
                                $category = ProductCategory::find()
                                    ->where(['company_id' => $companyId, 'name' => $categoryName])
                                    ->one();
                                
                                if (!$category) {
                                    $category = new ProductCategory();
                                    $category->company_id = $companyId;
                                    $category->name = $categoryName;
                                    $category->description = 'Auto-created during import';
                                    $category->is_active = true;
                                    $category->save();
                                }
                                
                                $categories[$categoryName] = $category->id;
                            }
                            $categoryId = $categories[$categoryName];
                        }
                        
                        // Create product
                        $product = new Product();
                        $product->company_id = $companyId;
                        $product->name = $rowData['name'] ?? '';
                        $product->description = $rowData['description'] ?? '';
                        $product->type = in_array($rowData['type'] ?? '', [Product::TYPE_SERVICE, Product::TYPE_PRODUCT, Product::TYPE_NON_INVENTORY]) 
                            ? $rowData['type'] 
                            : Product::TYPE_SERVICE;
                        $product->category = $rowData['category'] ?? ''; // Legacy category field
                        $product->category_id = $categoryId;
                        $product->sku = $rowData['sku'] ?? '';
                        $product->unit = $rowData['unit'] ?? 'each';
                        $product->price = floatval($rowData['price'] ?? 0);
                        $product->cost = floatval($rowData['cost'] ?? 0);
                        $product->is_taxable = in_array(strtolower($rowData['is_taxable'] ?? '1'), ['1', 'true', 'yes', 'y']);
                        $product->is_active = true;
                        
                        if ($product->save()) {
                            $imported++;
                        } else {
                            $errors[] = Yii::t('app', 'Row {row}: {errors}', [
                                'row' => $rowIndex,
                                'errors' => implode(', ', array_map(function($fieldErrors) {
                                    return implode(', ', $fieldErrors);
                                }, $product->errors))
                            ]);
                            
                            // Stop import if too many errors
                            if (count($errors) >= 10) {
                                break;
                            }
                        }
                    }
                    $rowIndex++;
                }
                
                if (empty($errors)) {
                    $transaction->commit();
                    return ['success' => true, 'imported' => $imported];
                } else {
                    $transaction->rollBack();
                    return ['success' => false, 'errors' => $errors];
                }
                
            } catch (\Exception $e) {
                $transaction->rollBack();
                return ['success' => false, 'errors' => [Yii::t('app', 'Import failed: {error}', ['error' => $e->getMessage()])]];
            } finally {
                fclose($handle);
            }
        }
        
        return ['success' => false, 'errors' => [Yii::t('app', 'Could not read the CSV file.')]];
    }
}