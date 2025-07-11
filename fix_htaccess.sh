#!/bin/bash
# .htaccess 문제 해결 스크립트

echo "=== .htaccess 문제 해결 시작 ==="

# 현재 디렉토리 확인
cd /opt/bitnami/apps/invoice-manager

# 기존 .htaccess 백업
echo "기존 .htaccess 백업 중..."
if [ -f "web/.htaccess" ]; then
    cp web/.htaccess web/.htaccess.backup.$(date +%Y%m%d_%H%M%S)
    echo "✅ 백업 완료"
fi

# 새로운 .htaccess 생성
echo "새로운 .htaccess 생성 중..."
cat > web/.htaccess << 'EOF'
RewriteEngine on
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . index.php [L]
DirectoryIndex index.php
EOF

# 권한 설정
echo "권한 설정 중..."
sudo chown daemon:daemon web/.htaccess
sudo chmod 644 web/.htaccess

# Apache 설정 테스트
echo "Apache 설정 테스트 중..."
sudo apache2ctl configtest

if [ $? -eq 0 ]; then
    echo "✅ Apache 설정 테스트 통과"
    
    # Apache 재시작
    echo "Apache 재시작 중..."
    sudo systemctl reload apache2
    
    echo "✅ Apache 재시작 완료"
else
    echo "❌ Apache 설정 오류 발견"
    echo "기존 .htaccess 복원 중..."
    cp web/.htaccess.backup.* web/.htaccess 2>/dev/null || true
fi

echo ""
echo "🎉 .htaccess 문제 해결 완료!"
echo "✅ 새로운 .htaccess 파일이 생성되었습니다."
echo "✅ 권한이 설정되었습니다."
echo ""
echo "브라우저에서 웹사이트를 확인해보세요."
echo ""
echo "=== 완료 ==="