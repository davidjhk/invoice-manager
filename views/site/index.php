<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\models\Company;
use app\models\Invoice;
use app\models\Customer;

/** @var yii\web\View $this */

$this->title = (Yii::$app->params['siteName'] ?? Yii::t('app', 'Invoice Manager')) . ' ' . Yii::t('app', 'Dashboard');

// Get company and statistics
$company = Company::getCurrent();
if (!$company) {
    // Redirect to company selection if no company is selected
    return Yii::$app->response->redirect(['company/select']);
}

$totalInvoices = Invoice::find()->where(['company_id' => $company->id])->count();
$draftInvoices = Invoice::find()->where(['company_id' => $company->id, 'status' => 'draft'])->count();
$paidInvoices = Invoice::find()->where(['company_id' => $company->id, 'status' => 'paid'])->count();
$sentInvoices = Invoice::find()->where(['company_id' => $company->id, 'status' => 'sent'])->count();
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

// Calculate conversion rates
$conversionRate = $totalInvoices > 0 ? round(($paidInvoices / $totalInvoices) * 100, 1) : 0;
$averageInvoiceValue = $totalInvoices > 0 ? $totalAmount / $totalInvoices : 0;
?>

<div class="dashboard-container">
	<!-- Dashboard Header -->
	<div class="dashboard-header">
		<div class="header-content">
			<div class="header-info">
				<h1 class="dashboard-title"><?= Yii::t('app', 'Dashboard') ?></h1>
				<p class="dashboard-subtitle">
					<?= Yii::t('app', 'Welcome back, {company}', ['company' => Html::encode($company->company_name)]) ?>
				</p>
			</div>
			<div class="header-actions action-buttons">
				<?= Html::a('<i class="fas fa-plus mr-2"></i>' . Yii::t('app/invoice', 'New Invoice'), ['/invoice/create'], [
                    'class' => 'btn btn-primary'
                ]) ?>
			</div>
		</div>
	</div>

	<!-- Key Performance Indicators -->
	<div class="kpi-grid mobile-hidden">
		<div class="kpi-card revenue-card">
			<div class="kpi-header">
				<div class="kpi-icon">
					<i class="fas fa-dollar-sign"></i>
				</div>
				<div class="kpi-trend positive">
					<i class="fas fa-arrow-up"></i>
				</div>
			</div>
			<div class="kpi-body">
				<h3 class="kpi-value"><?= $company->formatAmount($totalAmount) ?></h3>
				<p class="kpi-label"><?= Yii::t('app', 'Total Revenue') ?></p>
				<small
					class="kpi-detail"><?= Yii::t('app', 'Average: {amount} per invoice', ['amount' => $company->formatAmount($averageInvoiceValue)]) ?></small>
			</div>
		</div>

		<div class="kpi-card paid-card">
			<div class="kpi-header">
				<div class="kpi-icon">
					<i class="fas fa-check-circle"></i>
				</div>
				<div class="kpi-trend positive">
					<i class="fas fa-arrow-up"></i>
				</div>
			</div>
			<div class="kpi-body">
				<h3 class="kpi-value"><?= $company->formatAmount($paidAmount) ?></h3>
				<p class="kpi-label"><?= Yii::t('app', 'Paid Amount') ?></p>
				<small
					class="kpi-detail"><?= Yii::t('app', '{rate}% collection rate', ['rate' => $conversionRate]) ?></small>
			</div>
		</div>

		<div class="kpi-card pending-card">
			<div class="kpi-header">
				<div class="kpi-icon">
					<i class="fas fa-clock"></i>
				</div>
				<div class="kpi-trend <?= $pendingAmount > 0 ? 'warning' : 'neutral' ?>">
					<i class="fas fa-<?= $pendingAmount > 0 ? 'exclamation-triangle' : 'minus' ?>"></i>
				</div>
			</div>
			<div class="kpi-body">
				<h3 class="kpi-value"><?= $company->formatAmount($pendingAmount) ?></h3>
				<p class="kpi-label"><?= Yii::t('app', 'Pending Amount') ?></p>
				<small
					class="kpi-detail"><?= Yii::t('app', '{count} invoices awaiting payment', ['count' => $sentInvoices]) ?></small>
			</div>
		</div>

		<div class="kpi-card customers-card">
			<div class="kpi-header">
				<div class="kpi-icon">
					<i class="fas fa-users"></i>
				</div>
				<div class="kpi-trend positive">
					<i class="fas fa-arrow-up"></i>
				</div>
			</div>
			<div class="kpi-body">
				<h3 class="kpi-value"><?= $totalCustomers ?></h3>
				<p class="kpi-label"><?= Yii::t('app/customer', 'Active Customers') ?></p>
				<small
					class="kpi-detail"><?= Yii::t('app', '{avg} avg invoices per customer', ['avg' => $totalCustomers > 0 ? round($totalInvoices / $totalCustomers, 1) : 0]) ?></small>
			</div>
		</div>
	</div>

	<!-- Invoice Status Overview -->
	<div class="status-overview mobile-hidden">
		<div class="status-header">
			<h2 class="section-title"><?= Yii::t('app/invoice', 'Invoice Status') ?></h2>
			<div class="status-actions">
				<?= Html::a(Yii::t('app', 'View All {item}', ['item' => Yii::t('app/invoice', 'Invoices')]), ['/invoice/index'], ['class' => 'btn btn-outline-primary']) ?>
			</div>
		</div>
		<div class="status-cards">
			<div class="status-card draft-status">
				<div class="status-number"><?= $draftInvoices ?></div>
				<div class="status-label"><?= Yii::t('app/invoice', 'Draft') ?></div>
				<div class="status-action">
					<?= Html::a(Yii::t('app', 'Review'), ['/invoice/index', 'status' => 'draft'], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
				</div>
			</div>
			<div class="status-card sent-status">
				<div class="status-number"><?= $sentInvoices ?></div>
				<div class="status-label"><?= Yii::t('app/invoice', 'Sent') ?></div>
				<div class="status-action">
					<?= Html::a(Yii::t('app', 'Follow Up'), ['/invoice/index', 'status' => 'sent'], ['class' => 'btn btn-sm btn-outline-warning']) ?>
				</div>
			</div>
			<div class="status-card paid-status">
				<div class="status-number"><?= $paidInvoices ?></div>
				<div class="status-label"><?= Yii::t('app/invoice', 'Paid') ?></div>
				<div class="status-action">
					<?= Html::a(Yii::t('app/invoice', 'View'), ['/invoice/index', 'status' => 'paid'], ['class' => 'btn btn-sm btn-outline-success']) ?>
				</div>
			</div>
		</div>
	</div>

	<!-- Main Content Grid -->
	<div class="content-grid">
		<!-- Recent Invoices -->
		<div class="content-section recent-invoices mobile-hidden">
			<div class="section-header">
				<h2 class="section-title">
					<?= Yii::t('app', 'Recent {item}', ['item' => Yii::t('app/invoice', 'Invoices')]) ?></h2>
				<?= Html::a(Yii::t('app', 'View All'), ['/invoice/index'], ['class' => 'btn btn-outline-primary btn-sm']) ?>
			</div>
			<div class="section-body">
				<?php if (!empty($recentInvoices)): ?>
				<div class="invoices-list">
					<?php foreach ($recentInvoices as $invoice): ?>
					<div class="invoice-item">
						<div class="invoice-info">
							<div class="invoice-number">
								<?= Html::a($invoice->invoice_number, ['/invoice/view', 'id' => $invoice->id], [
                                        'class' => 'invoice-link'
                                    ]) ?>
							</div>
							<div class="invoice-customer">
								<?= Html::encode($invoice->customer->customer_name) ?>
							</div>
							<div class="invoice-date">
								<?= Yii::$app->formatter->asDate($invoice->invoice_date) ?>
							</div>
						</div>
						<div class="invoice-amount">
							<?= $invoice->formatAmount($invoice->total_amount) ?>
						</div>
						<div class="invoice-status">
							<span class="status-badge status-<?= $invoice->status ?>">
								<?= $invoice->getStatusLabel() ?>
							</span>
						</div>
					</div>
					<?php endforeach; ?>
				</div>
				<?php else: ?>
				<div class="empty-state">
					<i class="fas fa-file-invoice fa-3x"></i>
					<h3><?= Yii::t('app', 'No {item} Yet', ['item' => Yii::t('app/invoice', 'Invoices')]) ?></h3>
					<p><?= Yii::t('app', 'Get started by creating your first invoice') ?>.</p>
					<?= Html::a(Yii::t('app/invoice', 'Create Invoice'), ['/invoice/create'], ['class' => 'btn btn-primary']) ?>
				</div>
				<?php endif; ?>
			</div>
		</div>

		<!-- Quick Actions -->
		<div class="content-section quick-actions">
			<div class="section-header">
				<h2 class="section-title"><?= Yii::t('app', 'Quick Actions') ?></h2>
			</div>
			<div class="section-body">
				<div class="actions-grid">
					<?= Html::a('<i class="fas fa-file-invoice"></i><span>' . Yii::t('app/invoice', 'Create Invoice') . '</span>', ['/invoice/create'], [
                        'class' => 'action-card primary-action'
                    ]) ?>
					<?= Html::a('<i class="fas fa-file-alt"></i><span>' . Yii::t('app/estimate', 'Create Estimate') . '</span>', ['/estimate/create'], [
                        'class' => 'action-card secondary-action'
                    ]) ?>
					<?= Html::a('<i class="fas fa-user-plus"></i><span>' . Yii::t('app/customer', 'Add Customer') . '</span>', ['/customer/create'], [
                        'class' => 'action-card secondary-action'
                    ]) ?>
					<?= Html::a('<i class="fas fa-box"></i><span>' . Yii::t('app', 'Add {item}', ['item' => Yii::t('app/product', 'Product')]) . '</span>', ['/product/create'], [
                        'class' => 'action-card secondary-action'
                    ]) ?>
					<?= Html::a('<i class="fas fa-users"></i><span>' . Yii::t('app', 'Manage {item}', ['item' => Yii::t('app/customer', 'Customers')]) . '</span>', ['/customer/index'], [
                        'class' => 'action-card tertiary-action'
                    ]) ?>
					<?= Html::a('<i class="fas fa-cog"></i><span>' . Yii::t('app', 'Settings') . '</span>', ['/company/settings'], [
                        'class' => 'action-card tertiary-action'
                    ]) ?>
				</div>
			</div>
		</div>
	</div>
</div>

<style>
/* Dashboard Styles */
.dashboard-container {
	max-width: 1600px;
	margin: 0 auto;
	padding: 0 1rem;
}

.dashboard-header {
	margin-bottom: 2rem;
}

.header-content {
	display: flex;
	justify-content: space-between;
	align-items: center;
	padding: 1.5rem 0;
}

.dashboard-title {
	font-size: 2.5rem;
	font-weight: 700;
	color: var(--text-primary);
	margin: 0;
}

.dashboard-subtitle {
	color: var(--text-secondary);
	margin: 0;
	font-size: 1.1rem;
}

.header-actions .btn {
	padding: 0.75rem 1.5rem;
	font-weight: 600;
	border-radius: 8px;
	box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
	transition: all 0.2s ease;
}

.header-actions .btn:hover {
	transform: translateY(-1px);
	box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

/* KPI Grid */
.kpi-grid {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
	gap: 1.5rem;
	margin-bottom: 2rem;
}

.kpi-card {
	background: white;
	border-radius: 12px;
	padding: 1.5rem;
	box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
	transition: all 0.3s ease;
	border: 1px solid var(--border-color);
}

.kpi-card:hover {
	transform: translateY(-2px);
	box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
}

.kpi-header {
	display: flex;
	justify-content: space-between;
	align-items: center;
	margin-bottom: 1rem;
}

.kpi-icon {
	width: 48px;
	height: 48px;
	border-radius: 12px;
	display: flex;
	align-items: center;
	justify-content: center;
	font-size: 20px;
	color: white;
}

.revenue-card .kpi-icon {
	background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}

.paid-card .kpi-icon {
	background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
}

.pending-card .kpi-icon {
	background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
}

.customers-card .kpi-icon {
	background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
}

.kpi-trend {
	width: 32px;
	height: 32px;
	border-radius: 8px;
	display: flex;
	align-items: center;
	justify-content: center;
	font-size: 14px;
}

.kpi-trend.positive {
	background: #dcfce7;
	color: #059669;
}

body.dark-mode .kpi-trend.positive {
	color: #059669 !important;
}

.kpi-trend.warning {
	background: #fef3c7;
	color: #d97706;
}

body.dark-mode .kpi-trend.warning {
	color: #d97706 !important;
}

.kpi-trend.neutral {
	background: #f3f4f6;
	color: #6b7280;
}

body.dark-mode .kpi-trend.neutral {
	color: #6b7280 !important;
}

.kpi-value {
	font-size: 2rem;
	font-weight: 700;
	color: var(--text-primary);
	margin: 0 0 0.5rem 0;
}

.kpi-label {
	color: var(--text-secondary);
	font-weight: 600;
	margin: 0 0 0.25rem 0;
}

.kpi-detail {
	color: var(--text-secondary);
	font-size: 0.875rem;
}

/* Status Overview */
.status-overview {
	background: white;
	border-radius: 12px;
	padding: 1.5rem;
	margin-bottom: 2rem;
	box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
	border: 1px solid var(--border-color);
}

.status-header {
	display: flex;
	justify-content: space-between;
	align-items: center;
	margin-bottom: 1.5rem;
}

.section-title {
	font-size: 1.5rem;
	font-weight: 600;
	color: var(--text-primary);
	margin: 0;
}

.status-cards {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
	gap: 1rem;
}

.status-card {
	text-align: center;
	padding: 1.5rem;
	border-radius: 8px;
	border: 2px solid var(--border-color);
	transition: all 0.2s ease;
}

.status-card:hover {
	border-color: var(--primary-color);
	transform: translateY(-1px);
}

.status-number {
	font-size: 2rem;
	font-weight: 700;
	margin-bottom: 0.5rem;
}

.status-label {
	font-weight: 600;
	color: var(--text-secondary);
	margin-bottom: 1rem;
}

.draft-status .status-number {
	color: #6b7280;
}

.sent-status .status-number {
	color: #f59e0b;
}

.paid-status .status-number {
	color: #10b981;
}

/* Content Grid */
.content-grid {
	display: grid;
	grid-template-columns: 2fr 1fr;
	gap: 2rem;
}

.content-section {
	background: white;
	border-radius: 12px;
	padding: 1.5rem;
	box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
	border: 1px solid var(--border-color);
}

.section-header {
	display: flex;
	justify-content: space-between;
	align-items: center;
	margin-bottom: 1.5rem;
}

/* Recent Invoices */
.invoices-list {
	display: flex;
	flex-direction: column;
	gap: 1rem;
}

.invoice-item {
	display: flex;
	align-items: center;
	justify-content: space-between;
	padding: 1rem;
	border: 1px solid var(--border-color);
	border-radius: 8px;
	transition: all 0.2s ease;
}

.invoice-item:hover {
	background: var(--bg-secondary);
	border-color: var(--primary-color);
}

.invoice-info {
	flex: 1;
}

.invoice-number {
	font-weight: 600;
	margin-bottom: 0.25rem;
}

.invoice-link {
	color: var(--primary-color);
	text-decoration: none;
	font-weight: 600;
}

.invoice-link:hover {
	text-decoration: underline;
}

.invoice-customer {
	color: var(--text-secondary);
	font-size: 0.9rem;
	margin-bottom: 0.25rem;
}

.invoice-date {
	color: var(--text-secondary);
	font-size: 0.85rem;
}

.invoice-amount {
	font-weight: 600;
	font-size: 1.1rem;
	color: var(--text-primary);
	margin-right: 1rem;
}

.status-badge {
	padding: 0.25rem 0.75rem;
	border-radius: 20px;
	font-size: 0.75rem;
	font-weight: 600;
	text-transform: uppercase;
}

.status-draft {
	background: #f3f4f6;
	color: #6b7280;
}

.status-sent {
	background: #fef3c7;
	color: #d97706;
}

body.dark-mode .status-sent {
	color: #d97706 !important;
}

.status-paid {
	background: #dcfce7;
	color: #059669;
}

body.dark-mode .status-paid {
	color: #059669 !important;

}

.status-printed {
	background: #e0e7ff;
	color: #3730a3;
}

body.dark-mode .status-printed {
	color: #3730a3 !important;
}

/* Quick Actions */
.actions-grid {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
	gap: 1rem;
}

.action-card {
	display: flex;
	flex-direction: column;
	align-items: center;
	justify-content: center;
	padding: 1.5rem 1rem;
	border-radius: 8px;
	text-decoration: none;
	transition: all 0.2s ease;
	text-align: center;
	min-height: 100px;
}

.action-card i {
	font-size: 1.5rem;
	margin-bottom: 0.5rem;
}

.action-card span {
	font-weight: 600;
	font-size: 0.9rem;
}

.primary-action {
	background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
	color: white;
}

.primary-action:hover {
	color: white;
	transform: translateY(-2px);
	box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
}

.secondary-action {
	background: #f8fafc;
	color: var(--text-primary);
	border: 1px solid var(--border-color);
}

.secondary-action:hover {
	background: #e2e8f0;
	color: var(--text-primary);
	transform: translateY(-2px);
}

.tertiary-action {
	background: transparent;
	color: var(--text-secondary);
	border: 1px solid var(--border-color);
}

.tertiary-action:hover {
	background: var(--bg-secondary);
	color: var(--text-primary);
	transform: translateY(-2px);
}

/* Empty State */
.empty-state {
	text-align: center;
	padding: 3rem 1rem;
	color: var(--text-secondary);
}

.empty-state i {
	color: var(--text-secondary);
	margin-bottom: 1rem;
}

.empty-state h3 {
	color: var(--text-primary);
	margin-bottom: 0.5rem;
}

.empty-state p {
	margin-bottom: 1.5rem;
}

/* Dark Mode */
.dark-mode .kpi-card,
.dark-mode .status-overview,
.dark-mode .content-section {
	background: #374151;
	border-color: #4b5563;
}

.dark-mode .invoice-item {
	border-color: #4b5563;
}

.dark-mode .invoice-item:hover {
	background: #4b5563;
	border-color: #6b7280;
}

.dark-mode .secondary-action {
	background: #4b5563;
	border-color: #6b7280;
}

.dark-mode .secondary-action:hover {
	background: #6b7280;
}

.dark-mode .tertiary-action {
	border-color: #6b7280;
}

.dark-mode .tertiary-action:hover {
	background: #4b5563;
}

/* Responsive Design */
@media (max-width: 1200px) {
	.content-grid {
		grid-template-columns: 1fr;
	}
}

@media (max-width: 768px) {
	.dashboard-container {
		padding: 0 0.5rem;
	}

	.header-content {
		flex-direction: column;
		gap: 1rem;
		text-align: center;
	}

	.kpi-grid {
		grid-template-columns: 1fr;
	}

	.status-cards {
		grid-template-columns: 1fr;
	}

	.invoice-item {
		flex-direction: column;
		align-items: flex-start;
		gap: 0.5rem;
	}

	.invoice-amount {
		margin-right: 0;
	}

	.actions-grid {
		grid-template-columns: repeat(2, 1fr);
	}
}

@media (max-width: 480px) {
	.actions-grid {
		grid-template-columns: 1fr;
	}
}
</style>