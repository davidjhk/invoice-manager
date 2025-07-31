<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\Customer $model */
/** @var app\models\Invoice[] $invoices */

$this->title = $model->customer_name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app/customer', 'Customers'), 'url' => ['index']];
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
<div class="customer-view">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>
            <?= Html::encode($this->title) ?>
            <?php if ($model->is_active): ?>
                <span class="badge badge-success ml-2"><?= Yii::t('app', 'Active') ?></span>
            <?php else: ?>
                <span class="badge badge-secondary ml-2"><?= Yii::t('app', 'Inactive') ?></span>
            <?php endif; ?>
        </h1>
        <div class="btn-group" role="group">
            <?= Html::a('<i class="fas fa-edit mr-1"></i>' . Yii::t('app', $isCompactMode ? '' : 'Edit'), ['update', 'id' => $model->id], [
                'class' => 'btn btn-primary',
                'encode' => false,
                'title' => $isCompactMode ? Yii::t('app', 'Edit') : '',
                'data-toggle' => $isCompactMode ? 'tooltip' : ''
            ]) ?>
            
            <?= Html::a('<i class="fas fa-file-invoice mr-1"></i>' . Yii::t('app/customer', $isCompactMode ? '' : 'Create Invoice'), ['/invoice/create', 'customer_id' => $model->id], [
                'class' => 'btn btn-success',
                'encode' => false,
                'title' => $isCompactMode ? Yii::t('app/customer', 'Create Invoice') : '',
                'data-toggle' => $isCompactMode ? 'tooltip' : ''
            ]) ?>
            
            <?php if ($model->is_active): ?>
                <?= Html::a('<i class="fas fa-ban mr-1"></i>' . Yii::t('app/customer', $isCompactMode ? '' : 'Deactivate'), ['toggle-status', 'id' => $model->id], [
                    'class' => 'btn btn-warning',
                    'data' => [
                        'confirm' => Yii::t('app/customer', 'Are you sure you want to deactivate this customer?'),
                        'method' => 'post',
                    ],
                    'encode' => false,
                    'title' => $isCompactMode ? Yii::t('app/customer', 'Deactivate') : '',
                    'data-toggle' => $isCompactMode ? 'tooltip' : ''
                ]) ?>
            <?php else: ?>
                <?= Html::a('<i class="fas fa-check mr-1"></i>' . Yii::t('app/customer', $isCompactMode ? '' : 'Activate'), ['toggle-status', 'id' => $model->id], [
                    'class' => 'btn btn-success',
                    'data' => [
                        'method' => 'post',
                    ],
                    'encode' => false,
                    'title' => $isCompactMode ? Yii::t('app/customer', 'Activate') : '',
                    'data-toggle' => $isCompactMode ? 'tooltip' : ''
                ]) ?>
            <?php endif; ?>
            
            <?php if ($model->getInvoicesCount() == 0): ?>
                <?= Html::a('<i class="fas fa-trash mr-1"></i>' . Yii::t('app', $isCompactMode ? '' : 'Delete'), ['delete', 'id' => $model->id], [
                    'class' => 'btn btn-danger',
                    'data' => [
                        'confirm' => Yii::t('app/customer', 'Are you sure you want to delete this customer?'),
                        'method' => 'post',
                    ],
                    'encode' => false,
                    'title' => $isCompactMode ? Yii::t('app', 'Delete') : '',
                    'data-toggle' => $isCompactMode ? 'tooltip' : ''
                ]) ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <!-- Customer Details -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0"><?= Yii::t('app/customer', 'Customer Information') ?></h5>
                </div>
                <div class="card-body">
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            'customer_name',
                            [
                                'attribute' => 'contact_name',
                                'value' => $model->contact_name ?: Yii::t('app/customer', 'Not provided'),
                            ],
                            [
                                'attribute' => 'customer_phone',
                                'value' => $model->customer_phone ?: Yii::t('app/customer', 'Not provided'),
                            ],
                            [
                                'attribute' => 'customer_email',
                                'format' => 'email',
                                'value' => $model->customer_email ?: Yii::t('app/customer', 'Not provided'),
                            ],
                            [
                                'attribute' => 'customer_address',
                                'format' => 'ntext',
                                'value' => $model->customer_address ?: Yii::t('app/customer', 'Not provided'),
                            ],
                            [
                                'attribute' => 'billing_address',
                                'format' => 'ntext',
                                'value' => $model->billing_address ?: Yii::t('app/customer', 'Not provided'),
                            ],
                            [
                                'attribute' => 'shipping_address',
                                'format' => 'ntext',  
                                'value' => $model->shipping_address ?: Yii::t('app/customer', 'Not provided'),
                            ],
                        ],
                    ]) ?>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <!-- Statistics -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0"><?= Yii::t('app/customer', 'Statistics') ?></h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <div class="display-4 text-primary"><?= $model->getInvoicesCount() ?></div>
                                <h6 class="text-muted"><?= Yii::t('app/customer', 'Total Invoices') ?></h6>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <h2 class="text-success">
                                    <?= $model->company->formatAmount($model->getTotalAmount()) ?>
								</h2>
                                <h6 class="text-muted"><?= Yii::t('app/customer', 'Total Amount') ?></h6>
                            </div>
                        </div>
                    </div>
                    
                    <?php if ($model->customer_email): ?>
                        <div class="text-center">
                            <?= Html::a('<i class="fas fa-envelope mr-1"></i>' . Yii::t('app/customer', $isCompactMode ? '' : 'Send Email'), 'mailto:' . $model->customer_email, [
                                'class' => 'btn btn-outline-primary',
                                'encode' => false,
                                'title' => $isCompactMode ? Yii::t('app/customer', 'Send Email') : '',
                                'data-toggle' => $isCompactMode ? 'tooltip' : ''
                            ]) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Contact -->
            <?php if ($model->customer_phone || $model->customer_email): ?>
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0"><?= Yii::t('app/customer', 'Settings') ?></h6>
                </div>
                <div class="card-body">
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            [
                                'attribute' => 'is_active',
                                'value' => function($model) {
                                    return Html::tag('span', $model->is_active ? Yii::t('app', 'Active') : Yii::t('app', 'Inactive'), [
                                        'class' => 'badge badge-' . ($model->is_active ? 'success' : 'secondary')
                                    ]);
                                },
                                'format' => 'html',
                            ],
                            [
                                'attribute' => 'created_at',
                                'format' => ['date', 'php:F j, Y g:i A'],
                            ],
                        ],
                    ]) ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Customer Invoices -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0"><?= Yii::t('app/customer', 'Customer Invoices') ?></h5>
            <?= Html::a('<i class="fas fa-plus mr-1"></i>' . Yii::t('app/customer', $isCompactMode ? '' : 'Create New Invoice'), ['/invoice/create', 'customer_id' => $model->id], [
                'class' => 'btn btn-success btn-sm',
                'encode' => false,
                'title' => $isCompactMode ? Yii::t('app/customer', 'Create New Invoice') : '',
                'data-toggle' => $isCompactMode ? 'tooltip' : ''
            ]) ?>
        </div>
        <div class="card-body">
            <?php if (!empty($invoices)): ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th><?= Yii::t('app/customer', 'Invoice #') ?></th>
                                <th><?= Yii::t('app', 'Date') ?></th>
                                <th><?= Yii::t('app', 'Due date') ?></th>
                                <th><?= Yii::t('app', 'Amount') ?></th>
                                <th><?= Yii::t('app', 'Status') ?></th>
                                <th><?= Yii::t('app', 'Actions') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($invoices as $invoice): ?>
                                <tr>
                                    <td>
                                        <?= Html::a($invoice->invoice_number, ['/invoice/view', 'id' => $invoice->id], [
                                            'class' => 'font-weight-bold text-decoration-none'
                                        ]) ?>
                                    </td>
                                    <td><?= Yii::$app->formatter->asDate($invoice->invoice_date) ?></td>
                                    <td>
                                        <?php if ($invoice->due_date): ?>
                                            <?php
                                            $isOverdue = $invoice->due_date < date('Y-m-d') && $invoice->status !== 'paid';
                                            $class = $isOverdue ? 'text-danger font-weight-bold' : '';
                                            ?>
                                            <span class="<?= $class ?>">
                                                <?= Yii::$app->formatter->asDate($invoice->due_date) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="font-weight-bold">
                                            <?= $invoice->formatAmount($invoice->total_amount) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?= $invoice->getStatusClass() ?>">
                                            <?= Html::encode($invoice->getStatusLabel()) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <?= Html::a('<i class="fas fa-eye"></i>', ['/invoice/view', 'id' => $invoice->id], [
                                                'class' => 'btn btn-outline-primary',
                                                'title' => Yii::t('app/customer', 'View Invoice')
                                            ]) ?>
                                            
                                            <?= Html::a('<i class="fas fa-search"></i>', ['/invoice/preview', 'id' => $invoice->id], [
                                                'class' => 'btn btn-outline-info',
                                                'title' => Yii::t('app/customer', 'Preview PDF'),
                                                'target' => '_blank'
                                            ]) ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-4">
                    <i class="fas fa-file-invoice fa-3x text-muted mb-3"></i>
                    <h5><?= Yii::t('app/customer', 'No Invoices') ?></h5>
                    <p class="text-muted"><?= Yii::t('app/customer', 'This customer doesn\'t have any invoices yet.') ?></p>
                    <?= Html::a('<i class="fas fa-plus mr-1"></i>' . Yii::t('app/customer', $isCompactMode ? '' : 'Create First Invoice'), ['/invoice/create', 'customer_id' => $model->id], [
                        'class' => 'btn btn-primary',
                        'encode' => false,
                        'title' => $isCompactMode ? Yii::t('app/customer', 'Create First Invoice') : '',
                        'data-toggle' => $isCompactMode ? 'tooltip' : ''
                    ]) ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<?php
$this->registerJs("
    $('[data-toggle=\"tooltip\"]').tooltip();
");
?>