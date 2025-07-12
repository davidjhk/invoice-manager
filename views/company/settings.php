<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\models\Company $model */

$this->title = Yii::t('app/company', 'Company Settings');
$this->params['breadcrumbs'][] = $this->title;

// Register collapse helper JavaScript
$this->registerJsFile('/js/collapse-helper.js', ['depends' => [\yii\web\JqueryAsset::class]]);
?>

<div class="company-settings">

	<div class="d-flex justify-content-between align-items-center mb-4">
		<h1><?= Html::encode($this->title) ?></h1>
		<?= Html::a(Yii::t('app/company', 'Back to Dashboard'), ['/site/index'], ['class' => 'btn btn-secondary']) ?>
	</div>

	<?php $form = ActiveForm::begin([
        'id' => 'company-settings-form',
        'options' => ['class' => 'needs-validation', 'novalidate' => true, 'enctype' => 'multipart/form-data'],
        'fieldConfig' => [
            'options' => ['class' => 'form-group'],
            'inputOptions' => ['class' => 'form-control'],
            'labelOptions' => ['class' => 'form-label font-weight-bold'],
        ],
    ]); ?>

	<div class="row">
		<!-- Company Information -->
		<div class="col-lg-6">
			<div class="card mb-4">
				<div class="card-header">
					<h5 class="card-title mb-0">
						<i class="fas fa-building mr-2"></i><?= Yii::t('app/company', 'Company Information') ?>
					</h5>
				</div>
				<div class="card-body">
					<?= $form->field($model, 'company_name')->textInput(['maxlength' => true]) ?>

					<?= $form->field($model, 'company_address')->textarea(['rows' => 4]) ?>

					<div class="row">
						<div class="col-md-6">
							<?= $form->field($model, 'company_phone')->textInput(['maxlength' => true]) ?>
						</div>
						<div class="col-md-6">
							<?= $form->field($model, 'company_email')->input('email', ['maxlength' => true]) ?>
						</div>
					</div>
				</div>
			</div>

		</div>

		<!-- Company Logo -->
		<div class="col-lg-6">
			<div class="card mb-4">
				<div class="card-header p-2" style="cursor: pointer;" data-custom-collapse="true"
					data-target="#company-logo-collapse" aria-expanded="false">
					<h5 class="card-title mb-0 d-flex justify-content-between align-items-center">
						<span><i class="fas fa-image mr-2"></i>Company Logo</span>
						<i class="fas fa-chevron-down collapse-icon"></i>
					</h5>
				</div>
				<div class="collapse" id="company-logo-collapse">
					<div class="card-body">
						<?php if ($model->hasLogo()): ?>
						<div class="current-logo mb-3">
							<label class="form-label font-weight-bold">Current Logo</label>
							<div class="logo-preview">
								<img src="<?= $model->getLogoUrl() ?>" alt="Company Logo" class="img-thumbnail"
									style="max-height: 150px;">
								<div class="mt-2">
									<small class="text-muted">Filename:
										<?= Html::encode($model->logo_filename) ?></small>
									<br>
									<?= Html::button('Delete Logo', [
                                        'class' => 'btn btn-outline-danger btn-sm mt-1',
                                        'id' => 'delete-logo-btn'
                                    ]) ?>
								</div>
							</div>
						</div>
						<?php endif; ?>

						<div class="form-group">
							<label class="form-label font-weight-bold">Upload New Logo</label>
							<div class="logo-upload-area" id="logo-upload-area">
								<div class="upload-content">
									<i class="fas fa-cloud-upload-alt upload-icon"></i>
									<div class="upload-text">
										<strong>Click to upload</strong> or drag and drop
									</div>
									<div class="upload-hint">
										PNG, JPG, JPEG, GIF up to 2MB
									</div>
								</div>
								<?= Html::activeFileInput($model, 'logo_upload', [
                                'class' => 'file-input',
                                'accept' => 'image/*',
                                'id' => 'logo-upload'
                            ]) ?>
							</div>
						</div>

						<div id="logo-preview" class="mt-3" style="display: none;">
							<label class="form-label">Preview</label>
							<div>
								<img id="logo-preview-img" src="#" alt="Logo Preview" class="img-thumbnail"
									style="max-height: 150px;">
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Language Settings -->
	<div class="card mb-4">
		<div class="card-header p-2" style="cursor: pointer;" data-custom-collapse="true"
			data-target="#language-settings-collapse" aria-expanded="false">
			<h5 class="card-title mb-0 d-flex justify-content-between align-items-center">
				<span><i class="fas fa-language mr-2"></i><?= Yii::t('app/company', 'Language Settings') ?></span>
				<i class="fas fa-chevron-down collapse-icon"></i>
			</h5>
		</div>
		<div class="collapse" id="language-settings-collapse">
			<div class="card-body">
				<div class="form-group">
					<?= $form->field($model, 'language')->dropDownList(
						\app\models\Company::getLanguageOptions(),
						[
							'prompt' => Yii::t('app/company', 'Select your preferred language for the interface'),
							'class' => 'form-control'
						]
					)->label(Yii::t('app/company', 'Interface Language')) ?>
					<small class="form-text text-muted">
						<?= Yii::t('app/company', 'Select your preferred language for the interface') ?>
					</small>
				</div>
				
				<div class="alert alert-info">
					<small>
						<i class="fas fa-info-circle mr-1"></i>
						<strong><?= Yii::t('app', 'Information') ?>:</strong> 
						<?= Yii::t('app/company', 'Changing the language will update the interface immediately after saving settings.') ?>
						<br>
						<strong><?= Yii::t('app', 'Available Languages') ?>:</strong>
						<br>• <?= Yii::t('app/company', 'English') ?> (English)
						<br>• <?= Yii::t('app/company', 'Spanish') ?> (Español)
						<br>• <?= Yii::t('app/company', 'Korean') ?> (한국어)
						<br>• <?= Yii::t('app/company', 'Chinese (Simplified)') ?> (简体中文)
						<br>• <?= Yii::t('app/company', 'Chinese (Traditional)') ?> (繁體中文)
					</small>
				</div>
			</div>
		</div>
	</div>

	<!-- Invoice Settings -->
	<div class="card mb-4">
		<div class="card-header p-2" style="cursor: pointer;" data-custom-collapse="true"
			data-target="#invoice-settings-collapse" aria-expanded="false">
			<h5 class="card-title mb-0 d-flex justify-content-between align-items-center">
				<span><i class="fas fa-file-invoice mr-2"></i>Invoice Settings</span>
				<i class="fas fa-chevron-down collapse-icon"></i>
			</h5>
		</div>
		<div class="collapse" id="invoice-settings-collapse">
			<div class="card-body">
				<div class="row">
					<div class="col-md-3">
						<?= $form->field($model, 'tax_rate')->textInput([
                        'type' => 'number',
                        'min' => 0,
                        'max' => 100,
                        'step' => 0.01,
                        'append' => '%'
                    ]) ?>
					</div>
					<div class="col-md-3">
						<?= $form->field($model, 'currency')->dropDownList([
                        'USD' => 'USD ($)',
                        'EUR' => 'EUR (€)',
                        'GBP' => 'GBP (£)',
                        'KRW' => 'KRW (₩)',
                    ], ['prompt' => 'Select Currency']) ?>
					</div>
					<div class="col-md-3">
						<?= $form->field($model, 'invoice_prefix')->textInput([
                        'maxlength' => true,
                        'placeholder' => 'INV'
                    ]) ?>
					</div>
					<div class="col-md-3">
						<?= $form->field($model, 'estimate_prefix')->textInput([
                        'maxlength' => true,
                        'placeholder' => 'EST'
                    ]) ?>
					</div>
					<div class="col-md-3">
						<?= $form->field($model, 'due_date_days')->textInput([
                        'type' => 'number',
                        'min' => 1,
                        'max' => 365
                    ]) ?>
					</div>
					<div class="col-md-3">
						<?= $form->field($model, 'estimate_validity_days')->textInput([
                        'type' => 'number',
                        'min' => 1,
                        'max' => 365
                    ]) ?>
					</div>
				</div>

				<div class="alert alert-light">
					<div class="row">
						<div class="col-md-3">
							<small><strong>Next Invoice Number:</strong> <span
									id="next-invoice-number"><?= $model->generateInvoiceNumber() ?></span></small>
						</div>
						<div class="col-md-3">
							<small><strong>Next Estimate Number:</strong> <span
									id="next-estimate-number"><?= $model->generateEstimateNumber() ?></span></small>
						</div>
						<div class="col-md-3">
							<small><strong>Default Due Date:</strong>
								<?= Yii::$app->formatter->asDate($model->getDefaultDueDate()) ?></small>
						</div>
						<div class="col-md-3">
							<small><strong>Default Estimate Expiry:</strong>
								<?= Yii::$app->formatter->asDate($model->getDefaultExpiryDate()) ?></small>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Display & PDF Settings -->
	<div class="card mb-4">
		<div class="card-header p-2" style="cursor: pointer;" data-custom-collapse="true"
			data-target="#display-settings-collapse" aria-expanded="false">
			<h5 class="card-title mb-0 d-flex justify-content-between align-items-center">
				<span><i class="fas fa-palette mr-2"></i>Display & PDF Settings</span>
				<i class="fas fa-chevron-down collapse-icon"></i>
			</h5>
		</div>
		<div class="collapse" id="display-settings-collapse">
			<div class="card-body">
				<div class="row">
					<div class="col-md-6">
						<div class="form-group">
							<label class="font-weight-bold">Dark Mode</label>
							<div class="custom-control custom-switch">
								<input type="hidden" name="Company[dark_mode]" value="0">
								<input type="checkbox" class="custom-control-input" id="dark-mode-switch"
									name="Company[dark_mode]" value="1" <?= $model->dark_mode ? 'checked' : '' ?>>
								<label class="custom-control-label" for="dark-mode-switch">
									Enable dark mode theme
								</label>
							</div>
							<small class="form-text text-muted">
								Enable dark mode theme for your company's interface
							</small>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<label class="font-weight-bold">Use CJK Fonts for PDF</label>
							<div class="custom-control custom-switch">
								<input type="hidden" name="Company[use_cjk_font]" value="0">
								<input type="checkbox" class="custom-control-input" id="cjk-font-switch"
									name="Company[use_cjk_font]" value="1" <?= $model->use_cjk_font ? 'checked' : '' ?>>
								<label class="custom-control-label" for="cjk-font-switch">
									Use CJK fonts for PDF generation
								</label>
							</div>
							<small class="form-text text-muted">
								Use CJK (Chinese, Japanese, Korean) fonts for better PDF rendering of Asian characters
							</small>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Email Settings -->
	<div class="card mb-4">
		<div class="card-header p-2" style="cursor: pointer;" data-custom-collapse="true"
			data-target="#email-settings-collapse" aria-expanded="false">
			<h5 class="card-title mb-0 d-flex justify-content-between align-items-center">
				<span><i class="fas fa-envelope mr-2"></i>Email Settings</span>
				<i class="fas fa-chevron-down collapse-icon"></i>
			</h5>
		</div>
		<div class="collapse" id="email-settings-collapse">
			<div class="card-body">
				<div class="card mb-4">
					<div class="card-header d-flex justify-content-between align-items-center">
						<h5 class="card-title mb-0">
							<i class="fas fa-envelope mr-2"></i>Email Settings
						</h5>
						<?= Html::button('Test Email', [
                        'class' => 'btn btn-outline-primary btn-sm',
                        'id' => 'test-email-btn',
                        'data-toggle' => 'modal',
                        'data-target' => '#test-email-modal'
                    ]) ?>
					</div>
					<div class="card-body">
						<?= $form->field($model, 'smtp2go_api_key')->passwordInput([
                        'maxlength' => true,
                        'placeholder' => 'Enter your SMTP2GO API key'
                    ]) ?>

						<?= $form->field($model, 'sender_email')->input('email', [
                        'maxlength' => true,
                        'placeholder' => 'noreply@yourcompany.com'
                    ]) ?>

						<?= $form->field($model, 'sender_name')->textInput([
                        'maxlength' => true,
                        'placeholder' => 'Your Company Name'
                    ]) ?>

						<?= $form->field($model, 'bcc_email')->input('email', [
                        'maxlength' => true,
                        'placeholder' => 'admin@yourcompany.com'
                    ]) ?>

						<div class="alert alert-info">
							<small>
								<i class="fas fa-info-circle mr-1"></i>
								To use email functionality, you need to configure SMTP2GO API.
								<a href="https://www.smtp2go.com" target="_blank">Get your API key here</a>.
								<br><br>
								<strong>Email Configuration:</strong>
								<br>• <strong>Sender Email:</strong> The email address that will appear as "From" in
								sent
								emails
								<br>• <strong>Sender Name:</strong> The name that will appear as sender (defaults to
								company
								name)
								<br>• <strong>BCC Email:</strong> Email address to receive blind carbon copy of all sent
								emails
							</small>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="form-group">
		<?= Html::submitButton('Save Settings', ['class' => 'btn btn-success']) ?>
		<?= Html::button('Reset to Defaults', [
            'class' => 'btn btn-outline-warning',
            'id' => 'reset-defaults-btn',
            'data-confirm' => 'Are you sure you want to reset all settings to default values?'
        ]) ?>
		<?= Html::a('Export Backup', ['/company/backup'], [
            'class' => 'btn btn-outline-info',
            'target' => '_blank'
        ]) ?>
	</div>

	<?php ActiveForm::end(); ?>

