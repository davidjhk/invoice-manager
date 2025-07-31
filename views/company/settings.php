<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\models\Company $model */

$this->title = Yii::t('app/company', 'Company Settings');
$this->params['breadcrumbs'][] = $this->title;

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
$isSubuser = Yii::$app->user->identity->isSubuser();

// collapse-helper.js is loaded via AppAsset
?>

<div class="company-settings">

	<?php if ($isSubuser): ?>
	<div class="alert alert-info mb-4">
		<i class="fas fa-info-circle mr-2"></i>
		<strong><?= Yii::t('app/company', 'Read-Only Access') ?>:</strong>
		<?= Yii::t('app/company', 'As a subuser, you can view company settings but cannot modify them. Contact your administrator to make changes.') ?>
	</div>
	<?php endif; ?>

	<div class="d-flex justify-content-between align-items-center mb-4">
		<h1><?= Html::encode($this->title) ?></h1>
		<div>
			<?php if (!$isSubuser && Yii::$app->user->identity->canCreateMoreCompanies()): ?>
			<?= Html::a('<i class="fas fa-plus mr-1"></i>' . Yii::t('app/company', $isCompactMode ? '' : 'Create New Company'), ['/company/create'], [
					'class' => 'btn btn-success me-2',
					'encode' => false,
					'title' => $isCompactMode ? Yii::t('app/company', 'Create New Company') : '',
					'data-toggle' => $isCompactMode ? 'tooltip' : ''
				]) ?>
			<?php endif; ?>
			<?= Html::a('<i class="fas fa-exchange-alt mr-1"></i>' . Yii::t('app/company', $isCompactMode ? '' : 'Switch Company'), ['/company/select'], [
				'class' => 'btn btn-outline-primary me-2',
				'encode' => false,
				'title' => $isCompactMode ? Yii::t('app/company', 'Switch Company') : '',
				'data-toggle' => $isCompactMode ? 'tooltip' : ''
			]) ?>
			<?= Html::a('<i class="fas fa-arrow-left mr-1"></i>' . Yii::t('app/company', $isCompactMode ? '' : 'Back to Dashboard'), ['/site/index'], [
				'class' => 'btn btn-secondary',
				'encode' => false,
				'title' => $isCompactMode ? Yii::t('app/company', 'Back to Dashboard') : '',
				'data-toggle' => $isCompactMode ? 'tooltip' : ''
			]) ?>
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
        'mode' => 'settings',
        'isCompactMode' => $isCompactMode,
        'isReadOnly' => $isSubuser
    ]) ?>


	<?php ActiveForm::end(); ?>

</div>

<!-- Template Preview Modal -->
<div id="templateModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="templateModalLabel"
	aria-hidden="true">
	<div class="modal-dialog modal-lg modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="templateModalLabel">Template Preview</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body p-0">
				<div id="templateModalContent" class="text-center">
					<!-- Template image will be loaded here -->
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>

<?php
$this->registerJs("
    $('[data-toggle=\"tooltip\"]').tooltip();
");

$this->registerCss("
/* Template preview button hover effect */
.template-preview-card button:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

/* Modal customization */
#templateModal .modal-content {
    border: none;
    border-radius: 12px;
    overflow: hidden;
}

#templateModal .modal-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-bottom: none;
}

#templateModal .modal-header .close {
    color: white;
    opacity: 0.8;
    text-shadow: none;
}

#templateModal .modal-header .close:hover {
    opacity: 1;
}

#templateModal .modal-footer {
    border-top: 1px solid #e9ecef;
    background: #f8f9fa;
}

/* Image zoom effect on hover */
#templateModalContent img {
    transition: transform 0.3s ease;
}

#templateModalContent img:hover {
    transform: scale(1.02);
}
");
?>

