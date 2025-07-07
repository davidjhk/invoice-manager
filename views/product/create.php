<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Product $model */
/** @var app\models\Company $company */

$this->title = 'Create Product/Service';
$this->params['breadcrumbs'][] = ['label' => 'Products', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="product-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'company' => $company,
    ]) ?>

</div>