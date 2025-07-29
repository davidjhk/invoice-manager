<?php

namespace app\components;

use Mpdf\Mpdf;
use app\models\Invoice;
use app\models\Estimate;
use app\components\PdfHtmlGenerator;

class MpdfGenerator implements PdfGeneratorInterface
{
    private static function getMpdfConfig($document)
    {
        $company = $document->company;

        $defaultFont = $company->use_cjk_font ? 'notosanskr' : 'freesans';

        return [
            'mode' => 'utf-8',
            'format' => 'Letter',
            'tempDir' => sys_get_temp_dir(),
            'fontDir' => [
                \Yii::getAlias('@app/fonts/mpdf-fonts'),
                \Yii::getAlias('@app/vendor/mpdf/mpdf/ttfonts')
            ],
            'fontdata' => [
                'poppins' => ['R' => 'Poppins-Regular.ttf', 'B' => 'Poppins-Bold.ttf'],
				'dejavusans' => ['R' => 'DejaVuSans.ttf', 'B' => 'DejaVuSans-Bold.ttf'],
				'dejavuserif' => ['R' => 'DejaVuSerif.ttf', 'B' => 'DejaVuSerif-Bold.ttf'],
                'freesans' => ['R' => 'FreeSans.ttf', 'B' => 'FreeSansBold.ttf'],
                'freeserif' => ['R' => 'FreeSerif.ttf', 'B' => 'FreeSerifBold.ttf'],
				'playfairdisplay' => [
					'R' => 'PlayfairDisplay-Regular.ttf',
					'B' => 'PlayfairDisplay-Bold.ttf',
				],
				'roboto' => [
					'R' => 'Roboto-Regular.ttf',
					'B' => 'Roboto-Bold.ttf',
				],
                'notosanskr' => [
					'R' => 'NotoSansKR-Regular.ttf',
					'B' => 'NotoSansKR-Bold.ttf',
				],
                'notosanssc' => [
					'R' => 'NotoSansSC-Regular.ttf',
					'B' => 'NotoSansSC-Bold.ttf',
				],
                'notosanstc' => [
					'R' => 'NotoSansTC-Regular.ttf',
					'B' => 'NotoSansTC-Bold.ttf',
				],
                'notosansjp' => [
					'R' => 'NotoSansJP-Regular.ttf',
					'B' => 'NotoSansJP-Bold.ttf',
				],
            ],
            'default_font' => $defaultFont,
            'useSubstitutions' => true,
        ];
    }

    public static function generateInvoicePdf(Invoice $invoice, $mode = 'I')
    {
        while (ob_get_level()) {
            ob_end_clean();
        }
        ob_start();

        $config = self::getMpdfConfig($invoice);
        $mpdf = new Mpdf($config);

        $mpdf->autoScriptToLang = false;
        $mpdf->autoLangToFont = false;

        $templateConfig = PdfHtmlGenerator::getTemplateConfig('invoice');
        self::setSimpleHeaderAndFooter($mpdf, $invoice->company, $templateConfig, $invoice);

        $html = PdfHtmlGenerator::generateInvoiceHtml($invoice);
        $html = self::adjustFontSizes($html);

        $mpdf->WriteHTML($html);

        $filename = 'Invoice_' . $invoice->invoice_number . '.pdf';
        ob_end_clean();
        return $mpdf->Output($filename, $mode);
    }

    public static function generateEstimatePdf(Estimate $estimate, $mode = 'I')
    {
        while (ob_get_level()) {
            ob_end_clean();
        }
        ob_start();

        $config = self::getMpdfConfig($estimate);
        $mpdf = new Mpdf($config);

        $mpdf->autoScriptToLang = false;
        $mpdf->autoLangToFont = false;

        $templateConfig = PdfHtmlGenerator::getTemplateConfig('estimate');
        self::setSimpleHeaderAndFooter($mpdf, $estimate->company, $templateConfig, $estimate);

        $html = PdfHtmlGenerator::generateEstimateHtml($estimate);
        $html = self::adjustFontSizes($html);

        $mpdf->WriteHTML($html);

        $filename = 'Estimate_' . $estimate->estimate_number . '.pdf';
        ob_end_clean();
        return $mpdf->Output($filename, $mode);
    }

    private static function setSimpleHeaderAndFooter($mpdf, $company, $config, $document)
    {
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

    private static function adjustFontSizes($html)
    {
        $html = str_replace('font-size: 9px;', 'font-size: 10px;', $html);
        $html = str_replace('font-size: 10px;', 'font-size: 11px;', $html);
        $html = str_replace('font-size: 11px;', 'font-size: 12px;', $html);
        $html = str_replace('font-size: 25px;', 'font-size: 26px;', $html);
        $html = preg_replace('/font-size:\s*9px;/i', 'font-size: 10px;', $html);
        return $html;
    }
}