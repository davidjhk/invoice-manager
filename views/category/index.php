<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Product Categories';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="category-index">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?= Html::encode($this->title) ?></h1>
        <div class="action-buttons">
            <?= Html::a('Create Category', ['create'], ['class' => 'btn btn-success']) ?>
        </div>
    </div>

    <?php Pjax::begin(); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            [
                'attribute' => 'sort_order',
                'label' => 'Order',
                'headerOptions' => ['style' => 'width: 80px'],
                'contentOptions' => ['class' => 'text-center'],
            ],
            [
                'attribute' => 'name',
                'format' => 'text',
                'label' => 'Category Name',
            ],
            [
                'attribute' => 'description',
                'format' => 'text',
                'value' => function($model) {
                    return $model->description ?: '-';
                },
            ],
            [
                'label' => 'Products Count',
                'value' => function($model) {
                    return $model->getProductsCount();
                },
                'headerOptions' => ['style' => 'width: 120px'],
                'contentOptions' => ['class' => 'text-center'],
            ],
            [
                'attribute' => 'is_active',
                'format' => 'boolean',
                'headerOptions' => ['style' => 'width: 80px'],
                'contentOptions' => ['class' => 'text-center'],
            ],
            [
                'attribute' => 'created_at',
                'format' => ['date', 'php:Y-m-d H:i'],
                'headerOptions' => ['style' => 'width: 140px'],
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'headerOptions' => ['style' => 'width: 120px'],
                'contentOptions' => ['class' => 'text-center'],
                'template' => '{view} {update} {delete}',
                'buttons' => [
                    'view' => function ($url, $model, $key) {
                        return Html::a('<i class="fas fa-eye"></i>', $url, [
                            'class' => 'btn btn-sm btn-outline-info',
                            'title' => 'View',
                        ]);
                    },
                    'update' => function ($url, $model, $key) {
                        return Html::a('<i class="fas fa-edit"></i>', $url, [
                            'class' => 'btn btn-sm btn-outline-primary',
                            'title' => 'Update',
                        ]);
                    },
                    'delete' => function ($url, $model, $key) {
                        $disabled = !$model->canDelete();
                        return Html::a('<i class="fas fa-trash"></i>', $url, [
                            'class' => 'btn btn-sm btn-outline-danger' . ($disabled ? ' disabled' : ''),
                            'title' => $disabled ? 'Cannot delete - category is in use' : 'Delete',
                            'data' => $disabled ? [] : [
                                'confirm' => 'Are you sure you want to delete this category?',
                                'method' => 'post',
                            ],
                        ]);
                    },
                ],
            ],
        ],
        'tableOptions' => ['class' => 'table table-striped table-bordered'],
        'options' => ['class' => 'grid-view table-responsive'],
    ]); ?>

    <?php Pjax::end(); ?>

</div>

<div class="alert alert-info mt-4">
    <i class="fas fa-info-circle mr-2"></i>
    <strong>Tip:</strong> Categories help organize your products and services. You can reorder categories by changing their sort order.
    Categories that are assigned to products cannot be deleted.
</div>