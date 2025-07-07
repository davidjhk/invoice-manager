# Yii2 마이그레이션 문제 해결 가이드

## 문제 상황
`./yii migrate` 명령어가 작동하지 않는 경우 해결 방법

## 해결 방법

### 1단계: Yii2 프로젝트 생성 확인

현재 디렉토리가 Yii2 프로젝트인지 확인:

```bash
# yii 콘솔 파일이 있는지 확인
ls -la yii

# composer.json 파일이 있는지 확인
ls -la composer.json
```

### 2단계: Yii2 프로젝트 새로 생성 (권장)

기존 디렉토리 외부에 새 Yii2 프로젝트 생성:

```bash
# 상위 디렉토리로 이동
cd ..

# 새 Yii2 프로젝트 생성
composer create-project --prefer-dist yiisoft/yii2-app-basic invoice-manager-yii2

# 새 프로젝트로 이동
cd invoice-manager-yii2
```

### 3단계: 마이그레이션 파일 복사

```bash
# 기존 마이그레이션 파일들을 새 프로젝트로 복사
cp ../invoice/console/migrations/*.php console/migrations/
```

### 4단계: 데이터베이스 설정

```bash
# 데이터베이스 설정 파일 편집
nano config/db.php
```

내용 수정:
```php
<?php
return [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=localhost;dbname=invoice_manager',
    'username' => 'your_db_user',
    'password' => 'your_db_password',
    'charset' => 'utf8mb4',
];
```

### 5단계: 쿠키 검증 키 설정

```bash
# 웹 설정 파일 편집
nano config/web.php
```

cookieValidationKey 설정:
```php
'cookieValidationKey' => 'your-secret-key-32-characters-long',
```

### 6단계: 마이그레이션 실행

```bash
# 마이그레이션 상태 확인
./yii migrate/history

# 마이그레이션 실행
./yii migrate

# 또는 자동 확인으로 실행
./yii migrate --interactive=0
```

## 대안 방법: 기존 디렉토리에 Yii2 설정 추가

현재 디렉토리에서 직접 작업하려면:

### 1단계: Composer 초기화

```bash
# composer.json 생성
composer init --no-interaction --require="yiisoft/yii2:~2.0.0" --require="yiisoft/yii2-app-basic:~2.0.0"

# Yii2 설치
composer install
```

### 2단계: 필수 디렉토리 생성

```bash
# 필요한 디렉토리들 생성
mkdir -p config runtime web/assets commands controllers models views
```

### 3단계: 기본 설정 파일 생성

config/console.php:
```php
<?php
return [
    'id' => 'invoice-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'app\commands',
    'components' => [
        'db' => require __DIR__ . '/db.php',
    ],
];
```

config/web.php:
```php
<?php
return [
    'id' => 'invoice-web',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'components' => [
        'db' => require __DIR__ . '/db.php',
        'request' => [
            'cookieValidationKey' => 'your-secret-key-here',
        ],
    ],
];
```

### 4단계: yii 콘솔 파일 생성

프로젝트 루트에 `yii` 파일 생성:
```php
#!/usr/bin/env php
<?php
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/vendor/yiisoft/yii2/Yii.php';

$config = require __DIR__ . '/config/console.php';

$application = new yii\console\Application($config);
$exitCode = $application->run();
exit($exitCode);
```

실행 권한 부여:
```bash
chmod +x yii
```

## 문제 해결 체크리스트

- [ ] `yii` 파일이 존재하고 실행 가능한가?
- [ ] `composer.json`과 `vendor/` 디렉토리가 있는가?
- [ ] `config/db.php` 설정이 올바른가?
- [ ] 데이터베이스 연결이 가능한가?
- [ ] PHP와 MySQL이 설치되어 있는가?
- [ ] 마이그레이션 파일들이 올바른 위치에 있는가?

## 테스트 명령어

```bash
# Yii2 설치 확인
./yii help

# 데이터베이스 연결 테스트
./yii migrate/history

# 마이그레이션 파일 목록 확인
./yii migrate/new
```

이 가이드를 따라하면 마이그레이션 문제를 해결할 수 있습니다.