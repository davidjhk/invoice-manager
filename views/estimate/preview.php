<?php

use yii\helpers\Html;
use app\components\PdfGenerator;

/** @var yii\web\View $this */
/** @var app\models\Estimate $model */

$this->title = Yii::t('app/estimate', 'Estimate Preview') . ': ' . $model->estimate_number;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app/estimate', 'Estimates'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->estimate_number, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app/estimate', 'Preview');

// Determine dark mode setting
$currentCompany = null;
if (!Yii::$app->user->isGuest) {
	$companyId = Yii::$app->session->get('current_company_id');
	if ($companyId) {
		$currentCompany = \app\models\Company::findForCurrentUser()->where(['c.id' => $companyId])->one();
	}
}
$isDarkMode = $currentCompany && $currentCompany->dark_mode;
$isCompactMode = $currentCompany && $currentCompany->compact_mode;
?>

<div class="estimate-preview">

    <div class="d-flex justify-content-between align-items-center mb-4 print-hidden">
        <h1><?= Html::encode($this->title) ?></h1>
        <div class="action-buttons">
            <?= Html::a('<i class="fas fa-download mr-1"></i>' . Yii::t('app/estimate', $isCompactMode ? '' : 'Download PDF'), ['download-pdf', 'id' => $model->id], [
                'class' => 'btn btn-primary',
                'target' => '_blank',
                'encode' => false,
                'title' => $isCompactMode ? Yii::t('app/estimate', 'Download PDF') : '',
                'data-toggle' => $isCompactMode ? 'tooltip' : ''
            ]) ?>

            <?= Html::a('<i class="fas fa-print mr-1"></i>' . Yii::t('app/estimate', $isCompactMode ? '' : 'Print'), '#', [
                'class' => 'btn btn-info',
                'id' => 'print-btn',
                'data-url' => \yii\helpers\Url::to(['mark-as-printed', 'id' => $model->id]),
                'encode' => false,
                'title' => $isCompactMode ? Yii::t('app/estimate', 'Print') : '',
                'data-toggle' => $isCompactMode ? 'tooltip' : ''
            ]) ?>

            <?php if (in_array($model->status, [\app\models\Estimate::STATUS_DRAFT, \app\models\Estimate::STATUS_PRINTED])): ?>
            <?php 
                $company = \app\models\Company::getCurrent();
                $hasEmailConfig = $company && $company->hasEmailConfiguration();
            ?>
            <?= Html::a(
                '<i class="fas fa-envelope mr-1"></i>' . Yii::t('app/estimate', $isCompactMode ? '' : 'Send Email'), 
                $hasEmailConfig ? ['send-email', 'id' => $model->id] : '#', 
                [
                    'class' => 'btn ' . ($hasEmailConfig ? 'btn-success' : 'btn-secondary'),
                    'encode' => false,
                    'disabled' => !$hasEmailConfig,
                    'title' => $hasEmailConfig ? ($isCompactMode ? Yii::t('app/estimate', 'Send Email') : '') : Yii::t('app/estimate', 'Email not configured. Configure SMTP2GO in Company Settings.'),
                    'data-toggle' => 'tooltip',
                    'style' => !$hasEmailConfig ? 'cursor: not-allowed; opacity: 0.6;' : ''
                ]
            ) ?>
            <?php endif; ?>

            <?php if (in_array($model->status, [\app\models\Estimate::STATUS_DRAFT, \app\models\Estimate::STATUS_PRINTED, \app\models\Estimate::STATUS_SENT])): ?>
            <?= Html::a('<i class="fas fa-check mr-1"></i>' . Yii::t('app/estimate', $isCompactMode ? '' : 'Mark as Accepted'), ['mark-as-accepted', 'id' => $model->id], [
                'class' => 'btn btn-success',
                'data' => [
                    'confirm' => Yii::t('app/estimate', 'Are you sure you want to mark this estimate as accepted?'),
                    'method' => 'post',
                ],
                'encode' => false,
                'title' => $isCompactMode ? Yii::t('app/estimate', 'Mark as Accepted') : '',
                'data-toggle' => $isCompactMode ? 'tooltip' : ''
            ]) ?>
            <?php endif; ?>

            <?= Html::a('<i class="fas fa-edit mr-1"></i>' . Yii::t('app/estimate', $isCompactMode ? '' : 'Edit'), ['update', 'id' => $model->id], [
                    'class' => 'btn btn-secondary',
                    'encode' => false,
                    'title' => $isCompactMode ? Yii::t('app/estimate', 'Edit') : '',
                    'data-toggle' => $isCompactMode ? 'tooltip' : ''
                ]) ?>

            <?= Html::a('<i class="fas fa-arrow-left mr-1"></i>' . Yii::t('app', $isCompactMode ? '' : 'Back'), ['view', 'id' => $model->id], [
                'class' => 'btn btn-outline-secondary',
                'encode' => false,
                'title' => $isCompactMode ? Yii::t('app', 'Back') : '',
                'data-toggle' => $isCompactMode ? 'tooltip' : ''
            ]) ?>
        </div>
    </div>

    <div class="estimate-preview-wrapper">
        <?= PdfGenerator::generateEstimatePreviewHtml($model) ?>
    </div>

</div>

<?php
$this->registerCss("
    @media print {
        .action-buttons, .breadcrumb, .navbar, .footer {
            display: none !important;
        }
        
        .estimate-preview-wrapper {
            box-shadow: none !important;
        }
        
        .estimate-preview-container {
            box-shadow: none !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        
        body {
            background: white !important;
        }
    }
    
    /* Estimate preview should always have white background regardless of dark mode */
    .estimate-preview-wrapper {
        background: #f8f9fa !important;
        padding: 40px !important;
    }
    
    /* Dark mode override - force white background and black text for preview */
    body.dark-mode .estimate-preview-wrapper {
        background: #111827 !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3) !important;
    }
    
    body.dark-mode .estimate-preview-wrapper *:not(table):not(thead):not(th):not(.items-table thead th),
    body.dark-mode .estimate-preview-container *:not(table):not(thead):not(th):not(.items-table thead th),
    body.dark-mode .estimate-preview-container,
    .dark-mode .estimate-preview-wrapper *:not(table):not(thead):not(th):not(.items-table thead th),
    .dark-mode .estimate-preview-container *:not(table):not(thead):not(th):not(.items-table thead th) {
    }
    
    /* Allow template-specific table header colors */
    body.dark-mode .estimate-preview-container table thead th,
    body.dark-mode .estimate-preview-container .items-table th,
    body.dark-mode .estimate-preview-container .items-table thead th,
    .dark-mode .estimate-preview-container table thead th,
    .dark-mode .estimate-preview-container .items-table th,
    .dark-mode .estimate-preview-container .items-table thead th {
        background: var(--table-header-bg, #667eea) !important;
        color: var(--table-header-color, white) !important;
    }
    
    body.dark-mode .estimate-preview-container .total-row,
    .dark-mode .estimate-preview-container .total-row {
        background: #f8f9fa !important;
        color: black !important;
    }
    
    body.dark-mode .estimate-preview-container .paid-row,
    .dark-mode .estimate-preview-container .paid-row {
        background: #e8f5e8 !important;
        color: black !important;
    }
    
    body.dark-mode .estimate-preview-container .notes-section,
    .dark-mode .estimate-preview-container .notes-section {
        background: #f8f9fa !important;
        color: black !important;
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