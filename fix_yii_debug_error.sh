#!/bin/bash
# Yii Debug Module 오류 해결 스크립트

echo "=== Yii Debug Module 오류 해결 시작 ==="

# 현재 디렉토리 확인
cd /opt/bitnami/apps/invoice-manager

# 현재 환경 확인
echo "현재 환경 설정 확인 중..."
grep -r "YII_ENV_DEV" web/index.php 2>/dev/null || echo "YII_ENV_DEV 설정을 찾을 수 없습니다."

# 옵션 선택
echo ""
echo "어떤 해결 방법을 선택하시겠습니까?"
echo "1) 프로덕션 환경으로 변경 (권장)"
echo "2) 개발 의존성 설치 (개발 환경)"
echo "3) 디버그 모듈만 비활성화"
echo ""
read -p "선택하세요 (1-3): " choice

case $choice in
    1)
        echo "프로덕션 환경으로 변경 중..."
        
        # web/index.php 백업
        cp web/index.php web/index.php.backup.$(date +%Y%m%d_%H%M%S)
        
        # 프로덕션 환경 설정
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
        
        echo "✅ 프로덕션 환경으로 변경 완료"
        ;;
        
    2)
        echo "개발 의존성 설치 중..."
        
        # Composer 개발 의존성 설치
        composer install --optimize-autoloader
        
        echo "✅ 개발 의존성 설치 완료"
        ;;
        
    3)
        echo "디버그 모듈만 비활성화 중..."
        
        # web.php 백업
        cp config/web.php config/web.php.backup.$(date +%Y%m%d_%H%M%S)
        
        # 디버그 모듈 비활성화
        sed -i.bak '/YII_ENV_DEV/,/^}$/c\
if (false) {\
    // Debug module disabled\
}' config/web.php
        
        echo "✅ 디버그 모듈 비활성화 완료"
        ;;
        
    *)
        echo "잘못된 선택입니다. 프로덕션 환경으로 변경합니다."
        
        # 프로덕션 환경 설정 (기본값)
        cp web/index.php web/index.php.backup.$(date +%Y%m%d_%H%M%S)
        
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
        ;;
esac

# 권한 설정
echo "권한 설정 중..."
sudo chown -R daemon:daemon .
sudo chmod -R 755 .
sudo chmod -R 777 runtime/ web/assets/ web/uploads/ 2>/dev/null || true

# 캐시 정리
echo "캐시 정리 중..."
rm -rf runtime/cache/* 2>/dev/null || true
rm -rf web/assets/* 2>/dev/null || true

echo ""
echo "🎉 Yii Debug Module 오류 해결 완료!"
echo "✅ 환경 설정이 수정되었습니다."
echo "✅ 권한이 설정되었습니다."
echo "✅ 캐시가 정리되었습니다."
echo ""
echo "브라우저에서 웹사이트를 확인해보세요."
echo ""
echo "=== 완료 ==="