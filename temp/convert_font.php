<?php
require '../vendor/autoload.php';

try {
    echo "Converting Noto Sans CJK to TCPDF format...\n";
    
    // TCPDF 폰트 변환 도구 사용
    $fontname = TCPDF_FONTS::addTTFfont(
        'fonts/NotoSansKR-Regular.otf', 
        'TrueTypeUnicode', 
        '', 
        32,
        '',
        array()
    );
    
    if ($fontname) {
        echo "Font converted successfully!\n";
        echo "Font name: " . $fontname . "\n";
        
        // 변환된 폰트 파일 확인
        $font_path = '../vendor/tecnickcom/tcpdf/fonts/';
        $font_files = glob($font_path . $fontname . '*');
        
        echo "Generated files:\n";
        foreach ($font_files as $file) {
            echo "- " . basename($file) . "\n";
        }
        
    } else {
        echo "Font conversion failed!\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
?>