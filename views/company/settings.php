<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\models\Company $model */

$this->title = Yii::t('app/company', 'Company Settings');
$this->params['breadcrumbs'][] = $this->title;

// Register collapse helper JavaScript
$this->registerJsFile('/js/collapse-helper.js', ['depends' => [\yii\web\JqueryAsset::class]]);
?>

<div class="company-settings">

	<div class="d-flex justify-content-between align-items-center mb-4">
		<h1><?= Html::encode($this->title) ?></h1>
		<?= Html::a(Yii::t('app/company', 'Back to Dashboard'), ['/site/index'], ['class' => 'btn btn-secondary']) ?>
	</div>

	<?php $form = ActiveForm::begin([
        'id' => 'company-settings-form',
        'options' => [
            'class' => 'needs-validation', 
            'novalidate' => true, // Disable browser validation
            'enctype' => 'multipart/form-data'
        ],
        'fieldConfig' => [
            'options' => ['class' => 'form-group'],
            'inputOptions' => ['class' => 'form-control'],
            'labelOptions' => ['class' => 'form-label font-weight-bold'],
        ],
    ]); ?>

	<?= $this->render('_form', [
        'model' => $model,
        'form' => $form,
        'mode' => 'settings'
    ]) ?>


	<?php ActiveForm::end(); ?>

</div>
