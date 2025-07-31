<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\models\Customer[] $customers */
/** @var string $searchTerm */
/** @var app\models\Company $company */

$this->title = Yii::t('app/customer', 'Customers');
$this->params['breadcrumbs'][] = $this->title;

// Determine dark mode setting
$currentCompany = null;
if (!Yii::$app->user->isGuest) {
	$companyId = Yii::$app->session->get('current_company_id');
	if ($companyId) {
		$currentCompany = \app\models\Company::findForCurrentUser()->where(['c.id' => $companyId])->one();
	}
}
$isDarkMode = $currentCompany && $currentCompany->dark_mode;
$isCompactMode = $currentCompany && $currentCompany->compact_mode;
?>

<div class="customer-index">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
		<div class="header-info">
			<h1 class="title"><?= Html::encode($this->title) ?></h1>
			<p class="subtitle"><?= Html::encode($company->company_name) ?></p>
		</div>
        <div class="action-buttons">
            <?= Html::a('<i class="fas fa-download mr-1"></i>' . Yii::t('app', $isCompactMode ? '' : 'Export') . ' CSV', ['export'], [
                'class' => 'btn btn-outline-info',
                'target' => '_blank',
                'encode' => false,
                'title' => $isCompactMode ? Yii::t('app', 'Export') . ' CSV' : '',
                'data-toggle' => $isCompactMode ? 'tooltip' : ''
            ]) ?>
            <?php if (Yii::$app->user->identity && Yii::$app->user->identity->canUseImport()): ?>
                <?= Html::a('<i class="fas fa-upload mr-1"></i>' . Yii::t('app', $isCompactMode ? '' : 'Import') . ' CSV', ['customer-import/index'], [
                    'class' => 'btn btn-outline-primary',
                    'encode' => false,
                    'title' => $isCompactMode ? Yii::t('app', 'Import') . ' CSV' : '',
                    'data-toggle' => $isCompactMode ? 'tooltip' : ''
                ]) ?>
            <?php endif; ?>
            <?= Html::a('<i class="fas fa-user-plus mr-1"></i>' . Yii::t('app/customer', $isCompactMode ? '' : 'Create New Customer'), ['create'], [
                'class' => 'btn btn-success',
                'encode' => false,
                'title' => $isCompactMode ? Yii::t('app/customer', 'Create New Customer') : '',
                'data-toggle' => $isCompactMode ? 'tooltip' : ''
            ]) ?>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-6">
            <?= Html::beginForm(['index'], 'get', ['class' => 'form-inline']) ?>
                <div class="input-group">
                    <?= Html::input('text', 'search', $searchTerm, [
                        'class' => 'form-control',
                        'placeholder' => Yii::t('app/customer', 'Search customers...'),
                        'id' => 'searchInput'
                    ]) ?>
                    <div class="input-group-append">
                        <?= Html::submitButton('<i class="fas fa-search"></i> ' . Yii::t('app', $isCompactMode ? '' : 'Search'), [
                        'class' => 'btn btn-outline-secondary', 
                        'encode' => false,
                        'title' => $isCompactMode ? Yii::t('app', 'Search') : '',
                        'data-toggle' => $isCompactMode ? 'tooltip' : ''
                    ]) ?>
                    </div>
                </div>
            <?= Html::endForm() ?>
        </div>
        <div class="col-md-6">
            <!-- Export button moved to header -->
        </div>
    </div>

    <?php if (empty($customers)): ?>
        <div class="alert alert-info text-center">
            <h4><?= Yii::t('app/customer', 'No customers found') ?></h4>
            <p><?= Yii::t('app/customer', 'You haven\'t created any customers yet.') ?></p>
            <?= Html::a('<i class="fas fa-user-plus mr-1"></i>' . Yii::t('app/customer', 'Create Your First Customer'), ['create'], ['class' => 'btn btn-primary', 'encode' => false]) ?>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="thead-dark">
                    <tr>
                        <th><?= Yii::t('app/customer', 'Customer Name') ?></th>
                        <th><?= Yii::t('app/customer', 'Contact Person') ?></th>
                        <th><?= Yii::t('app/customer', 'Email') ?></th>
                        <th><?= Yii::t('app/customer', 'Phone') ?></th>
                        <th><?= Yii::t('invoice', 'Total Invoices') ?></th>
                        <th><?= Yii::t('invoice', 'Total Amount') ?></th>
                        <th><?= Yii::t('app/customer', 'Status') ?></th>
                        <th><?= Yii::t('app', 'Actions') ?></th>
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
                                    <span class="badge badge-success"><?= Yii::t('app/customer', 'Active') ?></span>
                                <?php else: ?>
                                    <span class="badge badge-secondary"><?= Yii::t('app/customer', 'Inactive') ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <?= Html::a('<i class="fas fa-eye"></i>', ['view', 'id' => $customer->id], [
                                        'class' => 'btn btn-outline-primary',
                                        'title' => Yii::t('app/customer', 'View'),
                                        'data-toggle' => 'tooltip',
                                        'encode' => false
                                    ]) ?>
                                    
                                    <?= Html::a('<i class="fas fa-edit"></i>', ['update', 'id' => $customer->id], [
                                        'class' => 'btn btn-outline-secondary',
                                        'title' => Yii::t('app/customer', 'Edit'),
                                        'data-toggle' => 'tooltip',
                                        'encode' => false
                                    ]) ?>
                                    
                                    <?= Html::a('<i class="fas fa-file-invoice"></i>', ['/invoice/create', 'customer_id' => $customer->id], [
                                        'class' => 'btn btn-outline-success',
                                        'title' => Yii::t('app/customer', 'Create Invoice'),
                                        'data-toggle' => 'tooltip',
                                        'encode' => false
                                    ]) ?>
                                    
                                    <?php if ($customer->is_active): ?>
                                        <?= Html::a('<i class="fas fa-ban"></i>', ['toggle-status', 'id' => $customer->id], [
                                            'class' => 'btn btn-outline-warning',
                                            'title' => Yii::t('app/customer', 'Deactivate'),
                                            'data-toggle' => 'tooltip',
                                            'data-method' => 'post',
                                            'data-confirm' => Yii::t('app/customer', 'Are you sure you want to deactivate this customer?'),
                                            'encode' => false
                                        ]) ?>
                                    <?php else: ?>
                                        <?= Html::a('<i class="fas fa-check"></i>', ['toggle-status', 'id' => $customer->id], [
                                            'class' => 'btn btn-outline-success',
                                            'title' => Yii::t('app/customer', 'Activate'),
                                            'data-toggle' => 'tooltip',
                                            'data-method' => 'post',
                                            'encode' => false
                                        ]) ?>
                                    <?php endif; ?>
                                    
                                    <?php if ($customer->getInvoicesCount() == 0): ?>
                                        <?= Html::a('<i class="fas fa-trash"></i>', ['delete', 'id' => $customer->id], [
                                            'class' => 'btn btn-outline-danger',
                                            'title' => Yii::t('app/customer', 'Delete'),
                                            'data-toggle' => 'tooltip',
                                            'data-confirm' => Yii::t('app/customer', 'Are you sure you want to delete this customer?'),
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