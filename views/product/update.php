<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Product $model */
/** @var app\models\Company $company */

$this->title = Yii::t('app/product', 'Update Product') . ': ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app/product', 'Products'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app/product', 'Update Product');
?>
<div class="product-update">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?= Html::encode($this->title) ?></h1>
        <div>
            <?= Html::a(Yii::t('app/product', 'View') . ' ' . Yii::t('app/product', 'Product'), ['view', 'id' => $model->id], ['class' => 'btn btn-info']) ?>
            <?= Html::a(Yii::t('app/product', 'Back to Products'), ['index'], ['class' => 'btn btn-secondary']) ?>
        </div>
    </div>

    <?= $this->render('_form', [
        'model' => $model,
        'company' => $company,
    ]) ?>

</div>