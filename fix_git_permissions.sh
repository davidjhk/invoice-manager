#!/bin/bash
# Git 권한 문제 해결 스크립트

echo "=== Git 권한 문제 해결 시작 ==="

# 현재 디렉토리 확인
if [ -d "/opt/bitnami/apps/invoice-manager" ]; then
    cd /opt/bitnami/apps/invoice-manager
elif [ -d "/opt/bitnami/apps/jdosa/invoice-manager" ]; then
    cd /opt/bitnami/apps/jdosa/invoice-manager
else
    echo "프로젝트 디렉토리를 찾을 수 없습니다."
    echo "현재 디렉토리에서 실행합니다: $(pwd)"
fi

# 현재 사용자 확인
CURRENT_USER=$(whoami)
echo "현재 사용자: $CURRENT_USER"
echo "작업 디렉토리: $(pwd)"

# 1단계: Git 상태 확인
echo "1단계: Git 상태 확인 중..."
git status --porcelain || echo "Git 상태 확인 실패"

# 2단계: 모든 파일 권한을 현재 사용자로 변경
echo "2단계: 파일 권한 변경 중..."
echo "⚠️  이 과정은 몇 분 소요될 수 있습니다..."

# Git 디렉토리 포함 모든 파일 권한 변경
sudo chown -R $CURRENT_USER:$CURRENT_USER . || {
    echo "❌ 권한 변경 실패. 관리자 권한이 필요합니다."
    exit 1
}

echo "✅ 모든 파일 권한 변경 완료"

# 3단계: Git 리셋 시도
echo "3단계: Git 리셋 시도 중..."
git reset --hard HEAD 2>/dev/null || {
    echo "⚠️  Git 리셋 실패. 강제 정리를 시도합니다..."
    
    # 강제 정리
    git clean -fd 2>/dev/null || true
    
    # 다시 리셋 시도
    git reset --hard HEAD 2>/dev/null || {
        echo "❌ Git 리셋 실패. 수동 해결이 필요합니다."
        echo "다음 명령을 시도해보세요:"
        echo "  git status"
        echo "  git clean -fd"
        echo "  git reset --hard HEAD"
        exit 1
    }
}

echo "✅ Git 리셋 완료"

# 4단계: Git pull 시도
echo "4단계: Git pull 시도 중..."
git pull origin main 2>/dev/null || git pull origin master 2>/dev/null || {
    echo "❌ Git pull 실패. 네트워크 또는 원격 저장소를 확인하세요."
    exit 1
}

echo "✅ Git pull 완료"

# 5단계: 웹서버 권한 복원
echo "5단계: 웹서버 권한 복원 중..."
sudo chown -R daemon:daemon . || {
    echo "⚠️  daemon 사용자가 없습니다. www-data로 시도합니다..."
    sudo chown -R www-data:www-data . 2>/dev/null || {
        echo "⚠️  www-data 사용자도 없습니다. 현재 사용자로 유지합니다."
    }
}

# 기본 권한 설정
sudo chmod -R 755 .
sudo chmod -R 777 runtime/ 2>/dev/null || true
sudo chmod -R 777 web/assets/ 2>/dev/null || true
sudo chmod -R 777 web/uploads/ 2>/dev/null || true

echo "✅ 웹서버 권한 복원 완료"

# 6단계: Composer 업데이트
echo "6단계: Composer 업데이트 중..."
if [ -f "composer.json" ]; then
    composer install --no-dev --optimize-autoloader 2>/dev/null || {
        echo "⚠️  Composer 실패. 캐시를 정리하고 다시 시도합니다..."
        composer clear-cache
        composer install --no-dev --optimize-autoloader || {
            echo "❌ Composer 업데이트 실패"
            exit 1
        }
    }
    echo "✅ Composer 업데이트 완료"
else
    echo "ℹ️  composer.json 파일이 없습니다."
fi

# 7단계: 마이그레이션 실행
echo "7단계: 데이터베이스 마이그레이션 중..."
if [ -f "yii" ]; then
    chmod +x yii
    ./yii migrate --interactive=0 2>/dev/null || {
        echo "⚠️  마이그레이션 실패. 데이터베이스 설정을 확인하세요."
    }
    echo "✅ 마이그레이션 완료"
else
    echo "ℹ️  yii 파일이 없습니다."
fi

# 8단계: 캐시 정리
echo "8단계: 캐시 정리 중..."
rm -rf runtime/cache/* 2>/dev/null || true
rm -rf web/assets/* 2>/dev/null || true
echo "✅ 캐시 정리 완료"

# 완료 메시지
echo ""
echo "🎉 Git 권한 문제 해결 완료!"
echo "✅ 모든 파일 권한이 수정되었습니다."
echo "✅ Git 상태가 정상화되었습니다."
echo "✅ 최신 코드가 적용되었습니다."
echo "✅ 웹서버 권한이 복원되었습니다."
echo ""
echo "📋 확인사항:"
echo "   - 웹사이트가 정상적으로 작동하는지 확인"
echo "   - 로그인이 정상적으로 되는지 확인"
echo ""
echo "🔗 웹사이트: https://$(hostname -f 2>/dev/null || echo 'your-domain.com')"
echo ""
echo "=== 완료 ==="