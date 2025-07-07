<?php
/**
 * TCPDF Font Conversion Script (Instance Method)
 *
 * This script attempts to convert fonts by instantiating the TCPDF class
 * and calling its AddFont method. This may provide more detailed error output.
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('memory_limit', '512M');

require_once(__DIR__ . '/vendor/autoload.php');

// --- Font Definitions ---
$fontsToConvert = [
    'NotoSansKR-Regular' => __DIR__ . '/temp/fonts/NotoSansKR-Regular.ttf',
    'NotoSansKR-Bold'    => __DIR__ . '/temp/fonts/NotoSansKR-Bold.ttf',
];

echo "TCPDF Font Conversion Utility (Instance Method)
";
echo "=============================================

";

foreach ($fontsToConvert as $fontAlias => $fontPath) {
    echo "Processing: " . basename($fontPath) . "...
";

    if (!file_exists($fontPath)) {
        echo "  [SKIP] Font file not found at: " . $fontPath . "

";
        continue;
    }

    try {
        // Instantiate TCPDF class. This might reveal configuration issues.
        $pdf = new TCPDF();

        // Use the AddFont method on the instance.
        $fontName = $pdf->AddFont($fontAlias, '', $fontPath, 32);

        if ($fontName) {
            echo "  [SUCCESS] Font converted successfully!
";
            echo "  > Font Name: " . $fontName . "

";
        } else {
            echo "  [FAILED] The AddFont method returned false.

";
        }
    } catch (Exception $e) {
        echo "  [ERROR] An exception occurred during conversion:
";
        echo "  > " . $e->getMessage() . "

";
    }
}

echo "Conversion process finished.
";
?>