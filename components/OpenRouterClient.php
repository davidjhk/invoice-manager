<?php

namespace app\components;

use Yii;
use yii\base\Component;

/**
 * OpenRouter API Client for AI services
 */
class OpenRouterClient extends Component
{
    public $apiKey;
    public $baseUrl = 'https://openrouter.ai/api/v1';
    public $model = 'anthropic/claude-3.5-sonnet';
    public $maxTokens = 1000;
    public $temperature = 0.7;

    public function init()
    {
        parent::init();
        
        if (!$this->apiKey) {
            $this->apiKey = Yii::$app->params['openRouterApiKey'] ?? null;
        }
        
        // Get AI model from admin settings with fallback
        if (class_exists('\app\models\AdminSettings')) {
            $configuredModel = \app\models\AdminSettings::getAiModel();
            
            // Check if the configured model exists in available models list
            $availableModels = array_keys(Yii::$app->params['openRouterModels'] ?? []);
            
            if (in_array($configuredModel, $availableModels)) {
                $this->model = $configuredModel;
            } else {
                // Fallback to default model if configured model is not available
                $defaultModel = Yii::$app->params['defaultAiModel'] ?? 'anthropic/claude-3.5-sonnet';
                $this->model = $defaultModel;
                
                Yii::warning("Configured AI model '{$configuredModel}' not found in available models. Using default: '{$defaultModel}'", 'ai-helper');
                
                // Update admin settings to use the default model
                if (method_exists('\app\models\AdminSettings', 'setValue')) {
                    \app\models\AdminSettings::setValue('ai_model', $defaultModel);
                }
            }
        }
    }

    /**
     * Generate completion using OpenRouter API
     *
     * @param string $prompt The prompt to send to the AI
     * @param array $options Additional options
     * @return string|null The AI response or null on error
     */
    public function generateCompletion($prompt, $options = [])
    {
        // Check if properly configured
        if (!$this->isConfigured()) {
            Yii::error('OpenRouter API key is not configured', 'ai-helper');
            return null;
        }

        try {
            $model = $options['model'] ?? $this->model;
            $maxTokens = $options['max_tokens'] ?? $this->maxTokens;
            $temperature = $options['temperature'] ?? $this->temperature;

            // Validate model is in available list
            $availableModels = array_keys(Yii::$app->params['openRouterModels'] ?? []);
            if (!in_array($model, $availableModels)) {
                $defaultModel = Yii::$app->params['defaultAiModel'] ?? 'anthropic/claude-3.5-sonnet';
                Yii::warning("Model '{$model}' not available, using fallback: '{$defaultModel}'", 'ai-helper');
                $model = $defaultModel;
            }

            $requestData = [
                'model' => $model,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'max_tokens' => $maxTokens,
                'temperature' => $temperature,
                'stream' => false,
            ];

            Yii::info('OpenRouter API request started', 'ai-helper');
            Yii::info('Request details: ' . json_encode([
                'model' => $model,
                'max_tokens' => $maxTokens,
                'temperature' => $temperature,
                'prompt_length' => strlen($prompt)
            ]), 'ai-helper');

            // Try the primary model first
            $response = $this->makeCurlRequest('/chat/completions', $requestData);
            
            // If primary model fails, try fallback with default model
            if (!$response || !isset($response['choices'][0]['message']['content'])) {
                if ($model !== 'anthropic/claude-3.5-sonnet') {
                    Yii::warning("Primary model '{$model}' failed, trying fallback: anthropic/claude-3.5-sonnet", 'ai-helper');
                    $requestData['model'] = 'anthropic/claude-3.5-sonnet';
                    $response = $this->makeCurlRequest('/chat/completions', $requestData);
                }
            }
            
            // Log the raw response for debugging
            Yii::info('Raw API response: ' . json_encode($response), 'ai-helper');
            
            if ($response && isset($response['choices'][0]['message']['content'])) {
                $content = trim($response['choices'][0]['message']['content']);
                Yii::info('OpenRouter API response received successfully, length: ' . strlen($content), 'ai-helper');
                return $content;
            }

            // More detailed error logging
            if ($response) {
                Yii::error('OpenRouter API Error: Invalid response structure. Response: ' . json_encode($response), 'ai-helper');
                
                // Check for API error messages
                if (isset($response['error'])) {
                    Yii::error('OpenRouter API returned error: ' . json_encode($response['error']), 'ai-helper');
                }
            } else {
                Yii::error('OpenRouter API Error: No response received', 'ai-helper');
            }
            
            return null;

        } catch (\Exception $e) {
            Yii::error('OpenRouter API Exception: ' . $e->getMessage() . "\nTrace: " . $e->getTraceAsString(), 'ai-helper');
            return null;
        }
    }

