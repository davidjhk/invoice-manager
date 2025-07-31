<?php

/**
 * 공용 설정 파일.
 * 실제 설정 값은 params-local.php에서 불러옵니다.
 */

// Default parameters
$defaultParams = [
    'adminEmail' => 'admin@example.com',
    'senderEmail' => 'noreply@example.com',
    'senderName' => 'Invoice Manager',
    'user.passwordResetTokenExpire' => 3600,
    'user.passwordMinLength' => 8,
    
    // Free Plan settings
    'freeUserMonthlyLimit' => 10, // 10 invoices/estimates per month for free users
    'freeUserStorageLimit' => 100, // 100MB storage for free users
    'freeUserMaxCompanies' => 1, // 1 company for free users
    
    // AI Helper settings
    'openRouterApiKey' => null, // Set in params-local.php for production
    
    // Available OpenRouter AI models
    'openRouterModels' => [
        'anthropic/claude-3.5-sonnet' => [
            'name' => 'Claude 3.5 Sonnet',
            'description' => 'Most intelligent model with excellent reasoning capabilities',
            'provider' => 'Anthropic',
            'pricing' => 'Premium'
        ],
        'anthropic/claude-3-haiku' => [
            'name' => 'Claude 3 Haiku',
            'description' => 'Fast and efficient model for quick responses',
            'provider' => 'Anthropic',
            'pricing' => 'Budget'
        ],
        'openai/gpt-4o' => [
            'name' => 'GPT-4 Omni',
            'description' => 'Latest GPT-4 model with multimodal capabilities',
            'provider' => 'OpenAI',
            'pricing' => 'Premium'
        ],
        'openai/gpt-4o-mini' => [
            'name' => 'GPT-4 Omni Mini',
            'description' => 'Smaller, faster version of GPT-4 Omni',
            'provider' => 'OpenAI',
            'pricing' => 'Standard'
        ],
		'openai/o3-mini' => [
			'name' => 'O3 Mini',
			'description' => 'Optimized for speed and efficiency',
			'provider' => 'OpenAI',
			'pricing' => 'Budget'
		],
        'openai/gpt-3.5-turbo' => [
            'name' => 'GPT-3.5 Turbo',
            'description' => 'Cost-effective model for general tasks',
            'provider' => 'OpenAI',
            'pricing' => 'Budget'
        ],
        'google/gemini-pro' => [
            'name' => 'Gemini Pro',
            'description' => 'Google\'s advanced AI model for complex tasks',
            'provider' => 'Google',
            'pricing' => 'Standard'
        ],
        'meta-llama/llama-3.1-70b-instruct' => [
            'name' => 'Llama 3.1 70B',
            'description' => 'Open-source model with strong performance',
            'provider' => 'Meta',
            'pricing' => 'Standard'
		],
		'google/gemma-3n-e2b-it:free' => [
			'name' => 'Gemma 3N E2B',
			'description' => 'Google\'s advanced AI model for complex tasks',
			'provider' => 'Google',
			'pricing' => 'Budget'
		]
    ],
    
    // Default AI model
    'defaultAiModel' => 'anthropic/claude-3.5-sonnet'
];

$localParams = __DIR__ . '/params-local.php';

if (file_exists($localParams)) {
    $localParamsArray = require $localParams;
    return array_merge($defaultParams, $localParamsArray);
}

return $defaultParams;