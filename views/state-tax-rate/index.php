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

// Register tax management CSS
$this->registerCssFile('@web/css/tax-management.css');
?>
<div class="state-tax-rate-index">

	<div class="d-flex justify-content-between align-items-center mb-4">
		<h1><?= Html::encode($this->title) ?></h1>
		<div class="action-buttons tax-action-buttons">
			<?= Html::a('<i class="fas fa-plus mr-2"></i>' . Yii::t('app', 'Add'), ['create'], ['class' => 'btn btn-success']) ?>
			<?= Html::a('<i class="fas fa-upload mr-2"></i>' . Yii::t('app', 'Import'), ['bulk-import'], ['class' => 'btn btn-primary']) ?>
			<?= Html::a('<i class="fas fa-download mr-2"></i>' . Yii::t('app', 'Export'), ['/tax-jurisdiction/export-csv'], ['class' => 'btn btn-outline-secondary']) ?>
		</div>
	</div>

	<!-- Tax Management Navigation -->
	<div class="tax-card tax-management-navigation mb-4">
		<div class="card-header tax-card-header">
			<h6 class="mb-0"><i class="fas fa-sitemap mr-2"></i><?= Yii::t('app', 'Tax Management Tools') ?></h6>
		</div>
		<div class="card-body tax-card-body">
			<div class="row">
				<div class="col-md-3">
					<?= Html::a('
                        <div class="nav-item-card">
                            <i class="fas fa-map-marker-alt text-primary"></i>
                            <strong>' . Yii::t('app', 'ZIP Code Tax Rates') . '</strong>
                            <small class="text-muted">' . Yii::t('app', 'Detailed jurisdiction-based rates') . '</small>
                        </div>
                    ', ['/tax-jurisdiction/index'], ['class' => 'text-decoration-none']) ?>
				</div>
				<div class="col-md-3">
					<div class="nav-item-card active">
						<i class="fas fa-percent text-success"></i>
						<strong><?= Yii::t('app', 'State Tax Rates') ?></strong>
						<small class="text-muted"><?= Yii::t('app', 'State-level rates for calculations') ?></small>
					</div>
				</div>
				<div class="col-md-3">
					<?= Html::a('
                        <div class="nav-item-card">
                            <i class="fas fa-chart-line text-info"></i>
                            <strong>' . Yii::t('app', 'Tax Statistics') . '</strong>
                            <small class="text-muted">' . Yii::t('app', 'Analysis and reports') . '</small>
                        </div>
                    ', ['/tax-jurisdiction/stats'], ['class' => 'text-decoration-none']) ?>
				</div>
				<div class="col-md-3">
					<?= Html::a('
                        <div class="nav-item-card">
                            <i class="fas fa-file-import text-warning"></i>
                            <strong>' . Yii::t('app', 'Import Data') . '</strong>
                            <small class="text-muted">' . Yii::t('app', 'Bulk import tax rates') . '</small>
                        </div>
                    ', ['/tax-jurisdiction/import-csv'], ['class' => 'text-decoration-none']) ?>
				</div>
			</div>
		</div>
	</div>

	<div class="tax-card">
		<div class="card-body tax-card-body">
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
                        'headerOptions' => [ 'style' => 'width: 150px;'],
                        'contentOptions' => [ 'class' => 'text-center btn-group','style' => 'width: 150px;'],
                        'buttons' => [
                            'view' => function ($url, $model, $key) {
                                return Html::a('<i class="fas fa-eye"></i>', $url, [
                                    'title' => Yii::t('app', 'View'),
                                    'class' => 'btn btn-outline-info btn-sm mr-1',
                                    'data-pjax' => '0',
                                    'data-toggle' => 'tooltip',
                                ]);
                            },
                            'update' => function ($url, $model, $key) {
                                return Html::a('<i class="fas fa-edit"></i>', $url, [
                                    'title' => Yii::t('app', 'Update'),
                                    'class' => 'btn btn-outline-primary btn-sm mr-1',
                                    'data-pjax' => '0',
                                    'data-toggle' => 'tooltip',
                                ]);
                            },
                            'toggle' => function ($url, $model, $key) {
                                $icon = $model->is_active ? 'fas fa-toggle-on text-success' : 'fas fa-toggle-off text-danger';
                                $title = $model->is_active ? Yii::t('app', 'Deactivate') : Yii::t('app', 'Activate');
                                return Html::a("<i class=\"{$icon}\"></i>", ['toggle-active', 'id' => $model->id], [
                                    'title' => $title,
                                    'class' => 'btn btn-outline-secondary btn-sm mr-1',
                                    'data-confirm' => Yii::t('app', 'Are you sure you want to toggle the status of this tax rate?'),
                                    'data-method' => 'post',
                                    'data-toggle' => 'tooltip',
                                ]);
                            },
                            'delete' => function ($url, $model, $key) {
                                return Html::a('<i class="fas fa-trash"></i>', $url, [
                                    'title' => Yii::t('app', 'Delete'),
                                    'class' => 'btn btn-outline-danger btn-sm',
                                    'data-confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
                                    'data-method' => 'post',
                                    'data-toggle' => 'tooltip',
                                ]);
                            },
                        ],
                    ],
                ],
            ]); ?>

			<?php Pjax::end(); ?>
		</div>
	</div>

	<div class="mt-4">
		<div class="row">
			<div class="col-md-6">
				<div class="tax-card tax-info-card">
					<div class="card-header tax-card-header">
						<h6 class="mb-0"><i class="fas fa-info-circle mr-2"></i><?= Yii::t('app', 'Information') ?></h6>
					</div>
					<div class="card-body tax-card-body">
						<ul class="list-unstyled mb-0">
							<li><i
									class="fas fa-circle text-success mr-2"></i><?= Yii::t('app', 'Base Rate: State-level sales tax rate') ?>
							</li>
							<li><i
									class="fas fa-circle text-info mr-2"></i><?= Yii::t('app', 'Avg Total: Includes local taxes') ?>
							</li>
							<li><i
									class="fas fa-circle text-warning mr-2"></i><?= Yii::t('app', 'Revenue Threshold: Economic nexus limit') ?>
							</li>
						</ul>
					</div>
				</div>
			</div>
			<div class="col-md-6">
				<div class="tax-card tax-info-card">
					<div class="card-header tax-card-header">
						<h6 class="mb-0"><i
								class="fas fa-exclamation-triangle mr-2"></i><?= Yii::t('app', 'Important Notes') ?>
						</h6>
					</div>
					<div class="card-body tax-card-body">
						<ul class="list-unstyled mb-0">
							<li><i
									class="fas fa-circle text-danger mr-2"></i><?= Yii::t('app', 'Tax rates change frequently - keep updated') ?>
							</li>
							<li><i
									class="fas fa-circle text-primary mr-2"></i><?= Yii::t('app', 'Consult tax professionals for compliance') ?>
							</li>
							<li><i
									class="fas fa-circle text-secondary mr-2"></i><?= Yii::t('app', 'Effective dates determine which rate applies') ?>
							</li>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>

</div>

<?php
$this->registerJs("
    $('[data-toggle=\"tooltip\"]').tooltip();
");

?>