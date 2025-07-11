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

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?= Html::encode($this->title) ?></h1>
        <?= Html::a('Back to Products', ['index'], ['class' => 'btn btn-secondary']) ?>
    </div>

    <?= $this->render('_form', [
        'model' => $model,
        'company' => $company,
    ]) ?>

</div>