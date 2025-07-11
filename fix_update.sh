#!/bin/bash
# 업데이트 오류 수정 스크립트

echo "=== 업데이트 오류 수정 시작 ==="

# 현재 디렉토리 확인
cd /opt/bitnami/apps/invoice-manager

# Git 권한 설정
echo "Git 권한 설정 중..."
sudo chown -R $(whoami):$(whoami) .git/ || {
    echo "❌ Git 권한 변경 실패. sudo 권한을 확인해주세요."
    exit 1
}

# 기존 백업 디렉토리에서 문제가 있는 부분 정리
echo "기존 백업 정리 중..."
if [ -d "backups/local_changes_20250711_120853" ]; then
    rm -rf backups/local_changes_20250711_120853/backups/ 2>/dev/null || true
fi

# 새로운 백업 생성
echo "새로운 백업 생성 중..."
BACKUP_DIR="backups/manual_backup_$(date +%Y%m%d_%H%M%S)"
mkdir -p "$BACKUP_DIR"

# 중요 파일들 백업
echo "중요 파일들 백업 중..."
cp -r controllers/AdminController.php "$BACKUP_DIR/" 2>/dev/null || true
cp -r controllers/DemoController.php "$BACKUP_DIR/" 2>/dev/null || true
cp -r migrations/m250711_000003_create_admin_settings_table.php "$BACKUP_DIR/" 2>/dev/null || true
cp -r migrations/m250711_000004_add_role_to_users_table.php "$BACKUP_DIR/" 2>/dev/null || true
cp -r models/AdminSettings.php "$BACKUP_DIR/" 2>/dev/null || true
cp -r models/ChangePasswordForm.php "$BACKUP_DIR/" 2>/dev/null || true
cp -r views/admin/ "$BACKUP_DIR/" 2>/dev/null || true
cp -r views/demo/ "$BACKUP_DIR/" 2>/dev/null || true
cp -r views/site/change-password.php "$BACKUP_DIR/" 2>/dev/null || true

# 변경된 파일들 백업
echo "변경된 파일들 백업 중..."
git diff --name-only | while read file; do
    if [ -f "$file" ]; then
        mkdir -p "$BACKUP_DIR/$(dirname "$file")"
        cp "$file" "$BACKUP_DIR/$file"
    fi
done

echo "✅ 백업 완료: $BACKUP_DIR"

# 변경사항 스태시
echo "변경사항 스태시 중..."
git stash push -m "Manual stash before update $(date)"

# 새로운 파일들 제거
echo "새로운 파일들 제거 중..."
git clean -fd

# Git pull 실행
echo "Git pull 실행 중..."
git pull origin main || git pull origin master || {
    echo "❌ Git pull 실패. 네트워크 연결을 확인해주세요."
    exit 1
}

echo "✅ Git pull 완료"

# 백업된 파일들 복원
echo "백업된 파일들 복원 중..."
cp -r "$BACKUP_DIR"/* . 2>/dev/null || true

# 권한 복원
echo "권한 복원 중..."
sudo chown -R daemon:daemon .
sudo chmod -R 755 .
sudo chmod -R 777 runtime/ 2>/dev/null || true
sudo chmod -R 777 web/assets/ 2>/dev/null || true
sudo chmod -R 777 web/uploads/ 2>/dev/null || true

echo ""
echo "🎉 업데이트 수정 완료!"
echo "✅ 백업: $BACKUP_DIR"
echo "✅ 파일들이 복원되었습니다."
echo "✅ 권한이 설정되었습니다."
echo ""
echo "다음 단계:"
echo "1. 웹사이트가 정상 작동하는지 확인"
echo "2. 필요시 ./yii migrate 실행"
echo "3. 필요시 composer install 실행"
echo ""
echo "=== 수정 완료 ==="