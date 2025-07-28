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

$totalInvoices = Invoice::find()->where(['company_id' => $company->id])->count();
$draftInvoices = Invoice::find()->where(['company_id' => $company->id, 'status' => 'draft'])->count();
$paidInvoices = Invoice::find()->where(['company_id' => $company->id, 'status' => 'paid'])->count();
$sentInvoices = Invoice::find()->where(['company_id' => $company->id, 'status' => 'sent'])->count();
$printedInvoices = Invoice::find()->where(['company_id' => $company->id, 'status' => 'printed'])->count();
$totalAmount = Invoice::find()->where(['company_id' => $company->id])->sum('total_amount') ?: 0;
// Calculate actual paid amount including partial payments from Payment table
$paidAmount = \app\models\Payment::find()
    ->innerJoin('{{%jdosa_invoices}}', '{{%jdosa_invoices}}.id = {{%jdosa_payments}}.invoice_id')
    ->where(['{{%jdosa_invoices}}.company_id' => $company->id])
    ->sum('{{%jdosa_payments}}.amount') ?: 0;
$pendingAmount = $totalAmount - $paidAmount;
$totalCustomers = Customer::find()->where(['company_id' => $company->id])->count();

// Recent invoices
$recentInvoices = Invoice::find()
    ->where(['company_id' => $company->id])
    ->with(['customer'])
    ->orderBy(['invoice_number' => SORT_DESC])
    ->limit(10)
    ->all();

// Calculate conversion rates
$conversionRate = $totalInvoices > 0 ? round(($paidInvoices / $totalInvoices) * 100, 1) : 0;
$averageInvoiceValue = $totalInvoices > 0 ? $totalAmount / $totalInvoices : 0;
?>

