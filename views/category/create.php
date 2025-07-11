<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\ProductCategory $model */

$this->title = 'Create Category';
$this->params['breadcrumbs'][] = ['label' => 'Categories', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="category-create">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?= Html::encode($this->title) ?></h1>
        <?= Html::a('<i class="fas fa-arrow-left mr-1"></i>Back to Categories', ['index'], [
            'class' => 'btn btn-outline-secondary',
            'encode' => false
        ]) ?>
    </div>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>