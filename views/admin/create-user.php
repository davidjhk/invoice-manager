<?php

/* @var $this yii\web\View */
/* @var $model app\models\User */

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Create User';
$this->params['breadcrumbs'][] = ['label' => 'Admin', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => 'Users', 'url' => ['users']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="admin-create-user">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?= Html::encode($this->title) ?></h1>
        <?= Html::a('<i class="fas fa-arrow-left mr-2"></i>Back to Users', ['users'], ['class' => 'btn btn-secondary']) ?>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title"><i class="fas fa-user-plus mr-2"></i>User Information</h5>
                </div>
                <div class="card-body">
                    <?php $form = ActiveForm::begin([
                        'id' => 'create-user-form',
                        'options' => ['enctype' => 'multipart/form-data'],
                    ]); ?>

                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($model, 'username')->textInput([
                                'placeholder' => 'Enter username',
                                'class' => 'form-control',
                            ]) ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'email')->textInput([
                                'placeholder' => 'Enter email address',
                                'class' => 'form-control',
                                'type' => 'email',
                            ]) ?>
                        </div>
                    </div>

                    <?= $form->field($model, 'full_name')->textInput([
                        'placeholder' => 'Enter full name',
                        'class' => 'form-control',
                    ]) ?>

                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($model, 'password')->passwordInput([
                                'placeholder' => 'Enter password (min 6 characters)',
                                'class' => 'form-control',
                            ]) ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'password_repeat')->passwordInput([
                                'placeholder' => 'Confirm password',
                                'class' => 'form-control',
                            ]) ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($model, 'role')->dropDownList($model::getRoleOptions(), [
                                'prompt' => 'Select role',
                                'class' => 'form-control',
                            ]) ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'max_companies')->textInput([
                                'type' => 'number',
                                'min' => 1,
                                'max' => 100,
                                'class' => 'form-control',
                                'value' => 1,
                            ]) ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <?= Html::activeCheckbox($model, 'is_active', [
                                'class' => 'custom-control-input',
                                'id' => 'is_active',
                                'checked' => true,
                            ]) ?>
                            <label class="custom-control-label" for="is_active">
                                <strong>Active User</strong>
                            </label>
                        </div>
                        <small class="form-text text-muted">Inactive users cannot log in to the system.</small>
                    </div>

                    <div class="form-group">
                        <?= Html::submitButton('<i class="fas fa-save mr-2"></i>Create User', [
                            'class' => 'btn btn-primary',
                            'name' => 'create-user-button'
                        ]) ?>
                        <?= Html::a('<i class="fas fa-times mr-2"></i>Cancel', ['users'], [
                            'class' => 'btn btn-secondary ml-2'
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
                    <h6>Username</h6>
                    <p class="text-muted small">Unique identifier for the user. Used for login.</p>
                    
                    <h6>Email</h6>
                    <p class="text-muted small">User's email address. Must be unique.</p>
                    
                    <h6>Role</h6>
                    <p class="text-muted small">
                        <strong>Admin:</strong> Full system access<br>
                        <strong>User:</strong> Standard user access
                    </p>
                    
                    <h6>Max Companies</h6>
                    <p class="text-muted small">Maximum number of companies this user can create.</p>
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

.custom-control-input:checked ~ .custom-control-label::before {
    background-color: #4f46e5;
    border-color: #4f46e5;
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