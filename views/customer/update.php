<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Customer $model */
/** @var app\models\Company $company */

$this->title = Yii::t('app/customer', 'Update Customer') . ': ' . $model->customer_name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app/customer', 'Customers'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->customer_name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app/customer', 'Update Customer');
?>
<div class="customer-update">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?= Html::encode($this->title) ?></h1>
        <div>
            <?= Html::a(Yii::t('app/customer', 'View'), ['view', 'id' => $model->id], ['class' => 'btn btn-info']) ?>
            <?= Html::a(Yii::t('app/customer', 'Back to Customers'), ['index'], ['class' => 'btn btn-secondary']) ?>
        </div>
    </div>

    <?= $this->render('_form', [
        'model' => $model,
        'company' => $company,
    ]) ?>

</div>