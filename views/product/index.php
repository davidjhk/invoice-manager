<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\models\Product;

/** @var yii\web\View $this */
/** @var app\models\Product[] $products */
/** @var app\models\Company $company */
/** @var string $searchTerm */
/** @var string $typeFilter */
/** @var string $categoryFilter */

$this->title = 'Products & Services';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="product-index">

    <div class="row">
        <div class="col-md-6">
            <h1><?= Html::encode($this->title) ?></h1>
        </div>
        <div class="col-md-6 text-right">
            <?= Html::a('Create Product/Service', ['create'], ['class' => 'btn btn-success']) ?>
            <?= Html::a('Export CSV', ['export'], ['class' => 'btn btn-outline-secondary']) ?>
        </div>
    </div>

    <!-- Search and Filter Section -->
    <div class="card mb-4">
        <div class="card-body">
            <?php echo Html::beginForm(['index'], 'get', ['class' => 'form-inline']); ?>
                <div class="form-group mr-3">
                    <?= Html::textInput('search', $searchTerm, [
                        'class' => 'form-control',
                        'placeholder' => 'Search products...',
                        'style' => 'width: 250px;'
                    ]) ?>
                </div>
                <div class="form-group mr-3">
                    <?= Html::dropDownList('type', $typeFilter, [
                        '' => 'All Types'
                    ] + Product::getTypeOptions(), ['class' => 'form-control']) ?>
                </div>
                <div class="form-group mr-3">
                    <?= Html::dropDownList('category', $categoryFilter, [
                        '' => 'All Categories'
                    ] + Product::getCategoryOptions(), ['class' => 'form-control']) ?>
                </div>
                <div class="form-group">
                    <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
                    <?= Html::a('Clear', ['index'], ['class' => 'btn btn-outline-secondary ml-2']) ?>
                </div>
            <?php echo Html::endForm(); ?>
        </div>
    </div>

    <?php if (empty($products)): ?>
        <div class="alert alert-info">
            <h4>No products found</h4>
            <p>Start by creating your first product or service.</p>
            <?= Html::a('Create Product/Service', ['create'], ['class' => 'btn btn-success']) ?>
        </div>
    <?php else: ?>
        
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Category</th>
                                <th>SKU</th>
                                <th>Unit</th>
                                <th>Price</th>
                                <th>Cost</th>
                                <th>Margin</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                            <tr>
                                <td>
                                    <strong><?= Html::encode($product->name) ?></strong>
                                    <?php if ($product->description): ?>
                                        <br><small class="text-muted"><?= Html::encode($product->description) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $typeClass = 'secondary';
                                    if ($product->type === Product::TYPE_SERVICE) {
                                        $typeClass = 'info';
                                    } elseif ($product->type === Product::TYPE_PRODUCT) {
                                        $typeClass = 'primary';
                                    } elseif ($product->type === Product::TYPE_NON_INVENTORY) {
                                        $typeClass = 'warning';
                                    }
                                    ?>
                                    <?= Html::tag('span', $product->getTypeLabel(), [
                                        'class' => 'badge badge-' . $typeClass
                                    ]) ?>
                                </td>
                                <td><?= Html::encode($product->category) ?></td>
                                <td><?= Html::encode($product->sku) ?></td>
                                <td><?= Html::encode($product->getUnitLabel()) ?></td>
                                <td><?= $product->getFormattedPrice() ?></td>
                                <td><?= $product->getFormattedCost() ?></td>
                                <td>
                                    <?php 
                                    $margin = $product->getProfitMargin();
                                    $marginClass = $margin > 50 ? 'success' : ($margin > 20 ? 'warning' : 'danger');
                                    ?>
                                    <span class="text-<?= $marginClass ?>">
                                        <?= number_format($margin, 1) ?>%
                                    </span>
                                </td>
                                <td>
                                    <?php if ($product->is_active): ?>
                                        <span class="badge badge-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <?= Html::a('<i class="fas fa-eye"></i>', ['view', 'id' => $product->id], [
                                            'class' => 'btn btn-sm btn-outline-primary',
                                            'title' => 'View'
                                        ]) ?>
                                        <?= Html::a('<i class="fas fa-edit"></i>', ['update', 'id' => $product->id], [
                                            'class' => 'btn btn-sm btn-outline-secondary',
                                            'title' => 'Edit'
                                        ]) ?>
                                        <?= Html::a('<i class="fas fa-' . ($product->is_active ? 'pause' : 'play') . '"></i>', 
                                            ['toggle-status', 'id' => $product->id], [
                                            'class' => 'btn btn-sm btn-outline-' . ($product->is_active ? 'warning' : 'success'),
                                            'title' => $product->is_active ? 'Deactivate' : 'Activate',
                                            'data-method' => 'post',
                                            'data-confirm' => 'Are you sure you want to ' . ($product->is_active ? 'deactivate' : 'activate') . ' this product?'
                                        ]) ?>
                                        <?= Html::a('<i class="fas fa-trash"></i>', ['delete', 'id' => $product->id], [
                                            'class' => 'btn btn-sm btn-outline-danger',
                                            'title' => 'Delete',
                                            'data-method' => 'post',
                                            'data-confirm' => 'Are you sure you want to delete this product?'
                                        ]) ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-3">
                    <p class="text-muted">
                        Showing <?= count($products) ?> products
                        <?php if (!empty($searchTerm) || !empty($typeFilter) || !empty($categoryFilter)): ?>
                            (filtered)
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        </div>
    <?php endif; ?>

</div>