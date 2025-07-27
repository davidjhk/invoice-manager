<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\LinkPager;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var string $searchTerm */
/** @var string $statusFilter */
/** @var app\models\Company $company */

$this->title = Yii::t('app/estimate', 'Estimates');
$this->params['breadcrumbs'][] = $this->title;

// Get user estimate usage data
$user = Yii::$app->user->identity;
$monthlyCount = $user->getMonthlyEstimateCount();
$remainingEstimates = $user->getRemainingEstimates();
$usagePercentage = $user->getEstimateUsagePercentage();
$currentPlan = $user->getCurrentPlan();
?>

<div class="estimate-index">

	<div class="d-flex justify-content-between align-items-center mb-4">
		<h1><?= Html::encode($this->title) ?></h1>
		<div class="action-buttons">
			<?= Html::a('<i class="fas fa-download mr-1"></i>' . Yii::t('app', 'Export'), ['export'], [
                'class' => 'btn btn-outline-info',
                'target' => '_blank',
                'encode' => false
            ]) ?>
			<?php if ($remainingEstimates > 0 || $remainingEstimates === null): ?>
				<?= Html::a('<i class="fas fa-plus mr-1"></i>' . Yii::t('app/estimate', 'Create New Estimate'), ['create'], [
	                'class' => 'btn btn-success',
	                'encode' => false
	            ]) ?>
			<?php else: ?>
				<?= Html::button('<i class="fas fa-plus mr-1"></i>' . Yii::t('app/estimate', 'Create New Estimate'), [
					'class' => 'btn btn-secondary disabled',
					'disabled' => true,
					'encode' => false,
					'title' => Yii::t('app', 'Monthly estimate limit reached'),
					'data-toggle' => 'tooltip'
				]) ?>
			<?php endif; ?>
		</div>
	</div>

	<!-- Monthly Usage Display -->
	<?php if ($remainingEstimates !== null): ?>
	<div class="row mb-4">
		<div class="col-12">
			<div class="card border-0 bg-light">
				<div class="card-body py-3">
					<div class="row align-items-center">
						<div class="col-md-6">
							<h6 class="mb-1">
								<i class="fas fa-chart-line mr-2 text-primary"></i>
								<?= Yii::t('app', 'Monthly Estimate Usage') ?> - <?= date('F Y') ?>
							</h6>
							<p class="mb-0 text-muted">
								<?= Yii::t('app', '{used} of {limit} estimates used', [
									'used' => $monthlyCount,
									'limit' => $currentPlan ? $currentPlan->getMonthlyInvoiceLimit() : (Yii::$app->params['freeUserMonthlyLimit'] ?? 5)
								]) ?>
								<?php if ($remainingEstimates > 0): ?>
									<span class="text-success ml-2">
										<i class="fas fa-check-circle"></i>
										<?= Yii::t('app', '{count} remaining', ['count' => $remainingEstimates]) ?>
									</span>
								<?php else: ?>
									<span class="text-danger ml-2">
										<i class="fas fa-exclamation-triangle"></i>
										<?= Yii::t('app', 'Limit reached') ?>
									</span>
								<?php endif; ?>
							</p>
						</div>
						<div class="col-md-4">
							<div class="progress" style="height: 10px;">
								<div class="progress-bar <?= $usagePercentage >= 90 ? 'bg-danger' : ($usagePercentage >= 70 ? 'bg-warning' : 'bg-success') ?>" 
									 role="progressbar" 
									 style="width: <?= min(100, $usagePercentage) ?>%"
									 aria-valuenow="<?= $usagePercentage ?>" 
									 aria-valuemin="0" 
									 aria-valuemax="100">
								</div>
							</div>
							<small class="text-muted"><?= round($usagePercentage, 1) ?>% used</small>
						</div>
						<div class="col-md-2 text-right">
							<?php if ($remainingEstimates === 0): ?>
								<?= Html::a(
									'<i class="fas fa-arrow-up mr-1"></i>' . Yii::t('app', 'Upgrade Plan'),
									['/subscription/my-account'],
									['class' => 'btn btn-primary btn-sm']
								) ?>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php endif; ?>

	<div class="row mb-3">
		<div class="col-md-6">
			<?= Html::beginForm(['index'], 'get', ['class' => 'form-inline']) ?>
			<div class="input-group">
				<?= Html::input('text', 'search', $searchTerm, [
                        'class' => 'form-control',
                        'placeholder' => Yii::t('app/estimate', 'Search estimates...'),
                        'id' => 'searchInput'
                    ]) ?>
				<div class="input-group-append">
					<?= Html::submitButton('<i class="fas fa-search"></i> ' . Yii::t('app', 'Search'), ['class' => 'btn btn-outline-secondary', 'encode' => false]) ?>
				</div>
			</div>
			<?= Html::endForm() ?>
		</div>
		<div class="col-md-6 text-right">
			<div class="btn-group" role="group">
				<?= Html::a(Yii::t('app/estimate', 'All'), ['index'], [
                    'class' => 'btn btn-sm ' . (empty($statusFilter) ? 'btn-primary' : 'btn-outline-primary')
                ]) ?>
				<?= Html::a(Yii::t('app/estimate', 'Draft'), ['index', 'status' => 'draft'], [
                    'class' => 'btn btn-sm ' . ($statusFilter === 'draft' ? 'btn-secondary' : 'btn-outline-secondary')
                ]) ?>
				<?= Html::a(Yii::t('app/estimate', 'Printed'), ['index', 'status' => 'printed'], [
                    'class' => 'btn btn-sm ' . ($statusFilter === 'printed' ? 'btn-primary' : 'btn-outline-primary')
                ]) ?>
				<?= Html::a(Yii::t('app/estimate', 'Sent'), ['index', 'status' => 'sent'], [
                    'class' => 'btn btn-sm ' . ($statusFilter === 'sent' ? 'btn-info' : 'btn-outline-info')
                ]) ?>
				<?= Html::a(Yii::t('app/estimate', 'Accepted'), ['index', 'status' => 'accepted'], [
                    'class' => 'btn btn-sm ' . ($statusFilter === 'accepted' ? 'btn-success' : 'btn-outline-success')
                ]) ?>
				<?= Html::a(Yii::t('app/estimate', 'Expired'), ['index', 'status' => 'expired'], [
                    'class' => 'btn btn-sm ' . ($statusFilter === 'expired' ? 'btn-danger' : 'btn-outline-danger')
                ]) ?>
				<?= Html::a(Yii::t('app/estimate', 'Declined'), ['index', 'status' => 'declined'], [
                    'class' => 'btn btn-sm ' . ($statusFilter === 'declined' ? 'btn-secondary' : 'btn-outline-secondary')
                ]) ?>
				<?= Html::a(Yii::t('app/estimate', 'Void'), ['index', 'status' => 'void'], [
                    'class' => 'btn btn-sm ' . ($statusFilter === 'void' ? 'btn-dark' : 'btn-outline-dark')
                ]) ?>
			</div>
		</div>
	</div>

	<?php if ($dataProvider->totalCount == 0): ?>
	<div class="alert alert-info text-center">
		<h4><?= Yii::t('app/estimate', 'No estimates found') ?></h4>
		<p><?= Yii::t('app/estimate', 'You haven\'t created any estimates yet.') ?></p>
		<?= Html::a(Yii::t('app/estimate', 'Create Your First Estimate'), ['create'], ['class' => 'btn btn-primary']) ?>
	</div>
	<?php else: ?>
	<div class="table-responsive">
		<table class="table table-striped table-hover">
			<thead class="thead-dark">
				<tr>
					<th><?= Yii::t('app/estimate', 'Estimate Number') ?></th>
					<th><?= Yii::t('app/estimate', 'Customer') ?></th>
					<th><?= Yii::t('app/estimate', 'Estimate Date') ?></th>
					<th><?= Yii::t('app/estimate', 'Expiry Date') ?></th>
					<th><?= Yii::t('app/estimate', 'Amount') ?></th>
					<th><?= Yii::t('app/estimate', 'Status') ?></th>
					<th><?= Yii::t('app', 'Actions') ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($dataProvider->models as $estimate): ?>
				<tr>
					<td>
						<?= Html::a(Html::encode($estimate->estimate_number), ['view', 'id' => $estimate->id], [
                                    'class' => 'font-weight-bold text-decoration-none'
                                ]) ?>
					</td>
					<td>
						<?= Html::encode($estimate->customer->customer_name) ?>
						<?php if ($estimate->customer->customer_email): ?>
						<br><small class="text-muted"><?= Html::encode($estimate->customer->customer_email) ?></small>
						<?php endif; ?>
					</td>
					<td><?= Yii::$app->formatter->asDate($estimate->estimate_date) ?></td>
					<td>
						<?php if ($estimate->expiry_date): ?>
						<?php
                                    $isExpired = $estimate->expiry_date < date('Y-m-d') && $estimate->status !== 'accepted';
                                    $class = $isExpired ? 'text-danger font-weight-bold' : '';
                                    ?>
						<span class="<?= $class ?>">
							<?= Yii::$app->formatter->asDate($estimate->expiry_date) ?>
						</span>
						<?php else: ?>
						<span class="text-muted">-</span>
						<?php endif; ?>
					</td>
					<td>
						<span class="font-weight-bold">
							<?= $estimate->formatAmount($estimate->total_amount) ?>
						</span>
					</td>
					<td>
						<span class="badge badge-<?= $estimate->getStatusClass() ?>">
							<?= Html::encode($estimate->getStatusLabel()) ?>
						</span>
						<?php if ($estimate->converted_to_invoice): ?>
						<br><small class="text-success"><?= Yii::t('app/estimate', 'Converted') ?></small>
						<?php endif; ?>
					</td>
					<td>
						<div class="btn-group btn-group-sm" role="group">
							<?= Html::a('<i class="fas fa-eye"></i>', ['view', 'id' => $estimate->id], [
                                        'class' => 'btn btn-outline-primary',
                                        'title' => Yii::t('app/estimate', 'View'),
                                        'data-toggle' => 'tooltip',
                                        'encode' => false
                                    ]) ?>

							<?php if ($estimate->canEdit()): ?>
							<?= Html::a('<i class="fas fa-edit"></i>', ['update', 'id' => $estimate->id], [
                                            'class' => 'btn btn-outline-secondary',
                                            'title' => Yii::t('app/estimate', 'Edit'),
                                            'data-toggle' => 'tooltip',
                                            'encode' => false
                                        ]) ?>
							<?php endif; ?>

							<?= Html::a('<i class="fas fa-file-pdf"></i>', ['preview', 'id' => $estimate->id], [
                                        'class' => 'btn btn-outline-info',
                                        'title' => Yii::t('app/estimate', 'Estimate Preview'),
                                        'data-toggle' => 'tooltip',
                                        'encode' => false
                                    ]) ?>

							<?= Html::a('<i class="fas fa-download"></i>', ['download-pdf', 'id' => $estimate->id], [
                                        'class' => 'btn btn-outline-primary',
                                        'title' => Yii::t('app/estimate', 'Download PDF'),
                                        'data-toggle' => 'tooltip',
                                        'encode' => false
                                    ]) ?>

							<?php if ($estimate->customer->customer_email && in_array($estimate->status, ['draft', 'printed'])): ?>
							<?php 
								$company = \app\models\Company::getCurrent();
								$hasEmailConfig = $company && $company->hasEmailConfiguration();
							?>
							<?= Html::a('<i class="fas fa-envelope"></i>', $hasEmailConfig ? ['send-email', 'id' => $estimate->id] : '#', [
                                            'class' => 'btn ' . ($hasEmailConfig ? 'btn-outline-success' : 'btn-outline-secondary'),
                                            'title' => $hasEmailConfig ? Yii::t('app/estimate', 'Send Email') : Yii::t('app/estimate', 'Email not configured. Configure SMTP2GO in Company Settings.'),
                                            'data-toggle' => 'tooltip',
                                            'encode' => false,
                                            'disabled' => !$hasEmailConfig,
                                            'style' => !$hasEmailConfig ? 'cursor: not-allowed; opacity: 0.6;' : ''
                                        ]) ?>
							<?php endif; ?>

							<?php if (in_array($estimate->status, [\app\models\Estimate::STATUS_DRAFT, \app\models\Estimate::STATUS_PRINTED, \app\models\Estimate::STATUS_SENT])): ?>
							<?= Html::a('<i class="fas fa-check"></i>', ['mark-as-accepted', 'id' => $estimate->id], [
                                            'class' => 'btn btn-outline-success',
                                            'title' => Yii::t('app/estimate', 'Mark as Accepted'),
                                            'data-toggle' => 'tooltip',
                                            'data-confirm' => Yii::t('app/estimate', 'Are you sure you want to mark this estimate as accepted?'),
                                            'data-method' => 'post',
                                            'encode' => false
                                        ]) ?>
							<?php endif; ?>

							<?php if ($estimate->canConvertToInvoice()): ?>
							<?= Html::a('<i class="fas fa-exchange-alt"></i>', ['convert-to-invoice', 'id' => $estimate->id], [
                                            'class' => 'btn btn-outline-warning',
                                            'title' => Yii::t('app/estimate', 'Convert to Invoice'),
                                            'data-toggle' => 'tooltip',
                                            'data-confirm' => Yii::t('app/estimate', 'Are you sure you want to convert this estimate to an invoice?'),
                                            'data-method' => 'post',
                                            'encode' => false
                                        ]) ?>
							<?php endif; ?>

							<?= Html::a('<i class="fas fa-copy"></i>', ['duplicate', 'id' => $estimate->id], [
                                        'class' => 'btn btn-outline-info',
                                        'title' => Yii::t('app/estimate', 'Duplicate'),
                                        'data-toggle' => 'tooltip',
                                        'data-confirm' => Yii::t('app/estimate', 'Are you sure you want to duplicate this estimate?'),
                                        'data-method' => 'post',
                                        'encode' => false
                                    ]) ?>

							<?php if ($estimate->canEdit() && !$estimate->converted_to_invoice): ?>
							<?= Html::a('<i class="fas fa-trash"></i>', ['delete', 'id' => $estimate->id], [
                                            'class' => 'btn btn-outline-danger',
                                            'title' => Yii::t('app/estimate', 'Delete'),
                                            'data-toggle' => 'tooltip',
                                            'data-confirm' => Yii::t('app/estimate', 'Are you sure you want to delete this estimate?'),
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

	<!-- Pagination -->
	<div class="row mt-3">
		<div class="col-md-6">
			<div class="dataTables_info">
				<?= Yii::t('app', 'Showing {count} of {total} {items}', [
                        'count' => $dataProvider->getCount(),
                        'total' => $dataProvider->totalCount,
                        'items' => Yii::t('app/estimate', 'estimates')
                    ]) ?>
			</div>
		</div>
		<div class="col-md-6">
			<?= LinkPager::widget([
                    'pagination' => $dataProvider->pagination,
                    'options' => ['class' => 'pagination justify-content-end'],
                    'linkOptions' => ['class' => 'page-link'],
                    'disabledListItemSubTagOptions' => ['class' => 'page-link'],
                    'activePageCssClass' => 'active',
                    'disabledPageCssClass' => 'disabled',
                    'prevPageLabel' => '&laquo; ' . Yii::t('app', 'Previous'),
                    'nextPageLabel' => Yii::t('app', 'Next') . ' &raquo;',
                    'firstPageLabel' => '&laquo;&laquo; ' . Yii::t('app', 'First'),
                    'lastPageLabel' => Yii::t('app', 'Last') . ' &raquo;&raquo;',
                ]) ?>
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
");

$this->registerJs("
    $('[data-toggle=\"tooltip\"]').tooltip();
");
?>