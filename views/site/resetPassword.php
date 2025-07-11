<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap4\ActiveForm $form */
/** @var app\models\ResetPasswordForm $model */

use yii\bootstrap4\ActiveForm;
use yii\bootstrap4\Html;

$this->title = 'Set New Password';
$this->params['breadcrumbs'][] = $this->title;

// Use custom layout for auth pages
$this->context->layout = 'auth';
?>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <div class="auth-logo">
                <i class="fas fa-lock"></i>
            </div>
            <h1 class="auth-title">Set New Password</h1>
            <p class="auth-subtitle">Enter your new password below to complete the reset process</p>
        </div>

        <div class="auth-form">
            <?php $form = ActiveForm::begin([
                'id' => 'reset-password-form',
                'options' => ['class' => 'auth-form-inner'],
                'fieldConfig' => [
                    'template' => '<div class="form-floating">{input}{label}{error}</div>',
                    'labelOptions' => ['class' => 'form-label'],
                    'inputOptions' => ['class' => 'form-control'],
                    'errorOptions' => ['class' => 'invalid-feedback'],
                ],
            ]); ?>

            <?= $form->field($model, 'password')->passwordInput([
                'autofocus' => true,
                'placeholder' => 'Enter new password',
                'class' => 'form-control'
            ])->label('New Password') ?>

            <?= $form->field($model, 'password_repeat')->passwordInput([
                'placeholder' => 'Confirm new password',
                'class' => 'form-control'
            ])->label('Confirm Password') ?>

            <div class="form-actions">
                <?= Html::submitButton('Update Password', [
                    'class' => 'btn btn-primary btn-block btn-auth',
                    'name' => 'reset-password-button'
                ]) ?>
            </div>

            <?php ActiveForm::end(); ?>

            <div class="auth-footer">
                <div class="auth-divider">
                    <span>Remember your password?</span>
                </div>
                <?= Html::a('Back to Login', ['site/login'], ['class' => 'btn btn-outline-primary btn-block btn-auth']) ?>
            </div>
        </div>
    </div>
</div>

