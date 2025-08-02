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

### 🧮 US Sales Tax 자동 계산 (2025년 7월 추가)

- **완전한 세율 데이터베이스**: 50개 주 + ZIP 코드별 정확한 세율
- **Economic Nexus 지원**: 각 주별 경제적 연결점 임계값 확인
- **관리자 전용**: Admin 권한 사용자만 세율 관리 가능
- **CSV 가져오기**: 외부 소스에서 세율 데이터 일괄 업로드
- **실시간 조회**: ZIP 코드 입력으로 즉시 세율 확인
- **이력 관리**: 세율 변경 이력 및 유효기간 추적

### 🤖 AI Helper (2025년 8월 추가)

- **인보이스 설명 생성**: 제품/서비스명으로 전문적인 인보이스 항목 설명 자동 생성
- **결제 조건 제안**: 고객 유형과 금액에 맞는 적절한 결제 조건 자동 제안
- **가격 제안**: 시장 데이터 기반의 제품/서비스 가격 제안
- **작업 범위 생성**: 서비스 키워드로 전문적인 작업 범위 및 세부 항목 자동 생성
- **관리자 설정**: AI 모델 선택으로 성능과 비용 최적화
- **결제 기반 권한**: 플랜 기반 AI 기능 사용 제어 (무료/유료 플랜 차별화)

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

### 🤖 AI Helper (2025년 8월 추가)

#### 주요 기능

- **인보이스 설명 생성**: 제품/서비스명만으로 전문적인 인보이스 항목 설명 자동 생성
- **결제 조건 제안**: 고객 유형(개인/소기업/대기업)과 금액에 맞는 적절한 결제 조건 자동 제안
- **가격 제안**: 시장 데이터 기반의 제품/서비스 가격 제안
- **작업 범위 생성**: 서비스 키워드로 전문적인 작업 범위 및 세부 항목 자동 생성
- **고객 맞춤형 제안**: 기존 고객 정보를 기반으로 맞춤형 설명 제안

#### 기술 사양

- **OpenRouter 통합**: Anthropic Claude, OpenAI GPT, Google Gemini 등 다양한 AI 모델 지원
- **관리자 설정**: 시스템 관리자가 기본 AI 모델 선택 가능
- **플랜 기반 접근**: 무료/표준/프로 플랜별 AI 기능 사용 권한 제어
- **보안 설계**: API 키는 서버측에서 안전하게 관리

#### 활용 방법

1. 인보이스 생성/편집 화면에서 AI Helper 아이콘 클릭
2. 제품/서비스명 입력
3. AI가 생성한 설명 중 원하는 것을 선택하거나 수정
4. 자동 생성된 결제 조건이나 가격 제안 검토 및 적용

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
- **AI Helper 통합**: 인보이스 항목 설명 자동 생성 (2025년 8월 추가)

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

## 🧮 US Sales Tax 자동 계산 시스템 (2025년 7월 추가)

### 📋 주요 기능

#### 🗄️ 완전한 세율 데이터베이스

- **50개 주 기본 세율**: 각 주별 기본 세율 및 평균 세율
- **ZIP 코드별 상세 세율**: 정확한 지역별 세율 지원
- **Economic Nexus 확인**: 각 주별 경제적 연결점 임계값 자동 확인
- **세율 분해**: 주/카운티/시/특별구역 세율 개별 관리

#### 🔐 Admin 전용 관리 시스템

- **관리자 권한 필요**: Admin Role 사용자만 세율 관리 접근 가능
- **Admin Dashboard 통합**: 관리자 패널에서 바로 접근
- **완전한 CRUD**: 세율 생성, 조회, 수정, 삭제 지원
- **벌크 작업**: 다중 선택으로 일괄 활성화/비활성화/삭제

#### 📊 실시간 세율 조회

- **ZIP 코드 검색**: 우편번호 입력으로 즉시 세율 확인
- **AJAX 기반**: 페이지 새로고침 없는 실시간 조회
- **상세 정보**: 세율 분해, 유효기간, 데이터 소스 표시
- **빠른 필터링**: 주/ZIP 코드/데이터 소스별 필터링

#### 📤 CSV 데이터 가져오기

- **파일 업로드**: CSV 파일을 통한 대량 세율 데이터 입력
- **샘플 다운로드**: 정확한 형식의 샘플 CSV 제공
- **데이터 검증**: 업로드 시 자동 유효성 검사
- **진행 상황**: 실시간 업로드 진행 상황 표시

#### 📈 통계 대시보드

