# Invoice Manager - Yii2 기반 인보이스 관리 시스템

현대적인 Yii2 프레임워크를 기반으로 한 멀티유저, 다국어 지원 인보이스 관리 시스템입니다.

## 주요 특징

### 🏢 멀티유저 지원

- **관리자 시스템**: 완전한 사용자 관리 및 시스템 설정
- **일반 사용자**: 개인 회사 및 인보이스 관리
- **데모 사용자**: 제한된 기능으로 시스템 체험 가능
- **역할 기반 접근 제어**: 세밀한 권한 관리

### 🌍 다국어 지원 (2025년 7월 추가)

- **5개 언어 지원**: 영어, 한국어, 스페인어, 중국어(간체/번체)
- **회사별 언어 설정**: 각 회사가 독립적으로 언어 선택 가능
- **실시간 언어 전환**: 페이지 새로고침 없이 즉시 언어 변경
- **완전 번역 지원**: 모든 UI 요소 및 메시지 번역

### 📊 핵심 기능

- **인보이스 관리**: 생성, 편집, PDF 생성, 이메일 발송
- **견적서 관리**: 견적서 생성 및 인보이스 변환
- **고객 관리**: 고객 정보 및 연락처 관리
- **제품 관리**: 제품/서비스 카탈로그 관리 (카테고리 지원)
- **회사 설정**: 회사 정보, 로고, 이메일 설정

### 🗂️ 제품 카테고리 시스템 (2025년 7월 추가)

- **카테고리 관리**: 제품 분류를 위한 완전한 카테고리 시스템
- **회사별 카테고리**: 각 회사가 독립적으로 카테고리 관리
- **정렬 및 상태 관리**: 드래그 앤 드롭 정렬, 활성/비활성 상태
- **AJAX 기반**: 페이지 새로고침 없는 실시간 카테고리 생성

### 💌 이메일 시스템

- **Symfony Mailer**: 최신 이메일 전송 시스템
- **파일 기반 전송**: 개발 환경에서 이메일 파일 저장
- **SMTP2GO 통합**: 프로덕션 환경에서 안정적인 이메일 전송

### 🎨 현대적 사용자 인터페이스

- **반응형 디자인**: 모든 디바이스에서 완벽 호환
- **다크모드 지원**: 회사별 라이트/다크 모드 전환 (2025년 7월 향상)
- **컴팩트 모드**: 아이콘 전용 네비게이션으로 공간 효율성 극대화 (2025년 7월 추가)
- **플로팅 네비게이션**: Liquid Glass 디자인의 고정 네비게이션
- **현대적 UI**: Bootstrap 4 + 커스텀 glassmorphism 디자인

### 📱 컴팩트 모드 (2025년 7월 신규)

- **공간 효율적 네비게이션**: 텍스트를 숨기고 아이콘만 표시
- **툴팁 지원**: 컴팩트 모드에서 마우스 오버 시 기능 설명 표시
- **회사별 설정**: 각 회사가 개별적으로 컴팩트 모드 활성화
- **반응형 지원**: 다양한 화면 크기에서 최적화된 경험

### 🔡 CJK 폰트 지원 (2025년 7월 추가)

- **아시아 언어 PDF 지원**: 중국어, 일본어, 한국어 문자 완벽 렌더링
- **회사별 설정**: 필요에 따라 CJK 폰트 활성화
- **TCPDF 통합**: 고급 다국어 PDF 생성

## 시스템 요구사항

- **PHP**: 8.1 이상 (권장)
- **데이터베이스**: MySQL 5.7+ 또는 MariaDB 10.2+
- **웹서버**: Apache 2.4+ 또는 Nginx 1.18+
- **Composer**: PHP 의존성 관리
- **Git**: 소스 코드 관리

## 설치 방법

### 1. 프로젝트 클론

```bash
git clone https://github.com/davidjhk/invoice-manager.git
cd invoice-manager
```

### 2. 의존성 설치

```bash
composer install
```

### 3. 데이터베이스 설정

```bash
# 데이터베이스 생성
mysql -u root -p
```

