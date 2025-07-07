<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Invoice $model */
/** @var app\models\Company $company */
/** @var app\models\Customer[] $customers */

$this->title = 'Update Invoice: ' . $model->invoice_number;
$this->params['breadcrumbs'][] = ['label' => 'Invoices', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->invoice_number, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="invoice-update">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?= Html::encode($this->title) ?></h1>
        <div>
            <?= Html::a('View Invoice', ['view', 'id' => $model->id], ['class' => 'btn btn-info']) ?>
            <?= Html::a('Back to Invoices', ['index'], ['class' => 'btn btn-secondary']) ?>
        </div>
    </div>

    <?= $this->render('_form', [
        'model' => $model,
        'company' => $company,
        'customers' => $customers,
    ]) ?>

</div>