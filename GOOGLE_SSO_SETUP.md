# Google SSO 설정 가이드

## 개요

Invoice Manager에는 Google SSO(Single Sign-On) 기능이 이미 완전히 구현되어 있습니다. 
이 가이드는 Google OAuth 2.0을 설정하여 Google 로그인을 활성화하는 방법을 안내합니다.

## 기능 특징

- ✅ Google OAuth 2.0 완전 구현
- ✅ 기존 이메일 계정과 Google 계정 자동 연동
- ✅ 신규 Google 사용자 자동 계정 생성
- ✅ 보안 설정 파일 (.gitignore로 보호)
- ✅ 다국어 지원 (한국어, 영어, 중국어 등)
- ✅ 관리자 페이지에서 Google 사용자 구분 표시

## 1. Google Cloud Console 설정

### 1.1 프로젝트 생성/선택
1. [Google Cloud Console](https://console.cloud.google.com/)에 접속
2. 새 프로젝트를 만들거나 기존 프로젝트 선택

### 1.2 Google+ API 활성화
1. 좌측 메뉴에서 **API 및 서비스 > 라이브러리** 선택
2. "Google+ API" 검색 후 활성화

### 1.3 OAuth 2.0 클라이언트 ID 생성
1. 좌측 메뉴에서 **API 및 서비스 > 사용자 인증 정보** 선택
2. **+ 사용자 인증 정보 만들기** 클릭
3. **OAuth 클라이언트 ID** 선택
4. 애플리케이션 유형: **웹 애플리케이션** 선택
5. 이름: `Invoice Manager OAuth` (원하는 이름)

### 1.4 승인된 리디렉션 URI 설정
**승인된 자바스크립트 출처:**
```
https://yourdomain.com
```

**승인된 리디렉션 URI:**
```
https://yourdomain.com/site/google-login
```

> **중요:** `yourdomain.com`을 실제 도메인으로 변경하세요.

### 1.5 클라이언트 ID와 시크릿 복사
생성 완료 후 다음 정보를 복사해 두세요:
- **클라이언트 ID**: `xxxxx.apps.googleusercontent.com`
- **클라이언트 시크릿**: `xxxxxxx`

## 2. Invoice Manager 설정

### 2.1 로컬 설정 파일 생성
```bash
# config 디렉토리로 이동
cd /path/to/invoice-manager/config

# 예제 파일을 복사하여 실제 설정 파일 생성
cp params-local.php.example params-local.php
```

### 2.2 Google OAuth 정보 입력
`config/params-local.php` 파일을 편집:

```php
<?php

return [
    'adminEmail' => 'admin@example.com',
    'supportEmail' => 'support@example.com',
    'senderEmail' => 'noreply@example.com',
    'senderName' => 'Your Company',
    'user.passwordResetTokenExpire' => 3600 * 24, // 24 hours
    'siteName' => 'Invoice Manager',
    
    // Google OAuth Settings
    'googleClientId' => 'YOUR_ACTUAL_CLIENT_ID.apps.googleusercontent.com',
    'googleClientSecret' => 'YOUR_ACTUAL_CLIENT_SECRET',
];
```

> **중요:** `YOUR_ACTUAL_CLIENT_ID`와 `YOUR_ACTUAL_CLIENT_SECRET`을 실제 값으로 변경하세요.

### 2.3 파일 권한 설정
```bash
# 설정 파일 보안을 위해 권한 제한
chmod 600 config/params-local.php
```

## 3. 동작 확인

### 3.1 로그인 페이지 확인
브라우저에서 `https://yourdomain.com/site/login`에 접속하여 **Google로 로그인** 버튼이 표시되는지 확인합니다.

### 3.2 Google 로그인 테스트
1. **Google로 로그인** 버튼 클릭
2. Google 계정 선택/로그인
3. 권한 승인
4. Invoice Manager로 자동 리디렉션 및 로그인 확인

## 4. 사용자 매칭 로직

### 4.1 기존 사용자 연동
- Google 이메일이 기존 데이터베이스 사용자와 일치하는 경우
- 해당 사용자 계정에 Google ID 연결
- `login_type`이 `google`로 업데이트

### 4.2 신규 사용자 접근 제한
- Google 이메일이 데이터베이스에 없는 경우
- 로그인을 거부하고 관리자 문의 메시지 표시
- 사용자는 미리 관리자가 생성한 계정으로만 Google SSO 사용 가능

## 5. 보안 고려사항

### 5.1 설정 파일 보안
- `config/params-local.php`는 `.gitignore`에 포함되어 Git에 업로드되지 않음
- 서버에서만 관리되는 민감한 정보

### 5.2 CSRF 보호
- OAuth state parameter를 통한 CSRF 공격 방지
- 세션 기반 상태 검증

### 5.3 사용자 데이터 보호
- Google 프로필 정보는 필요한 최소한만 저장
- 이메일, 이름, 프로필 이미지 URL만 저장

## 6. 문제 해결

### 6.1 "Google로 로그인" 버튼이 보이지 않는 경우
- `config/params-local.php` 파일 존재 확인
- `googleClientId`, `googleClientSecret` 설정 확인

### 6.2 OAuth 오류가 발생하는 경우
- Google Cloud Console에서 리디렉션 URI 확인
- 클라이언트 ID와 시크릿이 정확한지 확인
- Google+ API가 활성화되어 있는지 확인

### 6.3 로그인 후 오류가 발생하는 경우
- 데이터베이스 연결 확인
- 사용자 테이블에 Google 관련 필드 존재 확인
- 서버 로그 확인

## 7. 관리

### 7.1 Google 사용자 확인
관리자 페이지(`/admin/users`)에서 Google 로그인 사용자를 구분하여 볼 수 있습니다:
- **로그인 타입** 열에 "Google" 배지 표시
- 사용자 편집 시 Google ID 확인 가능

### 7.2 계정 연결 해제
현재는 UI를 통한 Google 계정 연결 해제 기능이 없습니다. 필요시 데이터베이스에서 직접 `google_id` 필드를 NULL로 설정할 수 있습니다.

---

**참고:** 이 기능은 Yii2 프레임워크 기반으로 구현되었으며, 보안과 사용자 경험을 고려하여 설계되었습니다.