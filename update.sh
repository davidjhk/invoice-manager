#!/bin/bash
# Invoice Manager 업데이트 스크립트
# 사용법: ./update.sh

set -e  # 오류 시 스크립트 중단

echo "=== Invoice Manager 업데이트 시작 ==="

# 현재 디렉토리 저장
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

# 현재 사용자 확인
CURRENT_USER=$(whoami)
echo "현재 사용자: $CURRENT_USER"

# Git 상태 확인
echo "Git 상태 확인 중..."
if [ ! -d ".git" ]; then
    echo "❌ 오류: .git 디렉토리가 없습니다. Git 저장소가 아닙니다."
    exit 1
fi

# 백업 생성 (선택사항)
echo "기존 설정 파일 백업 중..."
mkdir -p backups
cp -f config/db.php backups/db.php.$(date +%Y%m%d_%H%M%S) 2>/dev/null || true
cp -f config/web.php backups/web.php.$(date +%Y%m%d_%H%M%S) 2>/dev/null || true

# Git 권한 임시 변경
echo "Git 권한 설정 중..."
sudo chown -R $CURRENT_USER:$CURRENT_USER .git/ || {
    echo "❌ Git 권한 변경 실패. sudo 권한을 확인해주세요."
    exit 1
}

# 변경사항 확인
echo "변경사항 확인 중..."
git fetch origin

# 로컬 변경사항이 있는지 확인
if [ -n "$(git status --porcelain)" ]; then
    echo "⚠️  로컬에 변경사항이 있습니다:"
    git status --short
    echo ""
    read -p "변경사항을 백업하고 계속하시겠습니까? (y/N): " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        echo "변경사항 백업 중..."
        
        # 백업 디렉토리 생성
        BACKUP_DIR="backups/local_changes_$(date +%Y%m%d_%H%M%S)"
        mkdir -p "$BACKUP_DIR"
        
        # 수정된 파일 백업
        git diff --name-only | while read file; do
            if [ -f "$file" ]; then
                mkdir -p "$BACKUP_DIR/$(dirname "$file")"
                cp "$file" "$BACKUP_DIR/$file"
            fi
        done
        
        # 새로운 파일들 백업 (backups 디렉토리 제외)
        git status --porcelain | grep "^??" | cut -c4- | while read file; do
            # backups 디렉토리는 제외
            if [[ "$file" == backups/* ]]; then
                continue
            fi
            
            if [ -f "$file" ]; then
                mkdir -p "$BACKUP_DIR/$(dirname "$file")"
                cp "$file" "$BACKUP_DIR/$file"
            elif [ -d "$file" ]; then
                cp -r "$file" "$BACKUP_DIR/$file"
            fi
        done
        
        echo "✅ 백업 완료: $BACKUP_DIR"
        
        # 변경사항 스태시
        git stash push -m "Auto stash before update $(date)"
        
        # 새로운 파일들 제거
        git clean -fd
        
    else
        echo "업데이트가 취소되었습니다."
        exit 1
    fi
fi

# Git pull 실행
echo "최신 코드 가져오는 중..."
git pull origin main || git pull origin master || {
    echo "❌ Git pull 실패. 네트워크 연결을 확인해주세요."
    exit 1
}

# Composer 업데이트 (필요시)
echo "Composer 의존성 확인 중..."
if [ -f "composer.json" ]; then
    # 먼저 일반적인 설치 시도
    if ! composer install --no-dev --optimize-autoloader; then
        echo "⚠️  Composer 설치 실패. Lock 파일 문제일 수 있습니다."
        echo "Lock 파일을 재생성하고 다시 시도합니다..."
        
        # Lock 파일 백업
        if [ -f "composer.lock" ]; then
            cp composer.lock "backups/composer.lock.backup.$(date +%Y%m%d_%H%M%S)"
        fi
        
        # 캐시 정리 및 재설치
        composer clear-cache
        rm -f composer.lock
        
        # 재설치 시도
        if composer install --no-dev --optimize-autoloader; then
            echo "✅ Composer 의존성 재설치 완료"
        else
            echo "❌ Composer 설치 실패. 수동으로 해결해야 합니다."
            echo "다음 명령을 실행해보세요:"
            echo "  composer clear-cache"
            echo "  rm composer.lock"
            echo "  composer install"
            exit 1
        fi
    else
        echo "✅ Composer 의존성 업데이트 완료"
    fi
else
    echo "ℹ️  composer.json 파일이 없습니다."
fi

# 마이그레이션 실행 (필요시)
echo "데이터베이스 마이그레이션 확인 중..."
if [ -f "yii" ]; then
    ./yii migrate --interactive=0 || {
        echo "❌ 마이그레이션 실패. 데이터베이스 설정을 확인해주세요."
        exit 1
    }
    echo "✅ 데이터베이스 마이그레이션 완료"
else
    echo "ℹ️  yii 파일이 없습니다."
fi

# 권한 복원
echo "권한 복원 중..."
sudo chown -R daemon:daemon . || {
    echo "❌ 권한 복원 실패."
    exit 1
}

sudo chmod -R 755 .
sudo chmod -R 777 runtime/ 2>/dev/null || true
sudo chmod -R 777 web/assets/ 2>/dev/null || true
sudo chmod -R 777 web/uploads/ 2>/dev/null || true

# 스태시 복원 (있는 경우)
if git stash list | grep -q "Auto stash before update"; then
    echo "백업된 변경사항 복원 중..."
    read -p "스태시된 변경사항을 복원하시겠습니까? (y/N): " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        git stash pop || echo "⚠️  스태시 복원 중 충돌이 발생했습니다. 수동으로 해결해주세요."
    fi
fi

# 캐시 정리
echo "캐시 정리 중..."
rm -rf runtime/cache/* 2>/dev/null || true
rm -rf web/assets/* 2>/dev/null || true

# 완료 메시지
echo ""
echo "🎉 업데이트 완료!"
echo "✅ 최신 코드가 성공적으로 적용되었습니다."
echo "✅ 권한이 올바르게 설정되었습니다."
echo "✅ 캐시가 정리되었습니다."
echo ""
echo "📋 업데이트 후 확인사항:"
echo "   - 웹사이트가 정상적으로 작동하는지 확인"
echo "   - 로그인이 정상적으로 되는지 확인"
echo "   - 새로운 기능이 있는지 확인"
echo ""
echo "🔗 웹사이트: https://$(hostname -f 2>/dev/null || echo 'your-domain.com')"
echo "📁 로그 파일: runtime/logs/app.log"
echo ""
echo "=== 업데이트 완료 ==="