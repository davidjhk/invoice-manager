<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Product $model */
/** @var app\models\Company $company */

$this->title = 'Update Product: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Products', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="product-update">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?= Html::encode($this->title) ?></h1>
        <div>
            <?= Html::a('View Product', ['view', 'id' => $model->id], ['class' => 'btn btn-info']) ?>
            <?= Html::a('Back to Products', ['index'], ['class' => 'btn btn-secondary']) ?>
        </div>
    </div>

    <?= $this->render('_form', [
        'model' => $model,
        'company' => $company,
    ]) ?>

</div>