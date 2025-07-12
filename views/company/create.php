<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Company $model */

$this->title = Yii::t('app/company', 'Create New Company');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app/company', 'Select Company'), 'url' => ['select']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="company-create">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?= Html::encode($this->title) ?></h1>
        <?= Html::a(Yii::t('app/company', 'Back to Company List'), ['select'], ['class' => 'btn btn-secondary']) ?>
    </div>

    <?php if ($model->hasErrors()): ?>
        <div class="alert alert-danger">
            <h5><?= Yii::t('app', 'Please fix the following errors') ?>:</h5>
            <?= Html::errorSummary($model) ?>
        </div>
    <?php endif; ?>

    <?php $form = ActiveForm::begin([
        'id' => 'company-create-form',
        'options' => ['class' => 'needs-validation', 'novalidate' => true, 'enctype' => 'multipart/form-data'],
        'fieldConfig' => [
            'options' => ['class' => 'form-group'],
            'inputOptions' => ['class' => 'form-control'],
            'labelOptions' => ['class' => 'form-label font-weight-bold'],
        ],
    ]); ?>

    <?= $this->render('_form', [
        'model' => $model,
        'form' => $form,
        'mode' => 'create'
    ]) ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app/company', 'Create Company'), ['class' => 'btn btn-success btn-lg']) ?>
        <?= Html::a(Yii::t('app', 'Cancel'), ['select'], ['class' => 'btn btn-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

