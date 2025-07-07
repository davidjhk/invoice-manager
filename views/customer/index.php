<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\models\Customer[] $customers */
/** @var string $searchTerm */
/** @var app\models\Company $company */

$this->title = 'Customers';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="customer-index">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?= Html::encode($this->title) ?></h1>
        <?= Html::a('<i class="fas fa-user-plus mr-1"></i>Add Customer', ['create'], ['class' => 'btn btn-success', 'encode' => false]) ?>
    </div>

    <div class="row mb-3">
        <div class="col-md-6">
            <?= Html::beginForm(['index'], 'get', ['class' => 'form-inline']) ?>
                <div class="input-group">
                    <?= Html::input('text', 'search', $searchTerm, [
                        'class' => 'form-control',
                        'placeholder' => 'Search customers...',
                        'id' => 'searchInput'
                    ]) ?>
                    <div class="input-group-append">
                        <?= Html::submitButton('<i class="fas fa-search"></i> Search', ['class' => 'btn btn-outline-secondary', 'encode' => false]) ?>
                    </div>
                </div>
            <?= Html::endForm() ?>
        </div>
        <div class="col-md-6 text-right">
            <?= Html::a('<i class="fas fa-download mr-1"></i>Export CSV', ['export'], [
                'class' => 'btn btn-outline-info',
                'target' => '_blank',
                'encode' => false
            ]) ?>
        </div>
    </div>

    <?php if (empty($customers)): ?>
        <div class="alert alert-info text-center">
            <h4>No Customers Found</h4>
            <p>You haven't added any customers yet.</p>
            <?= Html::a('<i class="fas fa-user-plus mr-1"></i>Add Your First Customer', ['create'], ['class' => 'btn btn-primary', 'encode' => false]) ?>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="thead-dark">
                    <tr>
                        <th>Name</th>
                        <th>Contact</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Total Invoices</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($customers as $customer): ?>
                        <tr>
                            <td>
                                <?= Html::a(Html::encode($customer->customer_name), ['view', 'id' => $customer->id], [
                                    'class' => 'font-weight-bold text-decoration-none'
                                ]) ?>
                                <?php if ($customer->customer_address): ?>
                                    <br><small class="text-muted"><?= Html::encode($customer->customer_address) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($customer->contact_name): ?>
                                    <?= Html::encode($customer->contact_name) ?>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($customer->customer_email): ?>
                                    <?= Html::mailto(Html::encode($customer->customer_email)) ?>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($customer->customer_phone): ?>
                                    <?= Html::encode($customer->customer_phone) ?>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge badge-info"><?= $customer->getInvoicesCount() ?></span>
                            </td>
                            <td>
                                <span class="font-weight-bold">
                                    <?= $company->formatAmount($customer->getTotalAmount()) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($customer->is_active): ?>
                                    <span class="badge badge-success">Active</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <?= Html::a('<i class="fas fa-eye"></i>', ['view', 'id' => $customer->id], [
                                        'class' => 'btn btn-outline-primary',
                                        'title' => 'View',
                                        'data-toggle' => 'tooltip',
                                        'encode' => false
                                    ]) ?>
                                    
                                    <?= Html::a('<i class="fas fa-edit"></i>', ['update', 'id' => $customer->id], [
                                        'class' => 'btn btn-outline-secondary',
                                        'title' => 'Edit',
                                        'data-toggle' => 'tooltip',
                                        'encode' => false
                                    ]) ?>
                                    
                                    <?= Html::a('<i class="fas fa-file-invoice"></i>', ['/invoice/create', 'customer_id' => $customer->id], [
                                        'class' => 'btn btn-outline-success',
                                        'title' => 'Create Invoice',
                                        'data-toggle' => 'tooltip',
                                        'encode' => false
                                    ]) ?>
                                    
                                    <?php if ($customer->is_active): ?>
                                        <?= Html::a('<i class="fas fa-ban"></i>', ['toggle-status', 'id' => $customer->id], [
                                            'class' => 'btn btn-outline-warning',
                                            'title' => 'Deactivate',
                                            'data-toggle' => 'tooltip',
                                            'data-method' => 'post',
                                            'data-confirm' => 'Are you sure you want to deactivate this customer?',
                                            'encode' => false
                                        ]) ?>
                                    <?php else: ?>
                                        <?= Html::a('<i class="fas fa-check"></i>', ['toggle-status', 'id' => $customer->id], [
                                            'class' => 'btn btn-outline-success',
                                            'title' => 'Activate',
                                            'data-toggle' => 'tooltip',
                                            'data-method' => 'post',
                                            'encode' => false
                                        ]) ?>
                                    <?php endif; ?>
                                    
                                    <?php if ($customer->getInvoicesCount() == 0): ?>
                                        <?= Html::a('<i class="fas fa-trash"></i>', ['delete', 'id' => $customer->id], [
                                            'class' => 'btn btn-outline-danger',
                                            'title' => 'Delete',
                                            'data-toggle' => 'tooltip',
                                            'data-confirm' => 'Are you sure you want to delete this customer?',
                                            'data-method' => 'post',
                                            'encode' => false
                                        ]) ?>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
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
");

$this->registerJs("
    $('[data-toggle=\"tooltip\"]').tooltip();
");
?>