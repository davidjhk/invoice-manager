<?php
require 'vendor/autoload.php';

echo "Simple Noto Sans CJK Regular font converter...\n\n";

// 폰트 파일 경로 설정
$font_file = 'fonts/tcpdf-fonts/NotoSansCJK-Regular.ttc';

try {
    // 폰트 변환
    echo "Processing Noto Sans CJK Regular font...\n";
    // 폰트 인덱스 0을 명시적으로 지정 (TTC 파일의 첫 번째 폰트)
    $font_name = TCPDF_FONTS::addTTFfont($font_file, 'TrueTypeUnicode', '', 32, 'fonts/tcpdf-fonts/', 0);
    if ($font_name) {
        echo "✓ Font created: $font_name\n";

        echo "\nChecking generated files...\n";
        $font_path = 'fonts/tcpdf-fonts/';
        $files = glob($font_path . strtolower(str_replace(' ', '', $font_name)) . '.*');
        
        if (!empty($files)) {
            echo "Created font files:\n";
            foreach ($files as $file) {
                echo "- " . basename($file) . "\n";
            }
        } else {
            echo "Could not find generated files for $font_name.\n";
        }

    } else {
        echo "✗ Font conversion failed\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
?>