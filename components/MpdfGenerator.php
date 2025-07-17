<?php

namespace app\components;

use Mpdf\Mpdf;
use app\models\Invoice;
use app\models\Estimate;
use app\components\PdfHtmlGenerator;

class MpdfGenerator implements PdfGeneratorInterface
{
    /**
     * Generate PDF for invoice using mPDF
     *
     * @param Invoice $invoice
     * @param string $mode 'D' for download, 'I' for inline, 'S' for string
     * @return mixed
     */
    public static function generateInvoicePdf(Invoice $invoice, $mode = 'I')
    {
        // Use simple configuration to avoid TTC font issues
        $config = [
            'mode' => 'utf-8',
            'format' => 'A4',
            'tempDir' => sys_get_temp_dir(),
        ];

        // Only add custom fonts if CJK is needed and fonts exist
        if ($invoice->company->use_cjk_font) {
            $config['default_font'] = 'dejavusans'; // Use DejaVu Sans as fallback
        }

        $mpdf = new Mpdf($config);

        // Set CJK font preference with fallback fonts
        if ($invoice->company->use_cjk_font) {
            $mpdf->autoScriptToLang = true;
            $mpdf->autoLangToFont = true;
            // Use built-in fonts that support CJK
            $mpdf->SetDefaultFont('dejavusans');
        }

        // Set footer and get header HTML for later use
        $templateConfig = PdfHtmlGenerator::getTemplateConfig('invoice');
        $headerHtml = self::setHeaderFooter($mpdf, $invoice->company, $templateConfig, $invoice);

        // Generate HTML content with larger font sizes
        $html = PdfHtmlGenerator::generateInvoiceHtml($invoice);
        
        // Adjust font sizes in HTML for better readability
        $html = self::adjustFontSizes($html);

        // Write HTML content (first page without header)
        $mpdf->WriteHTML($html);
        
        // Set header for subsequent pages
        $mpdf->SetHTMLHeader($headerHtml);

        $filename = 'Invoice_' . $invoice->invoice_number . '.pdf';

        return $mpdf->Output($filename, $mode);
    }

    /**
     * Generate PDF for estimate using mPDF
     *
     * @param Estimate $estimate
     * @param string $mode 'D' for download, 'I' for inline, 'S' for string
     * @return mixed
     */
    public static function generateEstimatePdf(Estimate $estimate, $mode = 'I')
    {
        // Use simple configuration to avoid TTC font issues
        $config = [
            'mode' => 'utf-8',
            'format' => 'A4',
            'tempDir' => sys_get_temp_dir(),
        ];

        // Only add custom fonts if CJK is needed and fonts exist
        if ($estimate->company->use_cjk_font) {
            $config['default_font'] = 'dejavusans'; // Use DejaVu Sans as fallback
        }

        $mpdf = new Mpdf($config);

        // Set CJK font preference with fallback fonts
        if ($estimate->company->use_cjk_font) {
            $mpdf->autoScriptToLang = true;
            $mpdf->autoLangToFont = true;
            // Use built-in fonts that support CJK
            $mpdf->SetDefaultFont('dejavusans');
        }

        // Set footer and get header HTML for later use
        $templateConfig = PdfHtmlGenerator::getTemplateConfig('estimate');
        $headerHtml = self::setHeaderFooter($mpdf, $estimate->company, $templateConfig, $estimate);

        // Generate HTML content with larger font sizes
        $html = PdfHtmlGenerator::generateEstimateHtml($estimate);
        
        // Adjust font sizes in HTML for better readability
        $html = self::adjustFontSizes($html);

        // Write HTML content (first page without header)
        $mpdf->WriteHTML($html);
        
        // Set header for subsequent pages
        $mpdf->SetHTMLHeader($headerHtml);

        $filename = 'Estimate_' . $estimate->estimate_number . '.pdf';

        return $mpdf->Output($filename, $mode);
    }

    /**
     * Set header and footer for mPDF
     *
     * @param Mpdf $mpdf
     * @param $company
     * @param array $config
     * @param $document
     * @return string Header HTML for subsequent pages
     */
    private static function setHeaderFooter($mpdf, $company, $config, $document)
    {
        // Set header (for pages after the first)
        $documentNumber = '';
        if ($config['title'] === 'INVOICE' && isset($document->invoice_number)) {
            $documentNumber = $document->invoice_number;
        } elseif ($config['title'] === 'ESTIMATE' && isset($document->estimate_number)) {
            $documentNumber = $document->estimate_number;
        }
        
        $headerText = htmlspecialchars($company->company_name);
        if ($documentNumber) {
            $headerText .= ' - ' . $config['title'] . ' #' . htmlspecialchars($documentNumber);
        } else {
            $headerText .= ' - ' . $config['title'];
        }

        // Create header HTML for subsequent pages
        $headerHtml = '
        <div style="font-family: Arial, sans-serif; font-size: 9px; font-weight: bold; border-bottom: 1px solid #000; padding-bottom: 2px;">
            ' . $headerText . '
        </div>';

        // First set empty header for first page
        $mpdf->SetHTMLHeader('');

        // Set footer only if not hidden
        if (!$company->hide_footer) {
            $siteName = \Yii::$app->params['siteName'] ?? 'Invoice Manager';
            $footerHtml = '
            <div style="font-family: Arial, sans-serif; font-size: 8px; font-style: italic; border-top: 1px solid #000; padding-top: 2px;">
                <table width="100%" style="border-collapse: collapse;">
                    <tr>
                        <td style="text-align: left;">Generated by ' . htmlspecialchars($siteName) . '</td>
                        <td style="text-align: right;">' . \Yii::t('app', 'Page') . ' {PAGENO}/{nbpg}</td>
                    </tr>
                </table>
            </div>';

            $mpdf->SetHTMLFooter($footerHtml);
        }

        // Return header HTML for later use
        return $headerHtml;
    }

    /**
     * Adjust font sizes in HTML for better readability
     *
     * @param string $html
     * @return string
     */
    private static function adjustFontSizes($html)
    {
        // Increase base font size from 9px to 10px
        $html = str_replace('font-size: 9px;', 'font-size: 10px;', $html);
        
        // Increase other font sizes proportionally
        $html = str_replace('font-size: 10px;', 'font-size: 11px;', $html);
        $html = str_replace('font-size: 11px;', 'font-size: 12px;', $html);
        $html = str_replace('font-size: 25px;', 'font-size: 26px;', $html);
        
        // Adjust body font size
        $html = preg_replace('/body\s*{\s*font-family:[^}]+font-size:\s*9px;/i', 
            'body { font-family: "DejaVu Sans", "FreeSerif", "Times", sans-serif; font-size: 10px;', $html);
        
        return $html;
    }
}