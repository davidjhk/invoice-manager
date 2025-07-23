<?php

namespace app\components;

use Yii;
use app\models\Invoice;
use app\models\Estimate;

class PdfGenerator implements PdfGeneratorInterface
{
    /**
     * @var PdfGeneratorInterface The actual PDF generator instance
     */
    private static $generatorInstance;

    /**
     * Get the appropriate PDF generator instance based on configuration.
     *
     * @return PdfGeneratorInterface
     * @throws \Exception if an invalid PDF generator is configured
     */
    private static function getGenerator()
    {
        if (self::$generatorInstance === null) {
            $generatorType = Yii::$app->params['pdf.generator'] ?? 'tcpdf';

            switch ($generatorType) {
                case 'tcpdf':
                    self::$generatorInstance = new TcpdfGenerator();
                    break;
                case 'mpdf':
                    self::$generatorInstance = new MpdfGenerator();
                    break;
                default:
                    throw new \Exception('Invalid PDF generator configured: ' . $generatorType);
            }
        }
        return self::$generatorInstance;
    }

    /**
     * Generate PDF for invoice
     *
     * @param Invoice $invoice
     * @param string $mode 'D' for download, 'I' for inline, 'S' for string
     * @return mixed
     */
    public static function generateInvoicePdf(Invoice $invoice, $mode = 'I')
    {
        return self::getGenerator()->generateInvoicePdf($invoice, $mode);
    }

    /**
     * Generate PDF for estimate
     *
     * @param Estimate $estimate
     * @param string $mode 'D' for download, 'I' for inline, 'S' for string
     * @return mixed
     */
    public static function generateEstimatePdf(Estimate $estimate, $mode = 'I')
    {
        return self::getGenerator()->generateEstimatePdf($estimate, $mode);
    }

    /**
     * Generate HTML preview for invoice
     *
     * @param Invoice $invoice
     * @return string
     */
    public static function generateInvoicePreviewHtml(Invoice $invoice)
    {
        return PdfHtmlGenerator::generateInvoicePreviewHtml($invoice);
    }

    /**
     * Generate HTML preview for estimate
     *
     * @param Estimate $estimate
     * @return string
     */
    public static function generateEstimatePreviewHtml(Estimate $estimate)
    {
        return PdfHtmlGenerator::generateEstimatePreviewHtml($estimate);
    }
}