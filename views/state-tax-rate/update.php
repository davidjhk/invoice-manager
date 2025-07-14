<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\StateTaxRate $model */
/** @var app\models\State[] $states */
/** @var app\models\Country[] $countries */

$this->title = Yii::t('app', 'Update State Tax Rate: {name}', [
    'name' => $model->getDisplayName(),
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Admin'), 'url' => ['/admin/index']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'State Tax Rates'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->getDisplayName(), 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="state-tax-rate-update">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?= Html::encode($this->title) ?></h1>
        <div>
            <?= Html::a('<i class="fas fa-eye mr-1"></i>' . Yii::t('app', 'View'), ['view', 'id' => $model->id], ['class' => 'btn btn-info mr-2']) ?>
            <?= Html::a('<i class="fas fa-list mr-1"></i>' . Yii::t('app', 'Back to List'), ['index'], ['class' => 'btn btn-secondary']) ?>
        </div>
    </div>

    <?= $this->render('_form', [
        'model' => $model,
        'states' => $states,
        'countries' => $countries,
    ]) ?>

</div>