<div class="dashboard-container">
	<!-- Compact Header -->
	<div class="dashboard-header">
		<div class="header-content">
			<div class="header-info">
				<h1 class="dashboard-title"><?= Yii::t('app', 'Dashboard') ?></h1>
				<p class="dashboard-subtitle"><?= Html::encode($company->company_name) ?></p>
			</div>
			<div class="header-actions">
				<?= Html::a('<i class="fas fa-plus"></i>' . Yii::t('app/invoice', 'New Invoice'), ['/invoice/create'], [
                    'class' => 'btn btn-primary btn-sm'
                ]) ?>
				<?= Html::a('<i class="fas fa-chart-line"></i>' . Yii::t('app', 'Reports'), ['/invoice/index'], [
                    'class' => 'btn btn-outline-secondary btn-sm ml-2'
                ]) ?>
			</div>
		</div>
	</div>

	<!-- Compact KPI Overview -->
	<div class="kpi-overview">
		<div class="kpi-row">
			<div class="kpi-item revenue">
				<div class="kpi-icon"><i class="fas fa-dollar-sign"></i></div>
				<div class="kpi-content">
					<div class="kpi-value"><?= $company->formatAmount($totalAmount) ?></div>
					<div class="kpi-label"><?= Yii::t('app', 'Total Revenue') ?></div>
				</div>
			</div>
			<div class="kpi-item paid">
				<div class="kpi-icon"><i class="fas fa-check-circle"></i></div>
				<div class="kpi-content">
					<div class="kpi-value"><?= $company->formatAmount($paidAmount) ?></div>
					<div class="kpi-label"><?= Yii::t('app', 'Collected') ?> (<?= $conversionRate ?>%)</div>
				</div>
			</div>
			<div class="kpi-item pending">
				<div class="kpi-icon"><i class="fas fa-clock"></i></div>
				<div class="kpi-content">
					<div class="kpi-value"><?= $company->formatAmount($pendingAmount) ?></div>
					<div class="kpi-label"><?= Yii::t('app', 'Pending') ?> (<?= $sentInvoices ?> invoices)</div>
				</div>
			</div>
			<div class="kpi-item customers">
				<div class="kpi-icon"><i class="fas fa-users"></i></div>
				<div class="kpi-content">
					<div class="kpi-value"><?= $totalCustomers ?></div>
					<div class="kpi-label"><?= Yii::t('app/customer', 'Customers') ?></div>
				</div>
			</div>
		</div>
	</div>

	<!-- Monthly Usage Alert -->
	<?php 
	$user = Yii::$app->user->identity;
	$monthlyCount = $user->getMonthlyInvoiceCount();
	$remainingInvoices = $user->getRemainingInvoices();
	$usagePercentage = $user->getInvoiceUsagePercentage();
	$currentPlan = $user->getCurrentPlan();
	?>
	
	<?php if ($remainingInvoices !== null && $usagePercentage >= 80): ?>
	<div class="row mb-4">
		<div class="col-12">
			<div class="alert <?= $remainingInvoices === 0 ? 'alert-danger' : 'alert-warning' ?> border-0 shadow-sm">
				<div class="row align-items-center">
					<div class="col-md-8">
						<h6 class="alert-heading mb-1">
							<i class="fas <?= $remainingInvoices === 0 ? 'fa-exclamation-triangle' : 'fa-info-circle' ?> mr-2"></i>
							<?= $remainingInvoices === 0 ? Yii::t('app', 'Invoice Limit Reached') : Yii::t('app', 'Invoice Limit Warning') ?>
						</h6>
						<p class="mb-0">
							<?php if ($remainingInvoices === 0): ?>
								<?= Yii::t('app', 'You have used all {limit} invoices for this month on your {plan} plan.', [
									'limit' => $currentPlan ? $currentPlan->getMonthlyInvoiceLimit() : 5,
									'plan' => $currentPlan ? $currentPlan->name : 'Free'
								]) ?>
							<?php else: ?>
								<?= Yii::t('app', 'You have {remaining} invoices left this month. Consider upgrading for unlimited invoices.', [
									'remaining' => $remainingInvoices
								]) ?>
							<?php endif; ?>
						</p>
					</div>
					<div class="col-md-4 text-right">
						<?= Html::a(
							'<i class="fas fa-arrow-up mr-1"></i>' . Yii::t('app', 'Upgrade Plan'),
							['/subscription/my-account'],
							['class' => 'btn ' . ($remainingInvoices === 0 ? 'btn-light' : 'btn-outline-primary')]
						) ?>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php endif; ?>

	<!-- Main Dashboard Grid -->
	<div class="dashboard-grid">
		<!-- Left Column -->
		<div class="dashboard-left">
			<!-- Invoice Status Cards -->
			<div class="status-cards">
				<div class="status-card draft-card"
					onclick="location.href='<?= Url::to(['/invoice/index', 'status' => 'draft']) ?>'">
					<div class="status-header">
						<span class="status-count"><?= $draftInvoices ?></span>
						<span class="status-icon"><i class="fas fa-file-alt"></i></span>
					</div>
					<div class="status-label"><?= Yii::t('app/invoice', 'Draft') ?></div>
				</div>
				<div class="status-card printed-card"
					onclick="location.href='<?= Url::to(['/invoice/index', 'status' => 'printed']) ?>'">
					<div class="status-header">
						<span class="status-count"><?= $printedInvoices ?></span>
						<span class="status-icon"><i class="fas fa-print"></i></span>
					</div>
					<div class="status-label"><?= Yii::t('app/invoice', 'Printed') ?></div>
				</div>
				<div class="status-card sent-card"
					onclick="location.href='<?= Url::to(['/invoice/index', 'status' => 'sent']) ?>'">
					<div class="status-header">
						<span class="status-count"><?= $sentInvoices ?></span>
						<span class="status-icon"><i class="fas fa-paper-plane"></i></span>
					</div>
					<div class="status-label"><?= Yii::t('app/invoice', 'Sent') ?></div>
				</div>
				<div class="status-card paid-card"
					onclick="location.href='<?= Url::to(['/invoice/index', 'status' => 'paid']) ?>'">
					<div class="status-header">
						<span class="status-count"><?= $paidInvoices ?></span>
						<span class="status-icon"><i class="fas fa-check"></i></span>
					</div>
					<div class="status-label"><?= Yii::t('app/invoice', 'Paid') ?></div>
				</div>
			</div>

			<!-- Recent Invoices Compact -->
			<div class="recent-section">
				<div class="section-header">
					<h5><?= Yii::t('app', 'Recent Invoices') ?></h5>
					<?= Html::a(Yii::t('app', 'View All'), ['/invoice/index'], ['class' => 'btn-link']) ?>
				</div>
				<div class="recent-list">
					<?php if (!empty($recentInvoices)): ?>
					<?php foreach ($recentInvoices as $invoice): ?>
					<div class="recent-item">
						<div class="recent-info">
							<span
								class="invoice-number"><?= Html::a($invoice->invoice_number, ['/invoice/view', 'id' => $invoice->id]) ?></span>
							<span class="customer-name"><?= Html::encode($invoice->customer->customer_name) ?></span>
						</div>
						<div class="recent-meta">
							<span class="amount"><?= $invoice->formatAmount($invoice->total_amount) ?></span>
							<span
								class="status-badge status-<?= $invoice->status ?>"><?= $invoice->getStatusLabel() ?></span>
						</div>
					</div>
					<?php endforeach; ?>
					<?php else: ?>
					<div class="empty-state-compact">
						<p><?= Yii::t('app', 'No invoices yet') ?></p>
						<?= Html::a(Yii::t('app/invoice', 'Create First Invoice'), ['/invoice/create'], ['class' => 'btn btn-primary btn-sm']) ?>
					</div>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<!-- Right Column -->
		<div class="dashboard-right">
			<!-- Quick Actions Compact -->
			<div class="quick-actions">
				<h5 class="mb-4"><?= Yii::t('app', 'Quick Actions') ?></h5>
				<div class="action-grid">
					<?= Html::a('<i class="fas fa-file-invoice"></i>' . Yii::t('app/invoice', 'Invoice'), ['/invoice/create'], [
                        'class' => 'action-btn secondary'
                    ]) ?>
					<?= Html::a('<i class="fas fa-file-alt"></i>' . Yii::t('app/estimate', 'Estimate'), ['/estimate/create'], [
                        'class' => 'action-btn secondary'
                    ]) ?>
					<?= Html::a('<i class="fas fa-user-plus"></i>' . Yii::t('app/customer', 'Customer'), ['/customer/create'], [
                        'class' => 'action-btn secondary'
                    ]) ?>
					<?= Html::a('<i class="fas fa-box"></i>' . Yii::t('app/product', 'Product'), ['/product/create'], [
                        'class' => 'action-btn secondary'
                    ]) ?>
					<?= Html::a('<i class="fas fa-cog"></i>' . Yii::t('app/nav', 'Settings'), ['/company/settings'], [
                        'class' => 'action-btn secondary'
                    ]) ?>
					<?= Html::a('<i class="fas fa-key"></i>' . Yii::t('app/nav', 'Change Password'), ['/site/change-password'], [
                        'class' => 'action-btn secondary'
                    ]) ?>
				</div>
			</div>

			<!-- Quick Stats -->
			<div class="quick-stats">
				<h3><?= Yii::t('app', 'Summary') ?></h3>
				<div class="stats-list">
					<div class="stat-item">
						<span class="stat-label"><?= Yii::t('app', 'Total Invoices') ?></span>
						<span class="stat-value"><?= $totalInvoices ?></span>
					</div>
					<div class="stat-item">
						<span class="stat-label"><?= Yii::t('app', 'Average Invoice') ?></span>
						<span class="stat-value"><?= $company->formatAmount($averageInvoiceValue) ?></span>
					</div>
					<div class="stat-item">
						<span class="stat-label"><?= Yii::t('app', 'Collection Rate') ?></span>
						<span class="stat-value"><?= $conversionRate ?>%</span>
					</div>
					<div class="stat-item">
						<span class="stat-label"><?= Yii::t('app', 'Avg per Customer') ?></span>
						<span
							class="stat-value"><?= $totalCustomers > 0 ? round($totalInvoices / $totalCustomers, 1) : 0 ?></span>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<style>
