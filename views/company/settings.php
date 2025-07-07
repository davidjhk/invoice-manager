<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\models\Company $model */

$this->title = 'Company Settings';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="company-settings">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?= Html::encode($this->title) ?></h1>
        <?= Html::a('Back to Dashboard', ['/site/index'], ['class' => 'btn btn-secondary']) ?>
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
                        <i class="fas fa-building mr-2"></i>Company Information
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

            <!-- Company Logo -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-image mr-2"></i>Company Logo
                    </h5>
                </div>
                <div class="card-body">
                    <?php if ($model->hasLogo()): ?>
                        <div class="current-logo mb-3">
                            <label class="form-label font-weight-bold">Current Logo</label>
                            <div class="logo-preview">
                                <img src="<?= $model->getLogoUrl() ?>" alt="Company Logo" class="img-thumbnail" style="max-height: 150px;">
                                <div class="mt-2">
                                    <small class="text-muted">Filename: <?= Html::encode($model->logo_filename) ?></small>
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
                        <?= Html::activeFileInput($model, 'logo_upload', [
                            'class' => 'form-control-file',
                            'accept' => 'image/*',
                            'id' => 'logo-upload'
                        ]) ?>
                        <small class="form-text text-muted">
                            Recommended: PNG or JPG format, max size 2MB, optimal dimensions: 300x100px
                        </small>
                    </div>
                    
                    <div id="logo-preview" class="mt-3" style="display: none;">
                        <label class="form-label">Preview</label>
                        <div>
                            <img id="logo-preview-img" src="#" alt="Logo Preview" class="img-thumbnail" style="max-height: 150px;">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Email Settings -->
        <div class="col-lg-6">
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
                    
                    <div class="alert alert-info">
                        <small>
                            <i class="fas fa-info-circle mr-1"></i>
                            To use email functionality, you need to configure SMTP2GO API. 
                            <a href="https://www.smtp2go.com" target="_blank">Get your API key here</a>.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Invoice Settings -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-file-invoice mr-2"></i>Invoice Settings
            </h5>
        </div>
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
            </div>
            
            <div class="alert alert-light">
                <div class="row">
                    <div class="col-md-4">
                        <small><strong>Next Invoice Number:</strong> <span id="next-invoice-number"><?= $model->generateInvoiceNumber() ?></span></small>
                    </div>
                    <div class="col-md-4">
                        <small><strong>Next Estimate Number:</strong> <span id="next-estimate-number"><?= $model->generateEstimateNumber() ?></span></small>
                    </div>
                    <div class="col-md-4">
                        <small><strong>Default Due Date:</strong> <?= Yii::$app->formatter->asDate($model->getDefaultDueDate()) ?></small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="form-group">
        <?= Html::submitButton('Save Settings', ['class' => 'btn btn-success btn-lg']) ?>
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
    
    // Logo upload preview
    $('#logo-upload').change(function(e) {
        const file = e.target.files[0];
        if (file) {
            if (file.size > 2 * 1024 * 1024) { // 2MB limit
                alert('File size must be less than 2MB');
                this.value = '';
                return;
            }
            
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#logo-preview-img').attr('src', e.target.result);
                $('#logo-preview').show();
            };
            reader.readAsDataURL(file);
        } else {
            $('#logo-preview').hide();
        }
    });
    
    // Delete logo
    $('#delete-logo-btn').click(function() {
        if (confirm('Are you sure you want to delete the company logo?')) {
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
");
?>