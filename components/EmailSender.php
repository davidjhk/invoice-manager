<?php

namespace app\components;

use Yii;
use app\models\Invoice;
use app\models\Company;

/**
 * Email sender component using SMTP2GO API
 */
class EmailSender
{
    /**
     * Send invoice email via SMTP2GO
     *
     * @param Invoice $invoice
     * @param string $recipientEmail
     * @param string $subject
     * @param string $message
     * @param bool $attachPdf
     * @return array
     */
    public static function sendInvoiceEmail(Invoice $invoice, $recipientEmail, $subject = null, $message = null, $attachPdf = true)
    {
        $company = $invoice->company;
        
        if (empty($company->smtp2go_api_key)) {
            return [
                'success' => false,
                'message' => 'SMTP2GO API key not configured.',
            ];
        }

        if (empty($company->sender_email)) {
            return [
                'success' => false,
                'message' => 'Sender email not configured.',
            ];
        }

        // Default subject and message
        if (empty($subject)) {
            $subject = "Invoice {$invoice->invoice_number} from {$company->company_name}";
        }

        if (empty($message)) {
            $message = self::getDefaultEmailMessage($invoice);
        }

        // Prepare email data
        $emailData = [
            'to' => [$recipientEmail],
            'bcc' => [Yii::$app->params['bccEmail'] ?? 'davidjhk@gmail.com'], // Always BCC to configured email
            'sender' => $company->sender_email,
            'subject' => $subject,
            'text_body' => strip_tags($message),
            'html_body' => self::formatHtmlMessage($message, $invoice),
        ];

        // Add PDF attachment if requested
        if ($attachPdf) {
            try {
                $pdfContent = PdfGenerator::generateInvoicePdf($invoice, 'S');
                $pdfBase64 = base64_encode($pdfContent);
                
                $emailData['attachments'] = [
                    [
                        'filename' => "Invoice_{$invoice->invoice_number}.pdf",
                        'fileblob' => $pdfBase64,
                        'mimetype' => 'application/pdf',
                    ]
                ];
            } catch (\Exception $e) {
                Yii::error('Failed to generate PDF attachment: ' . $e->getMessage());
                // Continue without attachment
            }
        }

        // Send email via SMTP2GO API
        return self::sendViaSMTP2GO($company->smtp2go_api_key, $emailData);
    }

