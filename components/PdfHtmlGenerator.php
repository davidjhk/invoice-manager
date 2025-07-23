<?php

namespace app\components;

use app\models\Invoice;
use app\models\Estimate;
use app\components\PdfTemplateManager;

class PdfHtmlGenerator
{
    /**
     * Template configuration for different document types
     */
    private static $templates = [
        'invoice' => [
            'title' => 'INVOICE',
            'number_field' => 'invoice_number',
            'date_field' => 'invoice_date',
            'due_date_field' => 'due_date',
            'items_relation' => 'invoiceItems',
            'notes_fields' => ['notes'],
            'show_payments' => true,
            'show_discount' => false,
            'show_terms' => true,
            'terms_source' => 'company',
            'color_scheme' => '#667eea'
        ],
        'estimate' => [
            'title' => 'ESTIMATE',
            'number_field' => 'estimate_number',
            'date_field' => 'estimate_date',
            'due_date_field' => 'expiry_date',
            'items_relation' => 'estimateItems',
            'notes_fields' => ['customer_notes', 'payment_instructions'],
            'show_payments' => false,
            'show_discount' => true,
            'show_terms' => false,
            'terms_source' => 'document',
            'color_scheme' => '#667eea',
            'expiry_notice' => true
        ]
    ];

    /**
     * Get template configuration for document type
     *
     * @param string $type
     * @return array
     */
    public static function getTemplateConfig($type)
    {
        return self::$templates[$type] ?? self::$templates['invoice'];
    }

    /**
     * Get letter spacing style for CJK fonts
     *
     * @param bool $useCJKFont Whether CJK font is being used
     * @return string CSS letter-spacing property
     */
    public static function getCJKLetterSpacing($useCJKFont = false)
    {
        return $useCJKFont ? 'letter-spacing: 0.5px;' : '';
    }
    /**
     * Calculate optimal font size for company name to fit in container
     *
     * @param string $text The text to size
     * @param int $maxWidth Maximum width in pixels
     * @param int $baseFontSize Base font size
     * @param int $minFontSize Minimum font size
     * @return int Calculated font size
     */
    public static function calculateFontSize($text, $maxWidth, $baseFontSize = 24, $minFontSize = 12)
    {
        $textLength = strlen($text);
        // Conservative but not too aggressive estimation for TCPDF
        $estimatedWidth = $textLength * ($baseFontSize * 0.75); 
        
        if ($estimatedWidth > $maxWidth) {
            $fontSize = max($minFontSize, ($maxWidth / $textLength) * 1.0); // Less aggressive reduction
        } else {
            $fontSize = $baseFontSize;
        }
        
        return round($fontSize);
    }

    /**
     * Generate HTML content for invoice (PDF version)
     *
     * @param Invoice $invoice
     * @return string
     */
    public static function generateInvoiceHtml(Invoice $invoice)
    {
        $company = $invoice->company;
        $customer = $invoice->customer;
        $items = $invoice->invoiceItems;

        // Get template configuration
        $config = self::getTemplateConfig('invoice');
        $templateId = PdfTemplateManager::validateTemplateId($company->pdf_template ?? 'classic');
        
        // CSS Styles for PDF using selected template
        $css = PdfTemplateManager::generateTemplateStyles($templateId, $company->use_cjk_font);
        
        // HTML Content
        $html = $css;
        $html .= '<div style="page-break-inside: avoid;">';
        $html .= self::generateUnifiedPdfHeader($company, $config);
        $html .= self::generateUnifiedPdfSubHeader($customer, $invoice, $company, $config);
        $html .= self::generatePdfSeparator();
        $html .= '</div>';
        $html .= self::generatePdfItemsTable($items, $invoice);
        $html .= self::generatePdfTotalsSection($invoice);
        $html .= self::generatePdfNotes($invoice);

        return $html;
    }

    /**
     * Generate HTML content for estimate (PDF version)
     *
     * @param Estimate $estimate
     * @return string
     */
    public static function generateEstimateHtml(Estimate $estimate)
    {
        $company = $estimate->company;
        $customer = $estimate->customer;
        $items = $estimate->estimateItems;

        // Get template configuration
        $config = self::getTemplateConfig('estimate');
        $templateId = PdfTemplateManager::validateTemplateId($company->pdf_template ?? 'classic');
        
        // CSS Styles for PDF using selected template
        $css = PdfTemplateManager::generateTemplateStyles($templateId, $company->use_cjk_font);
        
        // HTML Content
        $html = $css;
        $html .= '<div style="page-break-inside: avoid;">';
        $html .= self::generateUnifiedPdfHeader($company, $config);
        $html .= self::generateUnifiedPdfSubHeader($customer, $estimate, $company, $config);
        $html .= self::generatePdfSeparator();
        $html .= '</div>';
        $html .= self::generatePdfItemsTable($items, $estimate);
        $html .= self::generateEstimatePdfTotalsSection($estimate);
        $html .= self::generateEstimatePdfNotes($estimate);

        return $html;
    }

    /**
     * Generate CSS styles for PDF based on template (DEPRECATED - use PdfTemplateManager)
     *
     * @param bool $useCJKFont Whether to use CJK fonts
     * @param string $colorScheme Primary color for the template
     * @return string
     * @deprecated Use PdfTemplateManager::generateTemplateStyles() instead
     */
    public static function getTemplateStyles($useCJKFont = false, $colorScheme = '#667eea')
    {
        // Fallback for backward compatibility - use classic template
        return PdfTemplateManager::generateTemplateStyles('classic', $useCJKFont);
    }

    /**
     * Backward compatibility method
     *
     * @param bool $useCJKFont
     * @return string
     * @deprecated Use PdfTemplateManager::generateTemplateStyles() instead
     */
    public static function getPdfStyles($useCJKFont = false)
    {
        return self::getTemplateStyles($useCJKFont, '#667eea');
    }

