<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\widgets\Pjax;
use app\models\State;
use app\models\Country;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = Yii::t('app', 'State Tax Rates');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Admin'), 'url' => ['/admin/index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="state-tax-rate-index">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?= Html::encode($this->title) ?></h1>
        <div>
            <?= Html::a(Yii::t('app', 'Bulk Import'), ['bulk-import'], [
                'class' => 'btn btn-info mr-2',
                'title' => Yii::t('app', 'Import default tax rates')
            ]) ?>
            <?= Html::a(Yii::t('app', 'Create State Tax Rate'), ['create'], ['class' => 'btn btn-success']) ?>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <?php Pjax::begin(); ?>

            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'options' => ['class' => 'table-responsive'],
                'tableOptions' => ['class' => 'table table-striped table-hover'],
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],

                    [
                        'attribute' => 'state_code',
                        'label' => Yii::t('app', 'State'),
                        'format' => 'raw',
                        'value' => function ($model) {
                            $stateName = $model->state ? $model->state->state_name : $model->state_code;
                            return Html::tag('span', $stateName . ' (' . $model->state_code . ')', [
                                'class' => 'badge badge-secondary'
                            ]);
                        },
                        'contentOptions' => ['style' => 'width: 150px;'],
                    ],
                    [
                        'attribute' => 'country_code',
                        'label' => Yii::t('app', 'Country'),
                        'format' => 'raw',
                        'value' => function ($model) {
                            return Html::tag('span', $model->country_code, [
                                'class' => 'badge badge-primary'
                            ]);
                        },
                        'contentOptions' => ['style' => 'width: 80px;'],
                    ],
                    [
                        'attribute' => 'base_rate',
                        'label' => Yii::t('app', 'Base Rate'),
                        'format' => 'raw',
                        'value' => function ($model) {
                            $color = $model->base_rate > 0 ? 'success' : 'secondary';
                            return Html::tag('span', number_format($model->base_rate, 2) . '%', [
                                'class' => "badge badge-{$color}"
                            ]);
                        },
                        'contentOptions' => ['class' => 'text-right', 'style' => 'width: 100px;'],
                    ],
                    [
                        'attribute' => 'average_total_rate',
                        'label' => Yii::t('app', 'Avg Total'),
                        'format' => 'raw',
                        'value' => function ($model) {
                            if (!$model->has_local_tax) {
                                return Html::tag('span', 'N/A', ['class' => 'text-muted']);
                            }
                            return Html::tag('span', number_format($model->average_total_rate, 2) . '%', [
                                'class' => 'badge badge-info'
                            ]);
                        },
                        'contentOptions' => ['class' => 'text-right', 'style' => 'width: 100px;'],
                    ],
                    [
                        'attribute' => 'has_local_tax',
                        'label' => Yii::t('app', 'Local Tax'),
                        'format' => 'raw',
                        'value' => function ($model) {
                            $icon = $model->has_local_tax ? 'fas fa-check text-success' : 'fas fa-times text-danger';
                            return Html::tag('i', '', ['class' => $icon]);
                        },
                        'contentOptions' => ['class' => 'text-center', 'style' => 'width: 80px;'],
                    ],
                    [
                        'attribute' => 'revenue_threshold',
                        'label' => Yii::t('app', 'Revenue Threshold'),
                        'format' => 'raw',
                        'value' => function ($model) {
                            if (!$model->revenue_threshold) {
                                return Html::tag('span', 'None', ['class' => 'text-muted']);
                            }
                            return Html::tag('span', '$' . number_format($model->revenue_threshold), [
                                'class' => 'text-primary'
                            ]);
                        },
                        'contentOptions' => ['class' => 'text-right', 'style' => 'width: 120px;'],
                    ],
                    [
                        'attribute' => 'effective_date',
                        'label' => Yii::t('app', 'Effective Date'),
                        'format' => 'date',
                        'contentOptions' => ['style' => 'width: 120px;'],
                    ],
                    [
                        'attribute' => 'is_active',
                        'label' => Yii::t('app', 'Status'),
                        'format' => 'raw',
                        'value' => function ($model) {
                            $class = $model->is_active ? 'badge-success' : 'badge-danger';
                            $text = $model->is_active ? Yii::t('app', 'Active') : Yii::t('app', 'Inactive');
                            return Html::tag('span', $text, ['class' => "badge {$class}"]);
                        },
                        'contentOptions' => ['class' => 'text-center', 'style' => 'width: 80px;'],
                    ],

                    [
                        'class' => 'yii\grid\ActionColumn',
                        'header' => Yii::t('app', 'Actions'),
                        'template' => '{view} {update} {toggle} {delete}',
                        'buttons' => [
                            'view' => function ($url, $model, $key) {
                                return Html::a('<i class="fas fa-eye"></i>', $url, [
                                    'title' => Yii::t('app', 'View'),
                                    'class' => 'btn btn-sm btn-outline-info mr-1',
                                    'data-pjax' => '0',
                                ]);
                            },
                            'update' => function ($url, $model, $key) {
                                return Html::a('<i class="fas fa-edit"></i>', $url, [
                                    'title' => Yii::t('app', 'Update'),
                                    'class' => 'btn btn-sm btn-outline-primary mr-1',
                                    'data-pjax' => '0',
                                ]);
                            },
                            'toggle' => function ($url, $model, $key) {
                                $icon = $model->is_active ? 'fas fa-toggle-on text-success' : 'fas fa-toggle-off text-danger';
                                $title = $model->is_active ? Yii::t('app', 'Deactivate') : Yii::t('app', 'Activate');
                                return Html::a("<i class=\"{$icon}\"></i>", ['toggle-active', 'id' => $model->id], [
                                    'title' => $title,
                                    'class' => 'btn btn-sm btn-outline-secondary mr-1',
                                    'data-confirm' => Yii::t('app', 'Are you sure you want to toggle the status of this tax rate?'),
                                    'data-method' => 'post',
                                ]);
                            },
                            'delete' => function ($url, $model, $key) {
                                return Html::a('<i class="fas fa-trash"></i>', $url, [
                                    'title' => Yii::t('app', 'Delete'),
                                    'class' => 'btn btn-sm btn-outline-danger',
                                    'data-confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
                                    'data-method' => 'post',
                                ]);
                            },
                        ],
                        'contentOptions' => ['style' => 'width: 180px;'],
                    ],
                ],
            ]); ?>

            <?php Pjax::end(); ?>
        </div>
    </div>

    <div class="mt-4">
        <div class="row">
            <div class="col-md-6">
                <div class="card border-info">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0"><i class="fas fa-info-circle mr-2"></i><?= Yii::t('app', 'Information') ?></h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li><i class="fas fa-circle text-success mr-2"></i><?= Yii::t('app', 'Base Rate: State-level sales tax rate') ?></li>
                            <li><i class="fas fa-circle text-info mr-2"></i><?= Yii::t('app', 'Avg Total: Includes local taxes') ?></li>
                            <li><i class="fas fa-circle text-warning mr-2"></i><?= Yii::t('app', 'Revenue Threshold: Economic nexus limit') ?></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-warning">
                    <div class="card-header bg-warning text-dark">
                        <h6 class="mb-0"><i class="fas fa-exclamation-triangle mr-2"></i><?= Yii::t('app', 'Important Notes') ?></h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li><i class="fas fa-circle text-danger mr-2"></i><?= Yii::t('app', 'Tax rates change frequently - keep updated') ?></li>
                            <li><i class="fas fa-circle text-primary mr-2"></i><?= Yii::t('app', 'Consult tax professionals for compliance') ?></li>
                            <li><i class="fas fa-circle text-secondary mr-2"></i><?= Yii::t('app', 'Effective dates determine which rate applies') ?></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>