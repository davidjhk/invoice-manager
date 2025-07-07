<?php

/**
 * 공용 설정 파일
 * 실제 DB 설정은 db-local.php에 따름
 */
$localDb = __DIR__ . '/db-local.php';

if (!file_exists($localDb)) {
    throw new \RuntimeException('Missing config/db-local.php file. Please create it based on your local settings.');
}

return require $localDb;