    /**
     * Generate unified PDF header section
     *
     * @param $company
     * @param array $config Template configuration
     * @return string
     */
    public static function generateUnifiedPdfHeader($company, $config)
    {
        // Get actual template accent color
        $templateId = PdfTemplateManager::validateTemplateId($company->pdf_template ?? 'classic');
        $template = PdfTemplateManager::getTemplate($templateId);
        $accentColor = $template['accent_color'];
        
        $html = '
        <!-- Header -->
        <div class="header">
            <table width="100%">
                <tr>
                    <td width="35%" class="company-info">
                        <div class="document-title">' . $config['title'] . '</div>
                        <strong>' . htmlspecialchars($company->company_name) . '</strong><br>';
        
        // Process company address line by line
        if ($company->company_address) {
            $addressLines = explode("\n", $company->company_address);
            foreach ($addressLines as $line) {
                $line = trim($line);
                if (!empty($line)) {
                    $html .= htmlspecialchars($line) . '<br>';
                }
            }
        }
        
        // Add company location information (city, state, zip code)
        $companyLocationParts = [];
        if ($company->city) $companyLocationParts[] = $company->city;
        if ($company->state) $companyLocationParts[] = $company->state;
        if ($company->zip_code) $companyLocationParts[] = $company->zip_code;
        if (!empty($companyLocationParts)) {
            $html .= htmlspecialchars(implode(', ', $companyLocationParts)) . '<br>';
        }
        if ($company->country && $company->country !== 'US') {
            $html .= htmlspecialchars($company->country) . '<br>';
        }

        if ($company->company_phone) {
            $html .= 'Phone: ' . htmlspecialchars($company->company_phone) . '<br>';
        }
        if ($company->company_email) {
            $html .= 'Email: ' . htmlspecialchars($company->company_email) . '<br>';
        }

        $html .= '
                    </td>
                    <td width="20%">&nbsp;</td>
                    <td width="45%" style="text-align: right; vertical-align: top;">';

        // Add logo if exists, otherwise show company name in large font
        if ($company->hasLogo()) {
            $logoPath = $company->getLogoAbsolutePath();
            if ($logoPath && file_exists($logoPath)) {
                // Get image dimensions
                $imageInfo = getimagesize($logoPath);
                if ($imageInfo) {
                    $originalWidth = $imageInfo[0];
                    $originalHeight = $imageInfo[1];
                    
                    // Calculate new dimensions with max height of 120px and max width of 45% of page width
                    $maxHeight = 120;
                    // Page width is approximately 595px for A4, so 45% is about 268px
                    $maxWidth = 268;
                    
                    $ratio = min($maxHeight / $originalHeight, $maxWidth / $originalWidth);
                    $newWidth = $originalWidth * $ratio;
                    $newHeight = $originalHeight * $ratio;
                    
                    $html .= '<img src="' . $logoPath . '" width="' . $newWidth . '" height="' . $newHeight . '" alt="Company Logo" style="vertical-align: top;">';
                } else {
                    // Fallback with 45% page width limit
                    $html .= '<img src="' . $logoPath . '" width="268" height="120" alt="Company Logo" style="vertical-align: top;">';
                }
            }
        } else {
            // No logo - display company name in large font with dynamic sizing
            $companyName = htmlspecialchars($company->company_name);
            $maxWidth = 268; // Max width in pixels (45% of A4 page width)
            $fontSize = self::calculateFontSize($company->company_name, $maxWidth, 20, 12); // Better balance
            
            // Replace spaces with non-breaking spaces to prevent line breaks
            $companyNameNoBreak = str_replace(' ', '&nbsp;', $companyName);
            
            // Better positioned and sized company name display with top alignment
            $html .= '<div style="font-size: ' . $fontSize . 'px; font-weight: bold; color: ' . $accentColor . '; margin-bottom: 10px; text-align: right; white-space: nowrap; max-width: ' . $maxWidth . 'px; line-height: 1.2; vertical-align: top;">' . $companyNameNoBreak . '</div>';
        }

        $html .= '
                    </td>
                </tr>
            </table>
        </div>';

        return $html;
    }

    /**
     * Legacy PDF header method for backward compatibility
     *
     * @param $company
     * @return string
     */
    public static function generatePdfHeader($company)
    {
        return self::generateUnifiedPdfHeader($company, self::getTemplateConfig('invoice'));
    }

