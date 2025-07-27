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
                    'debug-question' => ['GET', 'POST'],
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
     * Extract prices from work scope description and sum them up
     */
    private function extractPricesFromDescription($description)
    {
        if (empty($description)) {
            return null;
        }
        
        Yii::info('Extracting prices from description: ' . substr($description, 0, 200) . '...', 'ai-helper');
        
        // Regular expressions to match various price formats
        $patterns = [
            '/\$(\d{1,3}(?:,\d{3})*(?:\.\d{2})?)/i', // $1,000.00, $500, $10,000
            '/(\d{1,3}(?:,\d{3})*(?:\.\d{2})?)\s*(?:USD|dollars?|usd)/i', // 1000 USD, 500 dollars
            '/(?:USD|dollars?)\s*(\d{1,3}(?:,\d{3})*(?:\.\d{2})?)/i', // USD 1000, dollar 500
            '/(\d{1,3}(?:,\d{3})*(?:\.\d{2})?)\s*(?:per hour|\/hour|hourly)/i', // 100 per hour, 75/hour
        ];
        
        $totalPrice = 0;
        $foundPrices = [];
        
        foreach ($patterns as $pattern) {
            if (preg_match_all($pattern, $description, $matches)) {
                foreach ($matches[1] as $priceString) {
                    // Remove commas and convert to float
                    $price = (float) str_replace(',', '', $priceString);
                    if ($price > 0 && $price <= 50000) { // Reasonable price range check
                        $foundPrices[] = $price;
                        $totalPrice += $price;
                    }
                }
            }
        }
        
        if (!empty($foundPrices)) {
            Yii::info('Found prices in description: ' . implode(', ', $foundPrices) . ' = Total: $' . $totalPrice, 'ai-helper');
            
            // Apply reasonable caps
            if ($totalPrice > 50000) {
                Yii::warning("Extracted total price too high: $totalPrice, capping at 50000", 'ai-helper');
                $totalPrice = 50000;
            }
            
            return round($totalPrice, 2);
        }
        
        Yii::info('No prices found in description', 'ai-helper');
        return null;
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
        $responseLanguage = Yii::$app->request->post('response_language', 'en');
        
        // Log the incoming request for debugging
        Yii::info('AI Helper answer-question request: ' . json_encode([
            'question_length' => strlen($question),
            'customer_id' => $customerId,
            'business_type' => $businessType,
            'response_language' => $responseLanguage
        ]), 'ai-helper');
        
        if (empty($question)) {
            return [
                'success' => false,
                'error' => Yii::t('app', 'Question is required')
            ];
        }

        try {
            $openRouter = new OpenRouterClient();
            
            if (!$openRouter->isConfigured()) {
                Yii::warning('AI Helper not configured during answer-question', 'ai-helper');
                return [
                    'success' => false,
                    'error' => Yii::t('app', 'AI Helper is not configured. Please check API key settings.')
                ];
            }

            // Log before API call
            Yii::info('Calling OpenRouter API with question length: ' . strlen($question), 'ai-helper');
            
            // Language mapping for responses
            $languageNames = [
                'en' => 'English',
                'ko' => 'Korean', 
                'es' => 'Spanish',
                'zh-cn' => 'Chinese Simplified',
                'zh-tw' => 'Chinese Traditional'
            ];
            
            $selectedLanguage = $languageNames[$responseLanguage] ?? 'English';
            $businessContext = $businessType ? " for a {$businessType} business" : '';
            
            // Create comprehensive work scope prompt
            $workScopePrompt = "Based on the service/product keywords '{$question}'{$businessContext}, generate a comprehensive and professional work scope description suitable for invoice documentation.

REQUIREMENTS:
- Write in {$selectedLanguage} language
- Create detailed, professional work scope using industry-specific terminology
- Structure as bullet points with line breaks
- Include specific deliverables, methodologies, and technical processes
- Use appropriate professional jargon and technical terms for the field
- Make it comprehensive enough to justify professional pricing
- Minimum 4-6 detailed bullet points
- Each bullet point should be substantive (2-3 lines when possible)

CONTENT STRUCTURE WITH PRICING:
• Discovery & Analysis Phase: Include research, requirement gathering, stakeholder interviews (with estimated costs)
• Strategic Planning & Architecture: Cover system design, user experience planning, technical specifications (with time estimates and rates)
• Implementation & Development: Detail technical execution, coding standards, quality assurance processes (with development costs)
• Testing & Quality Assurance: Comprehensive testing methodologies, performance optimization (with testing costs)
• Deployment & Integration: Go-live procedures, system integration, data migration (with deployment costs)
• Documentation & Training: Technical documentation, user manuals, training materials (with documentation costs)
• Post-Launch Support: Maintenance protocols, ongoing optimization, performance monitoring (with support costs)

PRICING INCLUSION REQUIREMENTS:
- Include specific USD pricing for major phases/deliverables
- Use format like \"Phase X: \\$2,500\" or \"Development: \\$5,000\" 
- Break down costs for different components when applicable
- Ensure pricing is realistic and reflects market rates
- Total pricing should align with project complexity

INDUSTRY-SPECIFIC TERMINOLOGY EXAMPLES:
- Web Development: Frontend/backend architecture, responsive design, API integration, database optimization
- Marketing: Brand positioning, target audience segmentation, conversion rate optimization, analytics implementation
- Design: Information architecture, user interface design, brand identity systems, design system development
- Consulting: Strategic advisory, business process optimization, stakeholder engagement, change management
- Content: Content strategy development, editorial calendar, SEO optimization, content distribution channels

OUTPUT FORMAT:
• [Detailed professional description with technical terms and specific pricing: \\$2,500]
• [Specific deliverables and methodologies with costs: \\$3,000]
• [Quality assurance and testing procedures with pricing: \\$1,500]
• [Documentation and handover processes with costs: \\$1,000]

Service/Product: {$question}

Generate comprehensive work scope in {$selectedLanguage} with professional terminology, detailed bullet points, and SPECIFIC PRICING for each major component. Include dollar amounts like \\$2,500, \\$5,000, etc. for different phases/deliverables.";

            $answer = $openRouter->generateCompletion($workScopePrompt, [
                'max_tokens' => 1200,
                'temperature' => 0.7
            ]);
            
            // Generate pricing recommendation separately
            $pricingRecommendation = null;
            try {
                Yii::info('Attempting to generate pricing recommendation for: ' . $question, 'ai-helper');
                
                // First, try to extract prices from the generated work scope description
                $extractedPrice = $this->extractPricesFromDescription($answer);
                
                if ($extractedPrice !== null && $extractedPrice > 0) {
                    Yii::info('Extracted price from description: $' . $extractedPrice, 'ai-helper');
                    $pricingRecommendation = $extractedPrice;
                } else {
                    // If no prices found in description, use AI-based pricing
                    $pricingRecommendation = $this->generatePricingRecommendation($question, $businessType, $responseLanguage, $openRouter);
                }
                
                if ($pricingRecommendation !== null) {
                    Yii::info('Final pricing recommendation: $' . $pricingRecommendation, 'ai-helper');
                } else {
                    Yii::warning('Pricing recommendation returned null', 'ai-helper');
                }
            } catch (\Exception $e) {
                Yii::warning('Failed to generate pricing recommendation: ' . $e->getMessage(), 'ai-helper');
            }
            
            // Log the API response
            Yii::info('OpenRouter API response received, answer length: ' . strlen($answer ?: ''), 'ai-helper');
            
            if (empty($answer)) {
                Yii::warning('OpenRouter returned empty answer', 'ai-helper');
                return [
                    'success' => false,
                    'error' => Yii::t('app', 'Unable to generate answer. The AI service may be temporarily unavailable.'),
                    'debug' => [
                        'api_configured' => $openRouter->isConfigured(),
                        'question_length' => strlen($question),
                        'response_empty' => true
                    ]
                ];
            }

            Yii::info('AI Helper answer-question completed successfully', 'ai-helper');
            $result = [
                'success' => true,
                'answer' => $answer
            ];
            
            // Add pricing recommendation if available
            if ($pricingRecommendation !== null) {
                $result['recommended_price'] = $pricingRecommendation;
            }
            
            return $result;

        } catch (\Exception $e) {
            Yii::error('AI Helper Error in answerQuestion: ' . $e->getMessage() . "\nTrace: " . $e->getTraceAsString(), 'ai-helper');
            
            return [
                'success' => false,
                'error' => Yii::t('app', 'AI service error: {message}', ['message' => $e->getMessage()]),
                'debug' => [
                    'exception_class' => get_class($e),
                    'exception_message' => $e->getMessage(),
                    'question_length' => strlen($question)
                ]
            ];
        }
    }

    /**
     * Generate pricing recommendation for a service/product
     */
    private function generatePricingRecommendation($question, $businessType = '', $responseLanguage = 'en', $openRouter = null)
    {
        if (!$openRouter) {
            $openRouter = new OpenRouterClient();
        }
        
        // Language mapping
        $languageNames = [
            'en' => 'English',
            'ko' => 'Korean', 
            'es' => 'Spanish',
            'zh-cn' => 'Chinese Simplified',
            'zh-tw' => 'Chinese Traditional'
        ];
        
        $businessContext = $businessType ? " for a {$businessType} business" : '';
        
        $pricingPrompt = "Based on the service/product '{$question}'{$businessContext}, calculate a reasonable market price by breaking down individual work scopes.

PRICING METHODOLOGY:
1. Identify individual work scopes/tasks within the service
2. Estimate time/effort for each scope
3. Apply appropriate hourly rates or fixed pricing
4. Sum up all scope prices for total recommendation

CRITICAL REQUIREMENTS:
- Provide ONLY a single number (the total price in USD)
- ALWAYS use USD currency as the base reference
- No currency symbols, no explanations, just the number
- Be REALISTIC and CONSERVATIVE with pricing
- Consider scope breakdown and effort estimation

PRICING GUIDELINES BY SCOPE TYPE:
- Planning/Strategy: $75-$150/hour (2-8 hours typical)
- Design work: $50-$125/hour (5-20 hours typical)
- Development: $75-$175/hour (10-100 hours typical)
- Content creation: $25-$75/hour (2-10 hours typical)
- Testing/QA: $50-$100/hour (2-15 hours typical)
- Consultation: $100-$200/hour (1-5 hours typical)
- Project management: $75-$125/hour (5-20% of total work)

TOTAL PRICE LIMITS:
- Simple services: $100-$2,000
- Medium projects: $2,000-$15,000  
- Complex projects: $15,000-$50,000
- NEVER exceed $50,000 for any recommendation
- If calculation exceeds $50,000, cap at $50,000

Service/Product: {$question}

Calculate total price by summing individual scope estimates. Respond with only the final numeric USD value.";

        Yii::info('Sending pricing prompt for: ' . $question, 'ai-helper');
        
        $priceResponse = $openRouter->generateCompletion($pricingPrompt, [
            'max_tokens' => 100,
            'temperature' => 0.3
        ]);
        
        Yii::info('Pricing API response: ' . ($priceResponse ?: 'null/empty'), 'ai-helper');
        
        if (!$priceResponse) {
            Yii::warning('Empty pricing response from OpenRouter', 'ai-helper');
            return null;
        }
        
        // Extract numeric value from response
        $originalResponse = $priceResponse;
        $priceResponse = trim($priceResponse);
        $priceResponse = preg_replace('/[^\d\.]/', '', $priceResponse); // Remove non-numeric characters except decimal point
        
        Yii::info('Price extraction - Original: "' . $originalResponse . '", Cleaned: "' . $priceResponse . '"', 'ai-helper');
        
        if (is_numeric($priceResponse) && $priceResponse > 0) {
            $price = (float) $priceResponse;
            Yii::info('Parsed price: ' . $price, 'ai-helper');
            
            // Apply strict price validation and caps
            if ($price > 50000) {
                Yii::warning("Price recommendation too high: $price, capping at 50000", 'ai-helper');
                $price = 50000;
            }
            
            // Additional sanity checks
            if ($price < 1) {
                Yii::warning("Price recommendation too low: $price, setting minimum to 25", 'ai-helper');
                $price = 25;
            }
            
            // Round to 2 decimal places
            $finalPrice = round($price, 2);
            Yii::info('Final price recommendation: $' . $finalPrice, 'ai-helper');
            return $finalPrice;
        }
        
        Yii::warning('Could not parse numeric price from response: "' . $priceResponse . '"', 'ai-helper');
        return null;
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
     * Debug question endpoint for testing
     */
    public function actionDebugQuestion()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        try {
            $openRouter = new OpenRouterClient();
            
            if (!$openRouter->isConfigured()) {
                return [
                    'success' => false,
                    'error' => 'AI Helper is not configured'
                ];
            }

            // Simple test question
            $testQuestion = "website development";
            
            Yii::info('Debug test starting with question: ' . $testQuestion, 'ai-helper');
            
            // Test connection first
            $connectionTest = $openRouter->testConnection();
            
            $answer = $openRouter->generateCompletion($testQuestion, [
                'max_tokens' => 100,
                'temperature' => 0.7
            ]);
            
            return [
                'success' => !empty($answer),
                'question' => $testQuestion,
                'answer' => $answer,
                'answer_length' => strlen($answer ?: ''),
                'configured' => $openRouter->isConfigured(),
                'model' => $openRouter->model,
                'connection_test' => $connectionTest,
                'api_key_prefix' => substr($openRouter->apiKey ?: '', 0, 10) . '...',
                'available_models' => array_keys(Yii::$app->params['openRouterModels'] ?? [])
            ];

        } catch (\Exception $e) {
            Yii::error('Debug test failed: ' . $e->getMessage(), 'ai-helper');
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'exception_class' => get_class($e)
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