<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Invoice $model */
/** @var app\models\Company $company */
/** @var app\models\Customer[] $customers */

$this->title = Yii::t('app/invoice', 'Update Invoice') . ': ' . $model->invoice_number;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app/invoice', 'Invoices'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->invoice_number, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app/invoice', 'Update Invoice');
?>
<div class="invoice-update">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?= Html::encode($this->title) ?></h1>
        <div>
            <?= Html::a(Yii::t('app/invoice', 'View'), ['view', 'id' => $model->id], ['class' => 'btn btn-info']) ?>
            <?= Html::a(Yii::t('app/invoice', 'Back to Invoices'), ['index'], ['class' => 'btn btn-secondary']) ?>
        </div>
    </div>

    <?= $this->render('_form', [
        'model' => $model,
        'company' => $company,
        'customers' => $customers,
    ]) ?>

</div>