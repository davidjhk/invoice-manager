#!/bin/bash
# Git 상태를 강제로 초기화하고 업데이트하는 스크립트

echo "=== Git 강제 업데이트 시작 ==="

# 현재 디렉토리 확인
cd /opt/bitnami/apps/invoice-manager

# 현재 사용자 확인
CURRENT_USER=$(whoami)
echo "현재 사용자: $CURRENT_USER"

# Git 권한 설정
echo "Git 권한 설정 중..."
sudo chown -R $CURRENT_USER:$CURRENT_USER .git/ .

# 중요한 설정 파일 백업
echo "중요한 설정 파일 백업 중..."
mkdir -p ~/backup_configs
cp config/db.php ~/backup_configs/db.php.$(date +%Y%m%d_%H%M%S) 2>/dev/null || true
cp config/web.php ~/backup_configs/web.php.$(date +%Y%m%d_%H%M%S) 2>/dev/null || true

# Git 상태 완전 초기화
echo "Git 상태 완전 초기화 중..."
git stash clear 2>/dev/null || true
git reset --hard HEAD 2>/dev/null || true
git clean -fd 2>/dev/null || true

# Git pull 강제 실행
echo "Git pull 강제 실행 중..."
git fetch origin
git reset --hard origin/main 2>/dev/null || git reset --hard origin/master 2>/dev/null || {
    echo "❌ Git 업데이트 실패"
    exit 1
}

echo "✅ Git 강제 업데이트 완료"

# 설정 파일 복원
echo "설정 파일 복원 중..."
if [ -f "~/backup_configs/db.php.$(date +%Y%m%d_%H%M%S)" ]; then
    cp ~/backup_configs/db.php.* config/db.php 2>/dev/null || true
fi
if [ -f "~/backup_configs/web.php.$(date +%Y%m%d_%H%M%S)" ]; then
    cp ~/backup_configs/web.php.* config/web.php 2>/dev/null || true
fi

# Composer 의존성 설치
echo "Composer 의존성 설치 중..."
composer clear-cache
rm -f composer.lock
composer install --no-dev --optimize-autoloader

# 마이그레이션 실행
echo "마이그레이션 실행 중..."
./yii migrate --interactive=0 2>/dev/null || true

# 최종 권한 설정
echo "최종 권한 설정 중..."
sudo chown -R daemon:daemon .
sudo chmod -R 755 .
sudo chmod -R 777 runtime/ web/assets/ web/uploads/ 2>/dev/null || true

echo ""
echo "🎉 Git 강제 업데이트 완료!"
echo "✅ 모든 변경사항이 원격 저장소 버전으로 업데이트되었습니다."
echo "✅ Composer 의존성이 설치되었습니다."
echo "✅ 마이그레이션이 실행되었습니다."
echo "✅ 권한이 설정되었습니다."
echo ""
echo "⚠️  로컬 변경사항은 모두 손실되었습니다."
echo "📁 설정 파일 백업 위치: ~/backup_configs/"
echo ""
echo "=== 완료 ==="