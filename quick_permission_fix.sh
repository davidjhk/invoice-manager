#!/bin/bash
# 빠른 권한 수정 스크립트

echo "=== 긴급 권한 수정 ==="

# 현재 사용자 확인
CURRENT_USER=$(whoami)
echo "현재 사용자: $CURRENT_USER"

# 전체 권한 수정
echo "전체 프로젝트 권한 수정 중..."
sudo chown -R $CURRENT_USER:$CURRENT_USER .

# Git 상태 초기화
echo "Git 상태 초기화 중..."
git reset --hard HEAD
git clean -fd

# Git 상태 확인
echo "Git 상태 확인..."
if git status >/dev/null 2>&1; then
    echo "✅ 권한 문제 해결됨!"
    echo ""
    echo "이제 다음 명령어를 실행하세요:"
    echo "  git pull origin main"
    echo "  또는"
    echo "  ./update.sh"
else
    echo "❌ 여전히 문제가 있습니다."
fi