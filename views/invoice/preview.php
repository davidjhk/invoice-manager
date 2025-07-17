<?php

use yii\helpers\Html;
use app\components\PdfGenerator;

/** @var yii\web\View $this */
/** @var app\models\Invoice $model */

$this->title = Yii::t('app/invoice', 'Invoice Preview') . ': ' . $model->invoice_number;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app/invoice', 'Invoices'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->invoice_number, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app/invoice', 'Preview');
?>

<div class="invoice-preview">

	<div class="d-flex justify-content-between align-items-center mb-4 print-hidden">
		<h1><?= Html::encode($this->title) ?></h1>
		<div class="action-buttons">
			<?= Html::a('<i class="fas fa-file-pdf mr-1"></i>' . Yii::t('app/invoice', 'Download PDF'), ['download-pdf', 'id' => $model->id], [
                'class' => 'btn btn-primary',
                'target' => '_blank',
                'encode' => false
            ]) ?>

			<?= Html::a('<i class="fas fa-print mr-1"></i>' . Yii::t('app/invoice', 'Print'), '#', [
                'class' => 'btn btn-info',
                'onclick' => 'window.print(); return false;',
                'encode' => false
            ]) ?>

			<?php if ($model->canBeSent()): ?>
			<?php 
				$company = \app\models\Company::getCurrent();
				$hasEmailConfig = $company && $company->hasEmailConfiguration();
			?>
			<?= Html::a(
				'<i class="fas fa-envelope mr-1"></i>' . Yii::t('app/invoice', 'Send Email'), 
				$hasEmailConfig ? ['send-email', 'id' => $model->id] : '#', 
				[
					'class' => 'btn ' . ($hasEmailConfig ? 'btn-success' : 'btn-secondary'),
					'encode' => false,
					'disabled' => !$hasEmailConfig,
					'title' => $hasEmailConfig ? '' : Yii::t('app/invoice', 'Email not configured. Configure SMTP2GO in Company Settings.'),
					'data-toggle' => !$hasEmailConfig ? 'tooltip' : '',
					'style' => !$hasEmailConfig ? 'cursor: not-allowed; opacity: 0.6;' : ''
				]
			) ?>
			<?php endif; ?>

			<?php if ($model->isEditable()): ?>
			<?= Html::a('<i class="fas fa-edit mr-1"></i>' . Yii::t('app/invoice', 'Edit'), ['update', 'id' => $model->id], [
                    'class' => 'btn btn-secondary',
                    'encode' => false
                ]) ?>
			<?php endif; ?>

			<?= Html::a('<i class="fas fa-arrow-left mr-1"></i>' . Yii::t('app', 'Back'), ['view', 'id' => $model->id], [
                'class' => 'btn btn-outline-secondary',
                'encode' => false
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
        background: #f8f9fa !important;
        padding: 20px !important;
        border-radius: 8px !important;
    }
    
    /* Dark mode override - force white background and black text for preview */
    body.dark-mode .invoice-preview-wrapper {
        background: #111827 !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3) !important;
    }
    
    body.dark-mode .invoice-preview-wrapper *,
    body.dark-mode .invoice-preview-container *,
    body.dark-mode .invoice-preview-container,
    .dark-mode .invoice-preview-wrapper *,
    .dark-mode .invoice-preview-container * {
        color: black !important;
        background: white !important;
		border-radius: 10px;
    }
    
    body.dark-mode .invoice-preview-container .items-table th,
    .dark-mode .invoice-preview-container .items-table th {
        background: #667eea !important;
        color: white !important;
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
?>