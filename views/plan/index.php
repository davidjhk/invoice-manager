<?php

use yii\helpers\Html;
use yii\grid\GridView;
use app\models\Plan;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Subscription Plans';
$this->params['breadcrumbs'][] = ['label' => 'Admin', 'url' => ['/admin/index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="plan-index">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?= Html::encode($this->title) ?></h1>
        <div>
            <?= Html::a('Create Plan', ['create'], ['class' => 'btn btn-success']) ?>
        </div>
    </div>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'name',
            [
                'attribute' => 'description',
                'format' => 'ntext',
                'value' => function ($model) {
                    return \yii\helpers\StringHelper::truncate($model->description, 50);
                }
            ],
            [
                'attribute' => 'price',
                'value' => function ($model) {
                    return '$' . number_format($model->price, 2);
                }
            ],
            [
                'attribute' => 'is_active',
                'format' => 'raw',
                'value' => function ($model) {
                    if ($model->is_active) {
                        return '<span class="badge badge-success">Active</span>';
                    } else {
                        return '<span class="badge badge-secondary">Inactive</span>';
                    }
                },
            ],
            'sort_order',
            [
                'attribute' => 'created_at',
                'format' => 'datetime',
            ],

            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view} {update} {toggle} {delete}',
                'buttons' => [
                    'toggle' => function ($url, $model, $key) {
                        $icon = $model->is_active ? 'ban' : 'check';
                        $label = $model->is_active ? 'Deactivate' : 'Activate';
                        return Html::a(
                            '<i class="fas fa-' . $icon . '"></i>',
                            ['toggle-status', 'id' => $model->id],
                            [
                                'class' => 'btn btn-sm ' . ($model->is_active ? 'btn-warning' : 'btn-success'),
                                'title' => $label,
                                'data' => [
                                    'confirm' => 'Are you sure you want to ' . strtolower($label) . ' this plan?',
                                    'method' => 'post',
                                ],
                            ]
                        );
                    },
                ],
            ],
        ],
    ]); ?>

</div>
