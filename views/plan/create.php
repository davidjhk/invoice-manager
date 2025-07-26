<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\Plan;

/* @var $this yii\web\View */
/* @var $model app\models\Plan */

$this->title = 'Create Plan';
$this->params['breadcrumbs'][] = ['label' => 'Admin', 'url' => ['/admin/index']];
$this->params['breadcrumbs'][] = ['label' => 'Plans', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="plan-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="plan-form card">
        <div class="card-body">
            <?php $form = ActiveForm::begin(); ?>

            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'price')->textInput(['type' => 'number', 'step' => '0.01']) ?>
                </div>
            </div>

            <?= $form->field($model, 'description')->textarea(['rows' => 3]) ?>

            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'stripe_plan_id')->textInput(['maxlength' => true]) ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'paypal_plan_id')->textInput(['maxlength' => true]) ?>
                </div>
            </div>

            <?= $form->field($model, 'features')->textarea([
                'rows' => 6,
                'placeholder' => 'Enter features as JSON, e.g.:
{
  "invoices": "Unlimited",
  "customers": "Unlimited",
  "products": "Unlimited",
  "users": 1,
  "support": "Email support",
  "storage": "1GB"
}'
            ]) ?>

            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'is_active')->checkbox() ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'sort_order')->textInput(['type' => 'number']) ?>
                </div>
            </div>

            <div class="form-group">
                <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
                <?= Html::a('Cancel', ['index'], ['class' => 'btn btn-secondary']) ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>

</div>
