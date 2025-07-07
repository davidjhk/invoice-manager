<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\models\Company;
use app\models\Invoice;
use app\models\Customer;

/** @var yii\web\View $this */

$this->title = 'Invoice Manager Dashboard';

// Get company and statistics
$company = Company::getDefault();
if ($company) {
    $totalInvoices = Invoice::find()->where(['company_id' => $company->id])->count();
    $draftInvoices = Invoice::find()->where(['company_id' => $company->id, 'status' => 'draft'])->count();
    $paidInvoices = Invoice::find()->where(['company_id' => $company->id, 'status' => 'paid'])->count();
    $totalAmount = Invoice::find()->where(['company_id' => $company->id])->sum('total_amount') ?: 0;
    $paidAmount = Invoice::find()->where(['company_id' => $company->id, 'status' => 'paid'])->sum('total_amount') ?: 0;
    $pendingAmount = $totalAmount - $paidAmount;
    $totalCustomers = Customer::find()->where(['company_id' => $company->id])->count();
    
    // Recent invoices
    $recentInvoices = Invoice::find()
        ->where(['company_id' => $company->id])
        ->with(['customer'])
        ->orderBy(['created_at' => SORT_DESC])
        ->limit(5)
        ->all();
} else {
    $totalInvoices = $draftInvoices = $paidInvoices = $totalCustomers = 0;
    $totalAmount = $paidAmount = $pendingAmount = 0;
    $recentInvoices = [];
}
?>
<div class="site-index">

	<div class="jumbotron text-center bg-transparent"
		style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 10px;">
		<h1 class="display-4">Invoice Manager</h1>
		<p class="lead">Professional invoice management system</p>
		<?= Html::a('Create New Invoice', ['/invoice/create'], ['class' => 'btn btn-light']) ?>
	</div>

	<?php if ($company): ?>
	<!-- Statistics Cards -->
	<div class="row mb-4">
		<div class="col-md-3">
			<div class="card text-center border-0 shadow-sm">
				<div class="card-body">
					<div class="display-4 text-primary"><?= $totalInvoices ?></div>
					<h6 class="card-title text-muted">Total Invoices</h6>
				</div>
			</div>
		</div>
		<div class="col-md-3">
			<div class="card text-center border-0 shadow-sm">
				<div class="card-body">
					<div class="display-4 text-warning"><?= $draftInvoices ?></div>
					<h6 class="card-title text-muted">Draft Invoices</h6>
				</div>
			</div>
		</div>
		<div class="col-md-3">
			<div class="card text-center border-0 shadow-sm">
				<div class="card-body">
					<div class="display-4 text-success"><?= $paidInvoices ?></div>
					<h6 class="card-title text-muted">Paid Invoices</h6>
				</div>
			</div>
		</div>
		<div class="col-md-3">
			<div class="card text-center border-0 shadow-sm">
				<div class="card-body">
					<div class="display-4 text-info"><?= $totalCustomers ?></div>
					<h6 class="card-title text-muted">Customers</h6>
				</div>
			</div>
		</div>
	</div>

	<!-- Revenue Overview -->
	<div class="row mb-4">
		<div class="col-md-4">
			<div class="card border-0 shadow-sm">
				<div class="card-body text-center">
					<h5 class="card-title">Total Revenue</h5>
					<div class="display-5 text-primary"><?= $company->formatAmount($totalAmount) ?></div>
				</div>
			</div>
		</div>
		<div class="col-md-4">
			<div class="card border-0 shadow-sm">
				<div class="card-body text-center">
					<h5 class="card-title">Paid Amount</h5>
					<div class="display-5 text-success"><?= $company->formatAmount($paidAmount) ?></div>
				</div>
			</div>
		</div>
		<div class="col-md-4">
			<div class="card border-0 shadow-sm">
				<div class="card-body text-center">
					<h5 class="card-title">Pending Amount</h5>
					<div class="display-5 text-warning"><?= $company->formatAmount($pendingAmount) ?></div>
				</div>
			</div>
		</div>
	</div>

	<div class="row">
		<!-- Recent Invoices -->
		<div class="col-lg-8">
			<div class="card border-0 shadow-sm">
				<div class="card-header d-flex justify-content-between align-items-center">
					<h5 class="card-title mb-0">Recent Invoices</h5>
					<?= Html::a('View All', ['/invoice/index'], ['class' => 'btn btn-outline-primary btn-sm']) ?>
				</div>
				<div class="card-body">
					<?php if (!empty($recentInvoices)): ?>
					<div class="table-responsive">
						<table class="table table-hover">
							<thead>
								<tr>
									<th>Invoice #</th>
									<th>Customer</th>
									<th>Amount</th>
									<th>Status</th>
									<th>Date</th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($recentInvoices as $invoice): ?>
								<tr>
									<td>
										<?= Html::a($invoice->invoice_number, ['/invoice/view', 'id' => $invoice->id], [
                                                    'class' => 'text-decoration-none font-weight-bold'
                                                ]) ?>
									</td>
									<td><?= Html::encode($invoice->customer->customer_name) ?></td>
									<td><?= $invoice->formatAmount($invoice->total_amount) ?></td>
									<td>
										<span class="badge badge-<?= $invoice->getStatusClass() ?>">
											<?= $invoice->getStatusLabel() ?>
										</span>
									</td>
									<td><?= Yii::$app->formatter->asDate($invoice->invoice_date) ?></td>
								</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
					<?php else: ?>
					<div class="text-center py-4">
						<i class="fas fa-file-invoice fa-3x text-muted mb-3"></i>
						<h5>No Invoices Yet</h5>
						<p class="text-muted">Get started by creating your first invoice.</p>
						<?= Html::a('Create Invoice', ['/invoice/create'], ['class' => 'btn btn-primary']) ?>
					</div>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<!-- Quick Actions -->
		<div class="col-lg-4">
			<div class="card border-0 shadow-sm">
				<div class="card-header">
					<h5 class="card-title mb-0">Quick Actions</h5>
				</div>
				<div class="card-body">
					<div class="d-grid gap-2">
						<?= Html::a('<i class="fas fa-plus mr-2"></i>Create Invoice', ['/invoice/create'], [
                            'class' => 'btn btn-primary btn-block mb-2'
                        ]) ?>

						<?= Html::a('<i class="fas fa-users mr-2"></i>Manage Customers', ['/customer/index'], [
                            'class' => 'btn btn-outline-primary btn-block mb-2'
                        ]) ?>

						<?= Html::a('<i class="fas fa-cog mr-2"></i>Company Settings', ['/company/settings'], [
                            'class' => 'btn btn-outline-secondary btn-block mb-2'
                        ]) ?>

						<?= Html::a('<i class="fas fa-download mr-2"></i>Export Data', ['/company/backup'], [
                            'class' => 'btn btn-outline-info btn-block',
                            'target' => '_blank'
                        ]) ?>
					</div>
				</div>
			</div>

			<!-- Company Info -->
			<div class="card border-0 shadow-sm mt-3">
				<div class="card-header">
					<h6 class="card-title mb-0">Company Information</h6>
				</div>
				<div class="card-body">
					<strong><?= Html::encode($company->company_name) ?></strong><br>
					<small class="text-muted">
						<?= nl2br(Html::encode($company->company_address)) ?>
					</small>
				</div>
			</div>
		</div>
	</div>

	<?php else: ?>
	<!-- No Company Setup -->
	<div class="alert alert-warning text-center">
		<h4>Setup Required</h4>
		<p>Please configure your company settings to get started.</p>
		<?= Html::a('Setup Company', ['/company/settings'], ['class' => 'btn btn-warning']) ?>
	</div>
	<?php endif; ?>

</div>

<?php
$this->registerCss("
    .display-5 {
        font-size: 2rem;
        font-weight: 300;
    }
    
    .card {
        transition: transform 0.2s;
    }
    
    .card:hover {
        transform: translateY(-2px);
    }
    
    .jumbotron {
        margin-bottom: 2rem;
    }
");
?>