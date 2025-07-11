<?php

/* @var $this yii\web\View */
/* @var $model app\models\User */

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Update User: ' . $model->getDisplayName();
$this->params['breadcrumbs'][] = ['label' => 'Admin', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => 'Users', 'url' => ['users']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="admin-update-user">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?= Html::encode($this->title) ?></h1>
        <?= Html::a('<i class="fas fa-arrow-left mr-2"></i>Back to Users', ['users'], ['class' => 'btn btn-secondary']) ?>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title"><i class="fas fa-user-edit mr-2"></i>User Information</h5>
                </div>
                <div class="card-body">
                    <?php $form = ActiveForm::begin([
                        'id' => 'update-user-form',
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
                                'placeholder' => 'Leave blank to keep current password',
                                'class' => 'form-control',
                            ])->hint('Leave blank to keep current password') ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'role')->dropDownList($model::getRoleOptions(), [
                                'class' => 'form-control',
                            ]) ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($model, 'max_companies')->textInput([
                                'type' => 'number',
                                'min' => 1,
                                'max' => 100,
                                'class' => 'form-control',
                            ]) ?>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Login Type</label>
                                <input type="text" class="form-control" value="<?= ucfirst($model->login_type) ?>" readonly>
                                <small class="form-text text-muted">Login type cannot be changed.</small>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <?= Html::activeCheckbox($model, 'is_active', [
                                'class' => 'custom-control-input',
                                'id' => 'is_active',
                            ]) ?>
                            <label class="custom-control-label" for="is_active">
                                <strong>Active User</strong>
                            </label>
                        </div>
                        <small class="form-text text-muted">Inactive users cannot log in to the system.</small>
                    </div>

                    <div class="form-group">
                        <?= Html::submitButton('<i class="fas fa-save mr-2"></i>Update User', [
                            'class' => 'btn btn-primary',
                            'name' => 'update-user-button'
                        ]) ?>
                        <?= Html::a('<i class="fas fa-key mr-2"></i>Reset Password', ['reset-user-password', 'id' => $model->id], [
                            'class' => 'btn btn-warning ml-2'
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
                    <h5 class="card-title"><i class="fas fa-info-circle mr-2"></i>User Details</h5>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-5">ID:</dt>
                        <dd class="col-sm-7"><?= $model->id ?></dd>
                        
                        <dt class="col-sm-5">Created:</dt>
                        <dd class="col-sm-7"><?= Yii::$app->formatter->asRelativeTime($model->created_at) ?></dd>
                        
                        <dt class="col-sm-5">Updated:</dt>
                        <dd class="col-sm-7"><?= Yii::$app->formatter->asRelativeTime($model->updated_at) ?></dd>
                        
                        <dt class="col-sm-5">Companies:</dt>
                        <dd class="col-sm-7"><?= $model->getCompanyCount() ?> / <?= $model->max_companies ?></dd>
                        
                        <dt class="col-sm-5">Login Type:</dt>
                        <dd class="col-sm-7">
                            <?= $model->login_type === 'google' ? 
                                '<span class="badge badge-info">Google</span>' : 
                                '<span class="badge badge-secondary">Local</span>' ?>
                        </dd>
                        
                        <dt class="col-sm-5">Status:</dt>
                        <dd class="col-sm-7">
                            <?= $model->is_active ? 
                                '<span class="badge badge-success">Active</span>' : 
                                '<span class="badge badge-secondary">Inactive</span>' ?>
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

.btn-warning {
    background: #f59e0b;
    border: none;
    border-radius: 0.375rem;
    padding: 0.75rem 1.5rem;
    font-weight: 500;
    color: white;
}

.btn-warning:hover {
    background: #d97706;
    color: white;
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