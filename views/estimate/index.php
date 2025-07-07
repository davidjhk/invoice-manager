<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var app\models\Company $company */

$this->title = 'Estimates';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="estimate-index">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?= Html::encode($this->title) ?></h1>
        <div>
            <?= Html::a('<i class="fas fa-download mr-1"></i>Export', ['export'], [
                'class' => 'btn btn-outline-info mr-2',
                'target' => '_blank'
            ]) ?>
            <?= Html::a('<i class="fas fa-plus mr-1"></i>New Estimate', ['create'], [
                'class' => 'btn btn-success'
            ]) ?>
        </div>
    </div>

    <?php Pjax::begin(); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'tableOptions' => ['class' => 'table table-striped table-hover'],
        'summary' => 'Showing {begin}-{end} of {totalCount} estimates',
        'columns' => [
            [
                'attribute' => 'estimate_number',
                'format' => 'raw',
                'value' => function($model) {
                    return Html::a(Html::encode($model->estimate_number), ['view', 'id' => $model->id], [
                        'class' => 'font-weight-bold text-decoration-none'
                    ]);
                },
            ],
            [
                'attribute' => 'customer_id',
                'label' => 'Customer',
                'format' => 'raw',
                'value' => function($model) {
                    $html = Html::encode($model->customer->customer_name);
                    if ($model->customer->customer_email) {
                        $html .= '<br><small class="text-muted">' . Html::encode($model->customer->customer_email) . '</small>';
                    }
                    return $html;
                },
            ],
            [
                'attribute' => 'estimate_date',
                'format' => ['date', 'php:M j, Y'],
                'headerOptions' => ['style' => 'width: 120px'],
            ],
            [
                'attribute' => 'expiry_date',
                'format' => 'raw',
                'value' => function($model) {
                    if (!$model->expiry_date) {
                        return '<span class="text-muted">-</span>';
                    }
                    
                    $isExpired = $model->isExpired();
                    $class = $isExpired ? 'text-danger font-weight-bold' : '';
                    
                    return Html::tag('span', Yii::$app->formatter->asDate($model->expiry_date, 'php:M j, Y'), [
                        'class' => $class,
                        'title' => $isExpired ? 'Expired' : ''
                    ]);
                },
                'headerOptions' => ['style' => 'width: 120px'],
            ],
            [
                'attribute' => 'total_amount',
                'format' => 'raw',
                'value' => function($model) {
                    return '<span class="font-weight-bold">' . $model->formatAmount($model->total_amount) . '</span>';
                },
                'headerOptions' => ['style' => 'width: 120px', 'class' => 'text-right'],
                'contentOptions' => ['class' => 'text-right'],
            ],
            [
                'attribute' => 'status',
                'format' => 'raw',
                'value' => function($model) {
                    $badge = Html::tag('span', $model->getStatusLabel(), [
                        'class' => 'badge badge-' . $model->getStatusClass()
                    ]);
                    
                    if ($model->converted_to_invoice) {
                        $badge .= '<br><small class="text-success">Converted to Invoice</small>';
                    }
                    
                    return $badge;
                },
                'headerOptions' => ['style' => 'width: 120px'],
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'header' => 'Actions',
                'template' => '{view} {print} {email} {update} {duplicate} {convert} {delete}',
                'buttons' => [
                    'view' => function ($url, $model, $key) {
                        return Html::a('<i class="fas fa-eye"></i>', ['view', 'id' => $model->id], [
                            'class' => 'btn btn-sm btn-outline-primary',
                            'title' => 'View',
                            'data-pjax' => '0'
                        ]);
                    },
                    'print' => function ($url, $model, $key) {
                        return Html::a('<i class="fas fa-print"></i>', ['print', 'id' => $model->id], [
                            'class' => 'btn btn-sm btn-outline-secondary',
                            'title' => 'Print',
                            'target' => '_blank',
                            'data-pjax' => '0'
                        ]);
                    },
                    'email' => function ($url, $model, $key) {
                        if ($model->customer->customer_email) {
                            return Html::a('<i class="fas fa-envelope"></i>', ['send-email', 'id' => $model->id], [
                                'class' => 'btn btn-sm btn-outline-info',
                                'title' => 'Send Email',
                                'data-pjax' => '0'
                            ]);
                        }
                        return '';
                    },
                    'update' => function ($url, $model, $key) {
                        return Html::a('<i class="fas fa-edit"></i>', ['update', 'id' => $model->id], [
                            'class' => 'btn btn-sm btn-outline-secondary',
                            'title' => 'Edit',
                            'data-pjax' => '0'
                        ]);
                    },
                    'duplicate' => function ($url, $model, $key) {
                        return Html::a('<i class="fas fa-copy"></i>', ['duplicate', 'id' => $model->id], [
                            'class' => 'btn btn-sm btn-outline-info',
                            'title' => 'Duplicate',
                            'data-pjax' => '0'
                        ]);
                    },
                    'convert' => function ($url, $model, $key) {
                        if ($model->canConvertToInvoice()) {
                            return Html::a('<i class="fas fa-exchange-alt"></i>', ['convert-to-invoice', 'id' => $model->id], [
                                'class' => 'btn btn-sm btn-outline-success',
                                'title' => 'Convert to Invoice',
                                'data-method' => 'post',
                                'data-confirm' => 'Are you sure you want to convert this estimate to an invoice?',
                                'data-pjax' => '0'
                            ]);
                        }
                        return '';
                    },
                    'delete' => function ($url, $model, $key) {
                        if (!$model->converted_to_invoice) {
                            return Html::a('<i class="fas fa-trash"></i>', ['delete', 'id' => $model->id], [
                                'class' => 'btn btn-sm btn-outline-danger',
                                'title' => 'Delete',
                                'data-method' => 'post',
                                'data-confirm' => 'Are you sure you want to delete this estimate?',
                                'data-pjax' => '0'
                            ]);
                        }
                        return '';
                    },
                ],
                'headerOptions' => ['style' => 'width: 250px'],
                'contentOptions' => ['class' => 'text-center'],
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>

<?php
$this->registerCss("
    .table th {
        border-top: none;
        font-weight: 600;
        background-color: #f8f9fa;
    }
    
    .btn-group-sm > .btn, .btn-sm {
        margin: 1px;
    }
    
    .grid-view .summary {
        margin-bottom: 15px;
        color: #6c757d;
    }
");
?>