<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap4\ActiveForm $form */
/** @var app\models\LoginForm $model */

use yii\bootstrap4\ActiveForm;
use yii\bootstrap4\Html;

$this->title = 'Login';
$this->params['breadcrumbs'][] = $this->title;

// Use custom layout for auth pages
$this->context->layout = 'auth';
?>

<div class="auth-container">
	<div class="auth-card">
		<div class="auth-header">
			<div class="auth-logo">
				<i class="fas fa-user-circle"></i>
			</div>
			<h1 class="auth-title">Welcome Back</h1>
			<p class="auth-subtitle">Sign in to your account to continue</p>
		</div>

		<div class="auth-form">
			<?php $form = ActiveForm::begin([
                'id' => 'login-form',
                'options' => ['class' => 'auth-form-inner'],
                'fieldConfig' => [
                    'template' => '<div class="form-floating">{input}{label}{error}</div>',
                    'labelOptions' => ['class' => 'form-label'],
                    'inputOptions' => ['class' => 'form-control'],
                    'errorOptions' => ['class' => 'invalid-feedback'],
                ],
            ]); ?>

			<?= $form->field($model, 'username')->textInput([
                'autofocus' => true,
                'placeholder' => 'Enter your username',
                'class' => 'form-control'
            ])->label('Username') ?>

			<?= $form->field($model, 'password')->passwordInput([
                'placeholder' => 'Enter your password',
                'class' => 'form-control'
            ])->label('Password') ?>

			<div class="form-options">
				<div class="form-check">
					<?= $form->field($model, 'rememberMe')->checkbox([
                        'template' => '{input} {label}{error}',
                        'labelOptions' => ['class' => 'form-check-label'],
                        'inputOptions' => ['class' => 'form-check-input'],
                    ])->label('Remember me') ?>
				</div>
				<div class="forgot-password">
					<?= Html::a('Forgot Password?', ['site/request-password-reset'], ['class' => 'forgot-link']) ?>
				</div>
			</div>

			<div class="form-actions">
				<?= Html::submitButton('Sign In', [
                    'class' => 'btn btn-primary btn-block btn-auth',
                    'name' => 'login-button'
                ]) ?>
			</div>

			<?php ActiveForm::end(); ?>

			<div class="auth-divider">
				<span>or</span>
			</div>

			<div class="auth-social">
				<a href="<?= \yii\helpers\Url::to(['site/google-login']) ?>" class="btn btn-google btn-block btn-auth">
					<i class="fab fa-google"></i>
					Continue with Google
				</a>
			</div>

			<?php // Create Account 기능을 관리자 전용으로 변경 ?>
			<?php /*
            <div class="auth-divider">
                <span>Don't have an account?</span>
            </div>

            <div class="auth-footer">
                <?= Html::a('Create Account', ['site/signup'], ['class' => 'btn btn-outline-primary btn-block btn-auth']) ?>
		</div>
		*/ ?>
	</div>

	<div class="auth-demo-info">
		<small>
			<i class="fas fa-info-circle"></i>
			Demo accounts: <strong>demo/demo123</strong>
		</small>
	</div>
</div>
</div>