    /**
     * Generate unified PDF sub-header section
     *
     * @param $customer
     * @param $document
     * @param $company
     * @param array $config Template configuration
     * @return string
     */
    public static function generateUnifiedPdfSubHeader($customer, $document, $company, $config)
    {
        $html = '
        <!-- Sub Header with 3 columns -->
        <div class="sub-header">
            <table width="100%">
                <tr>
                    <td width="33%" style="vertical-align: top; padding: 0;">
                        <div style="background: none; padding: 0; margin: 0;">
                            <strong>Bill To:</strong><br>
                            <strong>' . htmlspecialchars($customer->customer_name) . '</strong><br>';
        // Process customer billing address using structured fields first, then fallback to address field
        if ($customer->customer_address) {
            $addressLines = explode("\n", $customer->customer_address);
            foreach ($addressLines as $line) {
                $line = trim($line);
                if (!empty($line)) {
                    $html .= htmlspecialchars($line) . '<br>';
                }
            }
        }
        // Add structured location information
        $locationParts = [];
        if ($customer->city) $locationParts[] = $customer->city;
        if ($customer->state) $locationParts[] = $customer->state;
        if ($customer->zip_code) $locationParts[] = $customer->zip_code;
        if (!empty($locationParts)) {
            $html .= htmlspecialchars(implode(', ', $locationParts)) . '<br>';
        }
        if ($customer->country && $customer->country !== 'US') {
            $html .= htmlspecialchars($customer->country) . '<br>';
        }
        if ($customer->customer_phone) {
            $html .= 'Phone: ' . htmlspecialchars($customer->customer_phone) . '<br>';
        }
        if ($customer->customer_fax && $config['title'] === 'INVOICE') {
            $html .= 'Fax: ' . htmlspecialchars($customer->customer_fax) . '<br>';
        }
        if ($customer->customer_mobile && $config['title'] === 'INVOICE') {
            $html .= 'Mobile: ' . htmlspecialchars($customer->customer_mobile) . '<br>';
        }
        if ($customer->customer_email) {
            $html .= 'Email: ' . htmlspecialchars($customer->customer_email) . '<br>';
        }

        $html .= '
                        </div>
                    </td>
                    <td width="33%" style="vertical-align: top; padding: 0;">
                        <div style="background: none; padding: 0; margin: 0;">
                            <strong>Ship To:</strong><br>';
        
        // Handle shipping address based on document type
        if ($config['title'] === 'ESTIMATE' && isset($document->ship_to_address) && $document->ship_to_address) {
            $shipLines = explode("\n", $document->ship_to_address);
            foreach ($shipLines as $line) {
                $line = trim($line);
                if (!empty($line)) {
                    $html .= htmlspecialchars($line) . '<br>';
                }
            }
        } else {
            $html .= '<strong>' . htmlspecialchars($customer->customer_name) . '</strong><br>';
            if ($customer->customer_address) {
                $addressLines = explode("\n", $customer->customer_address);
                foreach ($addressLines as $line) {
                    $line = trim($line);
                    if (!empty($line)) {
                        $html .= htmlspecialchars($line) . '<br>';
                    }
                }
            }
            // Add structured location information
            $locationParts = [];
            if ($customer->city) $locationParts[] = $customer->city;
            if ($customer->state) $locationParts[] = $customer->state;
            if ($customer->zip_code) $locationParts[] = $customer->zip_code;
            if (!empty($locationParts)) {
                $html .= htmlspecialchars(implode(', ', $locationParts)) . '<br>';
            }
            if ($customer->country && $customer->country !== 'US') {
                $html .= htmlspecialchars($customer->country) . '<br>';
            }
        }

        $html .= '
                        </div>
                    </td>
                    <td width="34%" style="vertical-align: top; padding: 0;">
                        <div style="background: none; padding: 0; margin: 0;">
                            <table>
                                <tr>
                                    <td><strong>' . ucfirst($config['title']) . ' #:</strong></td>
                                    <td>' . htmlspecialchars($document->{$config['number_field']}) . '</td>
                                </tr>
                                <tr>
                                    <td><strong>Date:</strong></td>
                                    <td>' . date('F j, Y', strtotime($document->{$config['date_field']})) . '</td>
                                </tr>';

        if ($document->{$config['due_date_field']}) {
            $dueDateLabel = $config['title'] === 'INVOICE' ? 'Due Date' : 'Expiry Date';
            $html .= '
                                <tr>
                                    <td><strong>' . $dueDateLabel . ':</strong></td>
                                    <td>' . date('F j, Y', strtotime($document->{$config['due_date_field']})) . '</td>
                                </tr>';
        }

        if ($config['show_terms']) {
            if ($config['terms_source'] === 'company') {
                $html .= '
                                <tr>
                                    <td><strong>Terms:</strong></td>
                                    <td>Net ' . $company->due_date_days . ' Days</td>
                                </tr>';
            } elseif ($config['terms_source'] === 'document' && isset($document->terms) && $document->terms) {
                $html .= '
                                <tr>
                                    <td><strong>Terms:</strong></td>
                                    <td>' . htmlspecialchars($document->terms) . '</td>
                                </tr>';
            }
        }

        $html .= '
                            </table>
                        </div>
                    </td>
                </tr>
            </table>
        </div>';

        return $html;
    }

    /**
     * Legacy PDF sub-header method for backward compatibility
     *
     * @param $customer
     * @param $invoice
     * @return string
     */
    public static function generatePdfSubHeader($customer, $invoice, $company)
    {
        return self::generateUnifiedPdfSubHeader($customer, $invoice, $company, self::getTemplateConfig('invoice'));
    }


    /**
     * Generate PDF separator
     *
     * @return string
     */
    public static function generatePdfSeparator()
    {
        return '
        <!-- Separator -->
        <div class="separator"></div>';
    }

    /**
     * Generate PDF items table
     *
     * @param $items
     * @param $invoice
     * @return string
     */
    public static function generatePdfItemsTable($items, $invoice)
    {
        $html = '
        <table class="items-table" cellpadding="0" cellspacing="0">
            <thead>
                <tr>
                    <th style="width: 62%;">Description</th>
                    <th style="width: 10%; text-align: center;">Qty</th>
                    <th style="width: 14%; text-align: right;">Rate</th>
                    <th style="width: 14%; text-align: right;">Amount</th>
                </tr>
            </thead>
            <tbody>';

        foreach ($items as $item) {
            $description = '';
            
            // Add Product/Service name if exists
            if (!empty($item->product_service_name)) {
                $description .= '<strong>' . htmlspecialchars($item->product_service_name) . '</strong>';
                if (!empty($item->description)) {
                    $description .= '<br>' . nl2br(htmlspecialchars($item->description));
                }
            } else if (!empty($item->description)) {
                $description = nl2br(htmlspecialchars($item->description));
            }
            
            $html .= '
                <tr>
                    <td>' . $description . '</td>
                    <td style="text-align: center;">' . $item->getFormattedQuantity() . '</td>
                    <td style="text-align: right;">' . $invoice->formatAmount($item->rate) . '</td>
                    <td style="text-align: right;">' . $invoice->formatAmount($item->amount) . '</td>
                </tr>';
        }

        $html .= '
            </tbody>
        </table>';

        return $html;
    }

