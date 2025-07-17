<?php
/**
 * Test script to verify PDF footer and page numbering
 * Delete this file after testing
 */

// Include Yii framework
require(__DIR__ . '/../vendor/autoload.php');
require(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');

$config = require(__DIR__ . '/../config/web.php');
new yii\web\Application($config);

try {
    // Find the first invoice for testing
    $invoice = \app\models\Invoice::find()->one();
    
    if (!$invoice) {
        echo "❌ No invoice found for testing. Please create an invoice first.";
        exit;
    }
    
    echo "✅ Testing PDF generation for Invoice #{$invoice->invoice_number}<br>";
    echo "📄 Company: {$invoice->company->company_name}<br>";
    echo "👤 Customer: {$invoice->customer->customer_name}<br><br>";
    
    // Test PDF generation
    $pdfContent = \app\components\PdfGenerator::generateInvoicePdf($invoice, 'S');
    
    if ($pdfContent) {
        echo "✅ PDF generated successfully! (" . strlen($pdfContent) . " bytes)<br>";
        echo "📋 <a href='#' onclick='downloadPdf()'>Download Test PDF</a><br>";
        
        echo "<script>
        function downloadPdf() {
            var link = document.createElement('a');
            link.href = 'data:application/pdf;base64," . base64_encode($pdfContent) . "';
            link.download = 'test_invoice.pdf';
            link.click();
        }
        </script>";
    } else {
        echo "❌ PDF generation failed<br>";
    }
    
    // Test hide_footer column
    echo "<br><strong>Testing hide_footer column:</strong><br>";
    $company = $invoice->company;
    if (isset($company->hide_footer)) {
        echo "✅ hide_footer column exists: " . ($company->hide_footer ? 'true' : 'false') . "<br>";
    } else {
        echo "⚠️ hide_footer column does not exist or cannot be accessed<br>";
        echo "💡 Run the add_hide_footer_column.php script first<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "Stack trace: <pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<br><strong>You can delete this file after testing: /web/test_pdf_footer.php</strong>";
?>