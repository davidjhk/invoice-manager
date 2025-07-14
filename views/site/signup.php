<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap4\ActiveForm $form */
/** @var app\models\SignupForm $model */

use yii\bootstrap4\ActiveForm;
use yii\bootstrap4\Html;

$this->title = 'Create Account';
$this->params['breadcrumbs'][] = $this->title;

// Use custom layout for auth pages
$this->context->layout = 'auth';
?>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <div class="auth-logo">
                <i class="fas fa-user-plus"></i>
            </div>
            <h1 class="auth-title">Create Account</h1>
            <p class="auth-subtitle">Sign up to get started with invoice management</p>
        </div>

        <div class="auth-form">
            <?php $form = ActiveForm::begin([
                'id' => 'signup-form',
                'options' => ['class' => 'auth-form-inner'],
                'fieldConfig' => [
                    'template' => '<div class="form-floating">{input}{label}{error}</div>',
                    'labelOptions' => ['class' => 'form-label'],
                    'inputOptions' => ['class' => 'form-control'],
                    'errorOptions' => ['class' => 'invalid-feedback'],
                ],
            ]); ?>

            <div class="auth-form-grid">
                <?= $form->field($model, 'full_name')->textInput([
                    'autofocus' => true,
                    'placeholder' => 'Enter your full name',
                    'class' => 'form-control'
                ])->label('Full Name') ?>

                <?= $form->field($model, 'username')->textInput([
                    'placeholder' => 'Choose a username',
                    'class' => 'form-control'
                ])->label('Username') ?>
            </div>

            <div class="auth-form-full">
                <?= $form->field($model, 'email')->textInput([
                    'placeholder' => 'Enter your email address',
                    'class' => 'form-control',
                    'type' => 'email'
                ])->label('Email Address') ?>
            </div>

            <div class="auth-form-grid">
                <?= $form->field($model, 'password')->passwordInput([
                    'placeholder' => 'Create a password',
                    'class' => 'form-control'
                ])->label('Password') ?>

                <?= $form->field($model, 'password_repeat')->passwordInput([
                    'placeholder' => 'Confirm your password',
                    'class' => 'form-control'
                ])->label('Confirm Password') ?>
            </div>

            <div class="form-actions">
                <?= Html::submitButton('Create Account', [
                    'class' => 'btn btn-primary btn-block btn-auth',
                    'name' => 'signup-button'
                ]) ?>
            </div>

            <?php ActiveForm::end(); ?>

            <?php
            // Check if Google SSO is properly configured
            $googleClientId = Yii::$app->params['googleClientId'] ?? '';
            $googleClientSecret = Yii::$app->params['googleClientSecret'] ?? '';
            $isGoogleSSOEnabled = !empty($googleClientId) && !empty($googleClientSecret);
            ?>

            <?php if ($isGoogleSSOEnabled): ?>
            <div class="auth-divider">
                <span>or</span>
            </div>

            <div class="auth-social">
                <a href="<?= \yii\helpers\Url::to(['site/google-login']) ?>" class="btn btn-google btn-block btn-auth">
                    <i class="fab fa-google"></i>
                    Sign up with Google
                </a>
            </div>
            <?php endif; ?>

            <div class="auth-divider">
                <span>Already have an account?</span>
            </div>

            <div class="auth-footer">
                <?= Html::a('Sign In', ['site/login'], ['class' => 'btn btn-outline-primary btn-block btn-auth']) ?>
            </div>
        </div>
    </div>
</div>

