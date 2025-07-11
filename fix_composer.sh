#!/bin/bash
# Composer 문제 해결 스크립트

echo "=== Composer 문제 해결 시작 ==="

# 현재 디렉토리 확인
cd /opt/bitnami/apps/invoice-manager

# 기존 lock 파일 백업
echo "기존 composer.lock 백업 중..."
if [ -f "composer.lock" ]; then
    cp composer.lock "backups/composer.lock.backup.$(date +%Y%m%d_%H%M%S)"
    echo "✅ 백업 완료"
fi

# vendor 디렉토리 정리
echo "vendor 디렉토리 정리 중..."
rm -rf vendor/

# composer.lock 제거
echo "composer.lock 제거 중..."
rm -f composer.lock

# Composer 캐시 정리
echo "Composer 캐시 정리 중..."
composer clear-cache

# 새로운 의존성 설치
echo "새로운 의존성 설치 중..."
composer install --no-dev --optimize-autoloader

# 설치 확인
echo "설치 확인 중..."
if [ $? -eq 0 ]; then
    echo "✅ Composer 설치 완료"
else
    echo "❌ Composer 설치 실패"
    echo "수동으로 composer update를 실행해보세요."
    exit 1
fi

# 권한 설정
echo "권한 설정 중..."
sudo chown -R daemon:daemon vendor/
sudo chmod -R 755 vendor/

echo ""
echo "🎉 Composer 문제 해결 완료!"
echo "✅ 의존성이 올바르게 설치되었습니다."
echo "✅ Lock 파일이 새로 생성되었습니다."
echo ""
echo "=== 완료 ==="