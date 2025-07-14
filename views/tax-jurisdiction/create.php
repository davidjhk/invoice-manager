<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\TaxJurisdiction */

$this->title = Yii::t('app', 'Create Tax Jurisdiction');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Admin Panel'), 'url' => ['/admin/index']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Tax Management'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="tax-jurisdiction-create">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?= Html::encode($this->title) ?></h1>
        <div class="action-buttons">
            <?= Html::a('<i class="fas fa-list mr-2"></i>' . Yii::t('app', 'Back to List'), ['index'], ['class' => 'btn btn-outline-secondary']) ?>
        </div>
    </div>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>