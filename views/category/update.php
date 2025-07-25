<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\ProductCategory $model */

$this->title = 'Update Category: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Categories', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="category-update">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?= Html::encode($this->title) ?></h1>
        <div class="action-buttons">
            <?= Html::a('<i class="fas fa-eye mr-1"></i>View Category', ['view', 'id' => $model->id], [
                'class' => 'btn btn-info',
                'encode' => false
            ]) ?>
            <?= Html::a('<i class="fas fa-arrow-left mr-1"></i>Back to Categories', ['index'], [
                'class' => 'btn btn-outline-secondary',
                'encode' => false
            ]) ?>
        </div>
    </div>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>