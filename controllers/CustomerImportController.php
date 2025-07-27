<?php

namespace app\controllers;

use Yii;
use app\models\Customer;
use app\models\Company;
use yii\web\Controller;
use yii\web\UploadedFile;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\data\ArrayDataProvider;
use yii\helpers\Html;

/**
 * CustomerImportController handles CSV import functionality for customers.
 * Available only for Pro plan users.
 */
class CustomerImportController extends Controller
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
     * Import customers from CSV
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
                        $tempFile = Yii::getAlias('@runtime') . '/customer_import_' . Yii::$app->user->id . '_' . time() . '.csv';
                        move_uploaded_file($uploadedFile->tempName, $tempFile);
                        Yii::$app->session->set('import_temp_file', $tempFile);
                    }
                }
            } elseif (Yii::$app->request->post('confirm_import')) {
                // Process import
                $tempFile = Yii::$app->session->get('import_temp_file');
                if ($tempFile && file_exists($tempFile)) {
                    $result = $this->processImport($tempFile, $company->id);
                    
                    // Clean up temp file
                    unlink($tempFile);
                    Yii::$app->session->remove('import_temp_file');
                    
                    if ($result['success']) {
                        Yii::$app->session->setFlash('success', Yii::t('app', 'Successfully imported {count} customers.', ['count' => $result['imported']]));
                        return $this->redirect(['customer/index']);
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
        $filename = 'customer_import_template.csv';
        
        // Create CSV content with headers and sample data
        $headers = [
            'customer_name',
            'customer_email',
            'customer_phone',
            'customer_mobile',
            'customer_fax',
            'contact_name',
            'customer_address',
            'city',
            'state',
            'zip_code',
            'country',
            'billing_address',
            'shipping_address',
            'payment_terms',
        ];
        
        $sampleData = [
            [
                'ABC Company Ltd.',
                'contact@abccompany.com',
                '+1-555-123-4567',
                '+1-555-987-6543',
                '+1-555-123-4568',
                'John Smith',
                '123 Business St',
                'New York',
                'NY',
                '10001',
                'US',
                '123 Business St, New York, NY 10001',
                '456 Delivery Ave, New York, NY 10002',
                'Net 30',
            ],
            [
                'XYZ Corporation',
                'admin@xyzcorp.com',
                '+1-555-234-5678',
                '',
                '',
                'Jane Doe',
                '789 Corporate Blvd',
                'Los Angeles',
                'CA',
                '90210',
                'US',
                '',
                '',
                'Net 15',
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
                        if (empty($rowData['customer_name'])) {
                            continue;
                        }
                        
                        // Create customer
                        $customer = new Customer();
                        $customer->company_id = $companyId;
                        $customer->customer_name = $rowData['customer_name'] ?? '';
                        $customer->customer_email = $rowData['customer_email'] ?? '';
                        $customer->customer_phone = $rowData['customer_phone'] ?? '';
                        $customer->customer_mobile = $rowData['customer_mobile'] ?? '';
                        $customer->customer_fax = $rowData['customer_fax'] ?? '';
                        $customer->contact_name = $rowData['contact_name'] ?? '';
                        $customer->customer_address = $rowData['customer_address'] ?? '';
                        $customer->city = $rowData['city'] ?? '';
                        $customer->state = $rowData['state'] ?? '';
                        $customer->zip_code = $rowData['zip_code'] ?? '';
                        $customer->country = $rowData['country'] ?? 'US';
                        $customer->billing_address = $rowData['billing_address'] ?? '';
                        $customer->shipping_address = $rowData['shipping_address'] ?? '';
                        $customer->payment_terms = $rowData['payment_terms'] ?? 'Net 30';
                        $customer->is_active = true;
                        
                        if ($customer->save()) {
                            $imported++;
                        } else {
                            $errors[] = Yii::t('app', 'Row {row}: {errors}', [
                                'row' => $rowIndex,
                                'errors' => implode(', ', array_map(function($fieldErrors) {
                                    return implode(', ', $fieldErrors);
                                }, $customer->errors))
                            ]);
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