/* Compact Dashboard Styles */
.dashboard-container {
	max-width: 1400px;
	margin: 0 auto;
	padding: 0 1rem;
}

/* Compact Header */
.dashboard-header {
	margin-bottom: 1.5rem;
}

.header-content {
	display: flex;
	justify-content: space-between;
	align-items: center;
	padding: 1rem 0;
	border-bottom: 1px solid var(--border-color);
}

.dashboard-title {
	font-size: 1.8rem;
	font-weight: 700;
	color: var(--text-primary);
	margin: 0;
}

.dashboard-subtitle {
	color: var(--text-secondary);
	margin: 0;
	font-size: 0.9rem;
	font-weight: 500;
}

.header-actions .btn {
	padding: 0.5rem 1rem;
	font-weight: 600;
	border-radius: 6px;
	font-size: 0.875rem;
}

/* Compact KPI Overview */
.kpi-overview {
	margin-bottom: 1.5rem;
	background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
	border-radius: 8px;
	border: 1px solid var(--border-color);
	overflow: hidden;
}

.kpi-row {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
}

.kpi-item {
	display: flex;
	align-items: center;
	padding: 1rem;
	border-right: 1px solid var(--border-color);
	transition: background-color 0.2s ease;
}

.kpi-item:last-child {
	border-right: none;
}

