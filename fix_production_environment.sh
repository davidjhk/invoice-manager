#!/bin/bash
# 프로덕션 환경 설정 스크립트

echo "=== 프로덕션 환경 설정 시작 ==="

# 현재 디렉토리 확인
cd /opt/bitnami/apps/invoice-manager

# 백업 생성
echo "설정 파일 백업 중..."
cp web/index.php web/index.php.backup.$(date +%Y%m%d_%H%M%S) 2>/dev/null || true
cp config/web.php config/web.php.backup.$(date +%Y%m%d_%H%M%S) 2>/dev/null || true

# 프로덕션 환경 설정
echo "프로덕션 환경 설정 중..."
cat > web/index.php << 'EOF'
<?php

// comment out the following two lines when deployed to production
// defined('YII_DEBUG') or define('YII_DEBUG', true);
// defined('YII_ENV') or define('YII_ENV', 'dev');

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';

$config = require __DIR__ . '/../config/web.php';

(new yii\web\Application($config))->run();
EOF

# 오류 로깅 설정
echo "오류 로깅 설정 중..."
cat > web/index-prod.php << 'EOF'
<?php

// Production environment with error logging
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../runtime/logs/php_errors.log');
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';

$config = require __DIR__ . '/../config/web.php';

(new yii\web\Application($config))->run();
EOF

# Composer 프로덕션 최적화
echo "Composer 프로덕션 최적화 중..."
composer install --no-dev --optimize-autoloader --no-scripts

# 권한 설정
echo "권한 설정 중..."
sudo chown -R daemon:daemon .
sudo chmod -R 755 .
sudo chmod -R 777 runtime/ web/assets/ web/uploads/ 2>/dev/null || true

# 캐시 정리
echo "캐시 정리 중..."
rm -rf runtime/cache/* 2>/dev/null || true
rm -rf web/assets/* 2>/dev/null || true

# 로그 디렉토리 생성
echo "로그 디렉토리 생성 중..."
mkdir -p runtime/logs
sudo chown daemon:daemon runtime/logs
sudo chmod 777 runtime/logs

echo ""
echo "🎉 프로덕션 환경 설정 완료!"
echo "✅ 디버그 모드가 비활성화되었습니다."
echo "✅ 개발 의존성이 제거되었습니다."
echo "✅ 권한이 설정되었습니다."
echo "✅ 캐시가 정리되었습니다."
echo "✅ 오류 로깅이 설정되었습니다."
echo ""
echo "파일 정보:"
echo "- web/index.php: 프로덕션 환경 설정"
echo "- web/index-prod.php: 로깅 포함 프로덕션 환경"
echo "- runtime/logs/php_errors.log: PHP 오류 로그"
echo ""
echo "브라우저에서 웹사이트를 확인해보세요."
echo ""
echo "=== 완료 ==="