    /**
     * Generate PDF totals section
     *
     * @param $invoice
     * @return string
     */
    public static function generatePdfTotalsSection($invoice)
    {
        $html = '
        <div class="totals">
            <table width="100%">
                <tr>
                    <td width="60%">&nbsp;</td>
                    <td width="40%">
                        <table class="totals-table" width="100%" cellpadding="6" cellspacing="0">
                <tr>
                    <td>&nbsp;<strong>Subtotal:</strong>&nbsp;</td>
                    <td style="text-align: right;">&nbsp;' . $invoice->formatAmount($invoice->subtotal) . '&nbsp;</td>
                </tr>
                <tr>
                    <td>&nbsp;<strong>Tax (' . number_format($invoice->tax_rate, 1) . '%):</strong>&nbsp;</td>
                    <td style="text-align: right;">&nbsp;' . $invoice->formatAmount($invoice->tax_amount) . '&nbsp;</td>
                </tr>';
                
        // Add shipping fee if exists
        if ($invoice->shipping_fee > 0) {
            $html .= '
                <tr>
                    <td>&nbsp;<strong>Shipping Fee:</strong>&nbsp;</td>
                    <td style="text-align: right;">&nbsp;' . $invoice->formatAmount($invoice->shipping_fee) . '&nbsp;</td>
                </tr>';
        }
        
        $html .= '
                <tr class="total-row">
                    <td>&nbsp;<strong>TOTAL:</strong>&nbsp;</td>
                    <td style="text-align: right; font-size: 11px;">&nbsp;' . $invoice->formatAmount($invoice->total_amount) . '&nbsp;</td>
                </tr>';

        // Add payment information if any payments exist
        $totalPaid = $invoice->getTotalPaidAmount();
        if ($totalPaid > 0) {
            $html .= '
                <tr style="background-color: #e8f5e8;">
                    <td>&nbsp;<strong>PAID:</strong>&nbsp;</td>
                    <td style="text-align: right; color: #28a745; font-weight: bold;">&nbsp;-' . $invoice->formatAmount($totalPaid) . '&nbsp;</td>
                </tr>';
        }

        $remainingBalance = $invoice->getRemainingBalance();
        if ($totalPaid > 0) {
            $balanceRowClass = $remainingBalance > 0 ? 'style="background-color: #fff3cd;"' : 'style="background-color: #d4edda;"';
            $balanceColor = $remainingBalance > 0 ? 'color: #856404;' : 'color: #155724;';
            $html .= '
                <tr ' . $balanceRowClass . '>
                    <td>&nbsp;<strong>BALANCE DUE:</strong>&nbsp;</td>
                    <td style="text-align: right; font-size: 11px; font-weight: bold; ' . $balanceColor . '">&nbsp;' . $invoice->formatAmount($remainingBalance) . '&nbsp;</td>
                </tr>';
        }

        $html .= '
                        </table>
                    </td>
                </tr>
            </table>
        </div>';

        return $html;
    }

    /**
     * Generate PDF notes section
     *
     * @param $invoice
     * @return string
     */
    public static function generatePdfNotes($invoice)
    {
        if ($invoice->notes) {
            return '
        <div class="notes">
            <strong>Notes:</strong><br>
            ' . nl2br(htmlspecialchars($invoice->notes)) . '
        </div>';
        }
        return '';
    }

    /**
     * Generate PDF totals section for estimate
     *
     * @param $estimate
     * @return string
     */
    public static function generateEstimatePdfTotalsSection($estimate)
    {
        $html = '
        <div class="totals">
            <table width="100%">
                <tr>
                    <td width="60%">&nbsp;</td>
                    <td width="40%">
                        <table class="totals-table" width="100%" cellpadding="6" cellspacing="0">
                <tr>
                    <td>&nbsp;<strong>Subtotal:</strong>&nbsp;</td>
                    <td style="text-align: right;">&nbsp;' . $estimate->formatAmount($estimate->subtotal) . '&nbsp;</td>
                </tr>';

        // Add discount if exists
        if ($estimate->discount_amount > 0) {
            $discountLabel = $estimate->discount_type == 'percentage' ? 
                'Discount (' . $estimate->discount_value . '%):' : 
                'Discount (Fixed):';
            $html .= '
                <tr>
                    <td>&nbsp;<strong>' . $discountLabel . '</strong>&nbsp;</td>
                    <td style="text-align: right; color: #dc3545;">&nbsp;-' . $estimate->formatAmount($estimate->discount_amount) . '&nbsp;</td>
                </tr>';
        }

        // Add tax if exists
        if ($estimate->tax_amount > 0) {
            $html .= '
                <tr>
                    <td>&nbsp;<strong>Tax (' . number_format($estimate->tax_rate, 1) . '%):</strong>&nbsp;</td>
                    <td style="text-align: right;">&nbsp;' . $estimate->formatAmount($estimate->tax_amount) . '&nbsp;</td>
                </tr>';
        }
        
        // Add shipping fee if exists
        if ($estimate->shipping_fee > 0) {
            $html .= '
                <tr>
                    <td>&nbsp;<strong>Shipping Fee:</strong>&nbsp;</td>
                    <td style="text-align: right;">&nbsp;' . $estimate->formatAmount($estimate->shipping_fee) . '&nbsp;</td>
                </tr>';
        }

        $html .= '
                <tr class="total-row">
                    <td>&nbsp;<strong>TOTAL:</strong>&nbsp;</td>
                    <td style="text-align: right; font-size: 11px;">&nbsp;' . $estimate->formatAmount($estimate->total_amount) . '&nbsp;</td>
                </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>';

        return $html;
    }

