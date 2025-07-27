<?php
/**
 * Simple OpenRouter API test script
 * Run this to test if your OpenRouter API key is working
 */

// Include Yii configuration to get API key
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/vendor/yiisoft/yii2/Yii.php';

$config = require __DIR__ . '/config/web.php';
(new yii\web\Application($config));

$apiKey = Yii::$app->params['openRouterApiKey'] ?? null;

if (empty($apiKey)) {
    echo "❌ OpenRouter API key not found in config/params-local.php\n";
    exit(1);
}

echo "🔑 API Key found: " . substr($apiKey, 0, 10) . "...\n";

// Test 1: Check API key format
if (strpos($apiKey, 'sk-or-') !== 0) {
    echo "⚠️  Warning: API key should start with 'sk-or-'\n";
}

// Test 2: Test models endpoint (lightweight)
echo "\n📋 Testing models endpoint...\n";
$modelsUrl = 'https://openrouter.ai/api/v1/models';

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $modelsUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $apiKey,
        'Content-Type: application/json',
    ],
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "❌ cURL Error: $error\n";
    exit(1);
}

echo "📡 HTTP Code: $httpCode\n";

if ($httpCode === 200) {
    $data = json_decode($response, true);
    if (isset($data['data'])) {
        echo "✅ Models endpoint working! Found " . count($data['data']) . " models\n";
        
        // Check if our target models are available
        $targetModels = [
            'anthropic/claude-3.5-sonnet',
            'anthropic/claude-3-haiku',
            'openai/gpt-4o-mini'
        ];
        
        $availableModels = array_column($data['data'], 'id');
        
        foreach ($targetModels as $model) {
            if (in_array($model, $availableModels)) {
                echo "✅ $model is available\n";
            } else {
                echo "❌ $model is NOT available\n";
            }
        }
    } else {
        echo "❌ Invalid response structure\n";
        echo "Response: " . substr($response, 0, 200) . "...\n";
    }
} else {
    echo "❌ HTTP Error $httpCode\n";
    echo "Response: " . substr($response, 0, 200) . "...\n";
    
    if ($httpCode === 401) {
        echo "💡 This usually means your API key is invalid or expired\n";
    } elseif ($httpCode === 429) {
        echo "💡 Rate limit exceeded - try again later\n";
    } elseif ($httpCode === 402) {
        echo "💡 Payment required - check your OpenRouter account balance\n";
    }
    exit(1);
}

// Test 3: Simple chat completion
echo "\n💬 Testing chat completion with Claude 3.5 Sonnet...\n";
$chatUrl = 'https://openrouter.ai/api/v1/chat/completions';

$requestData = [
    'model' => 'anthropic/claude-3.5-sonnet',
    'messages' => [
        [
            'role' => 'user',
            'content' => 'Say "Hello, this is a test" in a professional tone.'
        ]
    ],
    'max_tokens' => 50,
    'temperature' => 0.3
];

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $chatUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($requestData),
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $apiKey,
        'Content-Type: application/json',
        'User-Agent: Invoice Manager Test/1.0',
        'HTTP-Referer: https://localhost',
        'X-Title: Invoice Manager Test'
    ],
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "❌ cURL Error: $error\n";
    exit(1);
}

echo "📡 HTTP Code: $httpCode\n";

if ($httpCode === 200) {
    $data = json_decode($response, true);
    if (isset($data['choices'][0]['message']['content'])) {
        echo "✅ Chat completion working!\n";
        echo "🤖 Response: " . trim($data['choices'][0]['message']['content']) . "\n";
        echo "\n🎉 All tests passed! OpenRouter API is working correctly.\n";
    } else {
        echo "❌ Invalid chat response structure\n";
        echo "Response: " . substr($response, 0, 300) . "...\n";
    }
} else {
    echo "❌ Chat completion failed with HTTP $httpCode\n";
    echo "Response: " . substr($response, 0, 300) . "...\n";
    
    if ($httpCode === 401) {
        echo "💡 API key authentication failed\n";
    } elseif ($httpCode === 400) {
        echo "💡 Bad request - check the request format\n";
    } elseif ($httpCode === 402) {
        echo "💡 Payment required - check your OpenRouter account balance\n";
    } elseif ($httpCode === 429) {
        echo "💡 Rate limit exceeded\n";
    } elseif ($httpCode === 503) {
        echo "💡 Service unavailable - OpenRouter may be experiencing issues\n";
    }
}