<?php
require 'vendor/autoload.php';

// 사용 가능한 폰트 목록 출력
$pdf = new TCPDF();
$fonts = $pdf->getFontsList();
foreach ($fonts as $font) {
    if (stripos($font, 'noto') !== false || stripos($font, 'korean') !== false || stripos($font, 'kr') !== false) {
        echo 'Available font: ' . $font . PHP_EOL;
    }
}

// 모든 폰트 목록 출력 (처음 20개만)
echo "\nAll fonts (first 20):" . PHP_EOL;
$count = 0;
foreach ($fonts as $font) {
    echo $font . PHP_EOL;
    $count++;
    if ($count >= 20) break;
}
?>