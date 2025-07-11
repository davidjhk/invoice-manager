<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Company $model */

$this->title = 'Create New Company';
$this->params['breadcrumbs'][] = ['label' => 'Select Company', 'url' => ['select']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="company-create">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?= Html::encode($this->title) ?></h1>
        <?= Html::a('Back to Company List', ['select'], ['class' => 'btn btn-secondary']) ?>
    </div>

    <?php if ($model->hasErrors()): ?>
        <div class="alert alert-danger">
            <h5>Please fix the following errors:</h5>
            <?= Html::errorSummary($model) ?>
        </div>
    <?php endif; ?>

    <?php $form = ActiveForm::begin([
        'id' => 'company-create-form',
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
                    <?= $form->field($model, 'company_name')->textInput(['maxlength' => true, 'placeholder' => 'Enter company name']) ?>

                    <?= $form->field($model, 'company_address')->textarea(['rows' => 4, 'placeholder' => 'Enter company address']) ?>

                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($model, 'company_phone')->textInput(['maxlength' => true, 'placeholder' => 'Enter phone number']) ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'company_email')->input('email', ['maxlength' => true, 'placeholder' => 'Enter company email']) ?>
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
                    <div class="form-group">
                        <label class="form-label font-weight-bold">Upload Logo</label>
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
                            <img id="logo-preview-img" src="#" alt="Logo Preview" class="img-thumbnail"
                                style="max-height: 150px;">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Email Settings -->
        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-envelope mr-2"></i>Email Settings
                    </h5>
                </div>
                <div class="card-body">
                    <?= $form->field($model, 'smtp2go_api_key')->passwordInput([
                        'maxlength' => true,
                        'placeholder' => 'Enter your SMTP2GO API key (optional)'
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
                        'placeholder' => 'admin@yourcompany.com (optional)'
                    ]) ?>

                    <div class="alert alert-info">
                        <small>
                            <i class="fas fa-info-circle mr-1"></i>
                            Email settings are optional but recommended for sending invoices.
                            <a href="https://www.smtp2go.com" target="_blank">Get your SMTP2GO API key here</a>.
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
                        'placeholder' => '10.00'
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
                        'max' => 365,
                        'placeholder' => '30'
                    ]) ?>
                </div>
                <div class="col-md-3">
                    <?= $form->field($model, 'estimate_validity_days')->textInput([
                        'type' => 'number',
                        'min' => 1,
                        'max' => 365,
                        'placeholder' => '30'
                    ]) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="form-group">
        <?= Html::submitButton('Create Company', ['class' => 'btn btn-success btn-lg']) ?>
        <?= Html::a('Cancel', ['select'], ['class' => 'btn btn-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?php
$this->registerJs("
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
");
?>