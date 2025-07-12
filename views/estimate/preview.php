<?php

use yii\helpers\Html;
use app\components\PdfGenerator;

/** @var yii\web\View $this */
/** @var app\models\Estimate $model */

$this->title = Yii::t('app/estimate', 'Estimate Preview') . ': ' . $model->estimate_number;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app/estimate', 'Estimates'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->estimate_number, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app/estimate', 'Preview');
?>

<div class="estimate-preview">

	<div class="d-flex justify-content-between align-items-center mb-4 print-hidden">
		<h1><?= Html::encode($this->title) ?></h1>
		<div class="action-buttons">
			<?= Html::a('<i class="fas fa-file-pdf mr-1"></i>' . Yii::t('app/estimate', 'Download PDF'), ['download-pdf', 'id' => $model->id], [
                'class' => 'btn btn-primary',
                'target' => '_blank',
                'encode' => false
            ]) ?>

			<?= Html::a('<i class="fas fa-print mr-1"></i>' . Yii::t('app/estimate', 'Print'), '#', [
                'class' => 'btn btn-info',
                'onclick' => 'window.print(); return false;',
                'encode' => false
            ]) ?>

			<?php if ($model->status === \app\models\Estimate::STATUS_DRAFT): ?>
			<?= Html::a('<i class="fas fa-envelope mr-1"></i>' . Yii::t('app/estimate', 'Send Email'), ['send-email', 'id' => $model->id], [
                    'class' => 'btn btn-success',
                    'encode' => false
                ]) ?>
			<?php endif; ?>

			<?= Html::a('<i class="fas fa-edit mr-1"></i>' . Yii::t('app/estimate', 'Edit'), ['update', 'id' => $model->id], [
                    'class' => 'btn btn-secondary',
                    'encode' => false
                ]) ?>

			<?= Html::a('<i class="fas fa-arrow-left mr-1"></i>' . Yii::t('app', 'Back'), ['view', 'id' => $model->id], [
                'class' => 'btn btn-outline-secondary',
                'encode' => false
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
    
    .estimate-preview-wrapper {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
    }
");
?>