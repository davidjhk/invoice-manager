<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/**
 * 사용자 생성/수정 공통 폼 필드
 * @var yii\web\View $this
 * @var app\models\User $model
 * @var yii\widgets\ActiveForm $form
 * @var bool $isUpdate 업데이트 모드 여부
 */

$isUpdate = $isUpdate ?? false;
?>

<div class="row">
    <div class="col-md-6">
        <?= $form->field($model, 'username')->textInput(['maxlength' => true, 'readonly' => $isUpdate]) ?>
    </div>
    <div class="col-md-6">
        <?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <?= $form->field($model, 'password')->passwordInput(['maxlength' => true, 'placeholder' => $isUpdate ? Yii::t('app', 'Leave blank to keep current password') : '']) ?>
    </div>
    <div class="col-md-6">
        <?= $form->field($model, 'first_name')->textInput(['maxlength' => true]) ?>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <?= $form->field($model, 'last_name')->textInput(['maxlength' => true]) ?>
    </div>
    <div class="col-md-6">
        <?= $form->field($model, 'role')->dropDownList([
            'admin' => Yii::t('app', 'Administrator'),
            'user' => Yii::t('app', 'User'),
        ], ['prompt' => Yii::t('app', 'Select Role')]) ?>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <?= $form->field($model, 'is_active', [
            'template' => '<div class="form-group"><div class="custom-control custom-switch">{input}<label class="custom-control-label" for="is_active"><strong>' . Yii::t('app', 'Active User') . '</strong></label></div><small class="form-text text-muted">' . Yii::t('app', 'Inactive users cannot log in to the system.') . '</small>{error}</div>',
        ])->checkbox(['class' => 'custom-control-input', 'id' => 'is_active'], false) ?>
    </div>
</div>