    /**
     * Make cURL request to OpenRouter API
     *
     * @param string $endpoint API endpoint
     * @param array $data Request data
     * @return array|null Response data or null on error
     */
    private function makeCurlRequest($endpoint, $data = null)
    {
        $url = $this->baseUrl . $endpoint;
        
        Yii::info('Making cURL request to: ' . $url, 'ai-helper');
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->apiKey,
                'Content-Type: application/json'
            ],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);

        if ($data) {
            $jsonData = json_encode($data);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
            Yii::info('Request payload size: ' . strlen($jsonData) . ' bytes', 'ai-helper');
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);

        Yii::info('cURL request completed', 'ai-helper');
        Yii::info('HTTP Code: ' . $httpCode, 'ai-helper');
        Yii::info('Response size: ' . strlen($response ?: '') . ' bytes', 'ai-helper');

        if ($error) {
            Yii::error('cURL Error: ' . $error, 'ai-helper');
            Yii::error('cURL Info: ' . json_encode($info), 'ai-helper');
            return null;
        }

        if ($httpCode !== 200) {
            Yii::error('HTTP Error ' . $httpCode . ': ' . substr($response, 0, 500), 'ai-helper');
            return null;
        }

        $decoded = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            Yii::error('JSON decode error: ' . json_last_error_msg(), 'ai-helper');
            Yii::error('Raw response: ' . substr($response, 0, 500), 'ai-helper');
            return null;
        }

        return $decoded;
    }

    /**
     * Generate invoice description suggestions
     *
     * @param string $productName Product or service name
     * @param string $businessType Optional business type context
     * @return array Array of description suggestions
     */
    public function generateInvoiceDescriptions($productName, $businessType = '')
    {
        $businessContext = $businessType ? " for a {$businessType} business" : '';
        
        $prompt = "Generate 3 professional invoice line item descriptions for: '{$productName}'{$businessContext}.

Requirements:
- Professional business language
- Clear and specific
- 1-2 sentences each
- Format as a simple numbered list
- Use professional business language

Product/Service: {$productName}";

        $response = $this->generateCompletion($prompt, ['max_tokens' => 300]);
        
        if (!$response) {
            return [];
        }

        // Parse the response into an array
        $descriptions = [];
        $lines = explode("\n", $response);
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (preg_match('/^\d+\.\s*(.+)$/', $line, $matches)) {
                $descriptions[] = trim($matches[1]);
            }
        }

        return array_slice($descriptions, 0, 3); // Return max 3 suggestions
    }

    /**
     * Generate customer-specific invoice suggestions
     *
     * @param array $customerData Customer information
     * @param string $productName Product or service name
     * @return array Array of suggestions
     */
    public function generateCustomerSpecificSuggestions($customerData, $productName)
    {
        $customerName = $customerData['name'] ?? 'Customer';
        $customerIndustry = $customerData['industry'] ?? '';
        
        $industryContext = $customerIndustry ? " in the {$customerIndustry} industry" : '';
        
        $prompt = "Create a professional invoice description for '{$productName}' specifically for {$customerName}{$industryContext}.

Requirements:
- Tailored to the customer and industry
- Professional and specific
- Maximum 2 lines

Product/Service: {$productName}
Customer: {$customerName}{$industryContext}";

        $response = $this->generateCompletion($prompt, ['max_tokens' => 200]);
        
        return $response ? [$response] : [];
    }

    /**
     * Generate payment terms suggestions
     *
     * @param string $customerType Type of customer (individual, small business, enterprise, etc.)
     * @param float $amount Invoice amount
     * @return array Array of payment terms suggestions
     */
    public function generatePaymentTerms($customerType = '', $amount = 0)
    {
        $amountContext = $amount > 0 ? " for an amount of $" . number_format($amount, 2) : '';
        $customerContext = $customerType ? " dealing with {$customerType} customers" : '';
        
        $prompt = "Suggest 3 appropriate payment terms{$amountContext}{$customerContext}.

Requirements:
- Provide practical, common payment terms
- Consider the customer type and amount
- Format as simple list items
- Include both timeframe and any conditions

Examples format:
- Net 30 days
- Due upon receipt
- 2/10 Net 30 (2% discount if paid within 10 days)";

        $response = $this->generateCompletion($prompt, ['max_tokens' => 200]);
        
        if (!$response) {
            return ['Net 30', 'Due upon receipt', 'Net 15'];
        }

        // Parse the response
        $terms = [];
        $lines = explode("\n", $response);
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (preg_match('/^-\s*(.+)$/', $line, $matches)) {
                $terms[] = trim($matches[1]);
            }
        }

        return array_slice($terms, 0, 3);
    }

    /**
     * Check if API key is configured and valid
     *
     * @return bool
     */
    public function isConfigured()
    {
        $isConfigured = !empty($this->apiKey) && strlen($this->apiKey) > 10;
        
        if (!$isConfigured) {
            Yii::warning('OpenRouter API key validation failed: ' . 
                (!empty($this->apiKey) ? 'key too short' : 'key missing'), 'ai-helper');
        }
        
        return $isConfigured;
    }

    /**
     * Test API connection
     *
     * @return array Returns array with 'success' and 'message' keys
     */
    public function testConnection()
    {
        try {
            if (!$this->isConfigured()) {
                return [
                    'success' => false,
                    'message' => 'API key is not properly configured'
                ];
            }

            // Try models endpoint first (lightweight test)
            $response = $this->makeCurlRequest('/models');
            
            if ($response && isset($response['data'])) {
                return [
                    'success' => true,
                    'message' => 'API connection successful (models endpoint)',
                    'models_count' => count($response['data'])
                ];
            }

            // Fallback to chat completions test
            $response = $this->generateCompletion('Test', ['max_tokens' => 10]);
            
            if (!empty($response)) {
                return [
                    'success' => true,
                    'message' => 'API connection successful (chat endpoint)',
                    'response' => substr($response, 0, 50)
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'API request failed - no response received'
                ];
            }

        } catch (\Exception $e) {
            Yii::error('OpenRouter test connection failed: ' . $e->getMessage(), 'ai-helper');
            return [
                'success' => false,
                'message' => 'Connection test failed: ' . $e->getMessage()
            ];
        }
    }
}