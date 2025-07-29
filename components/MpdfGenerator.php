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
        // Clear any existing output buffers to prevent HeadersAlreadySentException
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        // Start clean output buffering
        ob_start();
        
        // Use simple configuration to avoid TTC font issues
        $config = [
            'mode' => 'utf-8',
            'format' => 'Letter',
            'tempDir' => sys_get_temp_dir(),
            'fontDir' => [
                \Yii::getAlias('@app/fonts/mpdf-fonts'),
                \Yii::getAlias('@app/vendor/mpdf/mpdf/ttfonts')
            ],
            'fontdata' => [
                'poppins' => [
                    'R' => 'Poppins-Regular.ttf',
                    'B' => 'Poppins-Bold.ttf',
                    'SB' => 'Poppins-SemiBold.ttf',
                ],
				'dejavusans' => [
					'R' => 'DejaVuSans.ttf',
					'B' => 'DejaVuSans-Bold.ttf',
					'SB' => 'DejaVuSans-BoldOblique.ttf',
				],
                'freesans' => [
                    'R' => 'FreeSans.ttf',
                    'B' => 'FreeSansBold.ttf',
                    'SB' => 'FreeSansOblique.ttf',
                ],
            ],
            // Enable substitute fonts for better CJK support
            'useSubstitutions' => true,
            'useKashida' => 75,
        ];

        // Set default font based on CJK preference
        if ($invoice->company->use_cjk_font) {
            $config['default_font'] = 'dejavusans'; // Use DejaVuSans for better Korean support
            $config['default_font_size'] = 9;
        } else {
            $config['default_font'] = 'freesans'; // Use FreeSans for non-CJK (helvetica alternative)
        }

        $mpdf = new Mpdf($config);

        // Set CJK font preference with fallback fonts
        if ($invoice->company->use_cjk_font) {
            // Use DejaVuSans which has Korean character support in mPDF
            $mpdf->SetDefaultFont('dejavusans');
            $mpdf->autoScriptToLang = true;
            $mpdf->autoLangToFont = true;
            
            // Force all text to use DejaVuSans
            $mpdf->SetDefaultBodyCSS('font-family', 'dejavusans');
        } else {
            // Explicitly set non-CJK font
            $mpdf->SetDefaultFont('freesans');
        }

        // Set header and footer
        $templateConfig = PdfHtmlGenerator::getTemplateConfig('invoice');
        self::setSimpleHeaderAndFooter($mpdf, $invoice->company, $templateConfig, $invoice);

        // Generate HTML content with larger font sizes
        $html = PdfHtmlGenerator::generateInvoiceHtml($invoice);
        
        // Adjust font sizes in HTML for better readability
        $html = self::adjustFontSizes($html);
        
        // Force DejaVu Sans font for CJK content in Bill To and Ship To areas
        if ($invoice->company->use_cjk_font) {
            $html = self::forceCJKFontInHTML($html);
        }


        // Write HTML content
        $mpdf->WriteHTML($html);

        $filename = 'Invoice_' . $invoice->invoice_number . '.pdf';

        // Clean any buffer content before output
        ob_end_clean();
        
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
        // Clear any existing output buffers to prevent HeadersAlreadySentException
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        // Start clean output buffering
        ob_start();
        
        // Use simple configuration to avoid TTC font issues
        $config = [
            'mode' => 'utf-8',
            'format' => 'Letter',
            'tempDir' => sys_get_temp_dir(),
            'fontDir' => [
                \Yii::getAlias('@app/fonts/mpdf-fonts'),
                \Yii::getAlias('@app/vendor/mpdf/mpdf/ttfonts')
            ],
            'fontdata' => [
                'poppins' => [
                    'R' => 'Poppins-Regular.ttf',
                    'B' => 'Poppins-Bold.ttf',
                    'SB' => 'Poppins-SemiBold.ttf',
                ],
				'dejavusans' => [
					'R' => 'DejaVuSans.ttf',
					'B' => 'DejaVuSans-Bold.ttf',
					'SB' => 'DejaVuSans-BoldOblique.ttf',
				],
                'freesans' => [
                    'R' => 'FreeSans.ttf',
                    'B' => 'FreeSansBold.ttf',
                    'SB' => 'FreeSansOblique.ttf',
                ],
            ],
            // Enable substitute fonts for better CJK support
            'useSubstitutions' => true,
            'useKashida' => 75,
        ];

        // Set default font based on CJK preference
        if ($estimate->company->use_cjk_font) {
            $config['default_font'] = 'dejavusans'; // Use DejaVuSans for better Korean support
            $config['default_font_size'] = 9;
        } else {
            $config['default_font'] = 'freesans'; // Use FreeSans for non-CJK
        }

        $mpdf = new Mpdf($config);

        // Set CJK font preference with fallback fonts
        if ($estimate->company->use_cjk_font) {
            // Use DejaVuSans which has Korean character support in mPDF
            $mpdf->SetDefaultFont('dejavusans');
            $mpdf->autoScriptToLang = true;
            $mpdf->autoLangToFont = true;
            
            // Force all text to use DejaVuSans
            $mpdf->SetDefaultBodyCSS('font-family', 'dejavusans');
        } else {
            // Explicitly set non-CJK font
            $mpdf->SetDefaultFont('freesans');
        }

        // Set header and footer
        $templateConfig = PdfHtmlGenerator::getTemplateConfig('estimate');
        self::setSimpleHeaderAndFooter($mpdf, $estimate->company, $templateConfig, $estimate);

        // Generate HTML content with larger font sizes
        $html = PdfHtmlGenerator::generateEstimateHtml($estimate);
        
        // Adjust font sizes in HTML for better readability
        $html = self::adjustFontSizes($html);
        
        // Force NotoSansCJK font for CJK content in Bill To and Ship To areas
        if ($estimate->company->use_cjk_font) {
            $html = self::forceCJKFontInHTML($html);
        }


        // Write HTML content
        $mpdf->WriteHTML($html);

        $filename = 'Estimate_' . $estimate->estimate_number . '.pdf';

        // Clean any buffer content before output
        ob_end_clean();
        
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
        <div style="font-family: helvetica; font-size: 9px; font-weight: bold; border-bottom: 1px solid #000; padding-bottom: 2px;">
            ' . $headerText . '
        </div>';

        // Don't use SetHTMLHeader - we'll add header to content instead

        // Set footer only if not hidden
        if (!$company->hide_footer) {
            $siteName = \Yii::$app->params['siteName'] ?? 'Invoice Manager';
            $footerHtml = '
            <div style="font-family: helvetica; font-size: 8px; font-style: italic; border-top: 1px solid #000; padding-top: 2px;">
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
        <div style="font-family: helvetica; font-size: 9px; font-weight: bold; border-bottom: 1px solid #000; padding-bottom: 2px; margin-bottom: 10px;">
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
        
        // Adjust body font size while preserving font-family
        $html = preg_replace('/font-size:\s*9px;/i', 'font-size: 10px;', $html);
        
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
        <div style="font-family: helvetica; font-size: 9px; font-weight: bold; border-bottom: 1px solid #000; padding-bottom: 2px;">
            ' . $headerText . '
        </div>';

        // Don't set header - we'll put the info in footer instead

        // Set footer with document info (instead of "Generated by")
        if (!$company->hide_footer) {
            $footerHtml = '
            <div style="font-family: helvetica; font-size: 8px; font-style: italic; border-top: 1px solid #000; padding-top: 2px;">
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

    /**
     * Force CJK font in HTML content for Bill To and Ship To areas
     *
     * @param string $html
     * @return string
     */
    private static function forceCJKFontInHTML($html)
    {
        // Replace bill-to and ship-to div classes with explicit font styling
        $html = preg_replace(
            '/<div class="bill-to">/i',
            '<div class="bill-to" style="font-family: dejavusans, DejaVuSans, sans-serif !important;">',
            $html
        );
        
        $html = preg_replace(
            '/<div class="ship-to">/i',
            '<div class="ship-to" style="font-family: dejavusans, DejaVuSans, sans-serif !important;">',
            $html
        );
        
        // Force font on all text content in these areas using font tags
        $html = preg_replace(
            '/(<div class="bill-to"[^>]*>)(.*?)(<\/div>)/is',
            '$1<font face="dejavusans,DejaVuSans,sans-serif">$2</font>$3',
            $html
        );
        
        $html = preg_replace(
            '/(<div class="ship-to"[^>]*>)(.*?)(<\/div>)/is',
            '$1<font face="dejavusans,DejaVuSans,sans-serif">$2</font>$3',
            $html
        );
        
        // Force font on all elements for better CJK support
        $html = preg_replace(
            '/<body([^>]*)>/i',
            '<body$1 style="font-family: dejavusans, DejaVuSans, sans-serif;">',
            $html
        );
        
        return $html;
    }
}