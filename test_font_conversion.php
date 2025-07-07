<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('memory_limit', '512M');

require 'vendor/autoload.php';

echo "Starting font conversion test...\n";

$fontPath = __DIR__ . '/temp/fonts/NotoSansKR-Regular.ttf';
echo "Attempting to convert: " . $fontPath . "\n";

if (!file_exists($fontPath)) {
    echo "Error: Font file does not exist at the specified path.\n";
    exit;
}

try {
    $fontname = TCPDF_FONTS::addTTFfont(
        $fontPath,
        'TrueTypeUnicode',
        '',
        32
    );

    if ($fontname) {
        echo "✓ Font converted successfully!\n";
        echo "Font name: " . $fontname . "\n";
        
        $font_path = __DIR__ . '/vendor/tecnickcom/tcpdf/fonts/';
        echo "Checking for generated files in: " . $font_path . "\n";
        $generated_files = glob($font_path . strtolower(basename($fontname, '.php')) . '.*');
        if (!empty($generated_files)) {
            echo "Generated files:\n";
            foreach ($generated_files as $file) {
                echo "- " . basename($file) . "\n";
            }
        } else {
            echo "Could not find generated font files for " . $fontname . "\n";
        }
    } else {
        echo "✗ Font conversion failed. No specific error message from TCPDF, but the function returned false.\n";
    }
} catch (Exception $e) {
    echo "An exception occurred:\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
?>