    /**
     * Generate PDF notes section for estimate
     *
     * @param $estimate
     * @return string
     */
    public static function generateEstimatePdfNotes($estimate)
    {
        $html = '';
        
        if ($estimate->customer_notes) {
            $html .= '
            <div class="notes">
                <strong>Notes:</strong><br>
                ' . nl2br(htmlspecialchars($estimate->customer_notes)) . '
            </div>';
        }
        
        if ($estimate->payment_instructions) {
            $html .= '
            <div class="notes">
                <strong>Payment Instructions:</strong><br>
                ' . nl2br(htmlspecialchars($estimate->payment_instructions)) . '
            </div>';
        }
        
        if ($estimate->expiry_date) {
            $html .= '
            <div style="margin-top: 15px; padding: 15px; background-color: #fff3cd !important; border-left: 4px solid #ffc107; border-radius: 4px;">
                <strong style="background-color: #fff3cd">This estimate is valid until ' . date('F j, Y', strtotime($estimate->expiry_date)) . '</strong>
            </div>';
        }
        
        return $html;
    }


    /**
     * Add PAID watermark to PDF
     *
     * @param TCPDF $pdf
     */
    public static function addPaidWatermark($pdf)
    {
        // Get current page number
        $pageCount = $pdf->getNumPages();
        
        // Add watermark to each page
        for ($i = 1; $i <= $pageCount; $i++) {
            $pdf->setPage($i);
            
            // Save the current graphic state
            $pdf->StartTransform();
            
            // Set the watermark text properties
            try {
                $pdf->SetFont('dejavusans', 'B', 80);
            } catch (Exception $e) {
                try {
                    $pdf->SetFont('freeserif', 'B', 80);
                } catch (Exception $e2) {
                    try {
                        $pdf->SetFont('times', 'B', 80);
                    } catch (Exception $e3) {
                        $pdf->SetFont('helvetica', 'B', 80);
                    }
                }
            }
            $pdf->SetTextColor(220, 220, 220); // Light gray color
            $pdf->SetAlpha(0.3); // Transparency
            
            // Calculate position (center of page)
            $pageWidth = $pdf->getPageWidth();
            $pageHeight = $pdf->getPageHeight();
            $x = $pageWidth / 2;
            $y = $pageHeight / 2;
            
            // Rotate text -45 degrees
            $pdf->Rotate(-45, $x, $y);
            
            // Add the PAID text
            $pdf->Text($x - 40, $y, 'PAID');
            
            // Restore the graphic state
            $pdf->StopTransform();
        }
        
        // Reset text properties
        $pdf->SetTextColor(0, 0, 0); // Black
        $pdf->SetAlpha(1); // Full opacity
    }

    /**
     * Generate invoice preview HTML (for web display)
     *
     * @param Invoice $invoice
     * @return string
     */
    public static function generateInvoicePreviewHtml(Invoice $invoice)
    {
        $company = $invoice->company;
        $customer = $invoice->customer;
        $items = $invoice->invoiceItems;

        // Get template configuration
        $config = self::getTemplateConfig('invoice');
        $templateId = PdfTemplateManager::validateTemplateId($company->pdf_template ?? 'classic');
        
        ob_start();
        ?>
<div class="document-preview-container"
	style="max-width: 1000px; margin: 0 auto; padding: 20px; font-family: Arial, sans-serif; position: relative;">

	<?php echo PdfTemplateManager::generatePreviewStyles($templateId); ?>

	<?php if ($invoice->status === 'paid' || $invoice->isFullyPaid()): ?>
	<div class="paid-watermark">PAID</div>
	<?php endif; ?>

	<?php echo self::generateUnifiedPreviewHeader($company, $config); ?>
	<?php echo self::generatePreviewSubHeader($customer, $invoice, $company); ?>
	<?php echo self::generatePreviewSeparator(); ?>
	<?php echo self::generatePreviewItemsTable($items, $invoice); ?>
	<?php echo self::generatePreviewTotalsSection($invoice); ?>
	<?php echo self::generatePreviewNotes($invoice); ?>
	<?php echo self::generatePreviewFooter(); ?>

</div>
<?php
        return ob_get_clean();
    }

    /**
     * Generate CSS styles for web preview based on template (DEPRECATED - use PdfTemplateManager)
     *
     * @param string $colorScheme Primary color for the template
     * @return string
     * @deprecated Use PdfTemplateManager::generatePreviewStyles() instead
     */
    public static function getTemplatePreviewStyles($colorScheme = '#667eea')
    {
        // Fallback for backward compatibility - use classic template
        return PdfTemplateManager::generatePreviewStyles('classic');
    }

    /**
     * Backward compatibility method
     *
     * @return string
     * @deprecated Use PdfTemplateManager::generatePreviewStyles() instead
     */
    public static function getPreviewStyles()
    {
        return self::getTemplatePreviewStyles('#667eea');
    }

