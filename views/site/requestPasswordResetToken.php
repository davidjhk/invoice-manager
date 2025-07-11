<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap4\ActiveForm $form */
/** @var app\models\PasswordResetRequestForm $model */

use yii\bootstrap4\ActiveForm;
use yii\bootstrap4\Html;

$this->title = 'Reset Password';
$this->params['breadcrumbs'][] = $this->title;

// Use custom layout for auth pages
$this->context->layout = 'auth';
?>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <div class="auth-logo">
                <i class="fas fa-key"></i>
            </div>
            <h1 class="auth-title">Reset Password</h1>
            <p class="auth-subtitle">Enter your email address and we'll send you a link to reset your password</p>
        </div>

        <div class="auth-form">
            <?php $form = ActiveForm::begin([
                'id' => 'request-password-reset-form',
                'options' => ['class' => 'auth-form-inner'],
                'fieldConfig' => [
                    'template' => '<div class="form-floating">{input}{label}{error}</div>',
                    'labelOptions' => ['class' => 'form-label'],
                    'inputOptions' => ['class' => 'form-control'],
                    'errorOptions' => ['class' => 'invalid-feedback'],
                ],
            ]); ?>

            <?= $form->field($model, 'email')->textInput([
                'autofocus' => true,
                'placeholder' => 'Enter your email address',
                'class' => 'form-control',
                'type' => 'email'
            ])->label('Email Address') ?>

            <div class="form-actions">
                <?= Html::submitButton('Send Reset Link', [
                    'class' => 'btn btn-primary btn-block btn-auth',
                    'name' => 'password-reset-button'
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
