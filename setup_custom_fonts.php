<?php
require 'vendor/autoload.php';

echo "Setting up custom fonts directory...\n\n";

// Custom fonts directory
$custom_fonts_dir = __DIR__ . '/assets/fonts/';
$noto_source_dir = __DIR__ . '/fonts/noto-sans-cjk/';

// Ensure custom fonts directory exists
if (!is_dir($custom_fonts_dir)) {
    mkdir($custom_fonts_dir, 0755, true);
    echo "✓ Created custom fonts directory: $custom_fonts_dir\n";
}

try {
    // Convert Noto Sans fonts to custom directory
    echo "Converting Noto Sans CJK fonts to custom directory...\n";
    
    // Regular font
    $regular_font = $noto_source_dir . 'NotoSansCJK-Regular.ttc';
    if (file_exists($regular_font)) {
        echo "Converting Regular font...\n";
        $fontname_regular = TCPDF_FONTS::addTTFfont(
            $regular_font, 
            'TrueTypeUnicode', 
            '', 
            32,
            $custom_fonts_dir  // Output to custom directory
        );
        
        if ($fontname_regular) {
            echo "✓ Regular font converted: $fontname_regular\n";
        } else {
            echo "✗ Regular font conversion failed\n";
        }
    } else {
        echo "✗ Regular font file not found: $regular_font\n";
    }
    
    // Bold font
    $bold_font = $noto_source_dir . 'NotoSansCJK-Bold.ttc';
    if (file_exists($bold_font)) {
        echo "Converting Bold font...\n";
        $fontname_bold = TCPDF_FONTS::addTTFfont(
            $bold_font, 
            'TrueTypeUnicode', 
            '', 
            32,
            $custom_fonts_dir  // Output to custom directory
        );
        
        if ($fontname_bold) {
            echo "✓ Bold font converted: $fontname_bold\n";
        } else {
            echo "✗ Bold font conversion failed\n";
        }
    } else {
        echo "✗ Bold font file not found: $bold_font\n";
    }
    
    // List generated files
    echo "\nGenerated files in custom directory:\n";
    $files = glob($custom_fonts_dir . '*.php');
    foreach ($files as $file) {
        echo "- " . basename($file) . "\n";
    }
    
    if (empty($files)) {
        echo "No PHP font files generated.\n";
        echo "Checking all files in custom directory:\n";
        $all_files = scandir($custom_fonts_dir);
        foreach ($all_files as $file) {
            if ($file !== '.' && $file !== '..') {
                echo "- $file\n";
            }
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}

echo "\nNext steps:\n";
echo "1. Configure TCPDF to use custom fonts directory\n";
echo "2. Update PdfGenerator to load fonts from custom directory\n";
echo "3. Test PDF generation with custom fonts\n";
?>