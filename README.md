# Yii2 Invoice Manager 서버 설정 가이드

이 가이드는 Yii2 기반 Invoice Manager를 신규 서버에 설정하기 위한 과정을 설명합니다.

## 시스템 요구사항

- PHP 7.4 이상 (PHP 8.1 권장)
- MySQL 5.7 이상 또는 MariaDB 10.2 이상
- Composer
- Apache 또는 Nginx 웹서버
- Git

## 1. 서버 환경 준비

### Ubuntu/Debian 시스템의 경우:

```bash
# 시스템 업데이트
sudo apt update && sudo apt upgrade -y

# PHP 및 필요한 확장 설치
sudo apt install -y php8.1 php8.1-cli php8.1-fpm php8.1-mysql php8.1-xml php8.1-mbstring php8.1-curl php8.1-zip php8.1-gd php8.1-intl php8.1-bcmath

# MySQL 설치
sudo apt install -y mysql-server

# Apache 또는 Nginx 설치
sudo apt install -y apache2
# 또는
sudo apt install -y nginx

# Git 설치
sudo apt install -y git

# Composer 설치
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer
```

### CentOS/RHEL 시스템의 경우:

```bash
# EPEL 및 Remi 저장소 추가
sudo yum install -y epel-release
sudo yum install -y https://rpms.remirepo.net/enterprise/remi-release-8.rpm

# PHP 8.1 활성화 및 설치
sudo yum module enable php:remi-8.1 -y
sudo yum install -y php php-cli php-fpm php-mysql php-xml php-mbstring php-curl php-zip php-gd php-intl php-bcmath

# MySQL 설치
sudo yum install -y mysql-server

# Apache 설치
sudo yum install -y httpd

# Git 설치
sudo yum install -y git

# Composer 설치
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer
```

## 2. 데이터베이스 설정

```bash
# MySQL 서비스 시작 및 활성화
sudo systemctl start mysql
sudo systemctl enable mysql

# MySQL 보안 설정
sudo mysql_secure_installation

# MySQL 접속
mysql -u root -p
```

MySQL 콘솔에서 다음 명령 실행:

```sql
-- 데이터베이스 생성
CREATE DATABASE invoice_manager CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- 사용자 생성 및 권한 부여
CREATE USER 'invoice_user'@'localhost' IDENTIFIED BY 'strong_password_here';
GRANT ALL PRIVILEGES ON invoice_manager.* TO 'invoice_user'@'localhost';
FLUSH PRIVILEGES;

-- 종료
EXIT;
```

**참고:** 현재 시스템에서는 `bitnami_wordpress` 데이터베이스와 `bn_wordpress` 사용자를 사용하고 있습니다.

## 3. Yii2 프로젝트 설정

**기존 프로젝트 사용:** 이미 완성된 Yii2 Invoice Manager 프로젝트를 사용합니다.

```bash
# 프로젝트 디렉토리로 이동 (예: /var/www/html)
cd /var/www/html

# Git에서 프로젝트 클론
git clone [repository-url] invoice-manager

# 프로젝트 디렉토리로 이동
cd invoice-manager

# Composer 의존성 설치
composer install

# 웹서버 사용자에게 권한 부여
sudo chown -R www-data:www-data /var/www/html/invoice-manager
sudo chmod -R 755 /var/www/html/invoice-manager
```

## 4. Yii2 설정

### 로컬 설정 파일 생성:

프로젝트에는 로컬 환경별 설정을 위한 예제 파일들이 포함되어 있습니다. 다음 파일들을 복사하여 로컬 설정을 생성하세요:

```bash
# 데이터베이스 설정 파일 복사
cp config/db-local.php.example config/db-local.php

# 사용자 파라미터 설정 파일 복사
cp config/params-local.php.example config/params-local.php
```

### 데이터베이스 연결 설정:

```bash
# 데이터베이스 설정 파일 편집
nano config/db-local.php
```

다음 내용으로 수정:

```php
<?php

return [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=localhost;dbname=invoice_manager',
    'username' => 'invoice_user',
    'password' => 'strong_password_here',
    'charset' => 'utf8mb4',
];
```