<script>
// Template preview functionality for Company Settings
function updateCompanyTemplatePreview(templateId) {
	const previewDiv = document.getElementById('company-template-preview');
	const baseUrl = '<?= Yii::$app->request->baseUrl ?>';
	const templates = {
		'classic': {
			name: '<?= Yii::t('invoice', 'template_classic') ?>',
			desc: '<?= Yii::t('invoice', 'template_classic_desc') ?>',
			color: '#667eea',
			image: baseUrl + '/images/template_classic.jpg'
		},
		'modern': {
			name: '<?= Yii::t('invoice', 'template_modern') ?>',
			desc: '<?= Yii::t('invoice', 'template_modern_desc') ?>',
			color: '#2563eb',
			image: baseUrl + '/images/template_modern.jpg'
		},
		'elegant': {
			name: '<?= Yii::t('invoice', 'template_elegant') ?>',
			desc: '<?= Yii::t('invoice', 'template_elegant_desc') ?>',
			color: '#059669',
			image: baseUrl + '/images/template_elegant.jpg'
		},
		'corporate': {
			name: '<?= Yii::t('invoice', 'template_corporate') ?>',
			desc: '<?= Yii::t('invoice', 'template_corporate_desc') ?>',
			color: '#1e3a8a',
			image: baseUrl + '/images/template_corporate.jpg'
		},
		'creative': {
			name: '<?= Yii::t('invoice', 'template_creative') ?>',
			desc: '<?= Yii::t('invoice', 'template_creative_desc') ?>',
			color: '#7c3aed',
			image: baseUrl + '/images/template_creative.jpg'
		}
	};

	const template = templates[templateId] || templates['classic'];

	previewDiv.innerHTML = `
		<div class="template-preview-card" style="border: 2px solid ${template.color}; border-radius: 8px; overflow: hidden; background: white;">
			<div style="position: relative;">
				<img src="${template.image}" alt="${template.name}" style="width: 100%; height: 200px; object-fit: cover; display: block;">
				<div style="position: absolute; top: 0; left: 0; right: 0; background: linear-gradient(180deg, ${template.color}dd 0%, transparent 100%); padding: 15px;">
					<div style="color: white; font-weight: bold; font-size: 16px; text-shadow: 0 1px 3px rgba(0,0,0,0.3);">
						${template.name}
					</div>
				</div>
			</div>
			<div style="padding: 15px;">
				<div style="color: #666; font-size: 13px; line-height: 1.4; margin-bottom: 10px;">
					${template.desc}
				</div>
				<button type="button" onclick="openTemplateModal('${templateId}')" style="width: 100%; padding: 8px 12px; background: ${template.color}15; border: 1px solid ${template.color}30; border-radius: 4px; font-size: 12px; color: ${template.color}; cursor: pointer; transition: all 0.2s;">
					<i class="fas fa-search mr-1"></i>Template Preview
				</button>
			</div>
		</div>
	`;
}

// Open template modal with full-size image
function openTemplateModal(templateId) {
	const baseUrl = '<?= Yii::$app->request->baseUrl ?>';
	const templates = {
		'classic': {
			name: '<?= Yii::t('invoice', 'template_classic') ?>',
			desc: '<?= Yii::t('invoice', 'template_classic_desc') ?>',
			color: '#667eea',
			image: baseUrl + '/images/template_classic.jpg'
		},
		'modern': {
			name: '<?= Yii::t('invoice', 'template_modern') ?>',
			desc: '<?= Yii::t('invoice', 'template_modern_desc') ?>',
			color: '#2563eb',
			image: baseUrl + '/images/template_modern.jpg'
		},
		'elegant': {
			name: '<?= Yii::t('invoice', 'template_elegant') ?>',
			desc: '<?= Yii::t('invoice', 'template_elegant_desc') ?>',
			color: '#059669',
			image: baseUrl + '/images/template_elegant.jpg'
		},
		'corporate': {
			name: '<?= Yii::t('invoice', 'template_corporate') ?>',
			desc: '<?= Yii::t('invoice', 'template_corporate_desc') ?>',
			color: '#1e3a8a',
			image: baseUrl + '/images/template_corporate.jpg'
		},
		'creative': {
			name: '<?= Yii::t('invoice', 'template_creative') ?>',
			desc: '<?= Yii::t('invoice', 'template_creative_desc') ?>',
			color: '#7c3aed',
			image: baseUrl + '/images/template_creative.jpg'
		}
	};

	const template = templates[templateId] || templates['classic'];

	// Update modal title
	document.getElementById('templateModalLabel').innerHTML = template.name + ' Template';

	// Update modal content
	document.getElementById('templateModalContent').innerHTML = `
		<div style="position: relative; background: #f8f9fa;">
			<img src="${template.image}" alt="${template.name}" style="width: 100%; height: auto; max-height: 70vh; object-fit: contain; display: block;">
			<div style="position: absolute; top: 15px; left: 15px; background: ${template.color}; color: white; padding: 8px 16px; border-radius: 20px; font-size: 14px; font-weight: bold; box-shadow: 0 2px 8px rgba(0,0,0,0.2);">
				${template.name}
			</div>
		</div>
		<div style="padding: 20px; background: white;">
			<p style="color: #666; font-size: 14px; line-height: 1.5; margin: 0;">
				${template.desc}
			</p>
		</div>
	`;

	// Show modal
	$('#templateModal').modal('show');
}

