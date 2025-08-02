<?php

/* @var $this yii\web\View */
/* @var $model app\models\ChangePasswordForm */

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = Yii::t('app', 'Change Password');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-change-password">
    <div class="row justify-content-center">
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-key mr-2"></i><?= Yii::t('app', 'Change Password') ?>
                    </h3>
                </div>
                <div class="card-body">
                    <p class="text-muted"><?= Yii::t('app', 'Please enter your current password and choose a new one.') ?></p>

                    <?php $form = ActiveForm::begin([
                        'id' => 'change-password-form',
                        'fieldConfig' => [
                            'template' => "<div class=\"form-group\">{label}\n<div class=\"input-group\">{input}</div>\n<div class=\"text-danger\">{error}</div></div>",
                            'labelOptions' => ['class' => 'form-label'],
                            'inputOptions' => ['class' => 'form-control'],
                        ],
                    ]); ?>

                    <?= $form->field($model, 'currentPassword')->passwordInput([
                        'placeholder' => Yii::t('app', 'Enter current password'),
                        'class' => 'form-control',
                    ])->label(Yii::t('app', 'Current Password')) ?>

                    <?= $form->field($model, 'newPassword')->passwordInput([
                        'placeholder' => Yii::t('app', 'Enter new password (min 6 characters)'),
                        'class' => 'form-control',
                    ])->label(Yii::t('app', 'New Password')) ?>

                    <?= $form->field($model, 'confirmPassword')->passwordInput([
                        'placeholder' => Yii::t('app', 'Confirm new password'),
                        'class' => 'form-control',
                    ])->label(Yii::t('app', 'Confirm New Password')) ?>

                    <div class="form-group">
                        <?= Html::submitButton('<i class="fas fa-save mr-2"></i>' . Yii::t('app', 'Change Password'), [
                            'class' => 'btn btn-primary',
                            'name' => 'change-password-button'
                        ]) ?>
                        <?= Html::a('<i class="fas fa-times mr-2"></i>' . Yii::t('app', 'Cancel'), ['/site/index'], [
                            'class' => 'btn btn-secondary ml-2'
                        ]) ?>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.site-change-password {
    padding: 2rem 0;
}

.card {
    border: none;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border-radius: 0.5rem;
}

.card-header {
    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
    color: white;
    border-radius: 0.5rem 0.5rem 0 0 !important;
}

.input-group {
    margin-bottom: 0.5rem;
}

.form-control {
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    padding: 0.75rem;
    font-size: 0.875rem;
    line-height: 1.5;
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

.btn-secondary {
    background: #6b7280;
    border: none;
    border-radius: 0.375rem;
    padding: 0.75rem 1.5rem;
    font-weight: 500;
    color: white;
}

.btn-secondary:hover {
    background: #4b5563;
    color: white;
}
</style>