- **전체 현황**: 총 관할지역, 활성 상태, 커버리지 통계
- **주별 분포**: 각 주별 관할지역 수 및 비율
- **데이터 소스 분석**: 소스별 데이터 분포 현황
- **최근 업데이트**: 최근 변경된 세율 이력

### 🛠️ 콘솔 명령어

#### 기본 세율 생성

```bash
# 50개 주 기본 세율 생성 (무료)
./yii tax-rate/seed-basic-rates

# 계산기 기반 세율 업데이트
./yii tax-rate/update-from-calculator
```

#### CSV 파일 관리

```bash
# CSV 파일에서 세율 가져오기
./yii tax-rate/import-csv /path/to/rates.csv

# 샘플 CSV 파일 생성
./yii tax-rate/generate-sample-csv sample_rates.csv
```

#### 시스템 관리

```bash
# 세율 통계 확인
./yii tax-rate/stats

# 특정 ZIP 코드 테스트
./yii tax-rate/test 90210

# 오래된 세율 정리
./yii tax-rate/cleanup-old-rates

# 세율 검증 및 만료 확인
./yii tax-rate/verify-rates
```

### 💾 데이터 소스 및 가져오기

#### 🆓 무료 소스

**1. Avalara (기본 주별 세율)**

- **URL**: https://www.avalara.com/taxrates/en/download-tax-tables.html
- **형식**: CSV
- **커버리지**: 50개 주 기본 세율
- **업데이트**: 월별
- **제한**: ZIP 코드별 상세 세율 없음

**2. 정부 소스 (각 주 세무청)**

- **워싱턴주**: https://dor.wa.gov/taxes-rates/sales-use-tax-rates/downloadable-database
- **캘리포니아, 뉴욕 등**: 각 주 세무청 공식 사이트
- **제한**: 주별로 개별 다운로드 필요

**3. GitHub 오픈소스**

- **URL**: https://github.com/dirk/sales_tax
- **URL**: https://github.com/MirzaAreebBaig/Woocommerce-US-ZipCodes-TaxRates
- **제한**: 데이터 정확성 및 최신성 확인 필요

#### 💰 유료 상용 소스 (정확한 ZIP 코드별 세율)

**1. Sales Tax Handbook** ⭐ 추천

- **URL**: https://www.salestaxhandbook.com/data
- **가격**:
  - 단일 주: $34.99 (일회성)
  - 전체 50개 주: $119.99 (일회성)
  - 월별 구독: $19.99/월 (단일 주), $59.99/월 (전체)
- **형식**: CSV, Excel
- **특징**: ZIP 코드별 상세 세율, 월별 업데이트

**2. Zip2Tax**

- **URL**: https://www.zip2tax.com/products/state-tax-rate-table
- **가격**: 구독 또는 일회성 구매
- **형식**: 다양한 CSV 형식 제공
- **특징**: API도 함께 제공

**3. Sales Tax USA (WooCommerce용)**

- **URL**: https://salestaxusa.com/woocommerce-tax-rates-csv/
- **가격**: 일회성 구매
- **형식**: WooCommerce 호환 CSV
- **특징**: 즉시 다운로드 가능

#### 🔌 API 기반 실시간 소스

**1. TaxJar API**

- **URL**: https://developers.taxjar.com/api/reference/
- **가격**: 유료 API 서비스
- **특징**: 실시간 세율 조회, JSON 응답

**2. Avalara API**

- **URL**: Avalara AvaTax API
- **가격**: 유료 API 서비스
- **특징**: 기업급 세무 솔루션

### 🎯 권장 사용 시나리오

#### 개발/테스트 단계

1. **기본 세율로 시작**:

   ```bash
   ./yii tax-rate/seed-basic-rates
   ```

2. **샘플 데이터 테스트**:
   - Admin Panel > Tax Management > Import Tax Rates
   - "Download Sample CSV" 버튼 클릭
   - 다운로드된 파일을 바로 import

#### 프로덕션 준비

1. **정확한 데이터 구매**: Sales Tax Handbook ($119.99 일회성)
2. **ZIP 코드별 정확한 세율 확보**
3. **월별 업데이트 구독 고려**

#### 대기업/고량 처리

1. **TaxJar 또는 Avalara API 연동**
2. **실시간 세율 조회**
3. **자동 업데이트 구현**

### 📋 CSV 파일 형식

#### 필수 컬럼

```csv
zip_code,state_code,combined_rate
90210,CA,9.5000
10001,NY,8.2500
```

#### 전체 컬럼 (권장)

