<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\Estimate $model */

$this->title = Yii::t('app/estimate', 'Estimate') . ': ' . $model->estimate_number;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app/estimate', 'Estimates'), 'url' => ['index']];
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
			<?= Html::a('<i class="fas fa-edit mr-1"></i>' . Yii::t('app/estimate', $isCompactMode ? '' : 'Edit'), ['update', 'id' => $model->id], [
				'class' => 'btn btn-primary', 
				'encode' => false,
				'title' => $isCompactMode ? Yii::t('app/estimate', 'Edit') : '',
				'data-toggle' => $isCompactMode ? 'tooltip' : ''
			]) ?>
			<?php endif; ?>

			<?= Html::a('<i class="fas fa-search mr-1"></i>' . Yii::t('app/estimate', $isCompactMode ? '' : 'Preview'), ['preview', 'id' => $model->id], [
                'class' => 'btn btn-info',
                'target' => '_blank',
                'encode' => false,
                'title' => $isCompactMode ? Yii::t('app/estimate', 'Preview') : '',
                'data-toggle' => $isCompactMode ? 'tooltip' : ''
            ]) ?>

			<?php if (in_array($model->status, [\app\models\Estimate::STATUS_DRAFT, \app\models\Estimate::STATUS_PRINTED])): ?>
			<?php 
				$company = \app\models\Company::getCurrent();
				$hasEmailConfig = $company && $company->hasEmailConfiguration();
			?>
			<?= Html::a(
				'<i class="fas fa-envelope mr-1"></i>' . Yii::t('app/estimate', $isCompactMode ? '' : 'Send Email'), 
				$hasEmailConfig ? ['send-email', 'id' => $model->id] : '#', 
				[
					'class' => 'btn ' . ($hasEmailConfig ? 'btn-success' : 'btn-secondary'),
					'encode' => false,
					'disabled' => !$hasEmailConfig,
					'title' => $hasEmailConfig ? ($isCompactMode ? Yii::t('app/estimate', 'Send Email') : '') : Yii::t('app/estimate', 'Email not configured. Configure SMTP2GO in Company Settings.'),
					'data-toggle' => 'tooltip',
					'style' => !$hasEmailConfig ? 'cursor: not-allowed; opacity: 0.6;' : ''
				]
			) ?>
			<?php endif; ?>

			<?php if (in_array($model->status, [\app\models\Estimate::STATUS_DRAFT, \app\models\Estimate::STATUS_PRINTED, \app\models\Estimate::STATUS_SENT])): ?>
			<?= Html::a('<i class="fas fa-check mr-1"></i>' . Yii::t('app/estimate', $isCompactMode ? '' : 'Mark as Accepted'), ['mark-as-accepted', 'id' => $model->id], [
				'class' => 'btn btn-success',
				'data' => [
					'confirm' => Yii::t('app/estimate', 'Are you sure you want to mark this estimate as accepted?'),
					'method' => 'post',
				],
				'encode' => false,
				'title' => $isCompactMode ? Yii::t('app/estimate', 'Mark as Accepted') : '',
				'data-toggle' => $isCompactMode ? 'tooltip' : ''
			]) ?>
			<?php endif; ?>

			<?php if ($model->canConvertToInvoice()): ?>
			<?= Html::a('<i class="fas fa-exchange-alt mr-1"></i>' . Yii::t('app/estimate', $isCompactMode ? '' : 'Convert to Invoice'), ['convert-to-invoice', 'id' => $model->id], [
				'class' => 'btn btn-warning',
				'data' => [
					'confirm' => Yii::t('app/estimate', 'Are you sure you want to convert this estimate to an invoice?'),
					'method' => 'post',
				],
				'encode' => false,
				'title' => $isCompactMode ? Yii::t('app/estimate', 'Convert to Invoice') : '',
				'data-toggle' => $isCompactMode ? 'tooltip' : ''
			]) ?>
			<?php endif; ?>

			<?= Html::a('<i class="fas fa-copy mr-1"></i>' . Yii::t('app/estimate', $isCompactMode ? '' : 'Duplicate'), ['duplicate', 'id' => $model->id], [
                'class' => 'btn btn-info',
                'data' => [
                    'confirm' => Yii::t('app/estimate', 'Are you sure you want to duplicate this estimate?'),
                    'method' => 'post',
                ],
                'encode' => false,
                'title' => $isCompactMode ? Yii::t('app/estimate', 'Duplicate') : '',
                'data-toggle' => $isCompactMode ? 'tooltip' : ''
            ]) ?>

			<?php if (!$model->converted_to_invoice): ?>
			<?= Html::a('<i class="fas fa-trash mr-1"></i>' . Yii::t('app/estimate', $isCompactMode ? '' : 'Delete'), ['delete', 'id' => $model->id], [
                    'class' => 'btn btn-danger',
                    'data' => [
                        'confirm' => Yii::t('app/estimate', 'Are you sure you want to delete this estimate?'),
                        'method' => 'post',
                    ],
                    'encode' => false,
                    'title' => $isCompactMode ? Yii::t('app/estimate', 'Delete') : '',
                    'data-toggle' => $isCompactMode ? 'tooltip' : ''
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
                                'format' => 'date',
                            ],
                            [
                                'attribute' => 'expiry_date',
                                'format' => 'date',
                                'value' => $model->expiry_date ?: Yii::t('app/estimate', 'Not set'),
                            ],
                            [
                                'attribute' => 'status',
                                'value' => $model->getStatusLabel(),
                            ],
                            'terms',
                            [
                                'attribute' => 'ship_to_address',
                                'format' => 'ntext',
                                'value' => $model->ship_to_address ?: Yii::t('app/estimate', 'Not provided'),
                            ],
                            [
                                'attribute' => 'shipping_method',
                                'value' => $model->shipping_method ?: Yii::t('app/estimate', 'Not specified'),
                            ],
                            [
                                'attribute' => 'customer_notes',
                                'format' => 'ntext',
                                'value' => $model->customer_notes ?: Yii::t('app/estimate', 'None'),
                            ],
                        ],
                    ]) ?>
                </div>
            </div>

            <!-- Estimate Items -->
            <div class="card">
                <div class="card-header" style="display:none;">
                    <h5 class="card-title mb-0"><?= Yii::t('app/estimate', 'Estimate Items') ?></h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($model->estimateItems)): ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th><?= Yii::t('app/estimate', 'Product/Service') ?></th>
                                        <th><?= Yii::t('app/estimate', 'Description') ?></th>
                                        <th class="text-right"><?= Yii::t('app/estimate', 'Qty') ?></th>
                                        <th class="text-right"><?= Yii::t('app/estimate', 'Rate') ?></th>
                                        <th class="text-right"><?= Yii::t('app/estimate', 'Amount') ?></th>
                                        <th class="text-center"><?= Yii::t('app/estimate', 'Tax') ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($model->estimateItems as $index => $item): ?>
                                        <tr>
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
                            <h5><?= Yii::t('app/estimate', 'No Items') ?></h5>
                            <p class="text-muted"><?= Yii::t('app/estimate', 'This estimate doesn\'t have any items yet.') ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Customer Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0"><?= Yii::t('app/estimate', 'Customer Information') ?></h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong><?= Html::encode($model->customer->customer_name) ?></strong>
                        <?php if ($model->customer->contact_name): ?>
                            <br><small class="text-muted"><?= Yii::t('app/estimate', 'Contact') ?>: <?= Html::encode($model->customer->contact_name) ?></small>
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
                            <small class="text-muted"><?= Yii::t('app/estimate', 'Billing Address') ?>:</small><br>
                            <?= nl2br(Html::encode($model->customer->billing_address)) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Estimate Totals -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><?= Yii::t('app/estimate', 'Estimate Totals') ?></h5>
                </div>
                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col-6"><?= Yii::t('app/estimate', 'Subtotal') ?>:</div>
                        <div class="col-6 text-right"><?= $model->formatAmount($model->subtotal) ?></div>
                    </div>
                    
                    <?php if ($model->discount_amount > 0): ?>
                        <div class="row mb-2">
                            <div class="col-6">
                                <?= Yii::t('app/estimate', 'Discount') ?> 
                                <?php if ($model->discount_type == 'percentage'): ?>
                                    (<?= $model->discount_value ?>%):
                                <?php else: ?>
                                    (<?= Yii::t('app/estimate', 'Fixed') ?>):
                                <?php endif; ?>
                            </div>
                            <div class="col-6 text-right text-danger">
                                -<?= $model->formatAmount($model->discount_amount) ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($model->shipping_fee > 0): ?>
                        <div class="row mb-2">
                            <div class="col-6"><?= Yii::t('app/estimate', 'Shipping Fee') ?>:</div>
                            <div class="col-6 text-right"><?= $model->formatAmount($model->shipping_fee) ?></div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($model->tax_amount > 0): ?>
                        <div class="row mb-2">
                            <div class="col-6"><?= Yii::t('app/estimate', 'Tax') ?> (<?= $model->tax_rate ?>%):</div>
                            <div class="col-6 text-right"><?= $model->formatAmount($model->tax_amount) ?></div>
                        </div>
                    <?php endif; ?>
                    
                    <hr>
                    <div class="row">
                        <div class="col-6"><strong><?= Yii::t('app/estimate', 'Total') ?>:</strong></div>
                        <div class="col-6 text-right"><strong><?= $model->formatAmount($model->total_amount) ?></strong></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>