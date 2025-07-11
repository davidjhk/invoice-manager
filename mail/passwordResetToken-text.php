<?php

/* @var $this yii\web\View */
/* @var $user app\models\User */

$resetLink = Yii::$app->urlManager->createAbsoluteUrl(['site/reset-password', 'token' => $user->password_reset_token]);
?>
Password Reset Request

Hello <?= $user->getDisplayName() ?>,

You have requested to reset your password. Please click the link below to reset your password:

<?= $resetLink ?>

This link will expire in 24 hours for security reasons.

If you did not request this password reset, please ignore this email.

---
This email was sent from <?= Yii::$app->name ?>.