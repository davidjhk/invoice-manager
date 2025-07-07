<?php

/**
 * 공용 설정 파일.
 * 실제 설정 값은 params-local.php에서 불러옵니다.
 */

$localParams = __DIR__ . '/params-local.php';

if (!file_exists($localParams)) {
    throw new \RuntimeException('Missing config/params-local.php. Please create it based on your local settings.');
}

return require $localParams;