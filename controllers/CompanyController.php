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
use yii\web\BadRequestHttpException;

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
                    'set-current' => ['GET', 'POST'], // Allow both GET and POST
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function beforeAction($action)
    {
        // Disable CSRF validation for AJAX requests to set-current action
        if ($action->id === 'set-current' && Yii::$app->request->isAjax) {
            $this->enableCsrfValidation = false;
        }
        
        return parent::beforeAction($action);
    }

    /**
     * Company selection page
     *
     * @return string
     */
    public function actionSelect()
    {
        $companies = Company::findForCurrentUser()->all();
        
        if (empty($companies)) {
            // If user has no companies, create a default one
            $company = new Company();
            $company->company_name = Yii::$app->user->identity->getDisplayName() . "'s Company";
            $company->company_email = Yii::$app->user->identity->email;
            $company->sender_email = Yii::$app->user->identity->email;
            $company->sender_name = Yii::$app->user->identity->getDisplayName();
            $company->user_id = Yii::$app->user->id;
            
            if ($company->save()) {
                $companies = [$company];
            }
        }
        
        return $this->render('select', [
            'companies' => $companies,
        ]);
    }

    /**
     * Create a new company
     *
     * @return string|Response
     */
    public function actionCreate()
    {
        // Check if user can create more companies
        $user = Yii::$app->user->identity;
        if (!$user->canCreateMoreCompanies()) {
            Yii::$app->session->setFlash('error', 'You have reached your maximum number of companies (' . $user->max_companies . '). Please upgrade your account or contact support.');
            return $this->redirect(['select']);
        }
        
        $model = new Company();
        $model->user_id = Yii::$app->user->id;
        
        // Set default values explicitly
        $model->tax_rate = 10.00;
        $model->currency = 'USD';
        $model->invoice_prefix = 'INV';
        $model->estimate_prefix = 'EST';
        $model->due_date_days = 30;
        $model->estimate_validity_days = 30;
        $model->is_active = true;
        
        if ($model->load(Yii::$app->request->post())) {
            // Handle logo upload
            $logoFile = UploadedFile::getInstance($model, 'logo_upload');
            if ($logoFile) {
                $uploadPath = Yii::getAlias('@webroot/uploads/logos/');
                
                // Create directory if it doesn't exist
                if (!is_dir($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
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
                // Set this as the current company
                Yii::$app->session->set('current_company_id', $model->id);
                Yii::$app->session->setFlash('success', 'New company created successfully.');
                return $this->redirect(['site/index']);
            } else {
                // Log validation errors for debugging
                Yii::error('Company creation failed: ' . json_encode($model->errors), 'app');
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Set current company in session
     *
     * @param int $id
     * @return Response
     */
    public function actionSetCurrent($id = null)
    {
        // Log request details for debugging
        Yii::info('SetCurrent action called with ID: ' . $id, 'app');
        Yii::info('POST data: ' . json_encode(Yii::$app->request->post()), 'app');
        Yii::info('Is AJAX: ' . (Yii::$app->request->isAjax ? 'Yes' : 'No'), 'app');
        
        try {
            // Get ID from POST if not in URL
            if ($id === null) {
                $id = Yii::$app->request->post('id');
            }
            
            if (!$id) {
                if (Yii::$app->request->isAjax) {
                    Yii::$app->response->format = Response::FORMAT_JSON;
                    return [
                        'success' => false,
                        'message' => 'Company ID is required.',
                        'debug' => [
                            'post_data' => Yii::$app->request->post(),
                            'url_id' => $id,
                        ]
                    ];
                }
                throw new BadRequestHttpException('Company ID is required.');
            }
            
            // Enhanced security: Verify company belongs to current user
            $company = Company::findForCurrentUser()->where(['id' => $id])->one();
            
            if (!$company) {
                // Log unauthorized access attempt
                Yii::warning('Unauthorized access attempt: User ID ' . Yii::$app->user->id . ' attempted to access company ID ' . $id, 'security');
                
                if (Yii::$app->request->isAjax) {
                    Yii::$app->response->format = Response::FORMAT_JSON;
                    return [
                        'success' => false,
                        'message' => 'Access denied. You do not have permission to access this company.',
                        'debug' => [
                            'requested_id' => $id,
                            'user_id' => Yii::$app->user->id,
                            'user_companies' => Company::findForCurrentUser()->select('id, company_name')->asArray()->all()
                        ]
                    ];
                }
                
                // Set flash message for non-AJAX requests
                Yii::$app->session->setFlash('error', 'Access denied. You do not have permission to access this company.');
                
                // Redirect to company selection page instead of throwing exception
                return $this->redirect(['company/select']);
            }
            
            // Additional verification: Double-check user_id matches
            if ($company->user_id !== Yii::$app->user->id) {
                Yii::warning('Security violation: Company user_id mismatch. Expected: ' . Yii::$app->user->id . ', Got: ' . $company->user_id, 'security');
                
                if (Yii::$app->request->isAjax) {
                    Yii::$app->response->format = Response::FORMAT_JSON;
                    return [
                        'success' => false,
                        'message' => 'Access denied. Security violation detected.',
                    ];
                }
                
                Yii::$app->session->setFlash('error', 'Access denied. Security violation detected.');
                return $this->redirect(['company/select']);
            }
            
            Yii::$app->session->set('current_company_id', $company->id);
            
            // Log successful company switch
            Yii::info('User ' . Yii::$app->user->id . ' switched to company ' . $company->id . ' (' . $company->company_name . ')', 'app');
            
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return [
                    'success' => true,
                    'company' => [
                        'id' => $company->id,
                        'name' => $company->company_name,
                    ]
                ];
            }
            
            return $this->redirect(['site/index']);
            
        } catch (\Exception $e) {
            Yii::error('Error in SetCurrent: ' . $e->getMessage() . ' - ' . $e->getTraceAsString(), 'app');
            
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return [
                    'success' => false,
                    'message' => 'An error occurred: ' . $e->getMessage(),
                    'debug' => [
                        'exception_class' => get_class($e),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                    ]
                ];
            }
            throw $e;
        }
    }

    /**
     * Displays company settings.
     *
     * @return string
     */
    public function actionSettings()
    {
        $model = Company::getCurrent();
        
        if (!$model) {
            return $this->redirect(['company/select']);
        }

        $oldLanguage = $model->language; // Store original language before loading POST data
        
        if ($model->load(Yii::$app->request->post())) {
            // Debug: Log POST data
            \Yii::error('POST Data: ' . print_r(Yii::$app->request->post(), true), __METHOD__);
            \Yii::error('Model attributes after load: ' . print_r($model->attributes, true), __METHOD__);
            
            // Handle language change immediately if language was modified
            $newLanguage = $model->language;
            if ($oldLanguage !== $newLanguage && $newLanguage) {
                // Set session language immediately
                Yii::$app->session->set('language', $newLanguage);
                // Set application language for immediate effect
                Yii::$app->language = $newLanguage;
            }
            
            // Handle logo upload
            $logoFile = UploadedFile::getInstance($model, 'logo_upload');
            \Yii::error('Logo file instance: ' . ($logoFile ? 'Found' : 'Not found'), __METHOD__);
            if ($logoFile) {
                \Yii::error('Logo file details: ' . print_r(['name' => $logoFile->name, 'size' => $logoFile->size, 'type' => $logoFile->type, 'extension' => $logoFile->extension], true), __METHOD__);
                $uploadPath = Yii::getAlias('@webroot/uploads/logos/');
                
                // Create directory if it doesn't exist
                if (!is_dir($uploadPath)) {
                    if (!mkdir($uploadPath, 0755, true)) {
                        Yii::$app->session->setFlash('error', 'Failed to create upload directory.');
                        return $this->render('settings', ['model' => $model]);
                    }
                }
                
                // Check if directory is writable
                if (!is_writable($uploadPath)) {
                    Yii::$app->session->setFlash('error', 'Upload directory is not writable.');
                    return $this->render('settings', ['model' => $model]);
                }
                
                // Validate file type
                $allowedTypes = ['png', 'jpg', 'jpeg', 'gif'];
                if (!in_array(strtolower($logoFile->extension), $allowedTypes)) {
                    Yii::$app->session->setFlash('error', 'Invalid file type. Please upload PNG, JPG, JPEG, or GIF files only.');
                    return $this->render('settings', ['model' => $model]);
                }
                
                // Validate file size (2MB limit)
                if ($logoFile->size > 2 * 1024 * 1024) {
                    Yii::$app->session->setFlash('error', 'File size too large. Please upload files smaller than 2MB.');
                    return $this->render('settings', ['model' => $model]);
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
                    
                    // Verify file was saved correctly
                    if (!file_exists($filePath)) {
                        Yii::$app->session->setFlash('error', 'Failed to save uploaded file.');
                        return $this->render('settings', ['model' => $model]);
                    }
                    
                    Yii::$app->session->setFlash('success', 'Logo uploaded successfully.');
                } else {
                    Yii::$app->session->setFlash('error', 'Failed to save uploaded file. Please try again.');
                    return $this->render('settings', ['model' => $model]);
                }
            }
            
            if ($model->save()) {
                $successMessage = Yii::t('app/company', 'Company settings updated successfully.');
                if ($oldLanguage !== $newLanguage && $newLanguage) {
                    $successMessage .= ' ' . Yii::t('app/company', 'Language has been changed to {language}.', [
                        'language' => \app\models\Company::getLanguageOptions()[$newLanguage] ?? $newLanguage
                    ]);
                }
                Yii::$app->session->setFlash('success', $successMessage);
                return $this->redirect(['settings']);
            } else {
                // Debug: Log validation errors
                \Yii::error('Model validation errors: ' . print_r($model->errors, true), __METHOD__);
                Yii::$app->session->setFlash('error', 'Failed to save settings: ' . implode(', ', $model->getFirstErrors()));
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
        
        $model = Company::getCurrent();
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
     * Get current company info via AJAX
     *
     * @return Response
     */
    public function actionGetCurrent()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        $companyId = Yii::$app->session->get('current_company_id');
        if (!$companyId) {
            $company = Company::findForCurrentUser()->one();
            if ($company) {
                Yii::$app->session->set('current_company_id', $company->id);
                $companyId = $company->id;
            }
        }
        
        if ($companyId) {
            $company = Company::findForCurrentUser()->where(['id' => $companyId])->one();
            if ($company) {
                return [
                    'success' => true,
                    'company' => [
                        'id' => $company->id,
                        'name' => $company->company_name,
                        'email' => $company->company_email,
                        'address' => $company->company_address,
                        'phone' => $company->company_phone,
                        'currency' => $company->currency,
                        'currency_symbol' => $company->getCurrencySymbol(),
                        'tax_rate' => $company->tax_rate,
                        'invoice_prefix' => $company->invoice_prefix,
                        'estimate_prefix' => $company->estimate_prefix,
                        'due_date_days' => $company->due_date_days,
                        'estimate_validity_days' => $company->estimate_validity_days,
                        'logo_url' => $company->getLogoUrl(),
                    ]
                ];
            }
        }
        
        return [
            'success' => false,
            'message' => 'No company selected'
        ];
    }

    /**
     * Get user's companies list
     *
     * @return Response
     */
    public function actionGetList()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        $companies = Company::findForCurrentUser()->all();
        
        return [
            'success' => true,
            'companies' => array_map(function($company) {
                return [
                    'id' => $company->id,
                    'name' => $company->company_name,
                    'email' => $company->company_email,
                    'is_current' => $company->id == Yii::$app->session->get('current_company_id'),
                ];
            }, $companies)
        ];
    }

    /**
     * Get company data as JSON
     *
     * @return Response
     */
    public function actionGetData()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        $companyId = Yii::$app->session->get('current_company_id');
        $model = Company::findForCurrentUser()->where(['id' => $companyId])->one();
        
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
        
        $model = Company::getCurrent();
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
        
        $model = Company::getCurrent();
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
        
        $model = Company::getCurrent();
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
        
        $model = Company::getCurrent();
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
        $model = Company::getCurrent();
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
        
        $model = Company::getCurrent();
        if (!$model) {
            return [
                'success' => false,
                'message' => 'Company not found.',
            ];
        }

        \Yii::error('Delete logo attempt for company: ' . $model->id . ', has logo: ' . ($model->hasLogo() ? 'Yes' : 'No') . ', logo_path: ' . $model->logo_path, __METHOD__);

        if ($model->deleteLogo() && $model->save()) {
            \Yii::error('Logo deleted successfully for company: ' . $model->id, __METHOD__);
            return [
                'success' => true,
                'message' => 'Logo deleted successfully.',
            ];
        }

        \Yii::error('Failed to delete logo for company: ' . $model->id . ', errors: ' . print_r($model->errors, true), __METHOD__);
        return [
            'success' => false,
            'message' => 'Failed to delete logo.',
        ];
    }
}