```csv
zip_code,state_code,state_name,county_name,city_name,tax_region_name,combined_rate,state_rate,county_rate,city_rate,special_rate,estimated_population
90210,CA,California,Los Angeles,Beverly Hills,Beverly Hills Tax Region,9.5000,6.0000,1.0000,2.5000,0.0000,34000
10001,NY,New York,New York,New York,Manhattan Tax Region,8.2500,4.0000,2.2500,2.0000,0.0000,1600000
```

### 🗄️ 데이터베이스 구조

#### 새로운 테이블

- `jdosa_tax_jurisdictions` - ZIP 코드별 세율 정보
- `jdosa_tax_rates_history` - 세율 변경 이력

#### 기존 테이블 확장

- `jdosa_companies` - 세금 설정 (주 코드, ZIP 코드, 자동 계산 설정)
- `jdosa_customers` - 고객 세금 정보 (면세 여부, 증명서)
- `jdosa_invoices` - 세금 계산 상세 정보
- `jdosa_invoice_items` - 아이템별 세금 카테고리

### 🔧 Administration

#### 관리자 패널 접근

1. **Admin 계정으로 로그인**
2. **Admin Panel > Tax Management**
3. **세율 목록, 통계, 가져오기 메뉴 사용**

#### 주요 관리 기능

- **세율 관리**: 개별 세율 생성, 수정, 삭제
- **벌크 작업**: 다중 선택으로 일괄 작업
- **데이터 가져오기**: CSV 파일 업로드
- **통계 확인**: 전체 시스템 현황 및 분석
- **데이터 내보내기**: 현재 세율 데이터 CSV 다운로드

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
- **mPDF**: PDF 생성 라이브러리
- **OpenRouter API**: AI Helper 기능을 위한 외부 API 통합

### 🎨 프론트엔드

- **Bootstrap 4**: 반응형 CSS 프레임워크
- **jQuery**: JavaScript 라이브러리
- **Font Awesome**: 아이콘 라이브러리
- **Custom CSS**: 맞춤형 스타일링

## 데이터베이스 구조

### 테이블 목록 (2025년 8월 업데이트)

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
- `jdosa_plans` - 구독 플랜 정보 (AI Helper 권한 포함)
- `jdosa_user_subscriptions` - 사용자 구독 정보

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

#### 충돌 유형 1-1: 스태시 복원 충돌 해결 ⚠️

`update.sh` 실행 중 **"스태시 복원 중 충돌이 발생했습니다"** 오류가 나타나는 경우:

##### 해결 방법 1: 현재 변경사항 유지 (권장)

```bash
# 현재 작업 디렉토리의 변경사항을 우선시 (로컬 설정 보존)
git checkout --ours .

# 스태시 제거
git stash drop

# 상태 확인
git status

# 필요시 변경사항 스테이징
git add .
```

##### 해결 방법 2: 스태시된 변경사항 적용

```bash
# 스태시에 저장된 이전 변경사항을 적용
git checkout --theirs .

# 변경사항 스테이징
git add .

# 스태시 정리
git stash drop
```

##### 해결 방법 3: 수동 병합 (고급 사용자)

```bash
# 충돌 파일 직접 편집 (<<<< ==== >>>> 마커 해결)
nano config/web.php

# 수정 완료 후 스테이징
git add config/web.php

# 스태시 정리
git stash drop
```

##### 💡 권장 해결 순서

```bash
# 1. 현재 변경사항 유지 (가장 안전)
git checkout --ours .
git stash drop

# 2. 상태 확인 및 정리
git add .
git status

# 3. 업데이트 스크립트 재실행 (필요시)
./update.sh
```

**이 방법의 장점:**

- ✅ 로컬 이메일 설정 (`web-local.php`) 보존
- ✅ 사용자 정의 설정 유지
- ✅ 충돌 즉시 해결
- ✅ 업데이트 스크립트 계속 진행 가능

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
- **US Sales Tax 시스템**: 완전한 미국 세율 자동 계산 시스템 구축

### 🗄️ 데이터베이스 변경

- 새로운 테이블: `jdosa_product_categories`, `jdosa_admin_settings`, `jdosa_tax_jurisdictions`, `jdosa_tax_rates_history`
- 업데이트된 테이블: `jdosa_companies` (언어, 테마, 세금 설정), `jdosa_users` (역할, 제한), `jdosa_customers` (세금 정보), `jdosa_invoices` (세금 상세), `jdosa_invoice_items` (세금 카테고리)
- 새로운 필드: 다국어, 테마, 카테고리, 관리자 설정, US Sales Tax 관련

---

**Invoice Manager** - 효율적이고 현대적인 다국어 인보이스 관리 솔루션
