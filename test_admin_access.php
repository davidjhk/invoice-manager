<?php
/**
 * Admin Settings 테스트 스크립트
 * 이 스크립트는 admin settings 시스템이 정상적으로 작동하는지 확인합니다.
 */

// Yii 애플리케이션 부트스트랩
require_once(__DIR__ . '/vendor/autoload.php');
require_once(__DIR__ . '/vendor/yiisoft/yii2/Yii.php');

$config = require(__DIR__ . '/config/web.php');
$application = new yii\web\Application($config);

echo "=== Admin Settings 테스트 ===\n";

try {
    // 1. AdminSettings 모델 테스트
    echo "1. AdminSettings 모델 테스트 중...\n";
    $settings = \app\models\AdminSettings::find()->all();
    
    if (empty($settings)) {
        echo "❌ 설정 데이터가 없습니다.\n";
        return;
    }
    
    echo "✅ " . count($settings) . "개의 설정을 찾았습니다.\n";
    
    // 2. 중요 설정 확인
    echo "2. 중요 설정 확인 중...\n";
    
    $allowSignup = \app\models\AdminSettings::getSetting('allow_signup', '1');
    echo "- 사용자 등록 허용: " . ($allowSignup == '1' ? '활성화' : '비활성화') . "\n";
    
    $maxUsers = \app\models\AdminSettings::getSetting('max_users', '100');
    echo "- 최대 사용자 수: " . $maxUsers . "\n";
    
    $maintenance = \app\models\AdminSettings::getSetting('site_maintenance', '0');
    echo "- 유지보수 모드: " . ($maintenance == '1' ? '활성화' : '비활성화') . "\n";
    
    // 3. 헬퍼 메소드 테스트
    echo "3. 헬퍼 메소드 테스트 중...\n";
    
    $signupAllowed = \app\models\AdminSettings::isSignupAllowed();
    echo "- isSignupAllowed(): " . ($signupAllowed ? '허용' : '차단') . "\n";
    
    $maintenanceMode = \app\models\AdminSettings::isMaintenanceMode();
    echo "- isMaintenanceMode(): " . ($maintenanceMode ? '활성화' : '비활성화') . "\n";
    
    // 4. 전체 설정 목록
    echo "4. 전체 설정 목록:\n";
    foreach ($settings as $setting) {
        echo "- {$setting->setting_key}: {$setting->setting_value} ({$setting->description})\n";
    }
    
    echo "\n✅ Admin Settings 시스템이 정상적으로 작동합니다!\n";
    echo "이제 웹 브라우저에서 다음 URL에 접근할 수 있습니다:\n";
    echo "• https://invoice.jdosa.com/admin\n";
    echo "• https://invoice.jdosa.com/admin/settings\n";
    echo "• https://invoice.jdosa.com/admin/create-user\n";
    
} catch (Exception $e) {
    echo "❌ 오류 발생: " . $e->getMessage() . "\n";
    echo "스택 트레이스:\n" . $e->getTraceAsString() . "\n";
    
    echo "\n해결 방법:\n";
    echo "1. 데이터베이스 연결 확인\n";
    echo "2. jdosa_admin_settings 테이블 존재 확인\n";
    echo "3. 다음 명령으로 테이블 생성: ./verify_admin_settings.sh\n";
}

echo "\n=== 테스트 완료 ===\n";
?>