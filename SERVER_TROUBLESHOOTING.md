# 서버 404 오류 해결 가이드

## 문제 상황

- 브라우저 콘솔에서 다음과 같은 404 오류 발생:
  - yii.js (404 Not Found)
  - jquery.js (404 Not Found)
  - site.css (404 Not Found)
  - bootstrap.css (404 Not Found)
  - bootstrap.bundle.js (404 Not Found)

## 해결 단계

### 1단계: 서버 접속 및 기본 정보 확인

SSH로 서버에 접속한 후 다음 명령어를 실행해주세요:

```bash
# 현재 디렉토리 확인
pwd

# 웹 디렉토리로 이동 (일반적으로 /var/www/html 또는 /var/www/invoice-manager)
cd /var/www/html
# 또는
cd /var/www/invoice-manager

# 파일 구조 확인
ls -la
```

**응답 필요**: 현재 디렉토리 경로와 파일 목록을 알려주세요.

---

### 2단계: 웹 자산 디렉토리 확인

```bash
# web 디렉토리 확인
ls -la web/

# assets 디렉토리 확인
ls -la web/assets/

# CSS 파일 확인
ls -la web/css/
```

**응답 필요**: 각 디렉토리의 파일 목록과 권한을 알려주세요.

---

### 3단계: 자산 캐시 정리

```bash
# 기존 자산 캐시 삭제
rm -rf web/assets/*

# 런타임 캐시 정리
rm -rf runtime/cache/*
rm -rf runtime/debug/*
```

**응답 필요**: 명령어 실행 결과를 알려주세요 (오류 메시지가 있다면 포함).

---

### 4단계: 권한 설정 확인

```bash
# 웹 디렉토리 권한 확인
ls -la web/

# 필요시 권한 설정 (웹서버 사용자에 따라 다름)
# Apache의 경우:
sudo chown -R daemon:daemon web/
sudo chmod -R 755 web/

# Nginx의 경우:
sudo chown -R nginx:nginx web/
sudo chmod -R 755 web/

# 또는 일반적인 설정:
sudo chown -R apache:apache web/
sudo chmod -R 755 web/
```

**응답 필요**: 웹서버 종류(Apache/Nginx)와 권한 설정 결과를 알려주세요.

---

### 5단계: 웹서버 설정 확인

```bash
# Apache 설정 확인
sudo apache2ctl -t
# 또는
sudo httpd -t

# Nginx 설정 확인
sudo nginx -t

# 웹서버 재시작
sudo systemctl restart apache2
# 또는
sudo systemctl restart nginx
# 또는
sudo systemctl restart httpd
```

**응답 필요**: 웹서버 테스트 결과와 재시작 결과를 알려주세요.

---

### 6단계: .htaccess 파일 확인

```bash
# .htaccess 파일 확인
cat web/.htaccess

# 없다면 생성
nano web/.htaccess
```

**응답 필요**: .htaccess 파일이 있는지, 내용은 무엇인지 알려주세요.

---

### 7단계: PHP 설정 확인

```bash
# PHP 버전 확인
php -v

# Composer 의존성 확인
composer install --no-dev

# 또는 업데이트
composer update --no-dev
```

**응답 필요**: PHP 버전과 Composer 실행 결과를 알려주세요.

---

### 8단계: 로그 확인

```bash
# 웹서버 에러 로그 확인
sudo tail -f /var/log/apache2/error.log
# 또는
sudo tail -f /var/log/nginx/error.log

# PHP 에러 로그 확인
sudo tail -f /var/log/php_errors.log
# 또는
sudo tail -f /var/log/php/error.log

# 애플리케이션 로그 확인
tail -f runtime/logs/app.log
```

**응답 필요**: 발견된 에러 메시지를 알려주세요.

---

### 9단계: 브라우저에서 직접 파일 접근 테스트

브라우저에서 다음 URL에 직접 접근해보세요:

- `https://invoice.jdosa.com/css/site.css`
- `https://invoice.jdosa.com/assets/`

**응답 필요**: 각 URL의 접근 결과를 알려주세요.

---

## 추가 체크리스트

### 서버 환경 정보 수집

```bash
# 시스템 정보
uname -a
cat /etc/os-release

# 디스크 사용량
df -h

# 메모리 사용량
free -h

# 프로세스 확인
ps aux | grep apache
ps aux | grep nginx
ps aux | grep php
```

### 네트워크 및 방화벽 확인

```bash
# 포트 확인
netstat -tulpn | grep :80
netstat -tulpn | grep :443

# 방화벽 상태 확인
sudo ufw status
# 또는
sudo firewall-cmd --list-all
```

---

## 문제 해결 후 확인사항

1. 브라우저 콘솔에서 404 오류가 사라졌는지 확인
2. 네비게이션 바가 정상적으로 표시되는지 확인
3. 드롭다운 메뉴가 작동하는지 확인
4. 다크/라이트 모드 전환이 정상적으로 작동하는지 확인

---

## 주의사항

- 서버 설정을 변경하기 전에 백업을 만드세요
- 권한 설정 시 보안을 고려하세요
- 웹서버 재시작 시 서비스 중단이 발생할 수 있습니다
- 로그 파일 경로는 시스템에 따라 다를 수 있습니다

---

## 해결된 문제 (2025-07-11)

### 문제: 404 오류 (yii.js, jquery.js, site.css, bootstrap.css, bootstrap.bundle.js)

**원인**: Apache 가상 호스트 설정에서 DocumentRoot 경로가 잘못되어 있음

- 설정된 경로: `/home/bitnami/apps/jdosa/invoice-manager/web/`
- 실제 경로: `/home/bitnami/stack/apps/jdosa/invoice-manager/web/`

**해결책**:

1. 가상 호스트 설정 파일 수정 (`/opt/bitnami/apache2/conf/vhosts/sample-vhost.conf`)
2. HTTP(80)과 HTTPS(443) 둘 다 DocumentRoot 경로 수정
3. Apache 재시작

**수정된 설정**:

```apache
DocumentRoot "/home/bitnami/stack/apps/jdosa/invoice-manager/web"
<Directory "/home/bitnami/stack/apps/jdosa/invoice-manager/web">
```

**상태**: ✅ 해결됨

---

## 연락처

문제가 해결되지 않으면 각 단계별 결과를 정리해서 알려주세요.
