# Invoice Manager - Yii2 기반 인보이스 관리 시스템

현대적인 Yii2 프레임워크를 기반으로 한 멀티유저 인보이스 관리 시스템입니다.

## 주요 특징

### 🏢 멀티유저 지원
- **관리자 시스템**: 완전한 사용자 관리 및 시스템 설정
- **일반 사용자**: 개인 회사 및 인보이스 관리
- **데모 사용자**: 제한된 기능으로 시스템 체험 가능

### 📊 핵심 기능
- **인보이스 관리**: 생성, 편집, PDF 생성, 이메일 발송
- **견적서 관리**: 견적서 생성 및 인보이스 변환
- **고객 관리**: 고객 정보 및 연락처 관리
- **제품 관리**: 제품/서비스 카탈로그 관리
- **회사 설정**: 회사 정보, 로고, 이메일 설정

### 💌 이메일 시스템
- **Symfony Mailer**: 최신 이메일 전송 시스템
- **파일 기반 전송**: 개발 환경에서 이메일 파일 저장
- **SMTP2GO 통합**: 프로덕션 환경에서 안정적인 이메일 전송

### 🎨 사용자 인터페이스
- **반응형 디자인**: 모든 디바이스에서 완벽 호환
- **다크모드 지원**: 라이트/다크 모드 전환 가능
- **플로팅 네비게이션**: 스크롤 고정 네비게이션 바
- **현대적 UI**: Bootstrap 4 기반 세련된 디자인

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

### 6. 웹서버 설정
DocumentRoot를 `web/` 디렉토리로 설정하고 URL 리라이팅 활성화

### 7. 파일 권한 설정
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
- **비밀번호**: `admin123`
- **권한**: 제한된 기능, 데모 데이터 관리

### 계정 유형

#### 🔧 관리자 (Admin)
- 전체 시스템 관리
- 사용자 계정 관리
- 사용자 비밀번호 재설정
- 회원가입 기능 활성화/비활성화
- 시스템 설정 관리

#### 👤 일반 사용자 (User)
- 개인 회사 관리
- 인보이스 및 견적서 관리
- 고객 및 제품 관리
- 비밀번호 변경

#### 🎭 데모 사용자 (Demo)
- 시스템 체험 및 테스트
- 제한된 기능 사용
- 데모 데이터 초기화 가능
- 비밀번호 변경 불가

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

## 관리자 기능

### 👑 사용자 관리
- **사용자 목록**: 모든 사용자 조회 및 관리
- **사용자 생성**: 새 사용자 계정 생성
- **사용자 편집**: 사용자 정보 수정
- **비밀번호 재설정**: 사용자 비밀번호 관리
- **계정 활성화/비활성화**: 사용자 계정 상태 관리

### ⚙️ 시스템 설정
- **회원가입 제어**: 신규 회원가입 허용/차단
- **시스템 파라미터**: 전체 시스템 설정 관리

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

### 테이블 목록
- `jdosa_users` - 사용자 계정 정보
- `jdosa_companies` - 회사 정보 및 설정
- `jdosa_customers` - 고객 정보
- `jdosa_products` - 제품/서비스 정보
- `jdosa_invoices` - 인보이스 데이터
- `jdosa_invoice_items` - 인보이스 항목
- `jdosa_estimates` - 견적서 데이터
- `jdosa_estimate_items` - 견적서 항목
- `jdosa_admin_settings` - 관리자 시스템 설정

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

## 지원 및 문의

### 🐛 버그 리포트
GitHub Issues를 통해 버그 리포트나 기능 요청을 제출해주세요.

### 📖 문서
- [Yii2 공식 문서](https://www.yiiframework.com/doc/guide/2.0/en)
- [Bootstrap 4 문서](https://getbootstrap.com/docs/4.6/getting-started/introduction/)

## 라이선스

이 프로젝트는 MIT 라이선스 하에 배포됩니다.

---

**Invoice Manager** - 효율적이고 현대적인 인보이스 관리 솔루션