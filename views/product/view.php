<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use app\models\Product;

/** @var yii\web\View $this */
/** @var app\models\Product $model */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Products', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="product-view">

	<div class="row">
		<div class="col-md-8">
			<h1><?= Html::encode($this->title) ?></h1>
		</div>
		<div class="col-md-4 text-right">
			<?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
			<?= Html::a('Delete', ['delete', 'id' => $model->id], [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => 'Are you sure you want to delete this product?',
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
					<h6 class="mb-0">Basic Information</h6>
				</div>
				<div class="card-body">
					<?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            'name',
                            [
                                'attribute' => 'type',
                                'value' => $model->getTypeLabel(),
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
                            'category',
                            'sku',
                            'description:ntext',
                        ],
                    ]) ?>
				</div>
			</div>

			<!-- Pricing Information -->
			<div class="card mb-4">
				<div class="card-header">
					<h6 class="mb-0">Pricing & Cost</h6>
				</div>
				<div class="card-body">
					<?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            [
                                'attribute' => 'unit',
                                'value' => $model->getUnitLabel(),
                            ],
                            [
                                'attribute' => 'price',
                                'value' => $model->getFormattedPrice(),
                            ],
                            [
                                'attribute' => 'cost',
                                'value' => $model->getFormattedCost(),
                            ],
                            [
                                'label' => 'Profit Margin',
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
                                'label' => 'Profit Amount',
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
					<h6 class="mb-0">Settings</h6>
				</div>
				<div class="card-body">
					<?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            [
                                'attribute' => 'is_taxable',
                                'format' => 'raw',
                                'value' => $model->is_taxable ? 
                                    '<span class="badge badge-success">Taxable</span>' : 
                                    '<span class="badge badge-secondary">Non-taxable</span>'
                            ],
                            [
                                'attribute' => 'is_active',
                                'format' => 'raw',
                                'value' => $model->is_active ? 
                                    '<span class="badge badge-success">Active</span>' : 
                                    '<span class="badge badge-secondary">Inactive</span>'
                            ],
                        ],
                    ]) ?>
				</div>
			</div>

			<!-- Company Information -->
			<div class="card mb-4">
				<div class="card-header">
					<h6 class="mb-0">Company</h6>
				</div>
				<div class="card-body">
					<?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            [
                                'attribute' => 'company.company_name',
                                'label' => 'Company',
                            ],
                            'created_at',
                            'updated_at',
                        ],
                    ]) ?>
				</div>
			</div>

			<!-- Actions -->
			<div class="card">
				<div class="card-header">
					<h6 class="mb-0">Actions</h6>
				</div>
				<div class="card-body">
					<?= Html::a('Toggle Status', ['toggle-status', 'id' => $model->id], [
                        'class' => 'btn btn-outline-' . ($model->is_active ? 'warning' : 'success') . ' btn-block',
                        'data-method' => 'post',
                        'data-confirm' => 'Are you sure you want to ' . ($model->is_active ? 'deactivate' : 'activate') . ' this product?'
                    ]) ?>
					<?= Html::a('Edit Product', ['update', 'id' => $model->id], [
                        'class' => 'btn btn-primary btn-block'
                    ]) ?>
				</div>
			</div>

		</div>
	</div>

</div>