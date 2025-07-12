<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Customer $model */
/** @var app\models\Company $company */

$this->title = Yii::t('app/customer', 'Create Customer');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app/customer', 'Customers'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="customer-create">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?= Html::encode($this->title) ?></h1>
        <?= Html::a(Yii::t('app/customer', 'Back to Customers'), ['index'], ['class' => 'btn btn-secondary']) ?>
    </div>

    <?= $this->render('_form', [
        'model' => $model,
        'company' => $company,
    ]) ?>

</div>