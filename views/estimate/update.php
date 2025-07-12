<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Estimate $model */
/** @var app\models\Company $company */
/** @var app\models\Customer[] $customers */

$this->title = Yii::t('app/estimate', 'Update Estimate') . ': ' . $model->estimate_number;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app/estimate', 'Estimates'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->estimate_number, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app/estimate', 'Update Estimate');
?>
<div class="estimate-update">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?= Html::encode($this->title) ?></h1>
        <div>
            <?= Html::a(Yii::t('app/estimate', 'View'), ['view', 'id' => $model->id], ['class' => 'btn btn-info']) ?>
            <?= Html::a(Yii::t('app/estimate', 'Back to Estimates'), ['index'], ['class' => 'btn btn-secondary']) ?>
        </div>
    </div>

    <?= $this->render('_form', [
        'model' => $model,
        'company' => $company,
        'customers' => $customers,
    ]) ?>

</div>