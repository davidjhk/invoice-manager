<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Estimate $model */
/** @var app\models\Company $company */
/** @var app\models\Customer[] $customers */

$this->title = 'Create Estimate';
$this->params['breadcrumbs'][] = ['label' => 'Estimates', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="estimate-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'company' => $company,
        'customers' => $customers,
    ]) ?>

</div>