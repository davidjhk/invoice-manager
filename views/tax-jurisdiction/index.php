<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use app\models\TaxJurisdiction;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $stateCode string */
/* @var $zipCode string */
/* @var $dataSource string */
/* @var $activeOnly bool */

$this->title = Yii::t('app', 'Tax Management');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Admin Panel'), 'url' => ['/admin/index']];
$this->params['breadcrumbs'][] = $this->title;

// Register tax management CSS
$this->registerCssFile('@web/css/tax-management.css');
?>
<div class="tax-jurisdiction-index">

	<div class="d-flex justify-content-between align-items-center mb-4">
		<h1><?= Html::encode($this->title) ?></h1>
		<div class="action-buttons tax-action-buttons">
			<?= Html::a('<i class="fas fa-plus mr-2"></i>' . Yii::t('app', 'Add'), ['create'], ['class' => 'btn btn-success']) ?>
			<?= Html::a('<i class="fas fa-upload mr-2"></i>' . Yii::t('app', 'Import'), ['import-csv'], ['class' => 'btn btn-primary']) ?>
			<?= Html::a('<i class="fas fa-download mr-2"></i>' . Yii::t('app', 'Export'), ['export-csv'] + Yii::$app->request->queryParams, ['class' => 'btn btn-outline-secondary']) ?>
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
					<div class="nav-item-card active">
						<i class="fas fa-map-marker-alt text-primary"></i>
						<strong><?= Yii::t('app', 'ZIP Code Tax Rates') ?></strong>
						<small class="text-muted"><?= Yii::t('app', 'Detailed jurisdiction-based rates') ?></small>
					</div>
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

	<div class="alert-info-custom mb-4">
		<i class="fas fa-info-circle mr-2"></i>
		<strong><?= Yii::t('app', 'Tax Management') ?>:</strong>
		<?= Yii::t('app', 'Manage US sales tax rates by ZIP code and jurisdiction for accurate tax calculations') ?>.
	</div>

	<!-- Filter Form -->
	<div class="card mb-4">
		<div class="card-header">
			<h5 class="mb-0"><i class="fas fa-filter mr-2"></i><?= Yii::t('app', 'Filter') ?></h5>
		</div>
		<div class="card-body">
			<?php $form = \yii\widgets\ActiveForm::begin(['method' => 'get']); ?>
			<div class="row">
				<div class="col-md-2">
					<?= Html::label(Yii::t('app', 'State Code'), 'state_code', ['class' => 'form-label']) ?>
					<?= Html::input('text', 'state_code', $stateCode, [
                        'class' => 'form-control',
                        'placeholder' => 'CA, NY, TX...',
                        'maxlength' => 2
                    ]) ?>
				</div>
				<div class="col-md-3">
					<?= Html::label(Yii::t('app', 'ZIP Code'), 'zip_code', ['class' => 'form-label']) ?>
					<?= Html::input('text', 'zip_code', $zipCode, [
                        'class' => 'form-control',
                        'placeholder' => '90210, 10001...'
                    ]) ?>
				</div>
				<div class="col-md-2">
					<?= Html::label(Yii::t('app', 'Data Source'), 'data_source', ['class' => 'form-label']) ?>
					<?= Html::dropDownList('data_source', $dataSource, 
                        ['' => Yii::t('app', 'All Sources')] + TaxJurisdiction::getDataSourceOptions(), 
                        ['class' => 'form-control']
                    ) ?>
				</div>
				<div class="col-md-2">
					<?= Html::label(Yii::t('app', 'Status'), 'active_only', ['class' => 'form-label']) ?>
					<?= Html::dropDownList('active_only', $activeOnly, [
                        1 => Yii::t('app', 'Active Only'),
                        0 => Yii::t('app', 'All Records')
                    ], ['class' => 'form-control']) ?>
				</div>
				<div class="col-md-3">
					<label class="form-label">&nbsp;</label>
					<div class="d-flex gap-2">
						<?= Html::submitButton('<i class="fas fa-search mr-2"></i>' . Yii::t('app', 'Filter'), ['class' => 'btn btn-primary']) ?>
						<?= Html::a('<i class="fas fa-times mr-2"></i>' . Yii::t('app', 'Clear'), ['index'], ['class' => 'btn btn-secondary']) ?>
					</div>
				</div>
			</div>
			<?php \yii\widgets\ActiveForm::end(); ?>
		</div>
	</div>

	<!-- Quick Lookup -->
	<div class="card mb-4">
		<div class="card-header">
			<h5 class="mb-0"><i class="fas fa-search mr-2"></i><?= Yii::t('app', 'Quick ZIP Code Lookup') ?></h5>
		</div>
		<div class="card-body">
			<div class="row">
				<div class="col-md-4">
					<div class="input-group">
						<?= Html::input('text', 'lookup_zip', '', [
                            'id' => 'lookup-zip',
                            'class' => 'form-control',
                            'placeholder' => Yii::t('app', 'Enter ZIP code (e.g., 90210)')
                        ]) ?>
						<div class="input-group-append">
							<button class="btn btn-outline-primary" type="button" id="lookup-btn">
								<i class="fas fa-search"></i>
							</button>
						</div>
					</div>
				</div>
				<div class="col-md-8">
					<div id="lookup-result" class="mt-2"></div>
				</div>
			</div>
		</div>
	</div>

	<?php Pjax::begin(); ?>

	<div class="card">
		<div class="card-body">
			<!-- Bulk Actions -->
			<?= Html::beginForm(['bulk-operation'], 'post', ['id' => 'bulk-form']) ?>
			<div class="row align-items-end mb-3">
				<div class="col-md-3">
					<?= Html::dropDownList('operation', '', [
                        '' => Yii::t('app', 'Select Action'),
                        'activate' => Yii::t('app', 'Activate'),
                        'deactivate' => Yii::t('app', 'Deactivate'),
                        'verify' => Yii::t('app', 'Mark as Verified'),
                        'delete' => Yii::t('app', 'Delete'),
                    ], ['class' => 'form-control', 'id' => 'bulk-operation']) ?>
				</div>
				<div class="col-md-3">
					<?= Html::submitButton('<i class="fas fa-play mr-2"></i>' . Yii::t('app', 'Execute'), [
                        'class' => 'btn btn-warning',
                        'id' => 'bulk-submit',
                        'disabled' => true
                    ]) ?>
				</div>
				<div class="col-md-6 text-right">
					<small class="text-muted">
						<span id="selected-count">0</span> <?= Yii::t('app', 'items selected') ?>
					</small>
				</div>
			</div>
			<?= Html::endForm() ?>

			<div class="table-container">
				<?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'options' => ['class' => 'tax-grid-view'],
                    'tableOptions' => ['class' => 'table table-striped table-hover'],
                    'layout' => "{summary}\n{items}\n{pager}",
        'columns' => [
            [
                'class' => 'yii\grid\CheckboxColumn',
                'checkboxOptions' => function ($model, $key, $index, $column) {
                    return ['value' => $model->id, 'class' => 'bulk-checkbox'];
                },
            ],
            [
                'attribute' => 'zip_code',
                'label' => Yii::t('app', 'ZIP Code'),
                'format' => 'text',
                'options' => ['style' => 'width: 100px;'],
            ],
            [
                'attribute' => 'state_code',
                'label' => Yii::t('app', 'State'),
                'format' => 'text',
                'options' => ['style' => 'width: 60px;'],
            ],
            [
                'attribute' => 'tax_region_name',
                'label' => Yii::t('app', 'Tax Region'),
                'format' => 'text',
                'value' => function ($model) {
                    return $model->tax_region_name ?: ($model->city_name . ', ' . $model->county_name);
                }
            ],
            [
                'attribute' => 'combined_rate',
                'label' => Yii::t('app', 'Total Rate'),
                'format' => 'text',
                'value' => function ($model) {
                    return TaxJurisdiction::formatRate($model->combined_rate);
                },
                'options' => ['style' => 'width: 100px; text-align: right;'],
                'contentOptions' => ['class' => 'text-right font-weight-bold text-primary'],
            ],
            [
                'label' => Yii::t('app', 'Rate Breakdown'),
                'format' => 'html',
                'value' => function ($model) {
                    $breakdown = [];
                    
                    // Get actual state tax rate from StateTaxRate table
                    $stateRateInfo = $model->getStateTaxRateInfo();
                    $actualStateRate = $stateRateInfo['actual_base_rate'];
                    $storedStateRate = $model->state_rate;
                    $hasMismatch = $stateRateInfo['has_mismatch'];
                    
                    // Display state rate with indicator if there's a mismatch
                    if ($actualStateRate > 0) {
                        $stateDisplay = 'State: ' . TaxJurisdiction::formatRate($actualStateRate);
                        if ($hasMismatch) {
                            $stateDisplay .= ' <span class="badge badge-warning" title="Stored: ' . TaxJurisdiction::formatRate($storedStateRate) . '"><i class="fas fa-exclamation-triangle"></i></span>';
                        }
                        $breakdown[] = $stateDisplay;
                    }
                    
                    if ($model->county_rate > 0) $breakdown[] = 'County: ' . TaxJurisdiction::formatRate($model->county_rate);
                    if ($model->city_rate > 0) $breakdown[] = 'City: ' . TaxJurisdiction::formatRate($model->city_rate);
                    if ($model->special_rate > 0) $breakdown[] = 'Special: ' . TaxJurisdiction::formatRate($model->special_rate);
                    
                    return '<small>' . implode('<br>', $breakdown) . '</small>';
                },
                'options' => ['style' => 'width: 220px;'],
            ],
            [
                'attribute' => 'data_source',
                'label' => Yii::t('app', 'Source'),
                'format' => 'text',
                'value' => function ($model) {
                    $sources = TaxJurisdiction::getDataSourceOptions();
                    return $sources[$model->data_source] ?? $model->data_source;
                },
                'options' => ['style' => 'width: 100px;'],
            ],
            [
                'attribute' => 'effective_date',
                'label' => Yii::t('app', 'Effective Date'),
                'format' => 'date',
                'options' => ['style' => 'width: 120px;'],
            ],
            [
                'attribute' => 'is_active',
                'label' => Yii::t('app', 'Status'),
                'format' => 'html',
                'value' => function ($model) {
                    if ($model->is_active) {
                        $class = 'success';
                        $icon = 'check-circle';
                        $text = Yii::t('app', 'Active');
                    } else {
                        $class = 'secondary';
                        $icon = 'times-circle';
                        $text = Yii::t('app', 'Inactive');
                    }
                    return "<span class=\"badge badge-{$class}\"><i class=\"fas fa-{$icon} mr-1\"></i>{$text}</span>";
                },
                'options' => ['style' => 'width: 100px;'],
                'contentOptions' => ['class' => 'text-center'],
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'header' => Yii::t('app', 'Actions'),
                'template' => '{view} {update} {delete}',
                'options' => ['style' => 'width: 120px;'],
                'contentOptions' => ['class' => 'text-center btn-group','style' => 'width: 80px;'],
                'buttons' => [
                    'view' => function ($url, $model, $key) {
                        return Html::a('<i class="fas fa-eye"></i>', $url, [
                            'title' => Yii::t('app', 'View'),
                            'class' => 'btn btn-outline-info btn-sm mr-1',
                            'data-toggle' => 'tooltip',
                        ]);
                    },
                    'update' => function ($url, $model, $key) {
                        return Html::a('<i class="fas fa-edit"></i>', $url, [
                            'title' => Yii::t('app', 'Update'),
                            'class' => 'btn btn-outline-primary btn-sm mr-1',
                            'data-toggle' => 'tooltip',
                        ]);
                    },
                    'delete' => function ($url, $model, $key) {
                        return Html::a('<i class="fas fa-trash"></i>', $url, [
                            'title' => Yii::t('app', 'Delete'),
                            'class' => 'btn btn-outline-danger btn-sm',
                            'data' => [
                                'confirm' => Yii::t('app', 'Are you sure you want to delete this tax jurisdiction?'),
                                'method' => 'post',
                            ],
                            'data-toggle' => 'tooltip',
                        ]);
                    },
                ],
            ],
        ],
    ]); ?>
			</div>
		</div>
	</div>

	<?php Pjax::end(); ?>