// Initialize template preview on page load
document.addEventListener('DOMContentLoaded', function() {
	const templateSelect = document.getElementById('pdf-template-select');
	if (templateSelect && templateSelect.value) {
		updateCompanyTemplatePreview(templateSelect.value);
	}

	// Test Email functionality
	const sendTestEmailBtn = document.getElementById('send-test-email');
	const testEmailInput = document.getElementById('test-email');
	const testEmailResult = document.getElementById('test-email-result');

	if (sendTestEmailBtn && testEmailInput && testEmailResult) {
		sendTestEmailBtn.addEventListener('click', function() {
			const email = testEmailInput.value.trim();

			if (!email) {
				alert('<?= Yii::t('app/company', 'Please enter an email address') ?>');
				return;
			}

			// Validate email format
			const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
			if (!emailRegex.test(email)) {
				alert('<?= Yii::t('app/company', 'Please enter a valid email address') ?>');
				return;
			}

			// Show loading state
			const originalText = sendTestEmailBtn.innerHTML;
			sendTestEmailBtn.innerHTML =
				'<i class="fas fa-spinner fa-spin"></i> <?= Yii::t('app/company', 'Sending...') ?>';
			sendTestEmailBtn.disabled = true;

			// Prepare form data
			const formData = new FormData();
			formData.append('email', email);
			formData.append('<?= Yii::$app->request->csrfParam ?>',
				'<?= Yii::$app->request->csrfToken ?>');

			// Send AJAX request
			fetch('<?= \yii\helpers\Url::to(['company/test-email']) ?>', {
					method: 'POST',
					body: formData
				})
				.then(response => response.json())
				.then(data => {
					// Show result
					testEmailResult.style.display = 'block';

					if (data.success) {
						testEmailResult.className = 'alert alert-success';
						testEmailResult.innerHTML = '<i class="fas fa-check-circle mr-2"></i>' +
							data.message;
					} else {
						testEmailResult.className = 'alert alert-danger';
						testEmailResult.innerHTML =
							'<i class="fas fa-exclamation-circle mr-2"></i>' + data.message;
						if (data.details) {
							testEmailResult.innerHTML += '<br><small>' + data.details + '</small>';
						}
					}

					// Reset button
					sendTestEmailBtn.innerHTML = originalText;
					sendTestEmailBtn.disabled = false;
				})
				.catch(error => {
					console.error('Test Email Error:', error);

					// Show error
					testEmailResult.style.display = 'block';
					testEmailResult.className = 'alert alert-danger';
					testEmailResult.innerHTML =
						'<i class="fas fa-exclamation-circle mr-2"></i><?= Yii::t('app/company', 'Network error occurred') ?>';

					// Reset button
					sendTestEmailBtn.innerHTML = originalText;
					sendTestEmailBtn.disabled = false;
				});
		});
	}
});
</script>