</div>

<style>
/* Custom switch styling to ensure proper display */
.custom-switch {
	padding-left: 2.25rem;
	position: relative;
}

.custom-switch .custom-control-input {
	position: absolute;
	left: 0;
	z-index: -1;
	width: 1rem;
	height: 1.25rem;
	opacity: 0;
}

.custom-switch .custom-control-label {
	position: relative;
	margin-bottom: 0;
	vertical-align: top;
}

.custom-switch .custom-control-label::before {
	position: absolute;
	top: 0.25rem;
	left: -2.25rem;
	width: 1.75rem;
	height: 1rem;
	pointer-events: none;
	content: "";
	background-color: #adb5bd;
	border: #adb5bd;
	border-radius: 0.5rem;
	transition: background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.custom-switch .custom-control-label::after {
	position: absolute;
	top: calc(0.25rem + 2px);
	left: calc(-2.25rem + 2px);
	width: calc(1rem - 4px);
	height: calc(1rem - 4px);
	content: "";
	background-color: #fff;
	border-radius: 0.5rem;
	transition: transform 0.15s ease-in-out;
}

.custom-switch .custom-control-input:checked~.custom-control-label::before {
	background-color: #007bff;
	border-color: #007bff;
}

.custom-switch .custom-control-input:checked~.custom-control-label::after {
	transform: translateX(0.75rem);
}

.custom-switch .custom-control-input:focus~.custom-control-label::before {
	box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.custom-switch .custom-control-input:focus:not(:checked)~.custom-control-label::before {
	border-color: #80bdff;
}

.custom-switch .custom-control-input:not(:disabled):active~.custom-control-label::before {
	background-color: #b3d7ff;
	border-color: #b3d7ff;
}

.custom-switch .custom-control-input:disabled~.custom-control-label,
.custom-switch .custom-control-input:disabled~.custom-control-label::before {
	color: #6c757d;
}

.custom-switch .custom-control-input:disabled~.custom-control-label::before {
	background-color: #e9ecef;
}
</style>

<!-- Test Email Modal -->
<div class="modal fade" id="test-email-modal" tabindex="-1" role="dialog">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Test Email Configuration</h5>
				<button type="button" class="close" data-dismiss="modal">
					<span>&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<form id="test-email-form">
					<div class="form-group">
						<label for="test-email">Email Address</label>
						<input type="email" class="form-control" id="test-email" required
							placeholder="Enter email to send test message">
					</div>
					<div id="test-email-result" class="mt-3" style="display: none;"></div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
				<button type="button" class="btn btn-primary" id="send-test-email">Send Test Email</button>
			</div>
		</div>
	</div>
</div>

<?php
$this->registerCss("
    /* Modern Logo Upload Area */
    .logo-upload-area {
        position: relative;
        border: 2px dashed #e5e7eb;
        border-radius: 12px;
        padding: 2rem;
        text-align: center;
        background: #f8fafc;
        transition: all 0.3s ease;
        cursor: pointer;
        min-height: 120px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .logo-upload-area:hover {
        border-color: #6366f1;
        background: #f0f9ff;
    }
    
    .logo-upload-area.dragover {
        border-color: #6366f1;
        background: #e0e7ff;
        transform: scale(1.02);
    }
    
    .upload-content {
        pointer-events: none;
    }
    
    .upload-icon {
        font-size: 2rem;
        color: #6b7280;
        margin-bottom: 0.5rem;
        display: block;
    }
    
    .upload-text {
        font-size: 1rem;
        color: #374151;
        margin-bottom: 0.25rem;
    }
    
    .upload-hint {
        font-size: 0.875rem;
        color: #6b7280;
    }
    
    .file-input {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        cursor: pointer;
        z-index: 1;
    }
    
    /* Dark Mode Support */
    body.dark-mode .logo-upload-area {
        border-color: #374151;
        background: #1f2937;
    }
    
    body.dark-mode .logo-upload-area:hover {
        border-color: #60a5fa;
        background: #1e293b;
    }
    
    body.dark-mode .logo-upload-area.dragover {
        border-color: #60a5fa;
        background: #334155;
    }
    
    body.dark-mode .upload-icon {
        color: #9ca3af;
    }
    
    body.dark-mode .upload-text {
        color: #f3f4f6;
    }
    
    body.dark-mode .upload-hint {
        color: #9ca3af;
    }
");

$this->registerJs("
    // Update next invoice number when prefix changes
    $('#company-invoice_prefix').on('input', function() {
        // This would need an AJAX call to get updated invoice number
        // Implementation depends on your needs
    });
    
    // Test email functionality
    $('#send-test-email').click(function() {
        const email = $('#test-email').val();
        if (!email) {
            alert('Please enter an email address');
            return;
        }
        
        const btn = $(this);
        btn.prop('disabled', true).text('Sending...');
        
        $.post('" . \yii\helpers\Url::to(['/company/test-email']) . "', {
            email: email
        })
        .done(function(response) {
            const alertClass = response.success ? 'alert-success' : 'alert-danger';
            $('#test-email-result').html(
                '<div class=\"alert ' + alertClass + '\">' + response.message + '</div>'
            ).show();
        })
        .fail(function() {
            $('#test-email-result').html(
                '<div class=\"alert alert-danger\">Failed to send test email. Please try again.</div>'
            ).show();
        })
        .always(function() {
            btn.prop('disabled', false).text('Send Test Email');
        });
    });
    
    // Reset to defaults
    $('#reset-defaults-btn').click(function() {
        if (confirm($(this).data('confirm'))) {
            $.post('" . \yii\helpers\Url::to(['/company/reset-to-default']) . "')
            .done(function(response) {
                if (response.success) {
                    alert(response.message);
                    location.reload();
                } else {
                    alert('Failed to reset settings: ' + response.message);
                }
            });
        }
    });
    
    // Modern Logo Upload with Drag & Drop
    const uploadArea = $('#logo-upload-area');
    const fileInput = $('#logo-upload');
    
    // Click to upload
    uploadArea.on('click', function() {
        fileInput.click();
    });
    
    // Drag & Drop functionality
    uploadArea.on('dragover', function(e) {
        e.preventDefault();
        $(this).addClass('dragover');
    });
    
    uploadArea.on('dragleave', function(e) {
        e.preventDefault();
        $(this).removeClass('dragover');
    });
    
    uploadArea.on('drop', function(e) {
        e.preventDefault();
        $(this).removeClass('dragover');
        
        const files = e.originalEvent.dataTransfer.files;
        if (files.length > 0) {
            const file = files[0];
            if (file.type.startsWith('image/')) {
                // Trigger file input change
                fileInput[0].files = files;
                fileInput.trigger('change');
            } else {
                alert('Please select an image file.');
            }
        }
    });
    
    // File input change handler
    fileInput.on('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            // Validate file size
            if (file.size > 2 * 1024 * 1024) { // 2MB limit
                alert('File size must be less than 2MB');
                this.value = '';
                return;
            }
            
            // Validate file type
            const allowedTypes = ['image/png', 'image/jpeg', 'image/jpg', 'image/gif'];
            if (!allowedTypes.includes(file.type)) {
                alert('Please select a valid image file (PNG, JPG, JPEG, or GIF)');
                this.value = '';
                return;
            }
            
            // Show preview
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#logo-preview-img').attr('src', e.target.result);
                $('#logo-preview').show();
                
                // Update upload area to show file selected
                uploadArea.find('.upload-text').html('<strong>File selected:</strong> ' + file.name);
                uploadArea.find('.upload-hint').text('Click to change or drag new file');
                uploadArea.find('.upload-icon').removeClass('fa-cloud-upload-alt').addClass('fa-check-circle').css('color', '#10b981');
            };
            reader.readAsDataURL(file);
        } else {
            $('#logo-preview').hide();
            // Reset upload area
            uploadArea.find('.upload-text').html('<strong>Click to upload</strong> or drag and drop');
            uploadArea.find('.upload-hint').text('PNG, JPG, JPEG, GIF up to 2MB');
            uploadArea.find('.upload-icon').removeClass('fa-check-circle').addClass('fa-cloud-upload-alt').css('color', '#6b7280');
        }
    });
    
    // Delete logo with modern confirmation
    $('#delete-logo-btn').click(function() {
        if (confirm('Are you sure you want to delete the company logo? This action cannot be undone.')) {
            $.post('" . \yii\helpers\Url::to(['/company/delete-logo']) . "')
            .done(function(response) {
                if (response.success) {
                    alert(response.message);
                    location.reload();
                } else {
                    alert('Failed to delete logo: ' + response.message);
                }
            });
        }
    });
    
    // Add event listeners for custom switches
    $('#dark-mode-switch').on('change', function() {
        console.log('Dark mode changed to: ' + $(this).is(':checked'));
    });
    
    $('#cjk-font-switch').on('change', function() {
        console.log('CJK font changed to: ' + $(this).is(':checked'));
    });
    
    // Language change handler
    $('#company-language').on('change', function() {
        var selectedLanguage = $(this).val();
        console.log('Language changed to:', selectedLanguage);
        
        // Optionally show a preview message
        if (selectedLanguage) {
            var languageNames = {
                'en-US': 'English',
                'es-ES': 'Español', 
                'ko-KR': '한국어',
                'zh-CN': '简体中文',
                'zh-TW': '繁體中文'
            };
            
            // Show temporary notification
            var languageName = languageNames[selectedLanguage] || selectedLanguage;
            console.log('Selected language name:', languageName);
        }
    });
    
    // Collapse functionality is handled by collapse-helper.js
");

// Include collapse helper CSS
$this->registerCss("
    /* Collapse functionality styles */
    .collapse {
        display: none;
        transition: all 0.3s ease;
    }
    
    .collapse.show {
        display: block;
    }
    
    .collapse-icon {
        transition: transform 0.3s ease;
    }
    
    .collapse-icon.rotated {
        transform: rotate(180deg);
    }
    
    [data-custom-collapse] {
        transition: background-color 0.3s ease;
    }
    
    [data-custom-collapse]:hover {
        background-color: rgba(0,0,0,0.05);
    }
");
?>