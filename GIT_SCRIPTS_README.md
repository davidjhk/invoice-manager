# Git Scripts Information

## Scripts Available

### 1. quick_fix.sh
- **목적**: Git 충돌 즉시 해결
- **상태**: ✅ 정상 작동
- **사용법**: `./quick_fix.sh`

### 2. update.sh
- **목적**: 종합적인 Invoice Manager 업데이트
- **상태**: ✅ 권한 문제 해결됨
- **사용법**: `./update.sh`

## 해결된 문제

### update.sh 권한 에러 해결
**문제**: `sudo: a terminal is required to read the password`

**해결방법**: 
- `sudo` 명령어들을 제거하고 일반 사용자 권한으로 실행되도록 수정
- 권한이 필요한 작업은 선택사항으로 처리하고 실패 시 경고만 표시
- 백업 및 Git 작업은 현재 사용자 권한으로 실행

**변경사항**:
- 백업 디렉토리 생성 시 `sudo` 제거
- Git 권한 변경 시 `sudo` 제거하고 실패 시 경고 처리
- 파일 권한 복원 시 `sudo` 제거하고 실패 시 건너뛰기

## 사용 권장사항

1. **quick_fix.sh**: 빠른 Git 충돌 해결이 필요할 때
2. **update.sh**: 전체적인 업데이트와 백업이 필요할 때

두 스크립트 모두 이제 sudo 권한 없이도 정상 작동합니다.