### 사용자 파라미터 설정:

```bash
# 사용자 파라미터 설정 파일 편집
nano config/params-local.php
```

다음 내용으로 수정:

```php
<?php

return [
    'senderEmail' => 'noreply@example.com',
    'senderName' => 'Your Name',
    'bccEmail' => 'bcc@example.com',
];
```

**중요:** `config/db-local.php`와 `config/params-local.php` 파일은 로컬 환경에서만 사용되며, Git에 추가되지 않습니다. 서버별로 다른 설정이 필요한 경우 각 서버에서 개별적으로 생성해야 합니다.

### 쿠키 검증 키 설정:

```bash
# 설정 파일 편집
nano config/web.php
```

`cookieValidationKey`를 랜덤 문자열로 설정:

```php
'cookieValidationKey' => 'your-secret-key-here-32-characters-long',
```

## 5. 데이터베이스 마이그레이션 실행

**새로운 통합 마이그레이션 파일 사용:**

```bash
# 마이그레이션 실행 (모든 테이블 생성)
./yii migrate

# 마이그레이션 실행 확인
./yii migrate/history
```

**포함된 테이블:**

- `jdosa_companies` - 회사 정보 및 설정
- `jdosa_customers` - 고객 정보 및 연락처
- `jdosa_products` - 제품/서비스 정보
- `jdosa_invoices` - 인보이스 정보 (배송, 할인, 결제 조건 등)
- `jdosa_invoice_items` - 인보이스 항목
- `jdosa_estimates` - 견적서 정보
- `jdosa_estimate_items` - 견적서 항목
- `jdosa_payments` - 결제 정보

## 6. 웹서버 설정

### Apache 설정:

```bash
# 가상 호스트 설정 파일 생성
sudo nano /etc/apache2/sites-available/invoice-manager.conf
```

다음 내용 추가:

```apache
<VirtualHost *:80>
    ServerName invoice-manager.yourdomain.com
    DocumentRoot /opt/bitnami/apps/jdosa/invoice-manager/web

    <Directory /opt/bitnami/apps/jdosa/invoice-manager/web>
        RewriteEngine on
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteRule . index.php

        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/invoice-manager_error.log
    CustomLog ${APACHE_LOG_DIR}/invoice-manager_access.log combined
</VirtualHost>
```

```bash
# 사이트 활성화
sudo a2ensite invoice-manager.conf
sudo a2enmod rewrite
sudo systemctl reload apache2
```

### Nginx 설정:

```bash
# 가상 호스트 설정 파일 생성
sudo nano /etc/nginx/sites-available/invoice-manager
```

다음 내용 추가:

```nginx
server {
    listen 80;
    server_name invoice-manager.yourdomain.com;
    root /var/www/html/invoice-manager/web;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$args;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(ht|svn|git) {
        deny all;
    }
}
```

```bash
# 사이트 활성화
sudo ln -s /etc/nginx/sites-available/invoice-manager /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

## 7. PHP-FPM 설정 (Nginx 사용시)

```bash
# PHP-FPM 시작 및 활성화
sudo systemctl start php8.1-fpm
sudo systemctl enable php8.1-fpm

# PHP 설정 최적화
sudo nano /etc/php/8.1/fpm/pool.d/www.conf
```

다음 설정들을 확인/수정:

```ini
user = www-data
group = www-data
listen = /var/run/php/php8.1-fpm.sock
listen.owner = www-data
listen.group = www-data
```

## 8. 보안 설정

```bash
# 파일 권한 재설정
sudo chown -R www-data:www-data /var/www/html/invoice-manager
sudo find /home/bitnami/apps/jdosa/invoice-manager -type f -exec chmod 644 {} \;
sudo find /home/bitnami/apps/jdosa/invoice-manager -type d -exec chmod 755 {} \;
sudo chmod -R 777 /home/bitnami/apps/jdosa/invoice-manager/runtime
sudo chmod -R 777 /home/bitnami/apps/jdosa/invoice-manager/web/assets

