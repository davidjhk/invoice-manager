<![CDATA[<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap4\ActiveForm $form */
/** @var app\models\SignupForm $model */
/** @var app\models\Plan[] $plans */

use yii\bootstrap4\ActiveForm;
use yii\bootstrap4\Html;
use yii\helpers\ArrayHelper;

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

            <?php if (!empty($plans)): ?>
            <div class="auth-form-full">
                <div class="plan-selection-section">
                    <h5 class="plan-section-title">Choose Your Plan</h5>
                    <?php if ($model->hasErrors('plan_id')): ?>
                        <div class="alert alert-danger" style="margin-bottom: 1rem;">
                            <?php foreach ($model->getErrors('plan_id') as $error): ?>
                                <div><?= Html::encode($error) ?></div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <div class="plan-options">
                        <?php foreach ($plans as $plan): ?>
                        <div class="plan-option">
                            <?= Html::radio('SignupForm[plan_id]', $model->plan_id == $plan->id, [
                                'value' => $plan->id,
                                'id' => 'plan-' . $plan->id,
                                'class' => 'plan-radio'
                            ]) ?>
                            <label for="plan-<?= $plan->id ?>" class="plan-label">
                                <div class="plan-info">
                                    <div class="plan-name"><?= Html::encode($plan->name) ?></div>
                                    <div class="plan-price"><?= $plan->getFormattedPrice() ?>/month</div>
                                    <div class="plan-description"><?= Html::encode($plan->description) ?></div>
                                </div>
                            </label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <div class="form-actions">
                <?= Html::submitButton('Create Account', [
                    'class' => 'btn btn-primary btn-block btn-auth',
                    'name' => 'signup-button'
                ]) ?>
            </div>

            <?php ActiveForm::end(); ?>

            <div class="auth-divider">
                <span>Already have an account?</span>
            </div>

            <div class="auth-footer">
                <?= Html::a('Sign In', ['site/login'], ['class' => 'btn btn-outline-primary btn-block btn-auth']) ?>
            </div>
        </div>
    </div>
</div>

<style>
/* Plan selection styling fixes for dark theme */
.plan-selection-section {
    margin-top: 1rem;
}

.plan-section-title {
    color: #ffffff !important;
    font-weight: 600;
    margin-bottom: 1rem;
    text-align: center;
}

.plan-options {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.plan-option {
    position: relative;
}

.plan-radio {
    position: absolute;
    opacity: 0;
    cursor: pointer;
}

.plan-label {
    display: block;
    background: rgba(255, 255, 255, 0.1);
    border: 2px solid rgba(255, 255, 255, 0.2);
    border-radius: 8px;
    padding: 1rem;
    cursor: pointer;
    transition: all 0.3s ease;
    margin-bottom: 0;
}

.plan-label:hover {
    background: rgba(255, 255, 255, 0.15);
    border-color: rgba(255, 255, 255, 0.4);
    transform: translateY(-2px);
}

.plan-radio:checked + .plan-label {
    background: rgba(79, 70, 229, 0.3);
    border-color: #4f46e5;
    box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.2);
}

.plan-info {
    text-align: center;
}

.plan-name {
    color: #ffffff !important;
    font-weight: 600;
    font-size: 1.1rem;
    margin-bottom: 0.25rem;
}

.plan-price {
    color: #a5b4fc !important;
    font-weight: 500;
    font-size: 1rem;
    margin-bottom: 0.25rem;
}

.plan-description {
    color: #cbd5e1 !important;
    font-size: 0.875rem;
    line-height: 1.4;
}

/* Radio button custom styling */
.plan-radio:checked + .plan-label::before {
    content: '✓';
    position: absolute;
    top: 0.5rem;
    right: 0.5rem;
    background: #4f46e5;
    color: white;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: bold;
}
</style>]]>