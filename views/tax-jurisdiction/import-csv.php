<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\TaxJurisdiction;

/* @var $this yii\web\View */

$this->title = Yii::t('app', 'Import Tax Rates');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Admin Panel'), 'url' => ['/admin/index']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Tax Management'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

// Register tax management CSS
$this->registerCssFile('@web/css/tax-management.css');
?>
<div class="tax-jurisdiction-import">

	<div class="d-flex justify-content-between align-items-center mb-4">
		<h1><?= Html::encode($this->title) ?></h1>
		<div class="action-buttons tax-action-buttons">
			<?= Html::a('<i class="fas fa-plus mr-2"></i>' . Yii::t('app', 'Add'), ['create'], ['class' => 'btn btn-success']) ?>
			<?= Html::a('<i class="fas fa-upload mr-2"></i>' . Yii::t('app', 'Import'), ['import-csv'], ['class' => 'btn btn-primary active', 'disabled' => true]) ?>
			<?= Html::a('<i class="fas fa-download mr-2"></i>' . Yii::t('app', 'Export'), ['export-csv'], ['class' => 'btn btn-outline-secondary']) ?>
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
					<?= Html::a('
                        <div class="nav-item-card">
                            <i class="fas fa-chart-line text-info"></i>
                            <strong>' . Yii::t('app', 'Tax Statistics') . '</strong>
                            <small class="text-muted">' . Yii::t('app', 'Analysis and reports') . '</small>
                        </div>
                    ', ['stats'], ['class' => 'text-decoration-none']) ?>
				</div>
				<div class="col-md-3">
					<div class="nav-item-card active">
						<i class="fas fa-file-import text-warning"></i>
						<strong><?= Yii::t('app', 'Import Data') ?></strong>
						<small class="text-muted"><?= Yii::t('app', 'Bulk import tax rates') ?></small>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="row">
		<div class="col-md-8">
			<div class="tax-card">
				<div class="card-header tax-card-header">
					<h5 class="mb-0"><i class="fas fa-upload mr-2"></i><?= Yii::t('app', 'Upload CSV File') ?></h5>
				</div>
				<div class="card-body tax-card-body">
					<?php $form = ActiveForm::begin([
                        'action' => ['import-csv'],
                        'method' => 'post',
                        'enableClientValidation' => false,
                        'enableAjaxValidation' => false,
                        'options' => [
                            'enctype' => 'multipart/form-data',
                            'id' => 'import-form'
                        ]
                    ]); ?>

					<div class="form-group">
						<label for="csv_file" class="form-label"><?= Yii::t('app', 'CSV File') ?></label>
						<input type="file" class="form-control" name="csv_file" id="csv_file" accept=".csv" required>
						<small class="form-text text-muted">
							<?= Yii::t('app', 'Maximum file size: 10MB. Only CSV files are accepted.') ?>
						</small>
					</div>

					<div class="form-group">
						<label for="data_source" class="form-label"><?= Yii::t('app', 'Data Source') ?></label>
						<?= Html::dropDownList('data_source', TaxJurisdiction::DATA_SOURCE_AVALARA, 
                            TaxJurisdiction::getDataSourceOptions(), 
                            ['class' => 'form-control', 'id' => 'data_source']
                        ) ?>
						<small class="form-text text-muted">
							<?= Yii::t('app', 'Select the source of this tax rate data. Choose "Avalara" for files from AvaTax API.') ?>
						</small>
					</div>

					<div class="form-group">
						<div class="custom-control custom-checkbox">
							<input type="checkbox" class="custom-control-input" id="replace_existing"
								name="replace_existing" value="1">
							<label class="custom-control-label" for="replace_existing">
								<?= Yii::t('app', 'Replace existing data from this source') ?>
							</label>
							<small class="form-text text-muted">
								<?= Yii::t('app', 'If checked, all existing records with the same data source will be deactivated before importing new data.') ?>
							</small>
						</div>
					</div>

					<div class="form-group">
						<?= Html::submitButton('<i class="fas fa-upload mr-2"></i>' . Yii::t('app', 'Import CSV'), [
                            'class' => 'btn btn-primary btn-lg',
                            'id' => 'import-btn'
                        ]) ?>
					</div>

					<?php ActiveForm::end(); ?>
				</div>
			</div>
		</div>

		<div class="col-md-4">

			<div class="tax-card mb-4">
				<div class="card-header tax-card-header">
					<h5 class="mb-0"><i class="fas fa-download mr-2"></i><?= Yii::t('app', 'Sample CSV') ?></h5>
				</div>
				<div class="card-body tax-card-body">
					<p><?= Yii::t('app', 'Download a sample CSV file to see the correct format:') ?></p>

					<div class="d-grid gap-2">
						<button type="button" class="btn btn-outline-primary btn-sm" id="download-sample">
							<i class="fas fa-download mr-2"></i><?= Yii::t('app', 'Download Sample CSV') ?>
						</button>
					</div>

					<hr>

					<h6><?= Yii::t('app', 'Sample Data Preview') ?>:</h6>
					<small class="text-muted"><?= Yii::t('app', 'Avalara Format') ?>:</small>
					<pre class="p-2 small rounded"><code>State,ZipCode,TaxRegionName,EstimatedCombinedRate,StateRate,EstimatedCountyRate,EstimatedCityRate,EstimatedSpecialRate,RiskLevel
CA,90210,BEVERLY HILLS,0.095000,0.060000,0.010000,0.025000,0.000000,0
NY,10001,NEW YORK,0.082500,0.040000,0.022500,0.020000,0.000000,0</code></pre>
					<small class="text-muted"><?= Yii::t('app', 'Standard Format') ?>:</small>
					<pre class="p-2 small rounded"><code>zip_code,state_code,state_name,county_name,city_name,tax_region_name,combined_rate,state_rate,county_rate,city_rate,special_rate
90210,CA,California,Los Angeles,Beverly Hills,Beverly Hills Tax Region,9.5000,6.0000,1.0000,2.5000,0.0000
10001,NY,New York,New York,New York,Manhattan Tax Region,8.2500,4.0000,2.2500,2.0000,0.0000</code></pre>
				</div>
			</div>
			<div class="tax-card">
				<div class="card-header tax-card-header">
					<h5 class="mb-0"><i
							class="fas fa-exclamation-triangle mr-2"></i><?= Yii::t('app', 'Important Notes') ?></h5>
				</div>
				<div class="card-body tax-card-body">
					<div class="alert alert-warning">
						<ul class="mb-0 small">
							<li><?= Yii::t('app', 'Tax rates should be entered as percentages (e.g., 8.25 for 8.25%)') ?>
							</li>
							<li><?= Yii::t('app', 'Always backup your data before importing') ?></li>
							<li><?= Yii::t('app', 'Large files may take several minutes to process') ?></li>
							<li><?= Yii::t('app', 'Verify all tax rates with official sources') ?></li>
							<li><?= Yii::t('app', 'Import is limited to 10,000 rows for performance') ?></li>
						</ul>
					</div>
				</div>
			</div>
			<div class="tax-card mb-4">
				<div class="card-header tax-card-header">
					<h5 class="mb-0"><i
							class="fas fa-info-circle mr-2"></i><?= Yii::t('app', 'CSV Format Requirements') ?></h5>
				</div>
				<div class="card-body tax-card-body">
					<p class="mb-3"><?= Yii::t('app', 'Your CSV file must include the following columns:') ?></p>

					<div class="table-responsive">
						<table class="table table-sm table-bordered">
							<thead class="thead-light">
								<tr>
									<th><?= Yii::t('app', 'Column') ?></th>
									<th><?= Yii::t('app', 'Required') ?></th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td><code>ZipCode</code> (Avalara) / <code>zip_code</code></td>
									<td><span class="badge badge-danger"><?= Yii::t('app', 'Yes') ?></span></td>
								</tr>
								<tr>
									<td><code>State</code> (Avalara) / <code>state_code</code></td>
									<td><span class="badge badge-danger"><?= Yii::t('app', 'Yes') ?></span></td>
								</tr>
								<tr>
									<td><code>EstimatedCombinedRate</code> (Avalara) / <code>combined_rate</code></td>
									<td><span class="badge badge-danger"><?= Yii::t('app', 'Yes') ?></span></td>
								</tr>
								<tr>
									<td><code>state_name</code></td>
									<td><span class="badge badge-secondary"><?= Yii::t('app', 'No') ?></span></td>
								</tr>
								<tr>
									<td><code>county_name</code></td>
									<td><span class="badge badge-secondary"><?= Yii::t('app', 'No') ?></span></td>
								</tr>
								<tr>
									<td><code>city_name</code></td>
									<td><span class="badge badge-secondary"><?= Yii::t('app', 'No') ?></span></td>
								</tr>
								<tr>
									<td><code>tax_region_name</code></td>
									<td><span class="badge badge-secondary"><?= Yii::t('app', 'No') ?></span></td>
								</tr>
								<tr>
									<td><code>state_rate</code></td>
									<td><span class="badge badge-secondary"><?= Yii::t('app', 'No') ?></span></td>
								</tr>
								<tr>
									<td><code>county_rate</code></td>
									<td><span class="badge badge-secondary"><?= Yii::t('app', 'No') ?></span></td>
								</tr>
								<tr>
									<td><code>city_rate</code></td>
									<td><span class="badge badge-secondary"><?= Yii::t('app', 'No') ?></span></td>
								</tr>
								<tr>
									<td><code>special_rate</code></td>
									<td><span class="badge badge-secondary"><?= Yii::t('app', 'No') ?></span></td>
								</tr>
								<tr>
									<td><code>estimated_population</code></td>
									<td><span class="badge badge-secondary"><?= Yii::t('app', 'No') ?></span></td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
			</div>

		</div>
	</div>

</div>

<script>
$(document).ready(function() {
	// File validation
	$('#csv_file').on('change', function() {
		const file = this.files[0];
		if (file) {
			// Check file size (10MB limit)
			if (file.size > 10 * 1024 * 1024) {
				alert(
					'<?= Yii::t('app', 'File size exceeds 10MB limit. Please select a smaller file.') ?>'
				);
				this.value = '';
				return;
			}

			// Check file extension
			const fileName = file.name.toLowerCase();
			if (!fileName.endsWith('.csv')) {
				alert('<?= Yii::t('app', 'Please select a CSV file (.csv extension required).') ?>');
				this.value = '';
				return;
			}
		}
	});

	// Import button with loading state
	$('#import-btn').on('click', function(e) {
		const file = $('#csv_file')[0].files[0];
		const form = $('#import-form')[0];

		if (!file) {
			alert('<?= Yii::t('app', 'Please select a CSV file to upload.') ?>');
			return false;
		}

		// Check file size (10MB limit)
		if (file.size > 10 * 1024 * 1024) {
			alert(
				'<?= Yii::t('app', 'File size exceeds 10MB limit. Please select a smaller file.') ?>'
			);
			return false;
		}

		$(this).prop('disabled', true).html(
			'<i class="fas fa-spinner fa-spin mr-2"></i><?= Yii::t('app', 'Importing...') ?>');

		// Prevent default and manually submit to avoid client-side validation issues
		e.preventDefault();

		if (form) {
			form.submit();
		} else {
			$(this).prop('disabled', false).html(
				'<i class="fas fa-upload mr-2"></i><?= Yii::t('app', 'Import CSV') ?>');
		}

		// Re-enable button after timeout (fallback)
		setTimeout(() => {
			$(this).prop('disabled', false).html(
				'<i class="fas fa-upload mr-2"></i><?= Yii::t('app', 'Import CSV') ?>');
		}, 30000);
	});

	// Download sample CSV (Avalara format by default)
	$('#download-sample').on('click', function() {
		const dataSource = $('#data_source').val();
		let csvContent, filename;

		if (dataSource === 'avalara') {
			csvContent = `State,ZipCode,TaxRegionName,EstimatedCombinedRate,StateRate,EstimatedCountyRate,EstimatedCityRate,EstimatedSpecialRate,RiskLevel
CA,90210,BEVERLY HILLS,0.095000,0.060000,0.010000,0.025000,0.000000,0
NY,10001,NEW YORK,0.082500,0.040000,0.022500,0.020000,0.000000,0
IL,60601,CHICAGO,0.102500,0.062500,0.017500,0.022500,0.000000,0
TX,75201,DALLAS,0.082500,0.062500,0.005000,0.015000,0.000000,0
FL,33101,MIAMI,0.070000,0.060000,0.005000,0.005000,0.000000,0`;
			filename = 'sample_avalara_tax_rates.csv';
		} else {
			csvContent = `zip_code,state_code,state_name,county_name,city_name,tax_region_name,combined_rate,state_rate,county_rate,city_rate,special_rate,estimated_population
90210,CA,California,Los Angeles,Beverly Hills,Beverly Hills Tax Region,9.5000,6.0000,1.0000,2.5000,0.0000,34000
10001,NY,New York,New York,New York,Manhattan Tax Region,8.2500,4.0000,2.2500,2.0000,0.0000,1600000
60601,IL,Illinois,Cook,Chicago,Chicago Tax Region,10.2500,6.2500,1.7500,2.2500,0.0000,2700000
75201,TX,Texas,Dallas,Dallas,Dallas Tax Region,8.2500,6.2500,0.5000,1.5000,0.0000,1300000
33101,FL,Florida,Miami-Dade,Miami,Miami Tax Region,7.0000,6.0000,0.5000,0.5000,0.0000,470000`;
			filename = 'sample_tax_rates.csv';
		}

		const blob = new Blob([csvContent], {
			type: 'text/csv'
		});
		const url = window.URL.createObjectURL(blob);
		const a = document.createElement('a');
		a.href = url;
		a.download = filename;
		document.body.appendChild(a);
		a.click();
		window.URL.revokeObjectURL(url);
		document.body.removeChild(a);
	});
});
</script>