# 방화벽 설정 (필요한 경우)
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw allow 22/tcp
sudo ufw enable
```

## 9. SSL 인증서 설정 (Let's Encrypt)

```bash
# Certbot 설치
sudo apt install certbot python3-certbot-apache  # Apache용
# 또는
sudo apt install certbot python3-certbot-nginx   # Nginx용

# SSL 인증서 발급
sudo certbot --apache -d invoice-manager.yourdomain.com  # Apache용
# 또는
sudo certbot --nginx -d invoice-manager.yourdomain.com   # Nginx용

# 자동 갱신 설정
sudo crontab -e
```

크론탭에 다음 라인 추가:

```cron
0 12 * * * /usr/bin/certbot renew --quiet
```

## 10. 추가 Composer 패키지 설치

```bash
# 프로젝트 디렉토리에서
cd /var/www/html/invoice-manager

# PDF 생성을 위한 패키지 (이미 설치됨)
composer require tecnickcom/tcpdf

# 이메일 발송을 위한 패키지 (이미 설치됨)
composer require swiftmailer/swiftmailer

# 날짜 처리를 위한 패키지 (이미 설치됨)
composer require nesbot/carbon
```

**참고:** 현재 프로젝트에는 이미 필요한 패키지들이 설치되어 있습니다.

## 11. 서비스 시작 및 상태 확인

```bash
# 모든 서비스 시작
sudo systemctl start mysql
sudo systemctl start apache2  # 또는 nginx
sudo systemctl start php8.1-fpm  # Nginx 사용시

# 서비스 자동 시작 설정
sudo systemctl enable mysql
sudo systemctl enable apache2  # 또는 nginx
sudo systemctl enable php8.1-fpm  # Nginx 사용시

# 서비스 상태 확인
sudo systemctl status mysql
sudo systemctl status apache2  # 또는 nginx
sudo systemctl status php8.1-fpm  # Nginx 사용시
```

## 12. 테스트 및 확인

```bash
# 브라우저에서 접속 테스트
curl -I http://invoice-manager.yourdomain.com

# 데이터베이스 연결 테스트
./yii migrate/history

# 로그 확인
tail -f runtime/logs/app.log
```

## 문제 해결

### 일반적인 문제들:

1. **권한 문제**: `sudo chmod -R 777 runtime web/assets`
2. **데이터베이스 연결 오류**: `config/db.php` 설정 확인
3. **웹서버 403 오류**: 가상 호스트 설정 및 DocumentRoot 확인
4. **PHP 오류**: `/var/log/apache2/error.log` 또는 `/var/log/nginx/error.log` 확인

### 로그 파일 위치:

- Apache: `/var/log/apache2/`
- Nginx: `/var/log/nginx/`
- PHP-FPM: `/var/log/php8.1-fpm.log`
- MySQL: `/var/log/mysql/`
- Yii2 App: `runtime/logs/app.log`

## 애플리케이션 특징

### 주요 기능:

1. **회사 관리** - 회사 정보, 로고, 이메일 설정
2. **고객 관리** - 고객 정보, 연락처, 결제 조건
3. **제품 관리** - 제품/서비스 정보, 가격 설정
4. **인보이스 관리** - 인보이스 생성, 편집, 발송
5. **견적서 관리** - 견적서 생성, 인보이스 변환
6. **결제 관리** - 결제 기록 관리
7. **PDF 생성** - 인보이스/견적서 PDF 생성
8. **이메일 발송** - SMTP2GO 및 SwiftMailer 지원

### 접근 URL:

- 메인 대시보드: `/`
- 인보이스 관리: `/invoice`
- 견적서 관리: `/estimate`
- 로그인: `/site/login`
- 회사 설정: `/company/settings`
- 고객 관리: `/customer`
- 제품 관리: `/product`

### 기본 로그인:

- 사용자명: `admin` / 비밀번호: `admin123`
- 비밀번호 변경은 `models/User.php` 파일을 수정하여 변경할 수 있습니다.

이제 Yii2 기반 Invoice Manager가 완전히 설정되었습니다.
