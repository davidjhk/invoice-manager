<?php
require 'vendor/autoload.php';

echo "Simple Noto Sans font converter...\n\n";

// 폰트 파일 경로 설정
$regular_font = 'vendor/tecnickcom/tcpdf/fonts/NotoSansCJKkr-Regular.otf';
$bold_font = 'vendor/tecnickcom/tcpdf/fonts/NotoSansCJKkr-Bold.otf';

try {
    // Regular 폰트 변환
    echo "Processing Regular font...\n";
    $regular_name = TCPDF_FONTS::addTTFfont($regular_font);
    if ($regular_name) {
        echo "✓ Regular font created: $regular_name\n";
    } else {
        echo "✗ Regular font conversion failed\n";
    }
    
    // Bold 폰트 변환  
    echo "Processing Bold font...\n";
    $bold_name = TCPDF_FONTS::addTTFfont($bold_font);
    if ($bold_name) {
        echo "✓ Bold font created: $bold_name\n";
    } else {
        echo "✗ Bold font conversion failed\n";
    }
    
    if ($regular_name || $bold_name) {
        echo "\nChecking generated files...\n";
        $files = glob('vendor/tecnickcom/tcpdf/fonts/*.php');
        $recent_files = array_filter($files, function($file) {
            return time() - filemtime($file) < 60; // Last minute
        });
        
        if (!empty($recent_files)) {
            echo "Recently created font files:\n";
            foreach ($recent_files as $file) {
                echo "- " . basename($file) . "\n";
            }
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>