<?php
require 'vendor/autoload.php';

echo "Testing Korean text rendering with current fonts...\n\n";

// Create a simple PDF with Korean text
$pdf = new TCPDF();
$pdf->AddPage();

// Test each font with Korean text
$korean_text = "안녕하세요. Noto Sans CJK 테스트입니다.";
$fonts = ['kozgopromedium', 'dejavusans', 'freesans', 'cid0kr', 'helvetica'];

$y = 30;
foreach ($fonts as $font) {
    try {
        $pdf->SetFont($font, '', 12);
        $pdf->SetXY(20, $y);
        $pdf->Cell(0, 10, $font . ': ' . $korean_text, 0, 1);
        echo "✓ $font: Korean text rendered successfully\n";
        $y += 15;
    } catch (Exception $e) {
        echo "✗ $font: Error - " . $e->getMessage() . "\n";
    }
}

// Output PDF
$pdf_content = $pdf->Output('test_korean.pdf', 'S');
file_put_contents('test_korean_fonts.pdf', $pdf_content);

echo "\nTest PDF created: test_korean_fonts.pdf\n";
echo "You can open this file to see how Korean text renders with different fonts.\n";
?>