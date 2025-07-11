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
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?= Html::encode($this->title) ?></h1>
        <div class="action-buttons">
            <?= Html::a('<i class="fas fa-download mr-1"></i>Export CSV', ['export'], [
                'class' => 'btn btn-outline-info',
                'target' => '_blank',
                'encode' => false
            ]) ?>
            <?= Html::a('<i class="fas fa-plus mr-1"></i>Add Product/Service', ['create'], [
                'class' => 'btn btn-success',
                'encode' => false
            ]) ?>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-6">
            <?= Html::beginForm(['index'], 'get', ['class' => 'form-inline']) ?>
                <div class="input-group">
                    <?= Html::input('text', 'search', $searchTerm, [
                        'class' => 'form-control',
                        'placeholder' => 'Search products...',
                        'id' => 'searchInput'
                    ]) ?>
                    <div class="input-group-append">
                        <?= Html::submitButton('<i class="fas fa-search"></i> Search', ['class' => 'btn btn-outline-secondary', 'encode' => false]) ?>
                    </div>
                </div>
            <?= Html::endForm() ?>
        </div>
        <div class="col-md-6 text-right">
            <div class="btn-group" role="group">
                <?= Html::a('All', ['index'], [
                    'class' => 'btn btn-sm ' . (empty($typeFilter) ? 'btn-primary' : 'btn-outline-primary')
                ]) ?>
                <?= Html::a('Products', ['index', 'type' => Product::TYPE_PRODUCT], [
                    'class' => 'btn btn-sm ' . ($typeFilter === Product::TYPE_PRODUCT ? 'btn-primary' : 'btn-outline-primary')
                ]) ?>
                <?= Html::a('Services', ['index', 'type' => Product::TYPE_SERVICE], [
                    'class' => 'btn btn-sm ' . ($typeFilter === Product::TYPE_SERVICE ? 'btn-info' : 'btn-outline-info')
                ]) ?>
                <?= Html::a('Non-Inventory', ['index', 'type' => Product::TYPE_NON_INVENTORY], [
                    'class' => 'btn btn-sm ' . ($typeFilter === Product::TYPE_NON_INVENTORY ? 'btn-warning' : 'btn-outline-warning')
                ]) ?>
            </div>
        </div>
    </div>

    <?php if (empty($products)): ?>
        <div class="alert alert-info text-center">
            <h4>No Products Found</h4>
            <p>You haven't added any products or services yet.</p>
            <?= Html::a('<i class="fas fa-plus mr-1"></i>Add Your First Product/Service', ['create'], ['class' => 'btn btn-primary', 'encode' => false]) ?>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="thead-dark">
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
                                <?= Html::a(Html::encode($product->name), ['view', 'id' => $product->id], [
                                    'class' => 'font-weight-bold text-decoration-none'
                                ]) ?>
                                <?php if ($product->description): ?>
                                    <?php
                                    $truncatedDescription = mb_strlen($product->description) > 80 
                                        ? mb_substr($product->description, 0, 80) . '...'
                                        : $product->description;
                                    ?>
                                    <br><small class="text-muted" title="<?= Html::encode($product->description) ?>">
                                        <?= Html::encode($truncatedDescription) ?>
                                    </small>
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
                                <span class="badge badge-<?= $typeClass ?>">
                                    <?= Html::encode($product->getTypeLabel()) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($product->getCategoryLabel()): ?>
                                    <?= Html::encode($product->getCategoryLabel()) ?>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($product->sku): ?>
                                    <?= Html::encode($product->sku) ?>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td><?= Html::encode($product->getUnitLabel()) ?></td>
                            <td>
                                <span class="font-weight-bold">
                                    <?= $product->getFormattedPrice() ?>
                                </span>
                            </td>
                            <td><?= $product->getFormattedCost() ?></td>
                            <td>
                                <?php 
                                $margin = $product->getProfitMargin();
                                $marginClass = $margin > 50 ? 'success' : ($margin > 20 ? 'warning' : 'danger');
                                ?>
                                <span class="text-<?= $marginClass ?> font-weight-bold">
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
                                <div class="btn-group btn-group-sm" role="group">
                                    <?= Html::a('<i class="fas fa-eye"></i>', ['view', 'id' => $product->id], [
                                        'class' => 'btn btn-outline-primary',
                                        'title' => 'View',
                                        'data-toggle' => 'tooltip',
                                        'encode' => false
                                    ]) ?>
                                    
                                    <?= Html::a('<i class="fas fa-edit"></i>', ['update', 'id' => $product->id], [
                                        'class' => 'btn btn-outline-secondary',
                                        'title' => 'Edit',
                                        'data-toggle' => 'tooltip',
                                        'encode' => false
                                    ]) ?>
                                    
                                    <?= Html::a('<i class="fas fa-' . ($product->is_active ? 'pause' : 'play') . '"></i>', 
                                        ['toggle-status', 'id' => $product->id], [
                                        'class' => 'btn btn-outline-' . ($product->is_active ? 'warning' : 'success'),
                                        'title' => $product->is_active ? 'Deactivate' : 'Activate',
                                        'data-toggle' => 'tooltip',
                                        'data-method' => 'post',
                                        'data-confirm' => 'Are you sure you want to ' . ($product->is_active ? 'deactivate' : 'activate') . ' this product?',
                                        'encode' => false
                                    ]) ?>
                                    
                                    <?= Html::a('<i class="fas fa-trash"></i>', ['delete', 'id' => $product->id], [
                                        'class' => 'btn btn-outline-danger',
                                        'title' => 'Delete',
                                        'data-toggle' => 'tooltip',
                                        'data-method' => 'post',
                                        'data-confirm' => 'Are you sure you want to delete this product?',
                                        'encode' => false
                                    ]) ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="row mt-3">
            <div class="col-md-6">
                <div class="dataTables_info">
                    Showing <?= count($products) ?> products
                    <?php if (!empty($searchTerm) || !empty($typeFilter) || !empty($categoryFilter)): ?>
                        (filtered)
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-md-6">
                <!-- Pagination can be added here if needed -->
            </div>
        </div>
    <?php endif; ?>

</div>

<?php
$this->registerCss("
    .table-responsive {
        box-shadow: 0 0 20px rgba(0,0,0,0.1);
        border-radius: 8px;
        overflow: hidden;
    }
    
    .table th {
        border-top: none;
        font-weight: 600;
    }
    
    .btn-group-sm > .btn {
        margin-right: 2px;
    }
    
    .btn-group-sm > .btn:last-child {
        margin-right: 0;
    }
    
    /* Limit Name column width */
    .table td:first-child {
        max-width: 300px;
        width: 300px;
    }
    
    .table th:first-child {
        max-width: 300px;
        width: 300px;
    }
    
    /* Ensure text wraps properly */
    .table td:first-child small {
        display: block;
        word-wrap: break-word;
        line-height: 1.2;
        margin-top: 2px;
    }
");

$this->registerJs("
    $('[data-toggle=\"tooltip\"]').tooltip();
");
?>