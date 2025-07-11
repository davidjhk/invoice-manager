<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\grid\GridView;
use yii\data\ActiveDataProvider;

/** @var yii\web\View $this */
/** @var app\models\ProductCategory $model */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Categories', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

// Get products in this category
$productsDataProvider = new ActiveDataProvider([
    'query' => $model->getProducts()->where(['is_active' => true]),
    'pagination' => [
        'pageSize' => 10,
    ],
]);
?>
<div class="category-view">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?= Html::encode($this->title) ?></h1>
        <div class="action-buttons">
            <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
            <?= Html::a('Delete', ['delete', 'id' => $model->id], [
                'class' => 'btn btn-danger' . ($model->canDelete() ? '' : ' disabled'),
                'title' => $model->canDelete() ? 'Delete' : 'Cannot delete - category is in use',
                'data' => $model->canDelete() ? [
                    'confirm' => 'Are you sure you want to delete this category?',
                    'method' => 'post',
                ] : [],
            ]) ?>
            <?= Html::a('Back to Categories', ['index'], ['class' => 'btn btn-secondary']) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle mr-2"></i>Category Details
                    </h5>
                </div>
                <div class="card-body">
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            'name',
                            'description:ntext',
                            [
                                'attribute' => 'is_active',
                                'format' => 'boolean',
                            ],
                            'sort_order',
                            [
                                'label' => 'Products Count',
                                'value' => $model->getProductsCount(),
                            ],
                            'created_at:datetime',
                            'updated_at:datetime',
                        ],
                        'options' => ['class' => 'table table-striped table-bordered detail-view'],
                    ]) ?>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-bar mr-2"></i>Category Statistics
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="border rounded p-3">
                                <h4 class="text-primary"><?= $model->getProductsCount() ?></h4>
                                <small class="text-muted">Total Products</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border rounded p-3">
                                <h4 class="text-success"><?= $model->getProducts()->where(['is_active' => true])->count() ?></h4>
                                <small class="text-muted">Active Products</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border rounded p-3">
                                <h4 class="text-warning"><?= $model->sort_order ?></h4>
                                <small class="text-muted">Sort Order</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if ($model->getProductsCount() > 0): ?>
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-box mr-2"></i>Products in this Category
            </h5>
        </div>
        <div class="card-body">
            <?= GridView::widget([
                'dataProvider' => $productsDataProvider,
                'columns' => [
                    'name',
                    [
                        'attribute' => 'type',
                        'value' => function($model) {
                            return $model->getTypeLabel();
                        },
                    ],
                    [
                        'attribute' => 'price',
                        'format' => 'currency',
                    ],
                    [
                        'attribute' => 'is_active',
                        'format' => 'boolean',
                        'headerOptions' => ['style' => 'width: 80px'],
                        'contentOptions' => ['class' => 'text-center'],
                    ],
                    [
                        'class' => 'yii\grid\ActionColumn',
                        'controller' => 'product',
                        'headerOptions' => ['style' => 'width: 120px'],
                        'contentOptions' => ['class' => 'text-center'],
                        'template' => '{view} {update}',
                        'buttons' => [
                            'view' => function ($url, $model, $key) {
                                return Html::a('<i class="fas fa-eye"></i>', $url, [
                                    'class' => 'btn btn-sm btn-outline-info',
                                    'title' => 'View Product',
                                ]);
                            },
                            'update' => function ($url, $model, $key) {
                                return Html::a('<i class="fas fa-edit"></i>', $url, [
                                    'class' => 'btn btn-sm btn-outline-primary',
                                    'title' => 'Update Product',
                                ]);
                            },
                        ],
                    ],
                ],
                'tableOptions' => ['class' => 'table table-striped table-bordered'],
                'options' => ['class' => 'grid-view table-responsive'],
            ]); ?>
        </div>
    </div>
    <?php endif; ?>

</div>