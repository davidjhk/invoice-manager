<?php

/* @var $this yii\web\View */
/* @var $settings app\models\AdminSettings[] */

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Admin Settings';
$this->params['breadcrumbs'][] = ['label' => 'Admin', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="admin-settings">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?= Html::encode($this->title) ?></h1>
        <?= Html::a('<i class="fas fa-arrow-left mr-2"></i>Back to Dashboard', ['index'], ['class' => 'btn btn-secondary']) ?>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title"><i class="fas fa-cogs mr-2"></i>System Settings</h5>
                </div>
                <div class="card-body">
                    <?php $form = ActiveForm::begin([
                        'method' => 'post',
                        'options' => ['enctype' => 'multipart/form-data'],
                    ]); ?>

                    <?php foreach ($settings as $setting): ?>
                    <div class="form-group">
                        <?php if ($setting->setting_key === 'allow_signup'): ?>
                            <div class="custom-control custom-switch">
                                <?= Html::checkbox($setting->setting_key, $setting->setting_value, [
                                    'class' => 'custom-control-input',
                                    'id' => $setting->setting_key,
                                    'value' => 1,
                                ]) ?>
                                <label class="custom-control-label" for="<?= $setting->setting_key ?>">
                                    <strong>Allow User Registration</strong>
                                </label>
                            </div>
                            <small class="form-text text-muted"><?= Html::encode($setting->description) ?></small>
                        <?php elseif ($setting->setting_key === 'site_maintenance'): ?>
                            <div class="custom-control custom-switch">
                                <?= Html::checkbox($setting->setting_key, $setting->setting_value, [
                                    'class' => 'custom-control-input',
                                    'id' => $setting->setting_key,
                                    'value' => 1,
                                ]) ?>
                                <label class="custom-control-label" for="<?= $setting->setting_key ?>">
                                    <strong>Maintenance Mode</strong>
                                </label>
                            </div>
                            <small class="form-text text-muted"><?= Html::encode($setting->description) ?></small>
                        <?php else: ?>
                            <label for="<?= $setting->setting_key ?>" class="form-label">
                                <strong><?= Html::encode(ucwords(str_replace('_', ' ', $setting->setting_key))) ?></strong>
                            </label>
                            <?= Html::textInput($setting->setting_key, $setting->setting_value, [
                                'class' => 'form-control',
                                'id' => $setting->setting_key,
                                'placeholder' => $setting->description,
                            ]) ?>
                            <small class="form-text text-muted"><?= Html::encode($setting->description) ?></small>
                        <?php endif; ?>
                    </div>
                    <hr>
                    <?php endforeach; ?>

                    <div class="form-group">
                        <?= Html::submitButton('<i class="fas fa-save mr-2"></i>Save Settings', [
                            'class' => 'btn btn-primary',
                            'name' => 'save-settings-button'
                        ]) ?>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title"><i class="fas fa-info-circle mr-2"></i>Help</h5>
                </div>
                <div class="card-body">
                    <h6>Allow User Registration</h6>
                    <p class="text-muted small">When enabled, new users can register accounts. When disabled, the signup page will be inaccessible.</p>
                    
                    <h6>Maximum Users</h6>
                    <p class="text-muted small">Sets the maximum number of users that can register on the system.</p>
                    
                    <h6>Maintenance Mode</h6>
                    <p class="text-muted small">When enabled, the site will show a maintenance message to regular users. Admins can still access the system.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.custom-control-input:checked ~ .custom-control-label::before {
    background-color: #4f46e5;
    border-color: #4f46e5;
}

.custom-control-input:focus ~ .custom-control-label::before {
    box-shadow: 0 0 0 0.2rem rgba(79, 70, 229, 0.25);
}

.card {
    border: none;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border-radius: 0.5rem;
}

.form-control {
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
}

.form-control:focus {
    border-color: #4f46e5;
    box-shadow: 0 0 0 0.2rem rgba(79, 70, 229, 0.25);
}

.btn-primary {
    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
    border: none;
    border-radius: 0.375rem;
    padding: 0.75rem 1.5rem;
    font-weight: 500;
    transition: all 0.2s ease;
}

.btn-primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(79, 70, 229, 0.4);
}
</style>