<?php

use yii\helpers\Html;
use app\components\PdfGenerator;

/** @var yii\web\View $this */
/** @var app\models\Invoice $model */

$this->title = Yii::t('invoice', 'Invoice Preview') . ': ' . $model->invoice_number;
$this->params['breadcrumbs'][] = ['label' => Yii::t('invoice', 'Invoices'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->invoice_number, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('invoice', 'Preview');

// Determine dark mode setting
$currentCompany = null;
if (!Yii::$app->user->isGuest) {
	$companyId = Yii::$app->session->get('current_company_id');
	if ($companyId) {
		$currentCompany = \app\models\Company::findForCurrentUser()->where(['id' => $companyId])->one();
	}
}
$isDarkMode = $currentCompany && $currentCompany->dark_mode;
$isCompactMode = $currentCompany && $currentCompany->compact_mode;
?>

<div class="invoice-preview">

    <div class="d-flex justify-content-between align-items-center mb-4 print-hidden">
        <h1><?= Html::encode($this->title) ?></h1>
        <div class="action-buttons">
            <?= Html::a('<i class="fas fa-file-pdf mr-1"></i>' . Yii::t('invoice', $isCompactMode ? '' : 'Download PDF'), ['download-pdf', 'id' => $model->id], [
                'class' => 'btn btn-primary',
                'target' => '_blank',
                'encode' => false,
                'title' => $isCompactMode ? Yii::t('invoice', 'Download PDF') : '',
                'data-toggle' => $isCompactMode ? 'tooltip' : ''
            ]) ?>

            <?= Html::a('<i class="fas fa-print mr-1"></i>' . Yii::t('invoice', $isCompactMode ? '' : 'Print'), '#', [
                'class' => 'btn btn-info',
                'id' => 'print-btn',
                'data-url' => \yii\helpers\Url::to(['mark-as-printed', 'id' => $model->id]),
                'encode' => false,
                'title' => $isCompactMode ? Yii::t('invoice', 'Print') : '',
                'data-toggle' => $isCompactMode ? 'tooltip' : ''
            ]) ?>

            <?php if ($model->canBeSent()): ?>
            <?php 
                $company = \app\models\Company::getCurrent();
                $hasEmailConfig = $company && $company->hasEmailConfiguration();
            ?>
            <?= Html::a(
                '<i class="fas fa-envelope mr-1"></i>' . Yii::t('invoice', $isCompactMode ? '' : 'Send Email'), 
                $hasEmailConfig ? ['send-email', 'id' => $model->id] : '#', 
                [
                    'class' => 'btn ' . ($hasEmailConfig ? 'btn-success' : 'btn-secondary'),
                    'encode' => false,
                    'disabled' => !$hasEmailConfig,
                    'title' => $hasEmailConfig ? ($isCompactMode ? Yii::t('invoice', 'Send Email') : '') : Yii::t('invoice', 'Email not configured. Configure SMTP2GO in Company Settings.'),
                    'data-toggle' => 'tooltip',
                    'style' => !$hasEmailConfig ? 'cursor: not-allowed; opacity: 0.6;' : ''
                ]
            ) ?>
            <?php endif; ?>

            <?php if ($model->isEditable()): ?>
            <?= Html::a('<i class="fas fa-edit mr-1"></i>' . Yii::t('invoice', $isCompactMode ? '' : 'Edit'), ['update', 'id' => $model->id], [
                    'class' => 'btn btn-secondary',
                    'encode' => false,
                    'title' => $isCompactMode ? Yii::t('invoice', 'Edit') : '',
                    'data-toggle' => $isCompactMode ? 'tooltip' : ''
                ]) ?>
            <?php endif; ?>

            <?= Html::a('<i class="fas fa-arrow-left mr-1"></i>' . Yii::t('app', $isCompactMode ? '' : 'Back'), ['view', 'id' => $model->id], [
                'class' => 'btn btn-outline-secondary',
                'encode' => false,
                'title' => $isCompactMode ? Yii::t('app', 'Back') : '',
                'data-toggle' => $isCompactMode ? 'tooltip' : ''
            ]) ?>
        </div>
    </div>

    <div class="invoice-preview-wrapper">
        <?= PdfGenerator::generateInvoicePreviewHtml($model) ?>
    </div>

</div>

<?php
$this->registerCss("
    @media print {
        .action-buttons, .breadcrumb, .navbar, .footer {
            display: none !important;
        }
        
        .invoice-preview-wrapper {
            box-shadow: none !important;
        }
        
        .invoice-preview-container {
            box-shadow: none !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        
        body {
            background: white !important;
        }
        
        .paid-watermark {
            -webkit-print-color-adjust: exact !important;
            color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
    }
    
    /* Invoice preview should always have white background regardless of dark mode */
    .invoice-preview-wrapper {
        background: #f8f9fa;
        padding: 40px !important;
    }
    
    /* Dark mode override - force white background and black text for preview */
    body.dark-mode .invoice-preview-wrapper {
        background: #111827 !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3) !important;
    }
    
    body.dark-mode .invoice-preview-wrapper *:not(table):not(thead):not(th):not(.items-table thead th),
    body.dark-mode .invoice-preview-container *:not(table):not(thead):not(th):not(.items-table thead th),
    body.dark-mode .invoice-preview-container,
    .dark-mode .invoice-preview-wrapper *:not(table):not(thead):not(th):not(.items-table thead th),
    .dark-mode .invoice-preview-container *:not(table):not(thead):not(th):not(.items-table thead th) {
    }
    
    /* Allow template-specific table header colors */
    body.dark-mode .invoice-preview-container table thead th,
    body.dark-mode .invoice-preview-container .items-table th,
    body.dark-mode .invoice-preview-container .items-table thead th,
    .dark-mode .invoice-preview-container table thead th,
    .dark-mode .invoice-preview-container .items-table th,
    .dark-mode .invoice-preview-container .items-table thead th {
        background: var(--table-header-bg, #667eea) !important;
        color: var(--table-header-color, white) !important;
    }
    
    body.dark-mode .invoice-preview-container .total-row,
    .dark-mode .invoice-preview-container .total-row {
        background: #f8f9fa !important;
        color: black !important;
    }
    
    body.dark-mode .invoice-preview-container .paid-row,
    .dark-mode .invoice-preview-container .paid-row {
        background: #e8f5e8 !important;
        color: black !important;
    }
    
    body.dark-mode .invoice-preview-container .notes-section,
    .dark-mode .invoice-preview-container .notes-section {
        background: #f8f9fa !important;
        color: black !important;
    }
    
    body.dark-mode .invoice-preview-container .paid-watermark,
    .dark-mode .invoice-preview-container .paid-watermark {
        color: rgba(220, 220, 220, 0.3) !important;
    }
");

$this->registerJs("
    $('#print-btn').on('click', function(e) {
        e.preventDefault();
        
        var url = $(this).data('url');
        
        // Send AJAX request to mark as printed
        $.ajax({
            url: url,
            type: 'POST',
            dataType: 'json',
            data: {
                '_csrf': $('meta[name=csrf-token]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    // Print after marking as printed
                    window.print();
                }
            },
            error: function() {
                // Print anyway if request fails
                window.print();
            }
        });
    });
");
?>