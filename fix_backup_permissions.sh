#!/bin/bash
# 백업 권한 문제 해결 스크립트

echo "=== 백업 권한 문제 해결 ==="

# 현재 디렉토리 확인
cd /opt/bitnami/apps/invoice-manager 2>/dev/null || cd .

# 현재 사용자 확인
CURRENT_USER=$(whoami)
echo "현재 사용자: $CURRENT_USER"

# 백업 디렉토리 생성 및 권한 설정
echo "백업 디렉토리 권한 설정 중..."
sudo mkdir -p backups
sudo chown -R $CURRENT_USER:$CURRENT_USER backups/
sudo chmod -R 755 backups/

# 기존 백업 파일들 권한 수정
echo "기존 백업 파일 권한 수정 중..."
sudo chown -R $CURRENT_USER:$CURRENT_USER backups/ 2>/dev/null || true

# 실패한 백업 디렉토리 정리
echo "실패한 백업 디렉토리 정리 중..."
sudo rm -rf backups/local_changes_* 2>/dev/null || true

echo "✅ 백업 권한 문제 해결 완료"
echo "이제 ./update.sh를 다시 실행하세요."