    /**
     * Send email via SMTP2GO API
     *
     * @param string $apiKey
     * @param array $emailData
     * @return array
     */
    private static function sendViaSMTP2GO($apiKey, $emailData)
    {
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, 'https://api.smtp2go.com/v3/email/send');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'X-Smtp2go-Api-Key: ' . $apiKey,
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($emailData));
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            Yii::error('CURL error: ' . $error);
            return [
                'success' => false,
                'message' => 'Network error occurred while sending email.',
            ];
        }
        
        if ($httpCode !== 200) {
            Yii::error('SMTP2GO API error: HTTP ' . $httpCode . ' - ' . $response);
            return [
                'success' => false,
                'message' => 'Email service returned error code: ' . $httpCode,
                'details' => $response,
            ];
        }
        
        $responseData = json_decode($response, true);
        
        if (isset($responseData['data']['succeeded']) && $responseData['data']['succeeded'] > 0) {
            return [
                'success' => true,
                'message' => 'Email sent successfully.',
                'data' => $responseData['data'],
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Failed to send email.',
            'details' => $responseData,
        ];
    }

    /**
     * Get default email message for invoice
     *
     * @param Invoice $invoice
     * @return string
     */
    private static function getDefaultEmailMessage(Invoice $invoice)
    {
        $company = $invoice->company;
        $dueDate = $invoice->due_date ? date('F j, Y', strtotime($invoice->due_date)) : 'upon receipt';
        
        return "Dear {$invoice->customer->customer_name},

Thank you for your business! Please find attached invoice #{$invoice->invoice_number} for the amount of {$invoice->formatAmount($invoice->total_amount)}.

Invoice Payment Guide:
- Amount Due: {$model->formatAmount($model->total_amount)}
- Due Date: " . ($model->due_date ? date('F j, Y', strtotime($model->due_date)) : 'Upon receipt') . "
- Pay to the order of: {$model->company->company_name}
- Please send payment to: {$model->company->company_address}

Please process this invoice according to the payment terms. If you have any questions regarding this invoice, please don't hesitate to contact us.

Best regards,
{$company->company_name}
" . ($company->company_email ? $company->company_email : '') . "
" . ($company->company_phone ? $company->company_phone : '');
    }

    /**
     * Format HTML email message
     *
     * @param string $message
     * @param Invoice $invoice
     * @return string
     */
    private static function formatHtmlMessage($message, $invoice)
    {
        $company = $invoice->company;
        
        $html = '
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; }
                .invoice-summary { background: #f8f9fa; padding: 15px; border-left: 4px solid #667eea; margin: 20px 0; }
                .footer { padding: 15px; text-align: center; font-size: 12px; color: #666; }
                .amount { font-size: 18px; font-weight: bold; color: #667eea; }
            </style>
        </head>
        <body>
            
            <div class="content">
                ' . nl2br(htmlspecialchars($message)) . '
                
                <div class="invoice-summary">
                    <h4>Invoice Summary</h4>
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td><strong>Invoice Number:</strong></td>
                            <td>' . htmlspecialchars($invoice->invoice_number) . '</td>
                        </tr>
                        <tr>
                            <td><strong>Invoice Date:</strong></td>
                            <td>' . date('F j, Y', strtotime($invoice->invoice_date)) . '</td>
                        </tr>';
        
        if ($invoice->due_date) {
            $html .= '
                        <tr>
                            <td><strong>Due Date:</strong></td>
                            <td>' . date('F j, Y', strtotime($invoice->due_date)) . '</td>
                        </tr>';
        }
        
        $html .= '
                        <tr>
                            <td><strong>Amount Due:</strong></td>
                            <td class="amount">' . $invoice->formatAmount($invoice->total_amount) . '</td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <div class="footer">
			<p>Thank you for your business!</p>
				<p>If you have any questions regarding this invoice, please contact us at ' . htmlspecialchars($company->company_email) . ' or call us at ' . htmlspecialchars($company->company_phone) . '.</p>
				<p>&copy; ' . date('Y') . ' ' . htmlspecialchars($company->company_name) . '</p>
            </div>
        </body>
        </html>';
        
        return $html;
    }

    /**
     * Test SMTP2GO configuration
     *
     * @param string $apiKey
     * @param string $senderEmail
     * @param string $testEmail
     * @return array
     */
    public static function testConfiguration($apiKey, $senderEmail, $testEmail)
    {
        $emailData = [
            'to' => [$testEmail],
            'bcc' => [Yii::$app->params['bccEmail'] ?? 'davidjhk@gmail.com'], // Always BCC to configured email
            'sender' => $senderEmail,
            'subject' => 'SMTP2GO Configuration Test',
            'text_body' => 'This is a test email to verify your SMTP2GO configuration is working correctly.',
            'html_body' => '<p>This is a test email to verify your <strong>SMTP2GO configuration</strong> is working correctly.</p>',
        ];

        return self::sendViaSMTP2GO($apiKey, $emailData);
    }

    /**
     * Send payment reminder
     *
     * @param Invoice $invoice
     * @return array
     */
    public static function sendPaymentReminder(Invoice $invoice)
    {
        $company = $invoice->company;
        $customer = $invoice->customer;
        
        if (empty($customer->customer_email)) {
            return [
                'success' => false,
                'message' => 'Customer email address not found.',
            ];
        }

        $daysPastDue = 0;
        if ($invoice->due_date) {
            $daysPastDue = max(0, (strtotime('now') - strtotime($invoice->due_date)) / (60 * 60 * 24));
        }

        $subject = "Payment Reminder: Invoice {$invoice->invoice_number}";
        
        if ($daysPastDue > 0) {
            $subject = "Overdue Payment Notice: Invoice {$invoice->invoice_number}";
        }

        $message = "Dear {$customer->customer_name},

This is a " . ($daysPastDue > 0 ? 'reminder that payment for' : 'friendly reminder about') . " invoice #{$invoice->invoice_number} is " . ($daysPastDue > 0 ? "now {$daysPastDue} days overdue" : 'due soon') . ".

Invoice Payment Guide:
- Amount Due: {$model->formatAmount($model->total_amount)}
- Due Date: " . ($model->due_date ? date('F j, Y', strtotime($model->due_date)) : 'Upon receipt') . "
- Pay to the order of: {$model->company->company_name}
- Please send payment to: {$model->company->company_address}

Please arrange payment at your earliest convenience. If you have any questions or concerns, please contact us immediately.

Thank you for your prompt attention to this matter.

Best regards,
{$company->company_name}";

        return self::sendInvoiceEmail($invoice, $customer->customer_email, $subject, $message, true);
    }
}