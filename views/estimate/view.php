<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\Estimate $model */

$this->title = Yii::t('app/estimate', 'Estimate') . ': ' . $model->estimate_number;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app/estimate', 'Estimates'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="estimate-view">

	<div class="d-flex justify-content-between align-items-center mb-4">
		<h1>
			<?= Html::encode($model->estimate_number) ?>
			<span class="badge badge-<?= $model->getStatusClass() ?> ml-2">
				<?= Html::encode($model->getStatusLabel()) ?>
			</span>
			<?php if ($model->isExpired()): ?>
				<span class="badge badge-warning ml-1"><?= Yii::t('app/estimate', 'Expired') ?></span>
			<?php endif; ?>
		</h1>
		<div class="btn-group" role="group">
			<?php if (!$model->converted_to_invoice): ?>
			<?= Html::a('<i class="fas fa-edit mr-1"></i>' . Yii::t('app/estimate', 'Edit'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary', 'encode' => false]) ?>
			<?php endif; ?>

			<?= Html::a('<i class="fas fa-file-pdf mr-1"></i>' . Yii::t('app/estimate', 'Estimate Preview'), ['preview', 'id' => $model->id], [
                'class' => 'btn btn-info',
                'target' => '_blank',
                'encode' => false
            ]) ?>

			<?php if ($model->status === \app\models\Estimate::STATUS_DRAFT): ?>
			<?= Html::a('<i class="fas fa-envelope mr-1"></i>' . Yii::t('app/estimate', 'Send Email'), ['send-email', 'id' => $model->id], ['class' => 'btn btn-success', 'encode' => false]) ?>
			<?php endif; ?>

			<?php if ($model->canConvertToInvoice()): ?>
			<?= Html::a('<i class="fas fa-exchange-alt mr-1"></i>' . Yii::t('app/estimate', 'Convert to Invoice'), ['convert-to-invoice', 'id' => $model->id], [
				'class' => 'btn btn-warning',
				'data' => [
					'confirm' => Yii::t('app/estimate', 'Are you sure you want to convert this estimate to an invoice?'),
					'method' => 'post',
				],
				'encode' => false
			]) ?>
			<?php endif; ?>

			<?= Html::a('<i class="fas fa-copy mr-1"></i>' . Yii::t('app/estimate', 'Duplicate'), ['duplicate', 'id' => $model->id], [
                'class' => 'btn btn-info',
                'data' => [
                    'confirm' => Yii::t('app/estimate', 'Are you sure you want to duplicate this estimate?'),
                    'method' => 'post',
                ],
                'encode' => false
            ]) ?>

			<?php if (!$model->converted_to_invoice): ?>
			<?= Html::a('<i class="fas fa-trash mr-1"></i>' . Yii::t('app/estimate', 'Delete'), ['delete', 'id' => $model->id], [
                    'class' => 'btn btn-danger',
                    'data' => [
                        'confirm' => Yii::t('app/estimate', 'Are you sure you want to delete this estimate?'),
                        'method' => 'post',
                    ],
                    'encode' => false
                ]) ?>
			<?php endif; ?>
		</div>
	</div>

    <?php if ($model->converted_to_invoice): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <?= Yii::t('app/estimate', 'This estimate has been converted to invoice') ?>: 
            <?= Html::a($model->invoice->invoice_number, ['/invoice/view', 'id' => $model->invoice_id], [
                'class' => 'font-weight-bold'
            ]) ?>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-8">
            <!-- Estimate Details -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0"><?= Yii::t('app/estimate', 'Estimate Information') ?></h5>
                </div>
                <div class="card-body">
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            'estimate_number',
                            [
                                'attribute' => 'customer_id',
                                'label' => Yii::t('app/estimate', 'Customer'),
                                'value' => $model->customer->customer_name,
                            ],
                            [
                                'attribute' => 'estimate_date',
                                'format' => ['date', 'php:F j, Y'],
                            ],
                            [
                                'attribute' => 'expiry_date',
                                'format' => ['date', 'php:F j, Y'],
                                'value' => $model->expiry_date ?: 'Not set',
                            ],
                            [
                                'attribute' => 'status',
                                'value' => $model->getStatusLabel(),
                            ],
                            'terms',
                            [
                                'attribute' => 'ship_to_address',
                                'format' => 'ntext',
                                'value' => $model->ship_to_address ?: 'Not provided',
                            ],
                            [
                                'attribute' => 'shipping_method',
                                'value' => $model->shipping_method ?: 'Not specified',
                            ],
                            [
                                'attribute' => 'customer_notes',
                                'format' => 'ntext',
                                'value' => $model->customer_notes ?: 'None',
                            ],
                        ],
                    ]) ?>
                </div>
            </div>

            <!-- Estimate Items -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Estimate Items</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($model->estimateItems)): ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Product/Service</th>
                                        <th>Description</th>
                                        <th class="text-right">Qty</th>
                                        <th class="text-right">Rate</th>
                                        <th class="text-right">Amount</th>
                                        <th class="text-center">Tax</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($model->estimateItems as $index => $item): ?>
                                        <tr>
                                            <td><?= $index + 1 ?></td>
                                            <td><?= Html::encode($item->product_service_name ?: '-') ?></td>
                                            <td><?= Html::encode($item->description) ?></td>
                                            <td class="text-right"><?= $item->getFormattedQuantity() ?></td>
                                            <td class="text-right"><?= $item->getFormattedRate() ?></td>
                                            <td class="text-right font-weight-bold"><?= $item->getFormattedAmount() ?></td>
                                            <td class="text-center">
                                                <?php if ($item->is_taxable): ?>
                                                    <i class="fas fa-check text-success"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-times text-muted"></i>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-list fa-3x text-muted mb-3"></i>
                            <h5>No Items</h5>
                            <p class="text-muted">This estimate doesn't have any items yet.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Customer Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="card-title mb-0">Customer Information</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong><?= Html::encode($model->customer->customer_name) ?></strong>
                        <?php if ($model->customer->contact_name): ?>
                            <br><small class="text-muted">Contact: <?= Html::encode($model->customer->contact_name) ?></small>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($model->customer->customer_email): ?>
                        <div class="mb-2">
                            <i class="fas fa-envelope text-primary mr-2"></i>
                            <a href="mailto:<?= Html::encode($model->customer->customer_email) ?>">
                                <?= Html::encode($model->customer->customer_email) ?>
                            </a>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($model->customer->customer_phone): ?>
                        <div class="mb-2">
                            <i class="fas fa-phone text-primary mr-2"></i>
                            <a href="tel:<?= Html::encode($model->customer->customer_phone) ?>">
                                <?= Html::encode($model->customer->customer_phone) ?>
                            </a>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($model->customer->billing_address): ?>
                        <div class="mt-3">
                            <small class="text-muted">Billing Address:</small><br>
                            <?= nl2br(Html::encode($model->customer->billing_address)) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Estimate Totals -->
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">Estimate Totals</h6>
                </div>
                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col-6">Subtotal:</div>
                        <div class="col-6 text-right"><?= $model->formatAmount($model->subtotal) ?></div>
                    </div>
                    
                    <?php if ($model->discount_amount > 0): ?>
                        <div class="row mb-2">
                            <div class="col-6">
                                Discount 
                                <?php if ($model->discount_type == 'percentage'): ?>
                                    (<?= $model->discount_value ?>%):
                                <?php else: ?>
                                    (Fixed):
                                <?php endif; ?>
                            </div>
                            <div class="col-6 text-right text-danger">
                                -<?= $model->formatAmount($model->discount_amount) ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($model->tax_amount > 0): ?>
                        <div class="row mb-2">
                            <div class="col-6">Tax (<?= $model->tax_rate ?>%):</div>
                            <div class="col-6 text-right"><?= $model->formatAmount($model->tax_amount) ?></div>
                        </div>
                    <?php endif; ?>
                    
                    <hr>
                    <div class="row">
                        <div class="col-6"><strong>Total:</strong></div>
                        <div class="col-6 text-right"><strong><?= $model->formatAmount($model->total_amount) ?></strong></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>