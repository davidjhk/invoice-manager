<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Company $model */

$this->title = Yii::t('app/company', 'Create New Company');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app/company', 'Select Company'), 'url' => ['select']];
$this->params['breadcrumbs'][] = $this->title;

// Determine dark mode setting
$currentCompany = null;
if (!Yii::$app->user->isGuest) {
	$companyId = Yii::$app->session->get('current_company_id');
	if ($companyId) {
		$currentCompany = \app\models\Company::findForCurrentUser()->where(['id' => $companyId])->one();
	}
}
$isDarkMode = $currentCompany && $currentCompany->dark_mode;
$isCompactMode = $currentCompany && $currentCompany->compact_mode;
?>

<div class="company-create">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?= Html::encode($this->title) ?></h1>
        <?= Html::a('<i class="fas fa-arrow-left mr-1"></i>' . Yii::t('app/company', $isCompactMode ? '' : 'Back to Company List'), ['select'], [
            'class' => 'btn btn-secondary',
            'encode' => false,
            'title' => $isCompactMode ? Yii::t('app/company', 'Back to Company List') : '',
            'data-toggle' => $isCompactMode ? 'tooltip' : ''
        ]) ?>
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
        'mode' => 'create',
        'isCompactMode' => $isCompactMode
    ]) ?>

    <div class="form-group">
        <?= Html::submitButton('<i class="fas fa-plus mr-1"></i>' . Yii::t('app/company', $isCompactMode ? '' : 'Create Company'), [
            'class' => 'btn btn-success btn-lg',
            'encode' => false,
            'title' => $isCompactMode ? Yii::t('app/company', 'Create Company') : '',
            'data-toggle' => $isCompactMode ? 'tooltip' : ''
        ]) ?>
        <?= Html::a('<i class="fas fa-times mr-1"></i>' . Yii::t('app', $isCompactMode ? '' : 'Cancel'), ['select'], [
            'class' => 'btn btn-secondary',
            'encode' => false,
            'title' => $isCompactMode ? Yii::t('app', 'Cancel') : '',
            'data-toggle' => $isCompactMode ? 'tooltip' : ''
        ]) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?php
$this->registerJs("
    $('[data-toggle=\"tooltip\"]').tooltip();
");
?>