    /**
     * Generate unified preview header section
     *
     * @param $company
     * @param array $config Template configuration
     * @return string
     */
    public static function generateUnifiedPreviewHeader($company, $config)
    {
        // Get actual template accent color
        $templateId = PdfTemplateManager::validateTemplateId($company->pdf_template ?? 'classic');
        $template = PdfTemplateManager::getTemplate($templateId);
        $accentColor = $template['accent_color'];
        
        ob_start();
        ?>
<!-- Header -->
<div class="document-header">
	<table style="width: 100%;">
		<tr>
			<td style="width: 35%; vertical-align: top;">
				<div class="company-info">
					<h2><span><?= $config['title'] ?></span></h2>
					<strong><?= htmlspecialchars($company->company_name) ?></strong><br>
					<?php if ($company->company_address): ?>
					<?php foreach (explode("\n", $company->company_address) as $line): ?>
					<?php $line = trim($line); if (!empty($line)): ?>
					<?= htmlspecialchars($line) ?><br>
					<?php endif; // !empty($line) ?>
					<?php endforeach; // explode("\n", $company->company_address) ?>
					<?php endif; // $company->company_address ?>
					<?php 
						$companyLocationParts = [];
						if ($company->city) $companyLocationParts[] = $company->city;
						if ($company->state) $companyLocationParts[] = $company->state;
						if ($company->zip_code) $companyLocationParts[] = $company->zip_code;
						if (!empty($companyLocationParts)): 
					?>
					<?= htmlspecialchars(implode(', ', $companyLocationParts)) ?><br>
					<?php endif; // !empty($companyLocationParts) ?>
					<?php if ($company->country && $company->country !== 'US'): ?>
					<?= htmlspecialchars($company->country) ?><br>
					<?php endif; // $company->country ?>
					<?php if ($company->company_phone): ?>
					Phone: <?= htmlspecialchars($company->company_phone) ?><br>
					<?php endif; // $company->company_phone ?>
					<?php if ($company->company_email): ?>
					Email: <?= htmlspecialchars($company->company_email) ?>
					<?php endif; // $company->company_email ?>
				</div>
			</td>
			<td style="width: 20%;">&nbsp;</td>
			<td style="width: 45%; text-align: right; vertical-align: top;">
				<div class="logo-section">
					<?php if ($company->hasLogo()): ?>
					<img src="<?= $company->getLogoUrl() ?>" alt="Company Logo" class="logo"
						style="max-height: 120px; max-width: 405px; height: auto;">
					<?php else: ?>
					<?php 
						$maxWidth = 405; // 45% of web preview width
						$fontSize = self::calculateFontSize($company->company_name, $maxWidth, 24, 16);
						$companyNameNoBreak = str_replace(' ', '&nbsp;', htmlspecialchars($company->company_name));
					?>
					<br><br>
					<div
						style="font-size: <?= $fontSize ?>px; font-weight: bold; color: <?= $accentColor ?>; white-space: nowrap; max-width: <?= $maxWidth ?>px; text-align: right; line-height: 1.2;">
						<?= $companyNameNoBreak ?></div>
					<?php endif; // $company->hasLogo() ?>
				</div>
			</td>
		</tr>
	</table>
</div>
<?php
        return ob_get_clean();
    }

    /**
     * Legacy preview header method for backward compatibility
     *
     * @param $company
     * @return string
     */
    public static function generatePreviewHeader($company)
    {
        return self::generateUnifiedPreviewHeader($company, self::getTemplateConfig('invoice'));
    }

    /**
     * Generate unified preview sub-header section
     *
     * @param $customer
     * @param $document
     * @param $company
     * @param array $config Template configuration
     * @return string
     */
    public static function generateUnifiedPreviewSubHeader($customer, $document, $company, $config)
    {
        ob_start();
        ?>
<!-- Sub Header with 3 columns -->
<div class="sub-header">
	<table>
		<tr>
			<td class="sub-header-column">
				<div class="bill-to">
					<strong>Bill To:</strong><br>
					<strong><?= htmlspecialchars($customer->customer_name) ?></strong><br>
					<?php if ($customer->customer_address): ?>
					<?php foreach (explode("\n", $customer->customer_address) as $line): ?>
					<?php $line = trim($line); if (!empty($line)): ?>
					<?= htmlspecialchars($line) ?><br>
					<?php endif; ?>
					<?php endforeach; ?>
					<?php endif; ?>
					<?php 
						$locationParts = [];
						if ($customer->city) $locationParts[] = $customer->city;
						if ($customer->state) $locationParts[] = $customer->state;
						if ($customer->zip_code) $locationParts[] = $customer->zip_code;
						if (!empty($locationParts)): 
					?>
					<?= htmlspecialchars(implode(', ', $locationParts)) ?><br>
					<?php endif; ?>
					<?php if ($customer->country && $customer->country !== 'US'): ?>
					<?= htmlspecialchars($customer->country) ?><br>
					<?php endif; ?>
					<?php if ($customer->customer_phone): ?>
					Phone: <?= htmlspecialchars($customer->customer_phone) ?><br>
					<?php endif; ?>
					<?php if ($customer->customer_fax && $config['title'] === 'INVOICE'): ?>
					Fax: <?= htmlspecialchars($customer->customer_fax) ?><br>
					<?php endif; ?>
					<?php if ($customer->customer_mobile && $config['title'] === 'INVOICE'): ?>
					Mobile: <?= htmlspecialchars($customer->customer_mobile) ?><br>
					<?php endif; ?>
					<?php if ($customer->customer_email): ?>
					Email: <?= htmlspecialchars($customer->customer_email) ?>
					<?php endif; ?>
				</div>
			</td>
			<td class="sub-header-column">
				<div class="ship-to">
					<strong>Ship To:</strong><br>
					<?php if ($config['title'] === 'ESTIMATE' && isset($document->ship_to_address) && $document->ship_to_address): ?>
					<?php foreach (explode("\n", $document->ship_to_address) as $line): ?>
					<?php $line = trim($line); if (!empty($line)): ?>
					<?= htmlspecialchars($line) ?><br>
					<?php endif; ?>
					<?php endforeach; ?>
					<?php else: ?>
					<strong><?= htmlspecialchars($customer->customer_name) ?></strong><br>
					<?php if ($customer->customer_address): ?>
					<?php foreach (explode("\n", $customer->customer_address) as $line): ?>
					<?php $line = trim($line); if (!empty($line)): ?>
					<?= htmlspecialchars($line) ?><br>
					<?php endif; ?>
					<?php endforeach; ?>
					<?php endif; ?>
					<?php 
						$locationParts = [];
						if ($customer->city) $locationParts[] = $customer->city;
						if ($customer->state) $locationParts[] = $customer->state;
						if ($customer->zip_code) $locationParts[] = $customer->zip_code;
						if (!empty($locationParts)): 
					?>
					<?= htmlspecialchars(implode(', ', $locationParts)) ?><br>
					<?php endif; ?>
					<?php if ($customer->country && $customer->country !== 'US'): ?>
					<?= htmlspecialchars($customer->country) ?><br>
					<?php endif; ?>
					<?php endif; ?>
				</div>
			</td>
			<td class="sub-header-column">
				<div style="background: none; padding: 0; margin: 0;">
					<table>
						<tr>
							<td><strong><?= ucfirst($config['title']) ?> #:</strong></td>
							<td><?= htmlspecialchars($document->{$config['number_field']}) ?></td>
						</tr>
						<tr>
							<td><strong>Date:</strong></td>
							<td><?= date('F j, Y', strtotime($document->{$config['date_field']})) ?></td>
						</tr>
						<?php if ($document->{$config['due_date_field']}): ?>
						<?php $dueDateLabel = $config['title'] === 'INVOICE' ? 'Due Date' : 'Expiry Date'; ?>
						<tr>
							<td><strong><?= $dueDateLabel ?>:</strong></td>
							<td><?= date('F j, Y', strtotime($document->{$config['due_date_field']})) ?></td>
						</tr>
						<?php endif; ?>
						<?php if ($config['show_terms']): ?>
						<?php if ($config['terms_source'] === 'company'): ?>
						<tr>
							<td><strong>Terms:</strong></td>
							<td>Net <?= $company->due_date_days ?> Days</td>
						</tr>
						<?php elseif ($config['terms_source'] === 'document' && isset($document->terms) && $document->terms): ?>
						<tr>
							<td><strong>Terms:</strong></td>
							<td><?= htmlspecialchars($document->terms) ?></td>
						</tr>
						<?php endif; ?>
						<?php endif; ?>
					</table>
				</div>
			</td>
		</tr>
	</table>
</div>
<?php
        return ob_get_clean();
    }