.kpi-item:hover {
	background: var(--bg-secondary);
}

.kpi-icon {
	width: 40px;
	height: 40px;
	border-radius: 8px;
	display: flex;
	align-items: center;
	justify-content: center;
	font-size: 18px;
	color: white;
	margin-right: 1rem;
	flex-shrink: 0;
}

.revenue .kpi-icon {
	background: linear-gradient(135deg, #10b981, #059669);
}

.paid .kpi-icon {
	background: linear-gradient(135deg, #3b82f6, #2563eb);
}

.pending .kpi-icon {
	background: linear-gradient(135deg, #f59e0b, #d97706);
}

.customers .kpi-icon {
	background: linear-gradient(135deg, #8b5cf6, #7c3aed);
}

.kpi-content {
	flex: 1;
}

.kpi-value {
	font-size: 1.5rem;
	font-weight: 700;
	color: var(--text-primary);
	margin: 0 0 0.25rem 0;
	line-height: 1;
}

.kpi-label {
	color: var(--text-secondary);
	font-size: 0.875rem;
	margin: 0;
	line-height: 1;
}

/* Dashboard Grid */
.dashboard-grid {
	display: grid;
	grid-template-columns: 2fr 1fr;
	gap: 1.5rem;
}

.dashboard-left,
.dashboard-right {
	display: flex;
	flex-direction: column;
	gap: 1.5rem;
}

/* Status Cards */
.status-cards {
	display: grid;
	grid-template-columns: repeat(4, 1fr);
	gap: 1rem;
}

.status-card {
	border: 1px solid var(--border-color);
	border-radius: 8px;
	padding: 1rem;
	text-align: center;
	cursor: pointer;
	transition: all 0.2s ease;
}

.draft-card {
	background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
}

.sent-card {
	background: linear-gradient(135deg, #fefce8 0%, #fef3c7 100%);
}

.paid-card {
	background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
}

.printed-card {
	background: linear-gradient(135deg, #fef7ff 0%, #fae8ff 100%);
}

.status-card:hover {
	border-color: var(--primary-color);
	transform: translateY(-1px);
	box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.status-header {
	display: flex;
	justify-content: space-between;
	align-items: center;
	margin-bottom: 0.5rem;
}

.status-count {
	font-size: 1.5rem;
	font-weight: 700;
}

.status-icon {
	opacity: 0.6;
	font-size: 1.2rem;
}

.status-label {
	color: var(--text-secondary);
	font-size: 0.875rem;
	font-weight: 500;
}

.draft-card .status-count {
	color: #6b7280;
}

.sent-card .status-count {
	color: #f59e0b;
}

.paid-card .status-count {
	color: #10b981;
}

.printed-card .status-count {
	color: #a855f7;
}

/* Recent Section */
.recent-section {
	background: linear-gradient(135deg, #fefbf3 0%, #f9f6ec 100%);
	border: 1px solid var(--border-color);
	border-radius: 8px;
	padding: 1rem;
}

.section-header {
	display: flex;
	justify-content: space-between;
	align-items: center;
	margin-bottom: 1rem;
	padding-bottom: 0.5rem;
	border-bottom: 1px solid var(--border-color);
}

.section-header h3 {
	font-size: 1.1rem;
	font-weight: 600;
	color: white !important;
	margin: 0;
	padding: 0.75rem 1rem;
	background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
	border-radius: var(--radius-md);
	display: inline-block;
	min-width: 150px;
	text-align: center;
}

.btn-link {
	color: var(--primary-color);
	text-decoration: none;
	font-size: 0.875rem;
	font-weight: 500;
}

.btn-link:hover {
	text-decoration: underline;
}

.recent-list {
	display: flex;
	flex-direction: column;
	gap: 0.75rem;
}

.recent-item {
	display: flex;
	justify-content: space-between;
	align-items: center;
	padding: 0.75rem;
	border: 1px solid var(--border-color);
	border-radius: 6px;
	transition: all 0.2s ease;
}

.recent-item:hover {
	background: var(--bg-secondary);
	border-color: var(--primary-color);
}

.recent-info {
	flex: 1;
	display: flex;
	flex-direction: column;
	gap: 0.25rem;
}

.invoice-number a {
	color: var(--primary-color);
	text-decoration: none;
	font-weight: 600;
	font-size: 0.9rem;
}

.invoice-number a:hover {
	text-decoration: underline;
}

.customer-name {
	color: var(--text-secondary);
	font-size: 0.8rem;
}

.recent-meta {
	text-align: right;
	display: flex;
	flex-direction: column;
	gap: 0.25rem;
	align-items: flex-end;
}

.amount {
	font-weight: 600;
	font-size: 0.9rem;
	color: var(--text-primary);
}

.status-badge {
	padding: 0.2rem 0.6rem;
	border-radius: 12px;
	font-size: 0.7rem;
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

.status-paid {
	background: #dcfce7;
	color: #059669;
}

/* Right Column Sections */
.dashboard-right>div {
	border: 1px solid var(--border-color);
	border-radius: 8px;
	padding: 1rem;
}

.quick-actions {
	background: linear-gradient(135deg, #fdf4ff 0%, #f3e8ff 100%);
}

.quick-stats {
	background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
}

.management-links {
	background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
}

.dashboard-right h3 {
	font-size: 1.1rem;
	font-weight: 600;
	color: white !important;
	margin: 0 0 1rem 0;
	padding: 0.75rem 1rem;
	background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
	border-radius: var(--radius-md);
	text-align: center;
	border-bottom: none;
}

/* Quick Actions */
.action-grid {
	display: grid;
	grid-template-columns: repeat(2, 1fr);
	gap: 0.75rem;
}

.action-btn {
	display: flex;
	flex-direction: column;
	align-items: center;
	justify-content: center;
	padding: 1rem 0.5rem;
	border-radius: 6px;
	text-decoration: none;
	transition: all 0.2s ease;
	text-align: center;
	font-size: 0.875rem;
	font-weight: 600;
	min-height: 80px;
}

.action-btn i {
	font-size: 1.2rem;
	margin-bottom: 0.5rem;
}

.action-btn.primary {
	background: linear-gradient(135deg, #4f46e5, #7c3aed);
	color: white;
}

.action-btn.primary:hover {
	color: white;
	transform: translateY(-1px);
	box-shadow: 0 4px 8px rgba(79, 70, 229, 0.3);
}

.action-btn.secondary {
	background: var(--bg-secondary);
	color: var(--text-primary);
	border: 1px solid var(--border-color);
}

.action-btn.secondary:hover {
	background: #e2e8f0;
	color: var(--text-primary);
	transform: translateY(-1px);
}

/* Quick Stats */
.stats-list {
	display: flex;
	flex-direction: column;
	gap: 0.75rem;
}

.stat-item {
	display: flex;
	justify-content: space-between;
	align-items: center;
	padding: 0.5rem 0;
	border-bottom: 1px solid var(--border-color);
}

.stat-item:last-child {
	border-bottom: none;
}

.stat-label {
	color: var(--text-secondary);
	font-size: 0.875rem;
}

.stat-value {
	font-weight: 600;
	color: var(--text-primary);
	font-size: 0.875rem;
}

/* Management Links */
.link-list {
	display: flex;
	flex-direction: column;
	gap: 0.5rem;
}

.mgmt-link {
	display: flex;
	align-items: center;
	padding: 0.75rem;
	color: var(--text-primary);
	text-decoration: none;
	border-radius: 6px;
	transition: all 0.2s ease;
	font-size: 0.875rem;
	font-weight: 500;
}

.mgmt-link:hover {
	background: var(--bg-secondary);
	color: var(--text-primary);
	transform: translateX(2px);
}

.mgmt-link i {
	margin-right: 0.75rem;
	width: 16px;
	text-align: center;
	opacity: 0.7;
}

/* Empty State */
.empty-state-compact {
	text-align: center;
	padding: 2rem 1rem;
	color: var(--text-secondary);
}

.empty-state-compact p {
	margin-bottom: 1rem;
	font-size: 0.9rem;
}

/* Dark Mode */
.dark-mode .kpi-overview {
	background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
	border-color: #4b5563;
}

.dark-mode .status-card {
	border-color: #4b5563;
}

.dark-mode .draft-card {
	background: linear-gradient(135deg, #374151 0%, #4b5563 100%);
}

.dark-mode .sent-card {
	background: linear-gradient(135deg, #422006 0%, #451a03 100%);
}

.dark-mode .paid-card {
	background: linear-gradient(135deg, #064e3b 0%, #065f46 100%);
}

.dark-mode .printed-card {
	background: linear-gradient(135deg, #581c87 0%, #6b21a8 100%);
}

.dark-mode .recent-section {
	background: linear-gradient(135deg, #1f2937 0%, #374151 100%);
	border-color: #4b5563;
}

.dark-mode .quick-actions {
	background: linear-gradient(135deg, #2d1b69 0%, #3730a3 100%);
	border-color: #4b5563;
}

.dark-mode .quick-stats {
	background: linear-gradient(135deg, #14532d 0%, #166534 100%);
	border-color: #4b5563;
}

.dark-mode .management-links {
	background: linear-gradient(135deg, #1e3a8a 0%, #1d4ed8 100%);
	border-color: #4b5563;
}

.dark-mode .dashboard-right h3,
.dark-mode .section-header h3 {
	background: linear-gradient(135deg, #4c51bf 0%, #553c9a 100%);
	color: white !important;
}

.dark-mode .kpi-item:hover,
.dark-mode .recent-item:hover {
	background: #4b5563;
}

.dark-mode .action-btn.secondary {
	background: #4b5563;
	border-color: #6b7280;
}

.dark-mode .action-btn.secondary:hover {
	background: #6b7280;
}

/* Dark mode status badge colors */
body.dark-mode .status-sent {
	color: #d97706 !important;
}

body.dark-mode .status-paid {
	color: #059669 !important;
}

/* Responsive Design */
@media (max-width: 1200px) {
	.dashboard-grid {
		grid-template-columns: 1fr;
	}

	.kpi-row {
		grid-template-columns: repeat(2, 1fr);
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

	.kpi-row {
		grid-template-columns: 1fr;
	}

	.kpi-item {
		border-right: none;
		border-bottom: 1px solid var(--border-color);
	}

	.kpi-item:last-child {
		border-bottom: none;
	}

	.status-cards {
		grid-template-columns: 1fr;
	}

	.action-grid {
		grid-template-columns: 1fr;
	}

	.recent-item {
		flex-direction: column;
		align-items: flex-start;
		gap: 0.5rem;
	}

	.recent-meta {
		align-items: flex-start;
		flex-direction: row;
		gap: 1rem;
	}
}
</style>