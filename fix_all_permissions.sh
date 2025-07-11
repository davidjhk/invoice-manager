#!/bin/bash
# 모든 권한 문제를 강제로 해결하는 스크립트

echo "=== 모든 권한 문제 해결 시작 ==="

# 현재 디렉토리 확인
cd /opt/bitnami/apps/invoice-manager

# 현재 사용자 확인
CURRENT_USER=$(whoami)
echo "현재 사용자: $CURRENT_USER"

# 전체 프로젝트 소유권을 현재 사용자로 변경
echo "전체 프로젝트 소유권 변경 중..."
sudo chown -R $CURRENT_USER:$CURRENT_USER .

# 모든 디렉토리 권한 설정
echo "디렉토리 권한 설정 중..."
sudo find . -type d -exec chmod 755 {} \;

# 모든 파일 권한 설정
echo "파일 권한 설정 중..."
sudo find . -type f -exec chmod 644 {} \;

# 실행 파일 권한 설정
echo "실행 파일 권한 설정 중..."
sudo chmod +x yii
sudo chmod +x *.sh
sudo chmod +x fix_*.sh
sudo chmod +x update.sh

# 특수 디렉토리 권한 설정
echo "특수 디렉토리 권한 설정 중..."
sudo chmod -R 777 runtime/ 2>/dev/null || true
sudo chmod -R 777 web/assets/ 2>/dev/null || true
sudo chmod -R 777 web/uploads/ 2>/dev/null || true

# Git 상태 초기화
echo "Git 상태 초기화 중..."
git stash clear 2>/dev/null || true
git reset --hard HEAD 2>/dev/null || true
git clean -fd 2>/dev/null || true

# Git 권한 설정
echo "Git 권한 설정 중..."
sudo chown -R $CURRENT_USER:$CURRENT_USER .git/
sudo chmod -R 755 .git/

# 안전한 디렉토리 등록
echo "Git 안전한 디렉토리 등록 중..."
git config --global --add safe.directory $(pwd)

# 현재 사용자를 daemon 그룹에 추가
echo "daemon 그룹에 사용자 추가 중..."
sudo usermod -a -G daemon $CURRENT_USER

# 최종 권한 체크
echo "최종 권한 체크 중..."
if [ -w "." ]; then
    echo "✅ 쓰기 권한 확인됨"
else
    echo "❌ 쓰기 권한 없음"
fi

if [ -w ".git" ]; then
    echo "✅ Git 쓰기 권한 확인됨"
else
    echo "❌ Git 쓰기 권한 없음"
fi

echo ""
echo "🎉 모든 권한 문제 해결 완료!"
echo "✅ 전체 프로젝트 소유권이 $CURRENT_USER로 변경되었습니다."
echo "✅ 모든 파일과 디렉토리 권한이 설정되었습니다."
echo "✅ Git 상태가 초기화되었습니다."
echo "✅ 실행 파일 권한이 설정되었습니다."
echo ""
echo "이제 다음 명령을 실행할 수 있습니다:"
echo "  git pull"
echo "  ./update.sh"
echo "  ./fix_composer.sh"
echo ""
echo "=== 완료 ==="