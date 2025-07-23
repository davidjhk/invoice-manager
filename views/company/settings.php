<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\models\Company $model */

$this->title = Yii::t('app/company', 'Company Settings');
$this->params['breadcrumbs'][] = $this->title;

// collapse-helper.js is loaded via AppAsset
?>

<div class="company-settings">

	<div class="d-flex justify-content-between align-items-center mb-4">
		<h1><?= Html::encode($this->title) ?></h1>
		<div>
			<?php if (Yii::$app->user->identity->canCreateMoreCompanies()): ?>
				<?= Html::a(Yii::t('app/company', 'Create New Company'), ['/company/create'], ['class' => 'btn btn-success me-2']) ?>
			<?php endif; ?>
			<?= Html::a(Yii::t('app/company', 'Switch Company'), ['/company/select'], ['class' => 'btn btn-outline-primary me-2']) ?>
			<?= Html::a(Yii::t('app/company', 'Back to Dashboard'), ['/site/index'], ['class' => 'btn btn-secondary']) ?>
		</div>
	</div>

	<?php $form = ActiveForm::begin([
        'id' => 'company-settings-form',
        'options' => [
            'class' => 'needs-validation', 
            'novalidate' => true, // Disable browser validation
            'enctype' => 'multipart/form-data'
        ],
        'fieldConfig' => [
            'options' => ['class' => 'form-group'],
            'inputOptions' => ['class' => 'form-control'],
            'labelOptions' => ['class' => 'form-label font-weight-bold'],
        ],
    ]); ?>

	<?= $this->render('_form', [
        'model' => $model,
        'form' => $form,
        'mode' => 'settings'
    ]) ?>


	<?php ActiveForm::end(); ?>

</div>

<script>
// Template preview functionality for Company Settings
function updateCompanyTemplatePreview(templateId) {
	const previewDiv = document.getElementById('company-template-preview');
	const templates = {
		'classic': {
			name: '<?= Yii::t('invoice', 'template_classic') ?>',
			desc: '<?= Yii::t('invoice', 'template_classic_desc') ?>',
			color: '#667eea'
		},
		'modern': {
			name: '<?= Yii::t('invoice', 'template_modern') ?>',
			desc: '<?= Yii::t('invoice', 'template_modern_desc') ?>',
			color: '#2563eb'
		},
		'elegant': {
			name: '<?= Yii::t('invoice', 'template_elegant') ?>',
			desc: '<?= Yii::t('invoice', 'template_elegant_desc') ?>',
			color: '#059669'
		},
		'corporate': {
			name: '<?= Yii::t('invoice', 'template_corporate') ?>',
			desc: '<?= Yii::t('invoice', 'template_corporate_desc') ?>',
			color: '#1e3a8a'
		},
		'creative': {
			name: '<?= Yii::t('invoice', 'template_creative') ?>',
			desc: '<?= Yii::t('invoice', 'template_creative_desc') ?>',
			color: '#7c3aed'
		}
	};

	const template = templates[templateId] || templates['classic'];
	
	previewDiv.innerHTML = `
		<div class="template-preview-card" style="border: 2px solid ${template.color}; border-radius: 8px; padding: 15px; background: white;">
			<div style="background: ${template.color}; color: white; padding: 10px; margin: -15px -15px 10px -15px; border-radius: 6px 6px 0 0; font-weight: bold;">
				${template.name}
			</div>
			<div style="color: #666; font-size: 12px; line-height: 1.4;">
				${template.desc}
			</div>
			<div style="margin-top: 10px; padding: 8px; background: ${template.color}15; border-radius: 4px; font-size: 11px; color: ${template.color};">
				<i class="fas fa-palette mr-1"></i>Preview
			</div>
		</div>
	`;
}

// Initialize template preview on page load
document.addEventListener('DOMContentLoaded', function() {
	const templateSelect = document.getElementById('pdf-template-select');
	if (templateSelect && templateSelect.value) {
		updateCompanyTemplatePreview(templateSelect.value);
	}
});
</script>