</div>

<?php
$this->registerJs("
// Quick ZIP code lookup functionality
$('#lookup-btn').click(function() {
    var zipCode = $('#lookup-zip').val().trim();
    if (!zipCode) {
        alert('" . Yii::t('app', 'Please enter a ZIP code') . "');
        return;
    }
    
    var btn = $(this);
    var originalHtml = btn.html();
    btn.html('<i class=\"fas fa-spinner fa-spin\"></i>').prop('disabled', true);
    
    $.ajax({
        url: '" . \yii\helpers\Url::to(['lookup']) . "',
        type: 'GET',
        data: { zipCode: zipCode },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                var data = response.data;
                var html = '<div class=\"alert alert-success\">' +
                    '<strong>' + data.zip_code + ' - ' + data.city_name + ', ' + data.state_code + '</strong><br>' +
                    '<div class=\"row mt-2\">' +
                        '<div class=\"col-md-6\">' +
                            '<strong>" . Yii::t('app', 'Tax Rates') . ":</strong><br>' +
                            '" . Yii::t('app', 'State') . ": ' + (data.state_rate * 100).toFixed(4) + '%<br>' +
                            '" . Yii::t('app', 'County') . ": ' + (data.county_rate * 100).toFixed(4) + '%<br>' +
                            '" . Yii::t('app', 'City') . ": ' + (data.city_rate * 100).toFixed(4) + '%<br>' +
                            (data.special_rate > 0 ? '" . Yii::t('app', 'Special') . ": ' + (data.special_rate * 100).toFixed(4) + '%<br>' : '') +
                        '</div>' +
                        '<div class=\"col-md-6\">' +
                            '<strong>" . Yii::t('app', 'Total Rate') . ": ' + (data.combined_rate * 100).toFixed(4) + '%</strong><br>' +
                            '<small class=\"text-muted\">" . Yii::t('app', 'Effective Date') . ": ' + data.effective_date + '</small><br>' +
                            '<small class=\"text-muted\">" . Yii::t('app', 'Source') . ": ' + data.data_source + '</small>' +
                        '</div>' +
                    '</div>' +
                '</div>';
                $('#lookup-result').html(html);
            } else {
                $('#lookup-result').html('<div class=\"alert alert-warning\">' + response.message + '</div>');
            }
        },
        error: function() {
            $('#lookup-result').html('<div class=\"alert alert-danger\">" . Yii::t('app', 'Error occurred while looking up ZIP code') . "</div>');
        },
        complete: function() {
            btn.html(originalHtml).prop('disabled', false);
        }
    });
});

