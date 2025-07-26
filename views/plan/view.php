<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use app\models\Plan;

/* @var $this yii\web\View */
/* @var $model app\models\Plan */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Admin', 'url' => ['/admin/index']];
$this->params['breadcrumbs'][] = ['label' => 'Plans', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="plan-view">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?= Html::encode($this->title) ?></h1>
        <div>
            <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
            <?= Html::a('Delete', ['delete', 'id' => $model->id], [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => 'Are you sure you want to delete this plan?',
                    'method' => 'post',
                ],
            ]) ?>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <?= DetailView::widget([
                'model' => $model,
                'attributes' => [
                    'id',
                    'name',
                    'description:ntext',
                    [
                        'attribute' => 'price',
                        'value' => '$' . number_format($model->price, 2),
                    ],
                    'stripe_plan_id',
                    'paypal_plan_id',
                    [
                        'attribute' => 'features',
                        'format' => 'raw',
                        'value' => function ($model) {
                            if (is_array($model->features)) {
                                $html = '<ul class="list-unstyled mb-0">';
                                foreach ($model->features as $key => $value) {
                                    $html .= '<li><strong>' . Html::encode(ucfirst(str_replace('_', ' ', $key))) . ':</strong> ' . Html::encode($value) . '</li>';
                                }
                                $html .= '</ul>';
                                return $html;
                            }
                            return json_encode($model->features, JSON_PRETTY_PRINT);
                        },
                    ],
                    [
                        'attribute' => 'is_active',
                        'value' => $model->is_active ? 'Yes' : 'No',
                    ],
                    'sort_order',
                    'created_at:datetime',
                    'updated_at:datetime',
                ],
            ]) ?>
        </div>
    </div>

    <div class="mt-3">
        <?= Html::a('Back to Plans', ['index'], ['class' => 'btn btn-secondary']) ?>
    </div>

</div>