    /**
     * Generate preview sub-header section (backward compatibility)
     *
     * @param $customer
     * @param $invoice
     * @param $company
     * @return string
     */
    public static function generatePreviewSubHeader($customer, $invoice, $company)
    {
        return self::generateUnifiedPreviewSubHeader($customer, $invoice, $company, self::getTemplateConfig('invoice'));
    }

    /**
     * Generate preview separator
     *
     * @return string
     */
    public static function generatePreviewSeparator()
    {
        return '<!-- Separator --><div class="separator"></div>';
    }

    /**
     * Generate preview items table
     *
     * @param $items
     * @param $invoice
     * @return string
     */
    public static function generatePreviewItemsTable($items, $invoice)
    {
        ob_start();
        ?>
<table class="items-table">
	<thead>
		<tr>
			<th style="width: 70%;">Description</th>
			<th style="width: 10%; text-align: center;">Qty</th>
			<th style="width: 10%; text-align: right;">Rate</th>
			<th style="width: 10%; text-align: right;">Amount</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($items as $item): ?>
		<tr>
			<td>
				<?php if (!empty($item->product_service_name)): ?>
				<strong><?= htmlspecialchars($item->product_service_name) ?></strong>
				<?php if (!empty($item->description)): ?>
				<br><small><?= nl2br(htmlspecialchars($item->description)) ?></small>
				<?php endif; ?>
				<?php elseif (!empty($item->description)): ?>
				<?= nl2br(htmlspecialchars($item->description)) ?>
				<?php endif; ?>
			</td>
			<td style="text-align: right;"><?= $item->getFormattedQuantity() ?></td>
			<td class="text-right"><?= $invoice->formatAmount($item->rate) ?></td>
			<td class="text-right"><?= $invoice->formatAmount($item->amount) ?></td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>
<?php
        return ob_get_clean();
    }

    /**
     * Generate preview totals section
     *
     * @param $invoice
     * @return string
     */
    public static function generatePreviewTotalsSection($invoice)
    {
        ob_start();
        ?>
<div class="totals-section">
	<table class="totals-table">
		<tr>
			<td><strong>Subtotal:</strong></td>
			<td class="text-right"><?= $invoice->formatAmount($invoice->subtotal) ?></td>
		</tr>
		<tr>
			<td><strong>Tax (<?= number_format($invoice->tax_rate, 1) ?>%):</strong></td>
			<td class="text-right"><?= $invoice->formatAmount($invoice->tax_amount) ?></td>
		</tr>
		<tr class="total-row">
			<td><strong>TOTAL:</strong></td>
			<td class="text-right"><?= $invoice->formatAmount($invoice->total_amount) ?></td>
		</tr>

		<?php $totalPaid = $invoice->getTotalPaidAmount(); ?>
		<?php if ($totalPaid > 0): ?>
		<tr style="background-color: #e8f5e8;">
			<td><strong>PAID:</strong></td>
			<td class="text-right" style="color: #28a745; font-weight: bold;">
				-<?= $invoice->formatAmount($totalPaid) ?>
			</td>
		</tr>
		<?php endif; ?>

		<?php if ($totalPaid > 0): ?>
		<?php $remainingBalance = $invoice->getRemainingBalance(); ?>
		<tr style="background-color: <?= $remainingBalance > 0 ? '#fff3cd' : '#d4edda' ?>;">
			<td><strong>BALANCE DUE:</strong></td>
			<td class="text-right"
				style="font-size: 16px; font-weight: bold; color: <?= $remainingBalance > 0 ? '#856404' : '#155724' ?>;">
				<?= $invoice->formatAmount($remainingBalance) ?>
			</td>
		</tr>
		<?php endif; ?>
	</table>
</div>
<?php
        return ob_get_clean();
    }

    /**
     * Generate preview notes section
     *
     * @param $invoice
     * @return string
     */
    public static function generatePreviewNotes($invoice)
    {
        if ($invoice->notes) {
            return '
            <div class="notes-section">
                <strong>Notes:</strong><br>
                ' . nl2br(htmlspecialchars($invoice->notes)) . '
            </div>';
        }
        return '';
    }

    /**
     * Generate preview footer
     *
     * @return string
     */
    public static function generatePreviewFooter()
    {
        return '
        <div style="margin-top: 40px; text-align: center; font-size: 12px; color: #666 !important; border-top: 1px solid #eee; padding-top: 20px;">
            Generated by ' . (\Yii::$app->params['siteName'] ?? 'Invoice Manager') . ' on ' . date('F j, Y \a\t g:i A') . '
        </div>';
    }

