<?php

/* @var $this yii\web\View */
/* @var $model app\models\User */

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Reset Password: ' . $model->getDisplayName();
$this->params['breadcrumbs'][] = ['label' => 'Admin', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => 'Users', 'url' => ['users']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="admin-reset-password">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?= Html::encode($this->title) ?></h1>
        <?= Html::a('<i class="fas fa-arrow-left mr-2"></i>Back to Users', ['users'], ['class' => 'btn btn-secondary']) ?>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title"><i class="fas fa-key mr-2"></i>Reset User Password</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-2"></i>
                        <strong>Important:</strong> This will reset the password for user <strong><?= Html::encode($model->getDisplayName()) ?></strong>. 
                        The user will need to use the new password to log in.
                    </div>

                    <?php $form = ActiveForm::begin([
                        'id' => 'reset-password-form',
                        'method' => 'post',
                    ]); ?>

                    <div class="form-group">
                        <label for="newPassword" class="form-label"><strong>New Password</strong></label>
                        <input type="password" class="form-control" id="newPassword" name="newPassword" 
                               placeholder="Enter new password (min 6 characters)" required minlength="6">
                        <small class="form-text text-muted">Password must be at least 6 characters long.</small>
                    </div>

                    <div class="form-group">
                        <label for="confirmPassword" class="form-label"><strong>Confirm New Password</strong></label>
                        <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" 
                               placeholder="Confirm new password" required>
                        <small class="form-text text-muted">Please confirm the new password.</small>
                    </div>

                    <div class="form-group">
                        <?= Html::submitButton('<i class="fas fa-key mr-2"></i>Reset Password', [
                            'class' => 'btn btn-warning',
                            'name' => 'reset-password-button',
                            'onclick' => 'return confirm("Are you sure you want to reset this user\'s password?")'
                        ]) ?>
                        <?= Html::a('<i class="fas fa-times mr-2"></i>Cancel', ['users'], [
                            'class' => 'btn btn-secondary ml-2'
                        ]) ?>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title"><i class="fas fa-user mr-2"></i>User Information</h5>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-4">Name:</dt>
                        <dd class="col-sm-8"><?= Html::encode($model->getDisplayName()) ?></dd>
                        
                        <dt class="col-sm-4">Email:</dt>
                        <dd class="col-sm-8"><?= Html::encode($model->email) ?></dd>
                        
                        <dt class="col-sm-4">Username:</dt>
                        <dd class="col-sm-8"><?= Html::encode($model->username) ?></dd>
                        
                        <dt class="col-sm-4">Role:</dt>
                        <dd class="col-sm-8">
                            <?php if ($model->role === 'admin'): ?>
                                <span class="badge badge-primary">Admin</span>
                            <?php elseif ($model->role === 'demo'): ?>
                                <span class="badge badge-warning">Demo</span>
                            <?php else: ?>
                                <span class="badge badge-secondary">User</span>
                            <?php endif; ?>
                        </dd>
                        
                        <dt class="col-sm-4">Status:</dt>
                        <dd class="col-sm-8">
                            <?= $model->is_active ? 
                                '<span class="badge badge-success">Active</span>' : 
                                '<span class="badge badge-secondary">Inactive</span>' ?>
                        </dd>
                        
                        <dt class="col-sm-4">Login Type:</dt>
                        <dd class="col-sm-8">
                            <?= $model->login_type === 'google' ? 
                                '<span class="badge badge-info">Google</span>' : 
                                '<span class="badge badge-secondary">Local</span>' ?>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
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

.alert-info {
    background-color: #dbeafe;
    border-color: #3b82f6;
    color: #1e40af;
}

.btn-warning {
    background: #f59e0b;
    border: none;
    border-radius: 0.375rem;
    padding: 0.75rem 1.5rem;
    font-weight: 500;
    color: white;
    transition: all 0.2s ease;
}

.btn-warning:hover {
    background: #d97706;
    color: white;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(245, 158, 11, 0.4);
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

.badge {
    font-size: 0.75rem;
    padding: 0.25em 0.5em;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const newPassword = document.getElementById('newPassword');
    const confirmPassword = document.getElementById('confirmPassword');
    
    function validatePasswords() {
        if (newPassword.value !== confirmPassword.value) {
            confirmPassword.setCustomValidity('Passwords do not match');
        } else {
            confirmPassword.setCustomValidity('');
        }
    }
    
    newPassword.addEventListener('input', validatePasswords);
    confirmPassword.addEventListener('input', validatePasswords);
});
</script>