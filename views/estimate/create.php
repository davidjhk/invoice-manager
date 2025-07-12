<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Estimate $model */
/** @var app\models\Company $company */
/** @var app\models\Customer[] $customers */

$this->title = Yii::t('app/estimate', 'Create Estimate');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app/estimate', 'Estimates'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="estimate-create">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?= Html::encode($this->title) ?></h1>
        <?= Html::a(Yii::t('app/estimate', 'Back to Estimates'), ['index'], ['class' => 'btn btn-secondary']) ?>
    </div>

    <?= $this->render('_form', [
        'model' => $model,
        'company' => $company,
        'customers' => $customers,
    ]) ?>

</div>