    /**
     * Generate estimate preview HTML (for web display)
     *
     * @param Estimate $estimate
     * @return string
     */
    public static function generateEstimatePreviewHtml(Estimate $estimate)
    {
        $company = $estimate->company;
        $customer = $estimate->customer;
        $items = $estimate->estimateItems;

        // Get template configuration
        $config = self::getTemplateConfig('estimate');
        $templateId = PdfTemplateManager::validateTemplateId($company->pdf_template ?? 'classic');
        
        ob_start();
        ?>
<div class="document-preview-container"
	style="max-width: 1000px; margin: 0 auto; padding: 20px; font-family: Arial, sans-serif; position: relative;">

	<?php echo PdfTemplateManager::generatePreviewStyles($templateId); ?>

	<?php echo self::generateUnifiedPreviewHeader($company, $config); ?>
	<?php echo self::generateEstimatePreviewSubHeader($customer, $estimate, $company); ?>
	<?php echo self::generatePreviewSeparator(); ?>
	<?php echo self::generatePreviewItemsTable($items, $estimate); ?>
	<?php echo self::generateEstimatePreviewTotalsSection($estimate); ?>
	<?php echo self::generateEstimatePreviewNotes($estimate); ?>
	<?php echo self::generatePreviewFooter(); ?>

</div>
<?php
        return ob_get_clean();
    }

    /**
     * Generate estimate preview header section
     *
     * @param $company
     * @return string
     */
    public static function generateEstimatePreviewHeader($company)
    {
        return self::generateUnifiedPreviewHeader($company, self::getTemplateConfig('estimate'));
    }

    

    /**
     * Generate estimate preview sub-header section
     *
     * @param $customer
     * @param $estimate
     * @param $company
     * @return string
     */
    public static function generateEstimatePreviewSubHeader($customer, $estimate, $company)
    {
        return self::generateUnifiedPreviewSubHeader($customer, $estimate, $company, self::getTemplateConfig('estimate'));
    }

    /**
     * Generate estimate preview items table
     *
     * @param $items
     * @param $estimate
     * @return string
     */
    public static function generateEstimatePreviewItemsTable($items, $estimate)
    {
        ob_start();
        ?>
<table class="items-table">
	<thead>
		<tr>
			<th style="width: 70%;">Description</th>
			<th style="width: 10%; text-align: center;">Qty</th>
			<th style="width: 10%; text-align: right;">Rate</th>
			<th style="width: 10%; text-align: right;">Amount</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($items as $item): ?>
		<tr>
			<td>
				<?php if (!empty($item->product_service_name)): ?>
				<strong><?= htmlspecialchars($item->product_service_name) ?></strong>
				<?php if (!empty($item->description)): ?>
				<br><small><?= nl2br(htmlspecialchars($item->description)) ?></small>
				<?php endif; ?>
				<?php elseif (!empty($item->description)): ?>
				<?= nl2br(htmlspecialchars($item->description)) ?>
				<?php endif; ?>
			</td>
			<td style="text-align: center;"><?= $item->getFormattedQuantity() ?></td>
			<td class="text-right"><?= $estimate->formatAmount($item->rate) ?></td>
			<td class="text-right"><?= $estimate->formatAmount($item->amount) ?></td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>
<?php
        return ob_get_clean();
    }

    /**
     * Generate estimate preview totals section
     *
     * @param $estimate
     * @return string
     */
    public static function generateEstimatePreviewTotalsSection($estimate)
    {
        ob_start();
        ?>
<div class="totals-section">
	<table class="totals-table">
		<tr>
			<td><strong>Subtotal:</strong></td>
			<td class="text-right"><?= $estimate->formatAmount($estimate->subtotal) ?></td>
		</tr>
		<?php if ($estimate->discount_amount > 0): ?>
		<tr>
			<td>
				<strong>Discount
					<?php if ($estimate->discount_type == 'percentage'): ?>
					(<?= $estimate->discount_value ?>%):
					<?php else: ?>
					(Fixed):
					<?php endif; ?>
				</strong>
			</td>
			<td class="text-right" style="color: #dc3545;">-<?= $estimate->formatAmount($estimate->discount_amount) ?>
			</td>
		</tr>
		<?php endif; ?>
		<?php if ($estimate->tax_amount > 0): ?>
		<tr>
			<td><strong>Tax (<?= number_format($estimate->tax_rate, 1) ?>%):</strong></td>
			<td class="text-right"><?= $estimate->formatAmount($estimate->tax_amount) ?></td>
		</tr>
		<?php endif; ?>
		<tr class="total-row">
			<td><strong>TOTAL:</strong></td>
			<td class="text-right"><?= $estimate->formatAmount($estimate->total_amount) ?></td>
		</tr>
	</table>
</div>
<?php
        return ob_get_clean();
    }

    /**
     * Generate estimate preview notes section
     *
     * @param $estimate
     * @return string
     */
    public static function generateEstimatePreviewNotes($estimate)
    {
        $html = '';
        
        if ($estimate->customer_notes) {
            $html .= '
            <div class="notes-section">
                <strong>Notes:</strong><br>
                ' . nl2br(htmlspecialchars($estimate->customer_notes)) . '
            </div>';
        }
        
        if ($estimate->payment_instructions) {
            $html .= '
            <div class="notes-section">
                <strong>Payment Instructions:</strong><br>
                ' . nl2br(htmlspecialchars($estimate->payment_instructions)) . '
            </div>';
        }
        
        if ($estimate->expiry_date) {
            $html .= '
            <div style="margin-top: 30px; padding: 15px; background: #fff3cd !important; border-left: 4px solid #ffc107; border-radius: 4px;">
                <strong style="background-color: #fff3cd !important">This estimate is valid until ' . date('F j, Y', strtotime($estimate->expiry_date)) . '
            </div>';
        }
        
        return $html;
    }
    
    
}