```sql
CREATE DATABASE invoice_manager CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'invoice_user'@'localhost' IDENTIFIED BY 'your_password';
GRANT ALL PRIVILEGES ON invoice_manager.* TO 'invoice_user'@'localhost';
FLUSH PRIVILEGES;
```

### 4. 설정 파일 수정

`config/db.php` 파일에서 데이터베이스 연결 정보 수정:

```php
return [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=localhost;dbname=invoice_manager',
    'username' => 'invoice_user',
    'password' => 'your_password',
    'charset' => 'utf8mb4',
];
```

### 5. 데이터베이스 마이그레이션

```bash
./yii migrate
```

**주요 마이그레이션 포함 사항:**
- 다국어 지원 (언어 필드)
- 다크모드/컴팩트모드 설정
- 제품 카테고리 시스템
- 고급 관리자 설정
- CJK 폰트 지원
- 사용자 역할 시스템

### 6. 이메일 설정 (선택사항)

Symphony Mailer를 사용한 SMTP 이메일 설정:

#### 6.1. 로컬 설정 파일 생성

```bash
# 샘플 파일을 복사하여 로컬 설정 생성
cp config/web-local.php.example config/web-local.php
```

#### 6.2. 이메일 설정 구성

`config/web-local.php` 파일을 편집하여 SMTP 설정을 입력:

```php
return [
    'components' => [
        'mailer' => [
            'class' => 'yii\symfonymailer\Mailer',
            'transport' => [
                'scheme' => 'smtp',
                'host' => 'smtp.gmail.com',        // SMTP 서버
                'username' => 'your@gmail.com',    // 이메일 주소
                'password' => 'your-app-password', // 앱 비밀번호
                'port' => 587,
                'encryption' => 'tls',
            ],
            'messageConfig' => [
                'charset' => 'UTF-8',
                'from' => ['noreply@yourdomain.com' => 'Invoice Manager'],
            ],
            'useFileTransport' => false, // 개발시에는 true
        ],
    ],
];
```

#### 6.3. 주요 이메일 제공업체 설정

**Gmail:**
- Host: `smtp.gmail.com`
- Port: `587` (TLS) 또는 `465` (SSL)
- 2단계 인증 활성화 후 앱 비밀번호 생성 필요

**Outlook/Hotmail:**
- Host: `smtp-mail.outlook.com`
- Port: `587` (TLS)

**Yahoo:**
- Host: `smtp.mail.yahoo.com`
- Port: `587` (TLS)

#### 6.4. 보안 주의사항

- `web-local.php` 파일은 `.gitignore`에 포함되어 Git에 업로드되지 않습니다
- 실제 이메일 계정 정보를 안전하게 보관하세요
- 가능한 앱 비밀번호를 사용하세요 (실제 비밀번호 대신)

### 7. 웹서버 설정

DocumentRoot를 `web/` 디렉토리로 설정하고 URL 리라이팅 활성화

#### Apache .htaccess 설정 옵션

프로젝트는 3가지 `.htaccess` 설정을 제공합니다:

**기본 설정 (현재 사용 중)**
```bash
# 단순한 Yii2 라우팅만 제공
web/.htaccess
```

**고급 설정 (보안 강화)**
```bash
# 고급 보안 및 성능 최적화 설정으로 교체
cp web/.htaccess.advanced web/.htaccess
```

**단순 설정 (문제 해결용)**
```bash
# 최소한의 설정 (문제 발생 시 사용)
cp web/.htaccess.simple web/.htaccess
```

**고급 설정 기능:**
- 🔒 보안 헤더 추가 (XSS, 클릭재킹 방지)
- 🚫 민감한 파일 접근 차단
- 📁 디렉토리 브라우징 방지
- 🗜️ 파일 압축 (성능 향상)
- ⚡ 브라우저 캐싱 (로딩 속도 향상)

**필요 Apache 모듈:**
- `mod_headers` (보안 헤더)
- `mod_deflate` (압축)
- `mod_expires` (캐싱)

**모듈 활성화:**
```bash
sudo a2enmod headers deflate expires
sudo systemctl restart apache2
```

### 8. 파일 권한 설정

