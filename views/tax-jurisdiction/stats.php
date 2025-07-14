<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $stats array */
/* @var $stateBreakdown array */
/* @var $sourceBreakdown array */
/* @var $recentUpdates app\models\TaxJurisdiction[] */

$this->title = Yii::t('app', 'Tax Statistics');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Admin Panel'), 'url' => ['/admin/index']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Tax Management'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

// Register tax management CSS
$this->registerCssFile('@web/css/tax-management.css');
?>
<div class="tax-jurisdiction-stats">

	<div class="d-flex justify-content-between align-items-center mb-4">
		<h1><?= Html::encode($this->title) ?></h1>
		<div class="action-buttons tax-action-buttons">
			<?= Html::a('<i class="fas fa-plus mr-2"></i>' . Yii::t('app', 'Add'), ['create'], ['class' => 'btn btn-success'])
            ?>
			<?= Html::a('<i class="fas fa-upload mr-2"></i>' . Yii::t('app', 'Import'), ['import-csv'], ['class' => 'btn btn-primary'])
            ?>
			<?= Html::a('<i class="fas fa-download mr-2"></i>' . Yii::t('app', 'Export'), ['export-csv'], ['class' => 'btn btn-outline-secondary'])
            ?>
		</div>
	</div>

	<!-- Tax Management Navigation -->
	<div class="tax-card tax-management-navigation mb-4">
		<div class="card-header tax-card-header">
			<h6 class="mb-0"><i class="fas fa-sitemap mr-2"></i><?= Yii::t('app', 'Tax Management Tools') ?></h6>
		</div>
		<div class="card-body tax-card-body">
			<div class="row">
				<div class="col-md-3">
					<?= Html::a('
                        <div class="nav-item-card">
                            <i class="fas fa-map-marker-alt text-primary"></i>
                            <strong>' . Yii::t('app', 'ZIP Code Tax Rates') . '</strong>
                            <small class="text-muted">' . Yii::t('app', 'Detailed jurisdiction-based rates') . '</small>
                        </div>
                    ', ['index'], ['class' => 'text-decoration-none']) ?>
				</div>
				<div class="col-md-3">
					<?= Html::a('
                        <div class="nav-item-card">
                            <i class="fas fa-percent text-success"></i>
                            <strong>' . Yii::t('app', 'State Tax Rates') . '</strong>
                            <small class="text-muted">' . Yii::t('app', 'State-level rates for calculations') . '</small>
                        </div>
                    ', ['/state-tax-rate/index'], ['class' => 'text-decoration-none']) ?>
				</div>
				<div class="col-md-3">
					<div class="nav-item-card active">
						<i class="fas fa-chart-line text-info"></i>
						<strong><?= Yii::t('app', 'Tax Statistics') ?></strong>
						<small class="text-muted"><?= Yii::t('app', 'Analysis and reports') ?></small>
					</div>
				</div>
				<div class="col-md-3">
					<?= Html::a('
                        <div class="nav-item-card">
                            <i class="fas fa-file-import text-warning"></i>
                            <strong>' . Yii::t('app', 'Import Data') . '</strong>
                            <small class="text-muted">' . Yii::t('app', 'Bulk import tax rates') . '</small>
                        </div>
                    ', ['import-csv'], ['class' => 'text-decoration-none']) ?>
				</div>
			</div>
		</div>
	</div>

	<!-- Overview Statistics -->
	<div class="card mb-4">
		<div class="card-header">
			<h5 class="card-title mb-0"><i class="fas fa-chart-pie mr-2"></i><?= Yii::t('app', 'Overview') ?></h5>
		</div>
		<div class="card-body">
			<div class="row">
				<div class="col-md-3">
					<div class="stats-card stats-card-primary">
						<div class="d-flex justify-content-between">
							<div>
								<h5 class="card-title"><?= Yii::t('app', 'Total Jurisdictions') ?></h5>
								<h2 class="card-text"><?= number_format($stats['total_jurisdictions']) ?></h2>
							</div>
							<div class="card-icon">
								<i class="fas fa-map-marker-alt fa-2x"></i>
							</div>
						</div>
					</div>
				</div>
				<div class="col-md-3">
					<div class="stats-card stats-card-success">
						<div class="d-flex justify-content-between">
							<div>
								<h5 class="card-title"><?= Yii::t('app', 'Active Jurisdictions') ?></h5>
								<h2 class="card-text"><?= number_format($stats['active_jurisdictions']) ?></h2>
							</div>
							<div class="card-icon">
								<i class="fas fa-check-circle fa-2x"></i>
							</div>
						</div>
					</div>
				</div>
				<div class="col-md-3">
					<div class="stats-card stats-card-info">
						<div class="d-flex justify-content-between">
							<div>
								<h5 class="card-title"><?= Yii::t('app', 'States Covered') ?></h5>
								<h2 class="card-text"><?= number_format($stats['states_covered']) ?></h2>
							</div>
							<div class="card-icon">
								<i class="fas fa-flag-usa fa-2x"></i>
							</div>
						</div>
					</div>
				</div>
				<div class="col-md-3">
					<div class="stats-card stats-card-warning">
						<div class="d-flex justify-content-between">
							<div>
								<h5 class="card-title"><?= Yii::t('app', 'Last Updated') ?></h5>
								<h6 class="card-text">
									<?= $stats['last_updated'] ? Yii::$app->formatter->asDate($stats['last_updated']) : Yii::t('app', 'Never') ?>
								</h6>
							</div>
							<div class="card-icon">
								<i class="fas fa-clock fa-2x"></i>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="row">
		<!-- State Breakdown -->
		<div class="col-md-6">
			<div class="card mb-4">
				<div class="card-header">
					<h5 class="mb-0"><i class="fas fa-chart-bar mr-2"></i><?= Yii::t('app', 'Jurisdictions by State') ?>
					</h5>
				</div>
				<div class="card-body">
					<div class="table-responsive" style="max-height: 400px;">
						<table class="table table-sm table-hover">
							<thead>
								<tr>
									<th><?= Yii::t('app', 'State') ?></th>
									<th class="text-right"><?= Yii::t('app', 'Count') ?></th>
									<th class="text-right"><?= Yii::t('app', 'Percentage') ?></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($stateBreakdown as $state): ?>
								<tr>
									<td>
										<strong><?= Html::encode($state['state_code']) ?></strong>
										<?= Html::a('<i class="fas fa-eye ml-2"></i>', ['index', 'state_code' => $state['state_code']], [
                                            'title' => Yii::t('app', 'View jurisdictions for {state}', ['state' => $state['state_code']]),
                                            'class' => 'text-muted'
                                        ]) ?>
									</td>
									<td class="text-right"><?= number_format($state['count']) ?></td>
									<td class="text-right">
										<?php 
                                        $percentage = $stats['active_jurisdictions'] > 0 ? ($state['count'] / $stats['active_jurisdictions']) * 100 : 0;
                                        ?>
										<?= number_format($percentage, 1) ?>%
										<div class="progress mt-1" style="height: 4px;">
											<div class="progress-bar" role="progressbar"
												style="width: <?= $percentage ?>%"></div>
										</div>
									</td>
								</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>

		<!-- Data Source Breakdown -->
		<div class="col-md-6">
			<div class="card mb-4">
				<div class="card-header">
					<h5 class="mb-0"><i class="fas fa-database mr-2"></i><?= Yii::t('app', 'Data Sources') ?></h5>
				</div>
				<div class="card-body">
					<div class="table-responsive">
						<table class="table table-sm table-hover">
							<thead>
								<tr>
									<th><?= Yii::t('app', 'Source') ?></th>
									<th class="text-right"><?= Yii::t('app', 'Count') ?></th>
									<th class="text-right"><?= Yii::t('app', 'Percentage') ?></th>
								</tr>
							</thead>
							<tbody>
								<?php 
                                $sourceOptions = \app\models\TaxJurisdiction::getDataSourceOptions();
                                foreach ($sourceBreakdown as $source): 
                                ?>
								<tr>
									<td>
										<strong><?= Html::encode($sourceOptions[$source['data_source']] ?? $source['data_source']) ?></strong>
										<?= Html::a('<i class="fas fa-eye ml-2"></i>', ['index', 'data_source' => $source['data_source']], [
                                            'title' => Yii::t('app', 'View jurisdictions from {source}', ['source' => $source['data_source']]),
                                            'class' => 'text-muted'
                                        ]) ?>
									</td>
									<td class="text-right"><?= number_format($source['count']) ?></td>
									<td class="text-right">
										<?php 
                                        $percentage = $stats['active_jurisdictions'] > 0 ? ($source['count'] / $stats['active_jurisdictions']) * 100 : 0;
                                        ?>
										<?= number_format($percentage, 1) ?>%
										<div class="progress mt-1" style="height: 4px;">
											<div class="progress-bar bg-info" role="progressbar"
												style="width: <?= $percentage ?>%"></div>
										</div>
									</td>
								</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Recent Updates -->
	<div class="card mb-4">
		<div class="card-header">
			<h5 class="mb-0"><i class="fas fa-history mr-2"></i><?= Yii::t('app', 'Recent Updates') ?></h5>
		</div>
		<div class="card-body">
			<?php if (!empty($recentUpdates)): ?>
			<div class="table-responsive">
				<table class="table table-sm table-hover">
					<thead>
						<tr>
							<th><?= Yii::t('app', 'ZIP Code') ?></th>
							<th><?= Yii::t('app', 'State') ?></th>
							<th><?= Yii::t('app', 'Region') ?></th>
							<th><?= Yii::t('app', 'Combined Rate') ?></th>
							<th><?= Yii::t('app', 'Source') ?></th>
							<th><?= Yii::t('app', 'Updated') ?></th>
							<th><?= Yii::t('app', 'Actions') ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($recentUpdates as $jurisdiction): ?>
						<tr>
							<td><strong><?= Html::encode($jurisdiction->zip_code) ?></strong></td>
							<td><?= Html::encode($jurisdiction->state_code) ?></td>
							<td><?= Html::encode($jurisdiction->tax_region_name ?: ($jurisdiction->city_name . ', ' . $jurisdiction->county_name)) ?>
							</td>
							<td class="text-right">
								<span
									class="badge badge-primary"><?= \app\models\TaxJurisdiction::formatRate($jurisdiction->combined_rate) ?></span>
							</td>
							<td>
								<small><?= Html::encode($sourceOptions[$jurisdiction->data_source] ?? $jurisdiction->data_source) ?></small>
							</td>
							<td>
								<small><?= Yii::$app->formatter->asRelativeTime($jurisdiction->updated_at) ?></small>
							</td>
							<td class="text-center btn-group">
								<?= Html::a('<i class="fas fa-eye"></i>', ['view', 'id' => $jurisdiction->id], [
                                        'class' => 'btn btn-sm btn-outline-info',
                                        'title' => Yii::t('app', 'View')
                                    ]) ?>
							</td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
			<?php else: ?>
			<div class="text-center text-muted py-4">
				<i class="fas fa-inbox fa-3x mb-3"></i>
				<p><?= Yii::t('app', 'No recent updates found') ?></p>
			</div>
			<?php endif; ?>
		</div>
	</div>

	<!-- System Information -->
	<div class="card">
		<div class="card-header">
			<h5 class="mb-0"><i class="fas fa-info-circle mr-2"></i><?= Yii::t('app', 'System Information') ?></h5>
		</div>
		<div class="card-body">
			<div class="row">
				<div class="col-md-6">
					<dl class="row">
						<dt class="col-sm-6"><?= Yii::t('app', 'Database Size') ?>:</dt>
						<dd class="col-sm-6"><?= number_format($stats['total_jurisdictions']) ?>
							<?= Yii::t('app', 'records') ?></dd>

						<dt class="col-sm-6"><?= Yii::t('app', 'Coverage') ?>:</dt>
						<dd class="col-sm-6"><?= number_format($stats['states_covered']) ?>
							<?= Yii::t('app', 'of 50 states') ?></dd>

						<dt class="col-sm-6"><?= Yii::t('app', 'Latest Effective Date') ?>:</dt>
						<dd class="col-sm-6">
							<?= $stats['latest_effective_date'] ? Yii::$app->formatter->asDate($stats['latest_effective_date']) : Yii::t('app', 'N/A') ?>
						</dd>
					</dl>
				</div>
				<div class="col-md-6">
					<div class="alert alert-info-custom">
						<h6><?= Yii::t('app', 'Data Quality Tips') ?>:</h6>
						<ul class="mb-0 small">
							<li><?= Yii::t('app', 'Regular updates ensure accuracy') ?></li>
							<li><?= Yii::t('app', 'Verify rates with official sources') ?></li>
							<li><?= Yii::t('app', 'Monitor for expired jurisdictions') ?></li>
							<li><?= Yii::t('app', 'Keep backup of rate changes') ?></li>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>

</div>