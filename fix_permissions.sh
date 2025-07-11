#!/bin/bash
# 권한 문제 해결 스크립트

echo "=== 권한 문제 해결 시작 ==="

# 현재 디렉토리 확인
cd /opt/bitnami/apps/invoice-manager

# 현재 사용자 확인
CURRENT_USER=$(whoami)
echo "현재 사용자: $CURRENT_USER"

# 필요한 디렉토리 생성 및 권한 설정
echo "필요한 디렉토리 생성 중..."
sudo mkdir -p backups
sudo mkdir -p runtime/cache
sudo mkdir -p runtime/logs
sudo mkdir -p web/assets
sudo mkdir -p web/uploads

# 사용자 권한 설정
echo "사용자 권한 설정 중..."
sudo chown -R $CURRENT_USER:$CURRENT_USER backups/
sudo chown -R $CURRENT_USER:$CURRENT_USER runtime/
sudo chown -R $CURRENT_USER:$CURRENT_USER web/assets/
sudo chown -R $CURRENT_USER:$CURRENT_USER web/uploads/

# Git 권한 설정
echo "Git 권한 설정 중..."
sudo chown -R $CURRENT_USER:$CURRENT_USER .git/

# 현재 사용자를 daemon 그룹에 추가
echo "daemon 그룹에 사용자 추가 중..."
sudo usermod -a -G daemon $CURRENT_USER

# 그룹 권한 설정
echo "그룹 권한 설정 중..."
sudo chgrp -R daemon .git/
sudo chmod -R g+w .git/

# 안전한 디렉토리로 등록
echo "Git 안전한 디렉토리 등록 중..."
git config --global --add safe.directory $(pwd)

echo ""
echo "🎉 권한 문제 해결 완료!"
echo "✅ 필요한 디렉토리가 생성되었습니다."
echo "✅ 사용자 권한이 설정되었습니다."
echo "✅ Git 권한이 설정되었습니다."
echo ""
echo "이제 다음 명령을 실행할 수 있습니다:"
echo "  ./update.sh"
echo "  ./fix_composer.sh"
echo "  git pull"
echo ""
echo "=== 완료 ==="