```bash
# 웹서버 사용자 권한 설정
sudo chown -R daemon:daemon /path/to/invoice-manager
sudo chmod -R 755 /path/to/invoice-manager
sudo chmod -R 777 /path/to/invoice-manager/runtime
sudo chmod -R 777 /path/to/invoice-manager/web/assets
sudo chmod -R 777 /path/to/invoice-manager/web/uploads

# Git 업데이트를 위한 권한 설정
sudo chgrp -R daemon /path/to/invoice-manager/.git
sudo chmod -R g+w /path/to/invoice-manager/.git

# 현재 사용자를 daemon 그룹에 추가
sudo usermod -a -G daemon $(whoami)
```

## 사용자 계정

### 기본 계정

시스템에는 다음 기본 계정들이 제공됩니다:

#### 관리자 계정

- **사용자명**: `admin`
- **비밀번호**: `admin123`
- **권한**: 전체 시스템 관리, 사용자 관리, 시스템 설정

#### 데모 계정

- **사용자명**: `demo`
- **비밀번호**: `demo123`
- **권한**: 제한된 기능, 데모 데이터 관리

### 계정 유형

#### 🔧 관리자 (Admin) - 권한 강화

- 전체 시스템 관리
- 사용자 계정 관리 및 역할 할당
- 사용자 비밀번호 재설정
- 회원가입 기능 활성화/비활성화
- 고급 시스템 설정 관리 (신규)
- 최대 사용자 수 제한 설정 (신규)
- 시스템 유지보수 모드 제어 (신규)

#### 👤 일반 사용자 (User) - 기능 확장

- 개인 회사 관리 (다중 회사 지원)
- 인보이스 및 견적서 관리
- 고객 및 제품 관리 (카테고리 지원)
- 다국어 인터페이스 선택 (신규)
- 다크모드/컴팩트모드 설정 (신규)
- 비밀번호 변경

#### 🎭 데모 사용자 (Demo) - 향상된 체험

- 시스템 체험 및 테스트
- 제한된 기능 사용
- 데모 데이터 초기화 가능
- 다국어 체험 가능 (신규)
- 테마 변경 체험 가능 (신규)
- 비밀번호 변경 불가

## 최신 기능 상세 (2025년 7월)

### 🌍 다국어 시스템

#### 지원 언어
- **English (en-US)**: 영어
- **한국어 (ko-KR)**: 완전한 한국어 지원
- **Español (es-ES)**: 스페인어
- **简体中文 (zh-CN)**: 중국어 간체
- **繁體中文 (zh-TW)**: 중국어 번체

#### 특징
- **실시간 언어 전환**: 페이지 새로고침 없이 즉시 언어 변경
- **회사별 설정**: 각 회사가 독립적으로 언어 선택
- **완전 번역**: 모든 UI 요소, 메뉴, 메시지, 오류 텍스트 번역
- **날짜/시간 현지화**: 언어에 맞는 날짜 및 시간 형식

### 🎨 테마 시스템

#### 다크모드 (Dark Mode)
- **개별 설정**: 각 회사가 독립적으로 다크모드 설정
- **테마 토글**: 상단바에서 원클릭 테마 전환
- **완전한 스타일링**: 모든 UI 요소가 다크모드 지원
- **시각적 일관성**: 전체 인터페이스의 통일된 다크모드 경험

#### 컴팩트 모드 (Compact Mode)
- **공간 효율성**: 네비게이션에서 텍스트를 숨기고 아이콘만 표시
- **툴팁 지원**: 마우스 오버시 기능 설명 툴팁 표시
- **반응형 디자인**: 모든 화면 크기에서 최적화
- **개별 제어**: 회사별로 컴팩트 모드 활성화/비활성화

### 🗂️ 제품 카테고리

#### 기능
- **계층적 분류**: 제품을 체계적으로 분류 관리
- **드래그 앤 드롭**: 직관적인 카테고리 순서 변경
- **AJAX 생성**: 페이지 새로고침 없는 실시간 카테고리 추가
- **상태 관리**: 활성/비활성 상태로 카테고리 제어

#### 사용법
1. 제품 관리 → 카테고리 탭
2. "새 카테고리" 버튼으로 즉시 추가
3. 드래그로 순서 변경
4. 체크박스로 활성/비활성 전환

