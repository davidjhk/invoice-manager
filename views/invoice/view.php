<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\Invoice $model */

$this->title = $model->invoice_number;
$this->params['breadcrumbs'][] = ['label' => Yii::t('invoice', 'Invoices'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

// Determine dark mode setting
$currentCompany = null;
if (!Yii::$app->user->isGuest) {
	$companyId = Yii::$app->session->get('current_company_id');
	if ($companyId) {
		$currentCompany = \app\models\Company::findForCurrentUser()->where(['id' => $companyId])->one();
	}
}
$isDarkMode = $currentCompany && $currentCompany->dark_mode;
$isCompactMode = $currentCompany && $currentCompany->compact_mode;
?>
<div class="invoice-view">

    <div class="d-flex justify-content-between align-items-center mb-4">
		<h1>
			<?= Html::encode($this->title) ?>
			<span class="badge badge-<?= $model->getStatusClass() ?> ml-2">
				<?= Html::encode($model->getStatusLabel()) ?>
			</span>
		</h1>
		<div class="btn-group" role="group">
			<?php if ($model->isEditable()): ?>
			<?= Html::a('<i class="fas fa-edit mr-1"></i>' . Yii::t('invoice', $isCompactMode ? '' : 'Edit'), ['update', 'id' => $model->id], [
				'class' => 'btn btn-primary', 
				'encode' => false,
				'title' => $isCompactMode ? Yii::t('invoice', 'Edit') : '',
				'data-toggle' => $isCompactMode ? 'tooltip' : ''
			]) ?>
			<?php endif; ?>

			<?= Html::a('<i class="fas fa-file-pdf mr-1"></i>' . Yii::t('invoice', $isCompactMode ? '' : 'Preview'), ['preview', 'id' => $model->id], [
                'class' => 'btn btn-info',
                'target' => '_blank',
                'encode' => false,
                'title' => $isCompactMode ? Yii::t('invoice', 'Preview') : '',
                'data-toggle' => $isCompactMode ? 'tooltip' : ''
            ]) ?>

			<?php if ($model->canBeSent()): ?>
			<?php 
				$company = \app\models\Company::getCurrent();
				$hasEmailConfig = $company && $company->hasEmailConfiguration();
			?>
			<?= Html::a(
				'<i class="fas fa-envelope mr-1"></i>' . Yii::t('invoice', $isCompactMode ? '' : 'Send Email'), 
				$hasEmailConfig ? ['send-email', 'id' => $model->id] : '#', 
				[
					'class' => 'btn ' . ($hasEmailConfig ? 'btn-success' : 'btn-secondary'),
					'encode' => false,
					'disabled' => !$hasEmailConfig,
					'title' => $hasEmailConfig ? ($isCompactMode ? Yii::t('invoice', 'Send Email') : '') : Yii::t('invoice', 'Email not configured. Configure SMTP2GO in Company Settings.'),
					'data-toggle' => 'tooltip',
					'style' => !$hasEmailConfig ? 'cursor: not-allowed; opacity: 0.6;' : ''
				]
			) ?>
			<?php endif; ?>

			<?php if ($model->canReceivePayment()): ?>
			<?= Html::a('<i class="fas fa-dollar-sign mr-1"></i>' . Yii::t('invoice', $isCompactMode ? '' : 'Receive Payment'), ['receive-payment', 'id' => $model->id], [
				'class' => 'btn btn-warning', 
				'encode' => false,
				'title' => $isCompactMode ? Yii::t('invoice', 'Receive Payment') : '',
				'data-toggle' => $isCompactMode ? 'tooltip' : ''
			]) ?>
			<?php endif; ?>

			<?= Html::a('<i class="fas fa-copy mr-1"></i>' . Yii::t('invoice', $isCompactMode ? '' : 'Duplicate'), ['duplicate', 'id' => $model->id], [
                'class' => 'btn btn-info',
                'data' => [
                    'confirm' => Yii::t('invoice', 'Are you sure you want to duplicate this invoice?'),
                    'method' => 'post',
                ],
                'encode' => false,
                'title' => $isCompactMode ? Yii::t('invoice', 'Duplicate') : '',
                'data-toggle' => $isCompactMode ? 'tooltip' : ''
            ]) ?>

			<?php if ($model->isEditable()): ?>
			<?= Html::a('<i class="fas fa-trash mr-1"></i>' . Yii::t('invoice', $isCompactMode ? '' : 'Delete'), ['delete', 'id' => $model->id], [
                    'class' => 'btn btn-danger',
                    'data' => [
                        'confirm' => Yii::t('invoice', 'Are you sure you want to delete this invoice?'),
                        'method' => 'post',
                    ],
                    'encode' => false,
                    'title' => $isCompactMode ? Yii::t('invoice', 'Delete') : '',
                    'data-toggle' => $isCompactMode ? 'tooltip' : ''
                ]) ?>
			<?php endif; ?>
		</div>
	</div>

	<div class="row">
		<div class="col-lg-8">
			<!-- Invoice Details -->
			<div class="card mb-4">
				<div class="card-header">
					<h5 class="card-title mb-0"><?= Yii::t('invoice', 'Invoice Details') ?></h5>
				</div>
				<div class="card-body">
					<?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            'invoice_number',
                            [
                                'attribute' => 'invoice_date',
                                'format' => 'date',
                            ],
                            [
                                'attribute' => 'due_date',
                                'value' => function($model) {
                                    if (!$model->due_date) return Yii::t('invoice', 'Not set');
                                    
                                    $isOverdue = $model->due_date < date('Y-m-d') && $model->status !== 'paid';
                                    $formatted = Yii::$app->formatter->asDate($model->due_date);
                                    
                                    if ($isOverdue) {
                                        return Html::tag('span', $formatted . ' (' . Yii::t('invoice', 'OVERDUE') . ')', ['class' => 'text-danger font-weight-bold']);
                                    }
                                    
                                    return $formatted;
                                },
                                'format' => 'html',
                            ],
                            [
                                'attribute' => 'status',
                                'value' => function($model) {
                                    return Html::tag('span', $model->getStatusLabel(), [
                                        'class' => 'badge badge-' . $model->getStatusClass()
                                    ]);
                                },
                                'format' => 'html',
                            ],
                            'currency',
                            'notes:ntext',
                        ],
                    ]) ?>
				</div>
			</div>

			<!-- Invoice Items -->
			<div class="card mb-4">
				<div class="card-header" style="display:none;">
					<h5 class="card-title mb-0"><?= Yii::t('invoice', 'Invoice Items') ?></h5>
				</div>
				<div class="card-body">
					<?php if (!empty($model->invoiceItems)): ?>
					<div class="table-responsive">
						<table class="table table-striped">
							<thead>
								<tr>
                                    <th><?= Yii::t('invoice', 'Product/Service') ?></th>
									<th><?= Yii::t('invoice', 'Description') ?></th>
									<th class="text-center"><?= Yii::t('invoice', 'Quantity') ?></th>
									<th class="text-right"><?= Yii::t('invoice', 'Price') ?></th>
									<th class="text-right"><?= Yii::t('invoice', 'Amount') ?></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($model->invoiceItems as $item): ?>
								<tr>
                                    <td><?= Html::encode($item->product_service_name ?: '-') ?></td>
									<td><?= nl2br(Html::encode($item->description)) ?></td>
									<td class="text-center"><?= $item->getFormattedQuantity() ?></td>
									<td class="text-right"><?= $item->getFormattedRate() ?></td>
									<td class="text-right font-weight-bold"><?= $item->getFormattedAmount() ?></td>
								</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
					<?php else: ?>
					<div class="alert alert-warning">
						<?= Yii::t('invoice', 'No items found for this invoice.') ?>
					</div>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<div class="col-lg-4">

			<!-- Customer Details -->
			<div class="card mb-4">
				<div class="card-header">
					<h5 class="card-title mb-0"><?= Yii::t('invoice', 'Customer Information') ?></h5>
				</div>
				<div class="card-body">
					<div class="row">
						<div class="col-md-6">
							<strong><?= Html::encode($model->customer->customer_name) ?></strong>
							<?php if ($model->customer->customer_address): ?>
							<br><?= nl2br(Html::encode($model->customer->customer_address)) ?>
							<?php endif; ?>
							<?php 
								$locationParts = [];
								if ($model->customer->city) $locationParts[] = $model->customer->city;
								if ($model->customer->state) $locationParts[] = $model->customer->state;
								if ($model->customer->zip_code) $locationParts[] = $model->customer->zip_code;
								if (!empty($locationParts)): 
							?>
							<br><?= Html::encode(implode(', ', $locationParts)) ?>
							<?php endif; ?>
							<?php if ($model->customer->country && $model->customer->country !== 'US'): ?>
							<br><?= Html::encode($model->customer->country) ?>
							<?php endif; ?>
						</div>
						<div class="col-md-6">
							<?php if ($model->customer->customer_phone): ?>
							<div><i class="fas fa-phone mr-2"></i><?= Html::encode($model->customer->customer_phone) ?>
							</div>
							<?php endif; ?>
							<?php if ($model->customer->customer_fax): ?>
							<div><i class="fas fa-fax mr-2"></i><?= Html::encode($model->customer->customer_fax) ?>
							</div>
							<?php endif; ?>
							<?php if ($model->customer->customer_mobile): ?>
							<div><i
									class="fas fa-mobile-alt mr-2"></i><?= Html::encode($model->customer->customer_mobile) ?>
							</div>
							<?php endif; ?>
							<?php if ($model->customer->customer_email): ?>
							<div><i
									class="fas fa-envelope mr-2"></i><?= Html::encode($model->customer->customer_email) ?>
							</div>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>

			<!-- Payment Information -->
			<div class="card mb-4">
				<div class="card-header">
					<h5 class="card-title mb-0"><?= Yii::t('invoice', 'Payment Status') ?></h5>
				</div>
				<div class="card-body">
					<dl class="row mb-0">
						<dt class="col-sm-6"><?= Yii::t('invoice', 'Invoice Total') ?>:</dt>
						<dd class="col-sm-6 text-right">
							<strong><?= $model->formatAmount($model->total_amount) ?></strong>
						</dd>

						<dt class="col-sm-6"><?= Yii::t('invoice', 'Amount Paid') ?>:</dt>
						<dd class="col-sm-6 text-right text-success">
							<strong><?= $model->formatAmount($model->getPaidAmount()) ?></strong>
						</dd>

						<dt class="col-sm-6"><?= Yii::t('invoice', 'Balance Due') ?>:</dt>
						<dd class="col-sm-6 text-right text-danger">
							<strong><?= $model->formatAmount($model->getBalance()) ?></strong>
						</dd>
					</dl>
					<?php if ($model->canReceivePayment()): ?>
					<div class="mt-3">
						<?= Html::a('<i class="fas fa-dollar-sign mr-1"></i> ' . Yii::t('invoice', $isCompactMode ? '' : 'Receive Payment'), ['receive-payment', 'id' => $model->id], [
							'class' => 'btn btn-warning btn-block', 
							'encode' => false,
							'title' => $isCompactMode ? Yii::t('invoice', 'Receive Payment') : '',
							'data-toggle' => $isCompactMode ? 'tooltip' : ''
						]) ?>
					</div>
					<?php endif; ?>
				</div>
			</div>

			<!-- Payment History -->
			<?php if (!empty($model->payments)): ?>
			<div class="card mb-4">
				<div class="card-header">
					<h5 class="card-title mb-0"><?= Yii::t('invoice', 'Payment History') ?></h5>
				</div>
				<div class="card-body p-0">
					<table class="table table-striped table-sm mb-0">
						<thead>
							<tr>
								<th><?= Yii::t('invoice', 'Payment Date') ?></th>
								<th><?= Yii::t('invoice', 'Payment Method') ?></th>
								<th class="text-right"><?= Yii::t('invoice', 'Amount') ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($model->payments as $payment): ?>
							<tr>
								<td><?= Yii::$app->formatter->asDate($payment->payment_date) ?></td>
								<td><?= Html::encode($payment->payment_method) ?></td>
								<td class="text-right"><?= $model->formatAmount($payment->amount) ?></td>
							</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			</div>
			<?php endif; ?>

			<!-- Totals -->
			<div class="card">
				<div class="card-header">
					<h5 class="card-title mb-0"><?= Yii::t('invoice', 'Summary') ?></h5>
				</div>
				<div class="card-body">
					<table class="table table-sm">
						<tr>
							<td><?= Yii::t('invoice', 'Subtotal') ?>:</td>
							<td class="text-right"><?= $model->formatAmount($model->subtotal) ?></td>
						</tr>
						<?php if ($model->shipping_fee > 0): ?>
						<tr>
							<td><?= Yii::t('invoice', 'Shipping Fee') ?>:</td>
							<td class="text-right"><?= $model->formatAmount($model->shipping_fee) ?></td>
						</tr>
						<?php endif; ?>
						<tr>
							<td><?= Yii::t('invoice', 'Tax') ?> (<?= number_format($model->tax_rate, 1) ?>%):</td>
							<td class="text-right"><?= $model->formatAmount($model->tax_amount) ?></td>
						</tr>
						<tr class="table-active font-weight-bold">
							<td><?= Yii::t('invoice', 'Total') ?>:</td>
							<td class="text-right h5 mb-0"><?= $model->formatAmount($model->total_amount) ?></td>
						</tr>

						<?php if ($model->getTotalPaidAmount() > 0): ?>
						<tr class="table-success">
							<td><strong><?= Yii::t('invoice', 'Paid') ?>:</strong></td>
							<td class="text-right text-success font-weight-bold">
								-<?= $model->formatAmount($model->getTotalPaidAmount()) ?>
							</td>
						</tr>
						<tr class="<?= $model->getRemainingBalance() > 0 ? 'table-warning' : 'table-success' ?>">
							<td><strong><?= Yii::t('invoice', 'Balance Due') ?>:</strong></td>
							<td
								class="text-right font-weight-bold h5 mb-0 <?= $model->getRemainingBalance() > 0 ? 'text-warning' : 'text-success' ?>">
								<?= $model->formatAmount($model->getRemainingBalance()) ?>
							</td>
						</tr>
						<?php endif; ?>
					</table>
				</div>
			</div>
		</div>
	</div>

</div>