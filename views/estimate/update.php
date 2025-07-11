<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Estimate $model */
/** @var app\models\Company $company */
/** @var app\models\Customer[] $customers */

$this->title = 'Update Estimate: ' . $model->estimate_number;
$this->params['breadcrumbs'][] = ['label' => 'Estimates', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->estimate_number, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="estimate-update">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?= Html::encode($this->title) ?></h1>
        <div>
            <?= Html::a('View Estimate', ['view', 'id' => $model->id], ['class' => 'btn btn-info']) ?>
            <?= Html::a('Back to Estimates', ['index'], ['class' => 'btn btn-secondary']) ?>
        </div>
    </div>

    <?= $this->render('_form', [
        'model' => $model,
        'company' => $company,
        'customers' => $customers,
    ]) ?>

</div>