### 👑 고급 관리자 기능

#### 시스템 설정
- **사용자 제한**: 최대 사용자 수 설정
- **회원가입 제어**: 신규 가입 허용/차단
- **유지보수 모드**: 시스템 점검시 사용자 접근 제한
- **보안 설정**: 비밀번호 정책, 세션 타임아웃

#### 사용자 관리
- **역할 할당**: Admin, User, Demo 역할 세밀한 제어
- **회사 제한**: 사용자별 최대 생성 가능 회사 수
- **계정 상태**: 사용자 계정 활성화/비활성화
- **비밀번호 재설정**: 관리자 권한으로 사용자 비밀번호 초기화

## 핵심 기능 상세

### 📋 인보이스 관리

- **생성 및 편집**: 직관적인 인터페이스로 인보이스 작성
- **PDF 생성**: 전문적인 PDF 인보이스 생성
- **이메일 발송**: 고객에게 직접 이메일 전송
- **상태 관리**: 초안, 발송, 결제 완료 등 상태 추적
- **번호 자동 생성**: 커스터마이즈 가능한 인보이스 번호

### 📄 견적서 관리

- **견적서 생성**: 고객용 견적서 작성
- **인보이스 변환**: 승인된 견적서를 인보이스로 변환
- **유효기간 관리**: 견적서 유효기간 설정
- **PDF 생성**: 견적서 PDF 다운로드

### 👥 고객 관리

- **고객 정보**: 연락처, 주소, 결제 조건 관리
- **고객 히스토리**: 과거 인보이스 및 견적서 기록
- **빠른 검색**: 고객 이름, 이메일로 빠른 검색

### 📦 제품 관리

- **제품 카탈로그**: 제품/서비스 목록 관리
- **가격 설정**: 제품별 가격 및 설명 설정
- **인보이스 연동**: 제품 선택으로 빠른 인보이스 작성

### 🏢 회사 설정

- **회사 정보**: 회사명, 주소, 연락처 관리
- **로고 업로드**: 회사 로고 업로드 및 관리
- **이메일 설정**: 발송자 정보 및 이메일 설정
- **인보이스 설정**: 번호 형식, 결제 조건, 세율 설정
- **언어 설정**: 회사별 인터페이스 언어 선택 (신규)
- **디스플레이 설정**: 다크모드, 컴팩트모드 개별 설정 (신규)
- **PDF 설정**: CJK 폰트 지원 활성화 (신규)

## 관리자 기능

### 👑 향상된 사용자 관리 (2025년 7월 업데이트)

- **사용자 목록**: 모든 사용자 조회 및 관리
- **사용자 생성**: 새 사용자 계정 생성
- **사용자 편집**: 사용자 정보 수정
- **비밀번호 재설정**: 사용자 비밀번호 관리
- **계정 활성화/비활성화**: 사용자 계정 상태 관리
- **역할 관리**: Admin, User, Demo 역할 할당
- **회사 제한**: 사용자별 최대 회사 수 설정

### ⚙️ 고급 시스템 설정 (2025년 7월 추가)

- **회원가입 제어**: 신규 회원가입 허용/차단
- **시스템 파라미터**: 전체 시스템 설정 관리
- **사용자 제한**: 최대 사용자 수 설정
- **유지보수 모드**: 시스템 유지보수 모드 활성화
- **보안 설정**: 비밀번호 최소 길이, 세션 타임아웃
- **이메일 알림**: 시스템 이메일 알림 제어
- **자동 백업**: 시스템 자동 백업 설정

## 기술 스택

### 🚀 백엔드

- **Yii2 Framework**: 최신 PHP MVC 프레임워크
- **MySQL**: 안정적인 데이터베이스 시스템
- **Symfony Mailer**: 현대적인 이메일 전송 시스템
- **TCPDF**: PDF 생성 라이브러리

### 🎨 프론트엔드

- **Bootstrap 4**: 반응형 CSS 프레임워크
- **jQuery**: JavaScript 라이브러리
- **Font Awesome**: 아이콘 라이브러리
- **Custom CSS**: 맞춤형 스타일링

## 데이터베이스 구조

