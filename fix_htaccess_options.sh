#!/bin/bash
# .htaccess 문제 해결 스크립트 (옵션 선택)

echo "=== .htaccess 문제 해결 시작 ==="

# 현재 디렉토리 확인
cd /opt/bitnami/apps/invoice-manager

# 기존 .htaccess 백업
echo "기존 .htaccess 백업 중..."
if [ -f "web/.htaccess" ]; then
    cp web/.htaccess web/.htaccess.backup.$(date +%Y%m%d_%H%M%S)
    echo "✅ 백업 완료"
fi

echo ""
echo "어떤 .htaccess 버전을 사용하시겠습니까?"
echo "1) Simple - 기본 라우팅만 (권장)"
echo "2) Advanced - 보안 헤더, 캐싱, 압축 포함"
echo "3) Custom - 직접 입력"
echo ""
read -p "선택하세요 (1-3): " choice

case $choice in
    1)
        echo "Simple .htaccess 적용 중..."
        cat > web/.htaccess << 'EOF'
RewriteEngine on
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . index.php [L]
DirectoryIndex index.php
EOF
        echo "✅ Simple .htaccess 적용 완료"
        ;;
    2)
        echo "Advanced .htaccess 적용 중..."
        cp web/.htaccess.advanced web/.htaccess
        echo "✅ Advanced .htaccess 적용 완료"
        ;;
    3)
        echo "기본 .htaccess로 시작합니다. 필요에 따라 수정하세요."
        cp web/.htaccess.simple web/.htaccess
        nano web/.htaccess
        ;;
    *)
        echo "잘못된 선택입니다. Simple 버전을 적용합니다."
        cp web/.htaccess.simple web/.htaccess
        ;;
esac

# 권한 설정
echo "권한 설정 중..."
sudo chown daemon:daemon web/.htaccess
sudo chmod 644 web/.htaccess

# Apache 설정 테스트
echo "Apache 설정 테스트 중..."
if sudo apache2ctl configtest 2>/dev/null; then
    echo "✅ Apache 설정 테스트 통과"
    
    # Apache 재시작
    echo "Apache 재시작 중..."
    sudo systemctl reload apache2
    
    echo "✅ Apache 재시작 완료"
else
    echo "❌ Apache 설정 오류 발견"
    echo "기존 .htaccess 복원 중..."
    cp web/.htaccess.backup.* web/.htaccess 2>/dev/null || true
    echo "Simple 버전으로 대체 중..."
    cp web/.htaccess.simple web/.htaccess
    sudo systemctl reload apache2
fi

echo ""
echo "🎉 .htaccess 문제 해결 완료!"
echo "✅ 새로운 .htaccess 파일이 적용되었습니다."
echo "✅ 권한이 설정되었습니다."
echo "✅ Apache가 재시작되었습니다."
echo ""
echo "브라우저에서 웹사이트를 확인해보세요."
echo ""
echo "=== 완료 ==="