#!/bin/bash
# 즉시 해결 스크립트 (충돌 파일 처리)

echo "=== 즉시 Git 충돌 해결 ==="

# 현재 사용자 확인
CURRENT_USER=$(whoami)
echo "현재 사용자: $CURRENT_USER"

# 1단계: 권한 변경
echo "1단계: 권한 변경 중..."
sudo chown -R $CURRENT_USER:$CURRENT_USER .

# 2단계: 충돌 파일 임시 제거
echo "2단계: 충돌 파일 임시 제거 중..."
mv fix_backup_permissions.sh fix_backup_permissions.sh.bak 2>/dev/null || true

# 3단계: Git 정리
echo "3단계: Git 정리 중..."
git reset --hard HEAD
git clean -fd

# 4단계: Git pull
echo "4단계: Git pull 중..."
git pull origin main

# 5단계: 백업 파일 복원
echo "5단계: 백업 파일 복원 중..."
mv fix_backup_permissions.sh.bak fix_backup_permissions.sh 2>/dev/null || true

# 6단계: 권한 복원
echo "6단계: 권한 복원 중..."
sudo chown -R daemon:daemon .
sudo chmod -R 755 .
sudo chmod -R 777 runtime/ web/assets/ web/uploads/ 2>/dev/null || true

echo "✅ 즉시 해결 완료!"