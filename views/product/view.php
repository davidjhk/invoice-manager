<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use app\models\Product;

/** @var yii\web\View $this */
/** @var app\models\Product $model */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app/product', 'Products'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="product-view">

	<div class="row">
		<div class="col-md-8">
			<h1><?= Html::encode($this->title) ?></h1>
		</div>
		<div class="col-md-4 text-right">
			<?= Html::a(Yii::t('app/product', 'Edit'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
			<?= Html::a(Yii::t('app/product', 'Delete'), ['delete', 'id' => $model->id], [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => Yii::t('app/product', 'Are you sure you want to delete this product?'),
                    'method' => 'post',
                ],
            ]) ?>
		</div>
	</div>

	<div class="row mt-4">
		<!-- Left Column -->
		<div class="col-lg-8">

			<!-- Basic Information -->
			<div class="card mb-4">
				<div class="card-header">
					<h6 class="mb-0"><?= Yii::t('app/product', 'Product Information') ?></h6>
				</div>
				<div class="card-body">
					<?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            [
                                'attribute' => 'name',
                                'label' => Yii::t('app/product', 'Name'),
                            ],
                            [
                                'attribute' => 'type',
                                'label' => Yii::t('app/product', 'Type'),
                                'format' => 'raw',
                                'value' => function($model) {
                                    $typeClass = 'secondary';
                                    if ($model->type === Product::TYPE_SERVICE) {
                                        $typeClass = 'info';
                                    } elseif ($model->type === Product::TYPE_PRODUCT) {
                                        $typeClass = 'primary';
                                    } elseif ($model->type === Product::TYPE_NON_INVENTORY) {
                                        $typeClass = 'warning';
                                    }
                                    return Html::tag('span', $model->getTypeLabel(), [
                                        'class' => 'badge badge-' . $typeClass
                                    ]);
                                }
                            ],
                            [
                                'attribute' => 'category',
                                'label' => Yii::t('app/product', 'Category'),
                            ],
                            [
                                'attribute' => 'sku',
                                'label' => Yii::t('app/product', 'SKU'),
                            ],
                            [
                                'attribute' => 'description',
                                'label' => Yii::t('app/product', 'Description'),
                                'format' => 'ntext',
                            ],
                        ],
                    ]) ?>
				</div>
			</div>

			<!-- Pricing Information -->
			<div class="card mb-4">
				<div class="card-header">
					<h6 class="mb-0"><?= Yii::t('app/product', 'Pricing Information') ?></h6>
				</div>
				<div class="card-body">
					<?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            [
                                'attribute' => 'unit',
                                'label' => Yii::t('app/product', 'Unit'),
                                'value' => $model->getUnitLabel(),
                            ],
                            [
                                'attribute' => 'price',
                                'label' => Yii::t('app/product', 'Price'),
                                'value' => $model->getFormattedPrice(),
                            ],
                            [
                                'attribute' => 'cost',
                                'label' => Yii::t('app/product', 'Cost'),
                                'value' => $model->getFormattedCost(),
                            ],
                            [
                                'label' => Yii::t('app', 'Profit Margin'),
                                'value' => number_format($model->getProfitMargin(), 2) . '%',
                                'format' => 'raw',
                                'value' => function($model) {
                                    $margin = $model->getProfitMargin();
                                    $class = $margin > 50 ? 'success' : ($margin > 20 ? 'warning' : 'danger');
                                    return Html::tag('span', number_format($margin, 2) . '%', [
                                        'class' => 'badge badge-' . $class
                                    ]);
                                }
                            ],
                            [
                                'label' => Yii::t('app', 'Profit Amount'),
                                'value' => '$' . number_format($model->price - $model->cost, 2),
                            ],
                        ],
                    ]) ?>
				</div>
			</div>

		</div>

		<!-- Right Column -->
		<div class="col-lg-4">

			<!-- Settings -->
			<div class="card mb-4">
				<div class="card-header">
					<h6 class="mb-0"><?= Yii::t('app/product', 'Product Settings') ?></h6>
				</div>
				<div class="card-body">
					<?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            [
                                'attribute' => 'is_taxable',
                                'format' => 'raw',
                                'label' => Yii::t('app/product', 'Is Taxable'),
                                'value' => $model->is_taxable ? 
                                    '<span class="badge badge-success">' . Yii::t('app', 'Taxable') . '</span>' : 
                                    '<span class="badge badge-secondary">' . Yii::t('app', 'Non-taxable') . '</span>'
                            ],
                            [
                                'attribute' => 'is_active',
                                'format' => 'raw',
                                'label' => Yii::t('app/product', 'Status'),
                                'value' => $model->is_active ? 
                                    '<span class="badge badge-success">' . Yii::t('app/product', 'Active') . '</span>' : 
                                    '<span class="badge badge-secondary">' . Yii::t('app/product', 'Inactive') . '</span>'
                            ],
                        ],
                    ]) ?>
				</div>
			</div>

			<!-- Company Information -->
			<div class="card mb-4">
				<div class="card-header">
					<h6 class="mb-0"><?= Yii::t('app', 'Company') ?></h6>
				</div>
				<div class="card-body">
					<?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            [
                                'attribute' => 'company.company_name',
                                'label' => Yii::t('app', 'Company'),
                            ],
                            [
                                'attribute' => 'created_at',
                                'label' => Yii::t('app', 'Created At'),
                            ],
                            [
                                'attribute' => 'updated_at', 
                                'label' => Yii::t('app', 'Updated At'),
                            ],
                        ],
                    ]) ?>
				</div>
			</div>

			<!-- Actions -->
			<div class="card">
				<div class="card-header">
					<h6 class="mb-0"><?= Yii::t('app', 'Actions') ?></h6>
				</div>
				<div class="card-body">
					<?= Html::a(Yii::t('app', 'Toggle Status'), ['toggle-status', 'id' => $model->id], [
                        'class' => 'btn btn-outline-' . ($model->is_active ? 'warning' : 'success') . ' btn-block',
                        'data-method' => 'post',
                        'data-confirm' => Yii::t('app', 'Are you sure you want to {action} this product?', ['action' => $model->is_active ? Yii::t('app', 'deactivate') : Yii::t('app', 'activate')])
                    ]) ?>
					<?= Html::a(Yii::t('app/product', 'Edit') . ' ' . Yii::t('app/product', 'Product'), ['update', 'id' => $model->id], [
                        'class' => 'btn btn-primary btn-block'
                    ]) ?>
				</div>
			</div>

		</div>
	</div>

</div>