// Allow Enter key to trigger lookup
$('#lookup-zip').keypress(function(e) {
    if (e.which == 13) {
        $('#lookup-btn').click();
    }
});

// Checkbox selection functionality
function updateSelectedCount() {
    var checkedCount = $('.bulk-checkbox:checked').length;
    $('#selected-count').text(checkedCount);
    $('#bulk-submit').prop('disabled', checkedCount === 0 || $('#bulk-operation').val() === '');
}

// Update count when checkboxes change
$(document).on('change', '.bulk-checkbox', function() {
    updateSelectedCount();
});

// Update submit button when operation changes
$('#bulk-operation').change(function() {
    updateSelectedCount();
});

// Handle select all checkbox (if exists)
$(document).on('change', '.select-on-check-all', function() {
    var isChecked = $(this).is(':checked');
    $('.bulk-checkbox').prop('checked', isChecked);
    updateSelectedCount();
});

// Bulk action confirmation
function confirmBulkAction() {
    var operation = $('#bulk-operation').val();
    var count = $('.bulk-checkbox:checked').length;
    
    if (operation === '' || count === 0) {
        alert('" . Yii::t('app', 'Please select an operation and at least one item') . "');
        return false;
    }
    
    var operationText = $('#bulk-operation option:selected').text();
    var message = '" . Yii::t('app', 'Are you sure you want to {operation} {count} items?') . "'
        .replace('{operation}', operationText.toLowerCase())
        .replace('{count}', count);
    
    if (operation === 'delete') {
        message = '" . Yii::t('app', 'Are you sure you want to permanently delete {count} items? This action cannot be undone.') . "'
            .replace('{count}', count);
    }
    
    if (confirm(message)) {
        // Clear any existing ID inputs
        $('#bulk-form input[name=\"ids[]\"]').remove();
        
        // Collect selected IDs
        var selectedIds = [];
        $('.bulk-checkbox:checked').each(function() {
            selectedIds.push($(this).val());
        });
        
        // Debug: log the selected IDs
        console.log('Selected IDs:', selectedIds);
        
        // Add selected IDs to form as hidden inputs
        $.each(selectedIds, function(index, id) {
            $('#bulk-form').append('<input type=\"hidden\" name=\"ids[]\" value=\"' + id + '\">');
        });
        
        // Debug: log the form data before submission
        console.log('Form action:', $('#bulk-form').attr('action'));
        console.log('Form method:', $('#bulk-form').attr('method'));
        console.log('Operation:', operation);
        console.log('Hidden inputs:', $('#bulk-form input[name=\"ids[]\"]').length);
        
        return true;
    }
    
    return false;
}

// Handle form submission
$('#bulk-form').on('submit', function(e) {
    e.preventDefault(); // Prevent default submission
    
    if (confirmBulkAction()) {
        // If confirmed, submit the form
        this.submit();
    }
});

// Initialize on page load
$(document).ready(function() {
    updateSelectedCount();
});
");
?>