### 테이블 목록 (2025년 7월 업데이트)

- `jdosa_users` - 사용자 계정 정보 (역할, 최대 회사 수 포함)
- `jdosa_companies` - 회사 정보 및 설정 (다크모드, 컴팩트모드, 언어, CJK폰트 설정)
- `jdosa_customers` - 고객 정보
- `jdosa_products` - 제품/서비스 정보 (카테고리 지원)
- `jdosa_product_categories` - 제품 카테고리 관리 (신규)
- `jdosa_invoices` - 인보이스 데이터
- `jdosa_invoice_items` - 인보이스 항목
- `jdosa_estimates` - 견적서 데이터
- `jdosa_estimate_items` - 견적서 항목
- `jdosa_admin_settings` - 관리자 시스템 설정 (신규)

## 보안 기능

### 🔒 인증 및 권한

- **Yii2 인증**: 프레임워크 내장 인증 시스템
- **역할 기반 접근**: 관리자/사용자/데모 역할 분리
- **세션 관리**: 안전한 세션 관리
- **CSRF 보호**: 크로스 사이트 요청 위조 방지

### 🛡️ 데이터 보안

- **데이터 검증**: 모든 입력 데이터 유효성 검사
- **SQL 인젝션 방지**: ORM 기반 안전한 데이터베이스 쿼리
- **파일 업로드 보안**: 안전한 파일 업로드 처리

## 개발 환경

### 디버깅

- **Yii2 Debug Bar**: 개발 환경에서 상세 디버깅 정보
- **로그 시스템**: 체계적인 로그 관리
- **오류 추적**: 상세한 오류 정보 제공

### 개발 도구

- **Gii**: 코드 생성 도구 (개발 환경)
- **Migration**: 데이터베이스 스키마 관리
- **Asset Management**: 정적 자원 관리

## 시스템 업데이트

### 🔄 업데이트 방법

#### 자동 업데이트 스크립트 (권장)

```bash
# 프로젝트 디렉토리에서 실행
./update.sh
```

**업데이트 스크립트 주요 기능:**
- ✅ 자동 백업 생성 (설정 파일 보존)
- ✅ Git 권한 자동 관리
- ✅ Composer 의존성 자동 업데이트
- ✅ 데이터베이스 마이그레이션 자동 실행
- ✅ 로컬 설정 파일 자동 복원 (.htaccess 등)
- ✅ 캐시 자동 정리
- ✅ 권한 자동 복원

#### 수동 업데이트

```bash
# Git 권한 임시 변경
sudo chown -R $(whoami):$(whoami) .git/

# 최신 코드 가져오기
git pull

# 의존성 업데이트
composer install --no-dev --optimize-autoloader

# 데이터베이스 마이그레이션
./yii migrate

# 권한 복원
sudo chown -R daemon:daemon .
sudo chmod -R 755 .
sudo chmod -R 777 runtime/ web/assets/ web/uploads/
```

### ⚠️ 권한 문제 해결

`git pull` 실행 시 "Permission denied" 오류가 발생하는 경우:

#### 방법 1: 그룹 권한 설정 (권장)

```bash
# 현재 사용자를 daemon 그룹에 추가
sudo usermod -a -G daemon $(whoami)

# Git 디렉토리 그룹 권한 설정
sudo chgrp -R daemon .git/
sudo chmod -R g+w .git/

# 안전한 디렉토리로 등록
git config --global --add safe.directory $(pwd)

# 그룹 변경사항 적용 (로그아웃 후 재로그인 또는)
newgrp daemon
```

#### 방법 2: 임시 권한 변경 (즉시 사용)

```bash
# Git 권한 임시 변경
sudo chown -R $(whoami):$(whoami) .git/

# Git pull 실행
git pull

# 권한 복원
sudo chown -R daemon:daemon .
```

### 🔀 Git 충돌 해결

`git pull` 실행 시 파일 충돌이 발생하는 경우:

#### 충돌 유형 1: 로컬 변경사항과 원격 변경사항 충돌

