<?php
require 'vendor/autoload.php';

echo "Converting Noto Sans CJK TTC fonts to TCPDF format...\n\n";

// Copy TTC files to TCPDF fonts directory first
$source_regular = 'fonts/noto-sans-cjk/NotoSansCJK-Regular.ttc';
$source_bold = 'fonts/noto-sans-cjk/NotoSansCJK-Bold.ttc';
$dest_regular = 'vendor/tecnickcom/tcpdf/fonts/NotoSansCJK-Regular.ttc';
$dest_bold = 'vendor/tecnickcom/tcpdf/fonts/NotoSansCJK-Bold.ttc';

echo "Copying font files to TCPDF directory...\n";
copy($source_regular, $dest_regular);
copy($source_bold, $dest_bold);

try {
    // Convert Regular font
    echo "Converting NotoSansCJK-Regular.ttc...\n";
    $fontname_regular = TCPDF_FONTS::addTTFfont(
        $dest_regular, 
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
    echo "Converting NotoSansCJK-Bold.ttc...\n";
    $fontname_bold = TCPDF_FONTS::addTTFfont(
        $dest_bold, 
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
    
    // Check for generated files
    echo "Checking for generated font files...\n";
    $font_dir = 'vendor/tecnickcom/tcpdf/fonts/';
    
    // Look for recently created PHP files
    $php_files = glob($font_dir . '*.php');
    $recent_files = array_filter($php_files, function($file) {
        return time() - filemtime($file) < 120; // Last 2 minutes
    });
    
    if (!empty($recent_files)) {
        echo "Recently created font files:\n";
        foreach ($recent_files as $file) {
            $basename = basename($file);
            echo "- " . $basename . " (" . date('H:i:s', filemtime($file)) . ")\n";
        }
        
        // Test the fonts
        echo "\nTesting converted fonts...\n";
        $pdf = new TCPDF();
        foreach ($recent_files as $file) {
            $fontname = str_replace('.php', '', basename($file));
            try {
                $pdf->SetFont($fontname, '', 12);
                echo "✓ $fontname: Font loads successfully\n";
            } catch (Exception $e) {
                echo "✗ $fontname: Error - " . $e->getMessage() . "\n";
            }
        }
    } else {
        echo "No recent font files found.\n";
        
        // Show latest files anyway
        usort($php_files, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });
        echo "Latest 5 font files:\n";
        for ($i = 0; $i < min(5, count($php_files)); $i++) {
            echo "- " . basename($php_files[$i]) . " (" . date('H:i:s', filemtime($php_files[$i])) . ")\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
?>