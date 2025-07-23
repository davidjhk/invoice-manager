<?php

namespace app\components;

use app\models\Invoice;
use app\models\Estimate;

interface PdfGeneratorInterface
{
    /**
     * Generate PDF for invoice
     *
     * @param Invoice $invoice
     * @param string $mode 'D' for download, 'I' for inline, 'S' for string
     * @return mixed
     */
    public static function generateInvoicePdf(Invoice $invoice, $mode = 'I');

    /**
     * Generate PDF for estimate
     *
     * @param Estimate $estimate
     * @param string $mode 'D' for download, 'I' for inline, 'S' for string
     * @return mixed
     */
    public static function generateEstimatePdf(Estimate $estimate, $mode = 'I');
}