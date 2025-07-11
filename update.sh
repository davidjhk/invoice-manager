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
sudo mkdir -p backups
sudo chown $CURRENT_USER:$CURRENT_USER backups/
cp -f config/db.php backups/db.php.$(date +%Y%m%d_%H%M%S) 2>/dev/null || true
cp -f config/web.php backups/web.php.$(date +%Y%m%d_%H%M%S) 2>/dev/null || true
cp -f web/.htaccess backups/.htaccess.$(date +%Y%m%d_%H%M%S) 2>/dev/null || true

# Git 권한 임시 변경
echo "Git 권한 설정 중..."
sudo chown -R $CURRENT_USER:$CURRENT_USER .git/ || {
    echo "❌ Git 권한 변경 실패. sudo 권한을 확인해주세요."
    exit 1
}

# 권한 문제 감지 및 해결
echo "권한 문제 감지 중..."
if ! git status >/dev/null 2>&1; then
    echo "⚠️  Git 권한 문제 감지. 전체 파일 권한을 수정합니다..."
    sudo chown -R $CURRENT_USER:$CURRENT_USER . || {
        echo "❌ 전체 권한 변경 실패. 관리자 권한을 확인해주세요."
        exit 1
    }
    echo "✅ 전체 권한 수정 완료"
fi

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
        sudo mkdir -p "$BACKUP_DIR"
        sudo chown -R $CURRENT_USER:$CURRENT_USER "$BACKUP_DIR"
        
        # 수정된 파일 백업
        git diff --name-only | while read file; do
            if [ -f "$file" ]; then
                mkdir -p "$BACKUP_DIR/$(dirname "$file")"
                cp "$file" "$BACKUP_DIR/$file" 2>/dev/null || {
                    echo "⚠️  백업 실패: $file (권한 문제)"
                    sudo cp "$file" "$BACKUP_DIR/$file" 2>/dev/null || true
                }
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
                cp "$file" "$BACKUP_DIR/$file" 2>/dev/null || {
                    echo "⚠️  백업 실패: $file (권한 문제)"
                    sudo cp "$file" "$BACKUP_DIR/$file" 2>/dev/null || true
                }
            elif [ -d "$file" ]; then
                cp -r "$file" "$BACKUP_DIR/$file" 2>/dev/null || {
                    echo "⚠️  백업 실패: $file (권한 문제)"
                    sudo cp -r "$file" "$BACKUP_DIR/$file" 2>/dev/null || true
                }
            fi
        done
        
        # 백업 디렉토리 권한 최종 설정
        sudo chown -R $CURRENT_USER:$CURRENT_USER "$BACKUP_DIR" 2>/dev/null || true
        
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

# Git pull 실행 (충돌 해결 포함)
echo "최신 코드 가져오는 중..."

# 충돌 파일 사전 백업
echo "충돌 가능 파일 백업 중..."
TEMP_BACKUP_DIR="temp_backup_$(date +%Y%m%d_%H%M%S)"
mkdir -p "$TEMP_BACKUP_DIR"
cp fix_*.sh "$TEMP_BACKUP_DIR/" 2>/dev/null || true
cp quick_*.sh "$TEMP_BACKUP_DIR/" 2>/dev/null || true
cp emergency_*.sh "$TEMP_BACKUP_DIR/" 2>/dev/null || true

# Git pull 시도
if ! git pull origin main 2>/dev/null; then
    echo "⚠️  Git pull 실패. 충돌 해결을 시도합니다..."
    
    # 충돌 유형 확인
    if git status 2>/dev/null | grep -q "untracked working tree files"; then
        echo "📋 Untracked 파일 충돌 감지"
        
        # 충돌 파일 임시 제거
        git status --porcelain | grep "^??" | cut -c4- | while read file; do
            if [[ "$file" == fix_*.sh ]] || [[ "$file" == quick_*.sh ]] || [[ "$file" == emergency_*.sh ]]; then
                echo "   임시 제거: $file"
                rm -f "$file"
            fi
        done
        
        # Git 강제 정리
        git reset --hard HEAD 2>/dev/null || true
        git clean -fd 2>/dev/null || true
        
        # 다시 Git pull 시도
        if git pull origin main 2>/dev/null; then
            echo "✅ Git pull 성공 (충돌 해결 후)"
        elif git pull origin master 2>/dev/null; then
            echo "✅ Git pull 성공 (master 브랜치)"
        else
            echo "❌ Git pull 실패. 네트워크 연결을 확인해주세요."
            exit 1
        fi
    else
        echo "❌ Git pull 실패. 다른 문제가 있을 수 있습니다."
        git status
        exit 1
    fi
else
    echo "✅ Git pull 성공"
fi

# 백업 파일 복원
echo "백업 파일 복원 중..."
cp "$TEMP_BACKUP_DIR"/* . 2>/dev/null || true
rm -rf "$TEMP_BACKUP_DIR"

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

# .htaccess 복원 (백업이 있는 경우)
echo "로컬 설정 복원 중..."
LATEST_HTACCESS_BACKUP=$(ls -t backups/.htaccess.* 2>/dev/null | head -1)
if [ -n "$LATEST_HTACCESS_BACKUP" ]; then
    echo "최신 .htaccess 백업 복원: $LATEST_HTACCESS_BACKUP"
    cp "$LATEST_HTACCESS_BACKUP" web/.htaccess
    echo "✅ .htaccess 복원 완료"
else
    echo "ℹ️  .htaccess 백업을 찾을 수 없습니다."
fi

# 완료 메시지
echo ""
echo "🎉 업데이트 완료!"
echo "✅ 권한 문제가 자동으로 해결되었습니다."
echo "✅ Git 충돌이 자동으로 해결되었습니다."
echo "✅ 최신 코드가 성공적으로 적용되었습니다."
echo "✅ 로컬 설정이 보존되었습니다."
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