<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;
use app\components\OpenRouterClient;
use app\models\Customer;
use app\models\Product;

/**
 * AI Helper Controller for Invoice and Estimate assistance
 */
class AiHelperController extends Controller
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
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            $user = Yii::$app->user->identity;
                            if (!$user) {
                                return false;
                            }
                            
                            // Check if user has AI Helper access
                            if (!$user->canUseAiHelper()) {
                                return false;
                            }
                            
                            return true;
                        }
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'generate-descriptions' => ['POST'],
                    'generate-payment-terms' => ['POST'],
                    'suggest-pricing' => ['POST'],
                    'answer-question' => ['POST'],
                    'test' => ['GET', 'POST'],
                    'status' => ['GET', 'POST'],
                ],
            ],
        ];
    }

    /**
     * Generate product/service descriptions for invoice items
     */
    public function actionGenerateDescriptions()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        $productName = Yii::$app->request->post('product_name');
        $customerId = Yii::$app->request->post('customer_id');
        $businessType = Yii::$app->request->post('business_type', '');
        
        if (empty($productName)) {
            return [
                'success' => false,
                'error' => Yii::t('app', 'Product name is required')
            ];
        }

        try {
            $openRouter = new OpenRouterClient();
            
            if (!$openRouter->isConfigured()) {
                Yii::warning('AI Helper configuration check failed', 'ai-helper');
                return [
                    'success' => false,
                    'error' => Yii::t('app', 'AI Helper is not configured. Please check API key settings.'),
                    'debug' => 'API key validation failed'
                ];
            }

            $descriptions = [];
            
            // Generate customer-specific suggestions if customer is selected
            if ($customerId) {
                $customer = Customer::findOne([
                    'id' => $customerId,
                    'user_id' => Yii::$app->user->id
                ]);
                
                if ($customer) {
                    $customerData = [
                        'name' => $customer->customer_name,
                        'industry' => $customer->industry ?? $businessType
                    ];
                    
                    Yii::info('Generating customer-specific suggestions for: ' . $customer->customer_name, 'ai-helper');
                    $customerSuggestions = $openRouter->generateCustomerSpecificSuggestions($customerData, $productName);
                    
                    if (!empty($customerSuggestions)) {
                        $descriptions = array_merge($descriptions, $customerSuggestions);
                        Yii::info('Generated ' . count($customerSuggestions) . ' customer-specific suggestions', 'ai-helper');
                    } else {
                        Yii::warning('Failed to generate customer-specific suggestions', 'ai-helper');
                    }
                }
            }
            
            // Generate general descriptions
            Yii::info('Generating general descriptions for product: ' . $productName, 'ai-helper');
            $generalDescriptions = $openRouter->generateInvoiceDescriptions($productName, $businessType);
            
            if (!empty($generalDescriptions)) {
                $descriptions = array_merge($descriptions, $generalDescriptions);
                Yii::info('Generated ' . count($generalDescriptions) . ' general descriptions', 'ai-helper');
            } else {
                Yii::warning('Failed to generate general descriptions', 'ai-helper');
            }
            
            // Remove duplicates and limit to 3
            $descriptions = array_unique($descriptions);
            $descriptions = array_slice($descriptions, 0, 3);
            
            Yii::info('Final description count: ' . count($descriptions), 'ai-helper');
            
            if (empty($descriptions)) {
                return [
                    'success' => false,
                    'error' => Yii::t('app', 'Unable to generate suggestions. The AI service may be temporarily unavailable.'),
                    'debug' => 'No descriptions generated from API calls'
                ];
            }

            return [
                'success' => true,
                'descriptions' => $descriptions
            ];

        } catch (\Exception $e) {
            Yii::error('AI Helper Error in generateDescriptions: ' . $e->getMessage() . "\nTrace: " . $e->getTraceAsString(), 'ai-helper');
            
            return [
                'success' => false,
                'error' => Yii::t('app', 'AI service error: {message}', ['message' => $e->getMessage()]),
                'debug' => get_class($e) . ': ' . $e->getMessage()
            ];
        }
    }

    /**
     * Generate payment terms suggestions
     */
    public function actionGeneratePaymentTerms()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        $customerType = Yii::$app->request->post('customer_type', '');
        $amount = (float) Yii::$app->request->post('amount', 0);
        $customerId = Yii::$app->request->post('customer_id');
        
        try {
            $openRouter = new OpenRouterClient();
            
            if (!$openRouter->isConfigured()) {
                return [
                    'success' => false,
                    'error' => Yii::t('app', 'AI Helper is not configured')
                ];
            }

            // Get customer type from database if customer is selected
            if ($customerId) {
                $customer = Customer::findOne([
                    'id' => $customerId,
                    'user_id' => Yii::$app->user->id
                ]);
                
                if ($customer) {
                    $customerType = $customer->customer_type ?? $customerType;
                }
            }

            $paymentTerms = $openRouter->generatePaymentTerms($customerType, $amount);
            
            if (empty($paymentTerms)) {
                // Fallback to default terms
                $paymentTerms = ['Net 30', 'Due upon receipt', 'Net 15'];
            }

            return [
                'success' => true,
                'payment_terms' => $paymentTerms
            ];

        } catch (\Exception $e) {
            Yii::error('AI Helper Error: ' . $e->getMessage(), 'ai-helper');
            
            return [
                'success' => false,
                'error' => Yii::t('app', 'AI service temporarily unavailable. Please try again later.'),
                'payment_terms' => ['Net 30', 'Due upon receipt', 'Net 15'] // Fallback
            ];
        }
    }

    /**
     * Suggest pricing based on product/service and market data
     */
    public function actionSuggestPricing()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        $productName = Yii::$app->request->post('product_name');
        $description = Yii::$app->request->post('description', '');
        $quantity = (int) Yii::$app->request->post('quantity', 1);
        $businessType = Yii::$app->request->post('business_type', '');
        
        if (empty($productName)) {
            return [
                'success' => false,
                'error' => Yii::t('app', 'Product name is required')
            ];
        }

        try {
            $openRouter = new OpenRouterClient();
            
            if (!$openRouter->isConfigured()) {
                return [
                    'success' => false,
                    'error' => Yii::t('app', 'AI Helper is not configured')
                ];
            }

            // Check if we have existing pricing data
            $existingProduct = Product::find()
                ->where(['like', 'product_name', $productName])
                ->andWhere(['user_id' => Yii::$app->user->id])
                ->one();

            $existingPrice = $existingProduct ? $existingProduct->selling_price : null;

            $businessContext = $businessType ? " for a {$businessType} business" : '';
            $descriptionContext = $description ? " Description: {$description}." : '';
            $existingPriceContext = $existingPrice ? " Current price: $" . number_format($existingPrice, 2) . "." : '';
            
            $prompt = "Suggest appropriate pricing for: '{$productName}'{$businessContext}.{$descriptionContext}{$existingPriceContext}

Requirements:
- Provide 3 pricing suggestions (low, medium, high)
- Consider market rates and value
- Include brief justification for each price point
- Format as: Price | Justification
- Use realistic pricing based on the product/service type

Quantity: {$quantity}";

            $response = $openRouter->generateCompletion($prompt, ['max_tokens' => 400]);
            
            if (!$response) {
                return [
                    'success' => false,
                    'error' => Yii::t('app', 'Unable to generate pricing suggestions')
                ];
            }

            // Parse pricing suggestions
            $suggestions = [];
            $lines = explode("\n", $response);
            
            foreach ($lines as $line) {
                $line = trim($line);
                if (preg_match('/\$?([\d,]+\.?\d*)\s*[|\-]\s*(.+)/', $line, $matches)) {
                    $price = str_replace(',', '', $matches[1]);
                    $justification = trim($matches[2]);
                    
                    if (is_numeric($price)) {
                        $suggestions[] = [
                            'price' => (float) $price,
                            'justification' => $justification
                        ];
                    }
                }
            }

            if (empty($suggestions)) {
                return [
                    'success' => false,
                    'error' => Yii::t('app', 'Unable to parse pricing suggestions')
                ];
            }

            return [
                'success' => true,
                'pricing_suggestions' => array_slice($suggestions, 0, 3)
            ];

        } catch (\Exception $e) {
            Yii::error('AI Helper Error: ' . $e->getMessage(), 'ai-helper');
            
            return [
                'success' => false,
                'error' => Yii::t('app', 'AI service temporarily unavailable. Please try again later.')
            ];
        }
    }

    /**
     * Answer user questions using AI
     */
    public function actionAnswerQuestion()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        $question = Yii::$app->request->post('question');
        $customerId = Yii::$app->request->post('customer_id');
        $businessType = Yii::$app->request->post('business_type', '');
        
        if (empty($question)) {
            return [
                'success' => false,
                'error' => Yii::t('app', 'Question is required')
            ];
        }

        try {
            $openRouter = new OpenRouterClient();
            
            if (!$openRouter->isConfigured()) {
                return [
                    'success' => false,
                    'error' => Yii::t('app', 'AI Helper is not configured. Please check API key settings.')
                ];
            }

            // Generate answer using the question directly
            $answer = $openRouter->generateCompletion($question, ['max_tokens' => 500]);
            
            if (empty($answer)) {
                return [
                    'success' => false,
                    'error' => Yii::t('app', 'Unable to generate answer. The AI service may be temporarily unavailable.')
                ];
            }

            return [
                'success' => true,
                'answer' => $answer
            ];

        } catch (\Exception $e) {
            Yii::error('AI Helper Error in answerQuestion: ' . $e->getMessage() . "\nTrace: " . $e->getTraceAsString(), 'ai-helper');
            
            return [
                'success' => false,
                'error' => Yii::t('app', 'AI service error: {message}', ['message' => $e->getMessage()])
            ];
        }
    }

    /**
     * Test AI Helper configuration and connection
     */
    public function actionTest()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        try {
            Yii::info('Starting AI Helper test', 'ai-helper');
            
            $openRouter = new OpenRouterClient();
            
            if (!$openRouter->isConfigured()) {
                Yii::warning('AI Helper test failed - not configured', 'ai-helper');
                return [
                    'success' => false,
                    'error' => 'AI Helper is not configured',
                    'debug' => [
                        'api_key_exists' => !empty(Yii::$app->params['openRouterApiKey']),
                        'api_key_length' => empty(Yii::$app->params['openRouterApiKey']) ? 0 : strlen(Yii::$app->params['openRouterApiKey'])
                    ]
                ];
            }

            $testResult = $openRouter->testConnection();
            
            Yii::info('AI Helper test completed: ' . json_encode($testResult), 'ai-helper');
            
            return [
                'success' => $testResult['success'],
                'message' => $testResult['message'],
                'debug' => $testResult
            ];

        } catch (\Exception $e) {
            Yii::error('AI Helper test exception: ' . $e->getMessage(), 'ai-helper');
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'debug' => [
                    'exception_class' => get_class($e),
                    'trace' => $e->getTraceAsString()
                ]
            ];
        }
    }

    /**
     * Check API status and configuration
     */
    public function actionStatus()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        $status = [
            'configured' => false,
            'api_key_exists' => false,
            'api_key_valid' => false,
            'connection_test' => false,
            'error_messages' => []
        ];

        try {
            // Check if API key exists in config
            $apiKey = Yii::$app->params['openRouterApiKey'] ?? null;
            $status['api_key_exists'] = !empty($apiKey);
            
            if (!$status['api_key_exists']) {
                $status['error_messages'][] = 'OpenRouter API key is not set in config/params-local.php';
                return $status;
            }

            // Check API key format
            $status['api_key_valid'] = strlen($apiKey) > 10 && strpos($apiKey, 'sk-or-') === 0;
            
            if (!$status['api_key_valid']) {
                $status['error_messages'][] = 'API key format appears invalid (should start with sk-or-)';
            }

            // Initialize OpenRouter client
            $openRouter = new OpenRouterClient();
            $status['configured'] = $openRouter->isConfigured();
            
            if (!$status['configured']) {
                $status['error_messages'][] = 'OpenRouter client failed configuration check';
                return $status;
            }

            // Test connection
            $testResult = $openRouter->testConnection();
            $status['connection_test'] = $testResult['success'];
            
            if (!$status['connection_test']) {
                $status['error_messages'][] = 'Connection test failed: ' . $testResult['message'];
            }

            return $status;

        } catch (\Exception $e) {
            $status['error_messages'][] = 'Exception: ' . $e->getMessage();
            Yii::error('AI Helper status check failed: ' . $e->getMessage(), 'ai-helper');
            return $status;
        }
    }
}