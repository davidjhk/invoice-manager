<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $user app\models\User */

$resetLink = Yii::$app->urlManager->createAbsoluteUrl(['site/reset-password', 'token' => $user->password_reset_token]);
?>
<div class="password-reset">
    <h1>Password Reset Request</h1>
    
    <p>Hello <?= Html::encode($user->getDisplayName()) ?>,</p>
    
    <p>You have requested to reset your password. Please click the link below to reset your password:</p>
    
    <p>
        <a href="<?= Html::encode($resetLink) ?>" style="display: inline-block; padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 4px;">
            Reset Password
        </a>
    </p>
    
    <p>If the button doesn't work, you can copy and paste this link into your browser:</p>
    <p><?= Html::encode($resetLink) ?></p>
    
    <p>This link will expire in 24 hours for security reasons.</p>
    
    <p>If you did not request this password reset, please ignore this email.</p>
    
    <hr>
    <p><small>This email was sent from <?= Html::encode(Yii::$app->name) ?>.</small></p>
</div>