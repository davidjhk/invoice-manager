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

        // Set header and footer
        $templateConfig = PdfHtmlGenerator::getTemplateConfig('invoice');
        self::setSimpleHeaderAndFooter($mpdf, $invoice->company, $templateConfig, $invoice);

        // Generate HTML content with larger font sizes
        $html = PdfHtmlGenerator::generateInvoiceHtml($invoice);
        
        // Adjust font sizes in HTML for better readability
        $html = self::adjustFontSizes($html);

        // Write HTML content
        $mpdf->WriteHTML($html);

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

        // Set header and footer
        $templateConfig = PdfHtmlGenerator::getTemplateConfig('estimate');
        self::setSimpleHeaderAndFooter($mpdf, $estimate->company, $templateConfig, $estimate);

        // Generate HTML content with larger font sizes
        $html = PdfHtmlGenerator::generateEstimateHtml($estimate);
        
        // Adjust font sizes in HTML for better readability
        $html = self::adjustFontSizes($html);

        // Write HTML content
        $mpdf->WriteHTML($html);

        $filename = 'Estimate_' . $estimate->estimate_number . '.pdf';

        return $mpdf->Output($filename, $mode);
    }

    /**
     * Set header and footer for mPDF (simple approach)
     *
     * @param Mpdf $mpdf
     * @param $company
     * @param array $config
     * @param $document
     */
    private static function setHeaderAndFooter($mpdf, $company, $config, $document)
    {
        // Set header for all pages first
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

        // Create header HTML - simple approach without CSS conditions
        $headerHtml = '
        <div style="font-family: Arial, sans-serif; font-size: 9px; font-weight: bold; border-bottom: 1px solid #000; padding-bottom: 2px;">
            ' . $headerText . '
        </div>';

        // Don't use SetHTMLHeader - we'll add header to content instead

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
    }

    /**
     * Add conditional header to HTML content
     *
     * @param string $html
     * @param $company
     * @param array $config
     * @param $document
     * @return string
     */
    private static function addConditionalHeader($html, $company, $config, $document)
    {
        // Create header text
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

        // Create header HTML
        $headerHtml = '
        <div style="font-family: Arial, sans-serif; font-size: 9px; font-weight: bold; border-bottom: 1px solid #000; padding-bottom: 2px; margin-bottom: 10px;">
            ' . $headerText . '
        </div>';

        // Add page break with header for subsequent pages
        $html .= '
        <pagebreak>
        ' . $headerHtml . '
        <div style="height: 100%;"></div>';

        return $html;
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

    /**
     * Set simple header and footer for mPDF (all pages)
     *
     * @param Mpdf $mpdf
     * @param $company
     * @param array $config
     * @param $document
     */
    private static function setSimpleHeaderAndFooter($mpdf, $company, $config, $document)
    {
        // Set header for all pages
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

        // Create header HTML for all pages
        $headerHtml = '
        <div style="font-family: Arial, sans-serif; font-size: 9px; font-weight: bold; border-bottom: 1px solid #000; padding-bottom: 2px;">
            ' . $headerText . '
        </div>';

        // Don't set header - we'll put the info in footer instead

        // Set footer with document info (instead of "Generated by")
        if (!$company->hide_footer) {
            $footerHtml = '
            <div style="font-family: Arial, sans-serif; font-size: 8px; font-style: italic; border-top: 1px solid #000; padding-top: 2px;">
                <table width="100%" style="border-collapse: collapse;">
                    <tr>
                        <td style="text-align: left;">' . $headerText . '</td>
                        <td style="text-align: right;">' . \Yii::t('app', 'Page') . ' {PAGENO}/{nbpg}</td>
                    </tr>
                </table>
            </div>';

            $mpdf->SetHTMLFooter($footerHtml);
        }
    }
}