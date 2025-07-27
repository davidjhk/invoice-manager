<?php
// 간단한 cURL 테스트 스크립트
echo "Testing cURL functionality...\n\n";

// 기본 cURL 정보
echo "cURL version: " . curl_version()['version'] . "\n";
echo "SSL version: " . curl_version()['ssl_version'] . "\n\n";

// OpenRouter API 테스트
$apiKey = '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';

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => 'https://openrouter.ai/api/v1/models',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $apiKey,
        'Content-Type: application/json',
        'User-Agent: Invoice Manager Test'
    ],
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_VERBOSE => true
]);

echo "Testing OpenRouter API connection...\n";
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);

echo "HTTP Code: " . $httpCode . "\n";

if ($error) {
    echo "cURL Error: " . $error . "\n";
} else {
    echo "Response received: " . strlen($response) . " bytes\n";
    if ($httpCode == 200) {
        echo "✅ API connection successful!\n";
        echo "First 200 chars: " . substr($response, 0, 200) . "\n";
    } else {
        echo "❌ API connection failed\n";
        echo "Response: " . $response . "\n";
    }
}

curl_close($ch);

// 테스트 일반적인 HTTPS 연결
echo "\n\nTesting general HTTPS connectivity...\n";
$ch2 = curl_init();
curl_setopt_array($ch2, [
    CURLOPT_URL => 'https://httpbin.org/get',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false
]);

$response2 = curl_exec($ch2);
$httpCode2 = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
$error2 = curl_error($ch2);

if ($error2) {
    echo "❌ General HTTPS failed: " . $error2 . "\n";
} else {
    echo "✅ General HTTPS working (Code: " . $httpCode2 . ")\n";
}

curl_close($ch2);
?>