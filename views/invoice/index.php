<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\LinkPager;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var string $searchTerm */
/** @var string $statusFilter */
/** @var app\models\Company $company */

$this->title = Yii::t('app/invoice', 'Invoices');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="invoice-index">

	<div class="d-flex justify-content-between align-items-center mb-4">
		<h1><?= Html::encode($this->title) ?></h1>
		<div class="action-buttons">
			<?= Html::a('<i class="fas fa-download mr-1"></i>' . Yii::t('app', 'Export'), ['export'], [
                'class' => 'btn btn-outline-info',
                'target' => '_blank',
                'encode' => false
            ]) ?>
			<?= Html::a('<i class="fas fa-plus mr-1"></i>' . Yii::t('app/invoice', 'Create New Invoice'), ['create'], [
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
                        'placeholder' => Yii::t('app/invoice', 'Search invoices...'),
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
				<?= Html::a(Yii::t('app/invoice', 'All'), ['index'], [
                    'class' => 'btn btn-sm ' . (empty($statusFilter) ? 'btn-primary' : 'btn-outline-primary')
                ]) ?>
				<?= Html::a(Yii::t('app/invoice', 'Draft'), ['index', 'status' => 'draft'], [
                    'class' => 'btn btn-sm ' . ($statusFilter === 'draft' ? 'btn-secondary' : 'btn-outline-secondary')
                ]) ?>
				<?= Html::a(Yii::t('app/invoice', 'Sent'), ['index', 'status' => 'sent'], [
                    'class' => 'btn btn-sm ' . ($statusFilter === 'sent' ? 'btn-info' : 'btn-outline-info')
                ]) ?>
				<?= Html::a(Yii::t('app/invoice', 'Paid'), ['index', 'status' => 'paid'], [
                    'class' => 'btn btn-sm ' . ($statusFilter === 'paid' ? 'btn-success' : 'btn-outline-success')
                ]) ?>
				<?= Html::a(Yii::t('app/invoice', 'Overdue'), ['index', 'status' => 'overdue'], [
                    'class' => 'btn btn-sm ' . ($statusFilter === 'overdue' ? 'btn-danger' : 'btn-outline-danger')
                ]) ?>
			</div>
		</div>
	</div>

	<?php if ($dataProvider->totalCount == 0): ?>
	<div class="alert alert-info text-center">
		<h4><?= Yii::t('app/invoice', 'No invoices found') ?></h4>
		<p><?= Yii::t('app/invoice', 'You haven\'t created any invoices yet.') ?></p>
		<?= Html::a(Yii::t('app/invoice', 'Create Your First Invoice'), ['create'], ['class' => 'btn btn-primary']) ?>
	</div>
	<?php else: ?>
	<div class="table-responsive">
		<table class="table table-striped table-hover">
			<thead class="thead-dark">
				<tr>
					<th><?= Yii::t('app/invoice', 'Invoice Number') ?></th>
					<th><?= Yii::t('app/invoice', 'Customer') ?></th>
					<th><?= Yii::t('app/invoice', 'Invoice Date') ?></th>
					<th><?= Yii::t('app/invoice', 'Due Date') ?></th>
					<th><?= Yii::t('app/invoice', 'Amount') ?></th>
					<th><?= Yii::t('app/invoice', 'Status') ?></th>
					<th><?= Yii::t('app', 'Actions') ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($dataProvider->models as $invoice): ?>
				<tr>
					<td>
						<?= Html::a(Html::encode($invoice->invoice_number), ['view', 'id' => $invoice->id], [
                                    'class' => 'font-weight-bold text-decoration-none'
                                ]) ?>
					</td>
					<td><?= Html::encode($invoice->customer->customer_name) ?></td>
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
						<div class="btn-group btn-group-sm" role="group">
							<?= Html::a('<i class="fas fa-eye"></i>', ['view', 'id' => $invoice->id], [
                                        'class' => 'btn btn-outline-primary',
                                        'title' => Yii::t('app/invoice', 'View'),
                                        'data-toggle' => 'tooltip',
                                        'encode' => false
                                    ]) ?>

							<?php if ($invoice->isEditable()): ?>
							<?= Html::a('<i class="fas fa-edit"></i>', ['update', 'id' => $invoice->id], [
                                            'class' => 'btn btn-outline-secondary',
                                            'title' => Yii::t('app/invoice', 'Edit'),
                                            'data-toggle' => 'tooltip',
                                            'encode' => false
                                        ]) ?>
							<?php endif; ?>

							<?= Html::a('<i class="fas fa-file-pdf"></i>', ['preview', 'id' => $invoice->id], [
                                        'class' => 'btn btn-outline-info',
                                        'title' => Yii::t('app/invoice', 'Invoice Preview'),
                                        'data-toggle' => 'tooltip',
                                        'encode' => false
                                    ]) ?>

							<?php if ($invoice->canBeSent()): ?>
							<?= Html::a('<i class="fas fa-envelope"></i>', ['send-email', 'id' => $invoice->id], [
                                            'class' => 'btn btn-outline-success',
                                            'title' => Yii::t('app/invoice', 'Send Email'),
                                            'data-toggle' => 'tooltip',
                                            'encode' => false
                                        ]) ?>
							<?php endif; ?>

							<?php if ($invoice->canReceivePayment()): ?>
							<?= Html::a('<i class="fas fa-dollar-sign"></i>', ['receive-payment', 'id' => $invoice->id], [
                                            'class' => 'btn btn-outline-warning',
                                            'title' => Yii::t('app/invoice', 'Receive Payment'),
                                            'data-toggle' => 'tooltip',
                                            'encode' => false
                                        ]) ?>
							<?php endif; ?>

							<?= Html::a('<i class="fas fa-copy"></i>', ['duplicate', 'id' => $invoice->id], [
                                        'class' => 'btn btn-outline-info',
                                        'title' => Yii::t('app/invoice', 'Duplicate'),
                                        'data-toggle' => 'tooltip',
                                        'data-confirm' => Yii::t('app/invoice', 'Are you sure you want to duplicate this invoice?'),
                                        'data-method' => 'post',
                                        'encode' => false
                                    ]) ?>

							<?php if ($invoice->isEditable()): ?>
							<?= Html::a('<i class="fas fa-trash"></i>', ['delete', 'id' => $invoice->id], [
                                            'class' => 'btn btn-outline-danger',
                                            'title' => Yii::t('app/invoice', 'Delete'),
                                            'data-toggle' => 'tooltip',
                                            'data-confirm' => Yii::t('app/invoice', 'Are you sure you want to delete this invoice?'),
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
                        'items' => Yii::t('app/invoice', 'invoices')
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