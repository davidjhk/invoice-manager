#!/bin/bash
# 응급 권한 문제 해결 스크립트 (빠른 해결)

echo "=== 응급 권한 문제 해결 ==="

# 현재 디렉토리에서 실행
CURRENT_USER=$(whoami)
echo "현재 사용자: $CURRENT_USER"

# 1단계: 모든 파일 권한을 현재 사용자로 변경
echo "1단계: 모든 파일 권한 변경 중..."
sudo chown -R $CURRENT_USER:$CURRENT_USER . || {
    echo "❌ 권한 변경 실패. sudo 권한이 필요합니다."
    exit 1
}

# 2단계: 충돌 파일 정리
echo "2단계: 충돌 파일 정리 중..."
# 백업 생성
mkdir -p temp_backup
cp fix_backup_permissions.sh temp_backup/ 2>/dev/null || true
cp fix_git_permissions.sh temp_backup/ 2>/dev/null || true
cp emergency_fix.sh temp_backup/ 2>/dev/null || true

# 충돌 파일 제거
rm -f fix_backup_permissions.sh fix_git_permissions.sh 2>/dev/null || true

# 3단계: Git 강제 리셋
echo "3단계: Git 강제 리셋 중..."
git reset --hard HEAD || {
    echo "❌ Git 리셋 실패"
    exit 1
}

# 4단계: Git clean (untracked 파일 정리)
echo "4단계: Git clean 중..."
git clean -fd || true

# 5단계: Git pull
echo "5단계: Git pull 중..."
git pull origin main || git pull origin master || {
    echo "❌ Git pull 실패"
    exit 1
}

# 6단계: 백업 파일 복원
echo "6단계: 백업 파일 복원 중..."
cp temp_backup/* . 2>/dev/null || true
rm -rf temp_backup

# 7단계: 웹서버 권한 복원
echo "7단계: 웹서버 권한 복원 중..."
sudo chown -R daemon:daemon . 2>/dev/null || sudo chown -R www-data:www-data . 2>/dev/null || true
sudo chmod -R 755 .
sudo chmod -R 777 runtime/ web/assets/ web/uploads/ 2>/dev/null || true

echo "✅ 응급 수리 완료!"
echo "웹사이트를 확인해보세요."