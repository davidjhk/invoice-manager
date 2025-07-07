<?php

namespace app\controllers;

use Yii;
use app\models\Company;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\Response;
use yii\web\UploadedFile;

/**
 * CompanyController implements the CRUD actions for Company model.
 */
class CompanyController extends Controller
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
     * Displays company settings.
     *
     * @return string
     */
    public function actionSettings()
    {
        $model = Company::getDefault();
        
        if (!$model) {
            // Create default company if none exists
            $model = new Company();
            $model->company_name = 'Company Name';
            $model->company_address = 'Company Address';
            $model->company_phone = 'Company Phone';
            $model->company_email = 'example@example.com';
            $model->sender_email = 'example@example.com';
            $model->tax_rate = 10.00;
            $model->currency = 'USD';
            $model->invoice_prefix = 'INV';
            $model->due_date_days = 30;
            $model->is_active = true;
            $model->save();
        }

        if ($model->load(Yii::$app->request->post())) {
            // Handle logo upload
            $logoFile = UploadedFile::getInstance($model, 'logo_upload');
            if ($logoFile) {
                $uploadPath = Yii::getAlias('@webroot/uploads/logos/');
                
                // Create directory if it doesn't exist
                if (!is_dir($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }
                
                // Delete old logo if exists
                if ($model->hasLogo()) {
                    $model->deleteLogo();
                }
                
                // Generate unique filename
                $filename = uniqid() . '.' . $logoFile->extension;
                $filePath = $uploadPath . $filename;
                
                if ($logoFile->saveAs($filePath)) {
                    $model->logo_path = '/uploads/logos/' . $filename;
                    $model->logo_filename = $logoFile->name;
                }
            }
            
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Company settings updated successfully.');
                return $this->redirect(['settings']);
            }
        }

        return $this->render('settings', [
            'model' => $model,
        ]);
    }

    /**
     * Test SMTP2GO connection
     *
     * @return Response
     */
    public function actionTestEmail()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        $model = Company::getDefault();
        if (!$model || empty($model->smtp2go_api_key)) {
            return [
                'success' => false,
                'message' => 'SMTP2GO API key not configured.',
            ];
        }

        $testEmail = Yii::$app->request->post('email');
        if (empty($testEmail)) {
            return [
                'success' => false,
                'message' => 'Test email address is required.',
            ];
        }

        try {
            // Test SMTP2GO API connection
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://api.smtp2go.com/v3/email/send');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'X-Smtp2go-Api-Key: ' . $model->smtp2go_api_key,
            ]);
            
            $data = [
                'to' => [$testEmail],
                'sender' => $model->sender_email,
                'subject' => 'SMTP2GO Test Email',
                'text_body' => 'This is a test email to verify SMTP2GO configuration.',
                'html_body' => '<p>This is a test email to verify SMTP2GO configuration.</p>',
            ];

            // Add sender name if configured
            if (!empty($model->sender_name)) {
                $data['sender_name'] = $model->sender_name;
            }

            // Add BCC if configured
            if (!empty($model->bcc_email)) {
                $data['bcc'] = [$model->bcc_email];
            }
            
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode === 200) {
                $responseData = json_decode($response, true);
                if (isset($responseData['data']['succeeded']) && $responseData['data']['succeeded'] > 0) {
                    return [
                        'success' => true,
                        'message' => 'Test email sent successfully.',
                    ];
                }
            }
            
            return [
                'success' => false,
                'message' => 'Failed to send test email. Please check your SMTP2GO configuration.',
                'details' => $response,
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error testing email: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get company data as JSON
     *
     * @return Response
     */
    public function actionGetData()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        $model = Company::getDefault();
        if (!$model) {
            return ['error' => 'Company not found'];
        }
        
        return [
            'id' => $model->id,
            'company_name' => $model->company_name,
            'company_address' => $model->company_address,
            'company_phone' => $model->company_phone,
            'company_email' => $model->company_email,
            'tax_rate' => $model->tax_rate,
            'currency' => $model->currency,
            'currency_symbol' => $model->getCurrencySymbol(),
            'invoice_prefix' => $model->invoice_prefix,
            'due_date_days' => $model->due_date_days,
        ];
    }

    /**
     * Get next invoice number
     *
     * @return Response
     */
    public function actionGetNextInvoiceNumber()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        $model = Company::getDefault();
        if (!$model) {
            return ['error' => 'Company not found'];
        }
        
        return [
            'invoice_number' => $model->generateInvoiceNumber(),
        ];
    }

    /**
     * Update company settings via AJAX
     *
     * @return Response
     */
    public function actionUpdateSettings()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        $model = Company::getDefault();
        if (!$model) {
            return [
                'success' => false,
                'message' => 'Company not found.',
            ];
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return [
                'success' => true,
                'message' => 'Company settings updated successfully.',
                'data' => [
                    'company_name' => $model->company_name,
                    'currency_symbol' => $model->getCurrencySymbol(),
                    'tax_rate' => $model->tax_rate,
                ],
            ];
        }

        return [
            'success' => false,
            'message' => 'Failed to update company settings.',
            'errors' => $model->errors,
        ];
    }

    /**
     * Get company statistics
     *
     * @return Response
     */
    public function actionStatistics()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        $model = Company::getDefault();
        if (!$model) {
            return ['error' => 'Company not found'];
        }

        // Get invoice statistics
        $totalInvoices = $model->getInvoices()->count();
        $draftInvoices = $model->getInvoices()->where(['status' => 'draft'])->count();
        $sentInvoices = $model->getInvoices()->where(['status' => 'sent'])->count();
        $paidInvoices = $model->getInvoices()->where(['status' => 'paid'])->count();
        
        $totalAmount = $model->getInvoices()->sum('total_amount') ?: 0;
        $paidAmount = $model->getInvoices()->where(['status' => 'paid'])->sum('total_amount') ?: 0;
        $pendingAmount = $totalAmount - $paidAmount;
        
        // Get customer statistics
        $totalCustomers = $model->getCustomers()->count();
        $activeCustomers = $model->getCustomers()->where(['is_active' => true])->count();
        
        // Get overdue invoices
        $overdueInvoices = $model->getInvoices()
            ->where(['!=', 'status', 'paid'])
            ->andWhere(['!=', 'status', 'cancelled'])
            ->andWhere(['<', 'due_date', date('Y-m-d')])
            ->count();
        
        return [
            'invoices' => [
                'total' => $totalInvoices,
                'draft' => $draftInvoices,
                'sent' => $sentInvoices,
                'paid' => $paidInvoices,
                'overdue' => $overdueInvoices,
            ],
            'amounts' => [
                'total' => number_format($totalAmount, 2),
                'paid' => number_format($paidAmount, 2),
                'pending' => number_format($pendingAmount, 2),
                'currency_symbol' => $model->getCurrencySymbol(),
            ],
            'customers' => [
                'total' => $totalCustomers,
                'active' => $activeCustomers,
                'inactive' => $totalCustomers - $activeCustomers,
            ],
        ];
    }

    /**
     * Reset company to default settings
     *
     * @return Response
     */
    public function actionResetToDefault()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        $model = Company::getDefault();
        if (!$model) {
            return [
                'success' => false,
                'message' => 'Company not found.',
            ];
        }

        // Reset to default values
        $model->company_name = 'Company Name';
        $model->company_address = 'Company Address';
        $model->company_phone = 'Company Phone';
        $model->company_email = 'example@example.com';
        $model->sender_email = 'example@example.com';
        $model->tax_rate = 10.00;
        $model->currency = 'USD';
        $model->invoice_prefix = 'INV';
        $model->due_date_days = 30;

        if ($model->save()) {
            return [
                'success' => true,
                'message' => 'Company settings reset to default values.',
            ];
        }

        return [
            'success' => false,
            'message' => 'Failed to reset company settings.',
            'errors' => $model->errors,
        ];
    }

    /**
     * Backup company data
     *
     * @return Response
     */
    public function actionBackup()
    {
        $model = Company::getDefault();
        if (!$model) {
            throw new NotFoundHttpException('Company not found.');
        }

        $data = [
            'company' => $model->toArray(),
            'customers' => $model->getCustomers()->where(['is_active' => true])->asArray()->all(),
            'invoices' => $model->getInvoices()->with('invoiceItems')->asArray()->all(),
            'exported_at' => date('Y-m-d H:i:s'),
        ];

        $filename = 'company_backup_' . date('Y-m-d_H-i-s') . '.json';
        
        Yii::$app->response->format = Response::FORMAT_RAW;
        Yii::$app->response->headers->add('Content-Type', 'application/json');
        Yii::$app->response->headers->add('Content-Disposition', 'attachment; filename="' . $filename . '"');
        
        return json_encode($data, JSON_PRETTY_PRINT);
    }

    /**
     * Delete company logo
     *
     * @return Response
     */
    public function actionDeleteLogo()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        $model = Company::getDefault();
        if (!$model) {
            return [
                'success' => false,
                'message' => 'Company not found.',
            ];
        }

        if ($model->deleteLogo() && $model->save()) {
            return [
                'success' => true,
                'message' => 'Logo deleted successfully.',
            ];
        }

        return [
            'success' => false,
            'message' => 'Failed to delete logo.',
        ];
    }
}