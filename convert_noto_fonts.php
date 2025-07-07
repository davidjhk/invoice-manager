<?php
require 'vendor/autoload.php';

echo "Converting Noto Sans CJK KR fonts to TCPDF format...\n\n";

try {
    // Convert Regular font
    echo "Converting temp/fonts/NotoSansKR-Regular.ttf...\n";
    $fontname_regular = TCPDF_FONTS::addTTFfont(
        'temp/fonts/NotoSansKR-Regular.ttf', 
        'TrueTypeUnicode', 
        '', 
        32
    );
    
    if ($fontname_regular) {
        echo "✓ Regular font converted successfully!\n";
        echo "Font name: " . $fontname_regular . "\n\n";
    } else {
        echo "✗ Regular font conversion failed!\n\n";
    }
    
    // Convert Bold font
    echo "Converting temp/fonts/NotoSansKR-Bold.ttf...\n";
    $fontname_bold = TCPDF_FONTS::addTTFfont(
        'temp/fonts/NotoSansKR-Bold.ttf', 
        'TrueTypeUnicode', 
        '', 
        32
    );
    
    if ($fontname_bold) {
        echo "✓ Bold font converted successfully!\n";
        echo "Font name: " . $fontname_bold . "\n\n";
    } else {
        echo "✗ Bold font conversion failed!\n\n";
    }
    
    // Check generated files
    if ($fontname_regular || $fontname_bold) {
        $font_path = 'vendor/tecnickcom/tcpdf/fonts/';
        echo "Checking generated files in: " . $font_path . "\n";
        
        // Look for newly created files
        $font_files = glob($font_path . '*noto*');
        if (empty($font_files)) {
            $font_files = glob($font_path . '*cjk*');
        }
        
        if (!empty($font_files)) {
            echo "Generated font files:\n";
            foreach ($font_files as $file) {
                echo "- " . basename($file) . "\n";
            }
        } else {
            echo "No font files found with 'noto' or 'cjk' in name.\n";
            echo "Checking latest files...\n";
            $all_files = glob($font_path . '*.php');
            usort($all_files, function($a, $b) {
                return filemtime($b) - filemtime($a);
            });
            echo "Latest 5 font files:\n";
            for ($i = 0; $i < min(5, count($all_files)); $i++) {
                echo "- " . basename($all_files[$i]) . " (" . date('Y-m-d H:i:s', filemtime($all_files[$i])) . ")\n";
            }
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
?>