```bash
# 로컬 변경사항 백업
git stash push -m "Local changes backup"

# 새로운 파일들 임시 백업
mkdir -p temp_backup
find . -name "*.php" -path "./controllers/*" -o -path "./models/*" -o -path "./views/*" -o -path "./migrations/*" | \
    grep -E "(Admin|Demo|ChangePassword)" | \
    xargs -I {} cp --parents {} temp_backup/ 2>/dev/null || true

# 새로운 파일들 제거
git clean -fd

# Git pull 실행
git pull

# 백업된 파일들 복원
cp -r temp_backup/* . 2>/dev/null || true
rm -rf temp_backup

# 스태시 복원 (선택사항)
git stash pop
```

#### 충돌 유형 2: 완전히 새로 시작 (주의: 모든 로컬 변경사항 손실)

```bash
# 모든 로컬 변경사항 무시
git reset --hard HEAD
git clean -fd
git pull
```

### 🔧 로컬 설정 보존

#### .htaccess 설정 보존

**문제**: 업데이트 후 `.htaccess.advanced` 설정이 기본 설정으로 되돌아감

**해결**: `update.sh` 스크립트가 자동으로 처리

```bash
# .htaccess.advanced 설정 적용
cp web/.htaccess.advanced web/.htaccess

# 업데이트 실행 (설정이 자동으로 보존됨)
./update.sh
```

**자동 보존 과정:**
1. 업데이트 전: 현재 `.htaccess` 자동 백업
2. Git Pull: 원격 저장소 파일로 덮어쓰기
3. 업데이트 후: 백업된 `.htaccess` 자동 복원

#### 기타 로컬 설정 보존

**자동 백업 파일들:**
- `config/db.php` - 데이터베이스 설정
- `config/web.php` - 웹 애플리케이션 설정
- `web/.htaccess` - Apache 설정

**백업 위치:** `backups/` 디렉토리

### 🔧 Composer 문제 해결

업데이트 후 Composer 오류가 발생하는 경우:

#### 문제: "Required package is not present in the lock file"

```bash
# 방법 1: 자동 해결 스크립트 사용
./fix_composer.sh

# 방법 2: 수동 해결
# Lock 파일 백업
cp composer.lock backups/composer.lock.backup.$(date +%Y%m%d_%H%M%S)

# 의존성 재설치
composer clear-cache
rm -f composer.lock
rm -rf vendor/
composer install --no-dev --optimize-autoloader
```

#### 문제: "Lock file is not up to date"

```bash
# 캐시 정리 후 업데이트
composer clear-cache
composer update --no-dev --optimize-autoloader
```

## 지원 및 문의

### 🐛 버그 리포트

GitHub Issues를 통해 버그 리포트나 기능 요청을 제출해주세요.

### 📖 문서

- [Yii2 공식 문서](https://www.yiiframework.com/doc/guide/2.0/en)
- [Bootstrap 4 문서](https://getbootstrap.com/docs/4.6/getting-started/introduction/)

## 라이선스

이 프로젝트는 MIT 라이선스 하에 배포됩니다.

## 📋 변경사항 (2025년 7월)

### 🆕 신규 기능
- **다국어 지원**: 5개 언어 완전 지원
- **컴팩트 모드**: 아이콘 전용 네비게이션 모드
- **제품 카테고리**: 완전한 카테고리 관리 시스템
- **고급 관리자 기능**: 시스템 설정 및 사용자 관리 강화
- **CJK 폰트 지원**: 아시아 언어 PDF 지원

### 🔧 개선사항
- **다크모드 향상**: 더 완전한 다크모드 지원
- **UI/UX 개선**: Liquid Glass 디자인 적용
- **보안 강화**: 역할 기반 접근 제어 향상
- **성능 최적화**: AJAX 기반 실시간 업데이트
- **접근성 향상**: 툴팁 및 키보드 네비게이션 지원

### 🗄️ 데이터베이스 변경
- 새로운 테이블: `jdosa_product_categories`, `jdosa_admin_settings`
- 업데이트된 테이블: `jdosa_companies` (언어, 테마 설정), `jdosa_users` (역할, 제한)
- 새로운 필드: 다국어, 테마, 카테고리, 관리자 설정 관련

---

**Invoice Manager** - 효율적이고 현대적인 다국어 인보이스 관리 솔루션
