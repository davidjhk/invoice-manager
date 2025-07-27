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
];

$localParams = __DIR__ . '/params-local.php';

if (file_exists($localParams)) {
    $localParamsArray = require $localParams;
    return array_merge($defaultParams, $localParamsArray);
}

return $defaultParams;