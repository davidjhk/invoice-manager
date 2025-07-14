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
?>
<div class="tax-jurisdiction-index">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?= Html::encode($this->title) ?></h1>
        <div class="action-buttons">
            <?= Html::a('<i class="fas fa-plus mr-2"></i>' . Yii::t('app', 'Create'), ['create'], ['class' => 'btn btn-success']) ?>
            <?= Html::a('<i class="fas fa-upload mr-2"></i>' . Yii::t('app', 'Import Tax Rates'), ['import-csv'], ['class' => 'btn btn-primary']) ?>
            <?= Html::a('<i class="fas fa-download mr-2"></i>' . Yii::t('app', 'Export CSV'), ['export-csv'] + Yii::$app->request->queryParams, ['class' => 'btn btn-outline-secondary']) ?>
            <?= Html::a('<i class="fas fa-chart-bar mr-2"></i>' . Yii::t('app', 'Tax Statistics'), ['stats'], ['class' => 'btn btn-outline-info']) ?>
        </div>
    </div>

    <div class="alert alert-info">
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

    <!-- Bulk Actions -->
    <div class="card mb-4">
        <div class="card-body">
            <?= Html::beginForm(['bulk-operation'], 'post', ['id' => 'bulk-form']) ?>
            <div class="row align-items-end">
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
                        'disabled' => true,
                        'onclick' => 'return confirmBulkAction()'
                    ]) ?>
                </div>
                <div class="col-md-6 text-right">
                    <small class="text-muted">
                        <span id="selected-count">0</span> <?= Yii::t('app', 'items selected') ?>
                    </small>
                </div>
            </div>
            <?= Html::endForm() ?>
        </div>
    </div>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
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
                    if ($model->state_rate > 0) $breakdown[] = 'State: ' . TaxJurisdiction::formatRate($model->state_rate);
                    if ($model->county_rate > 0) $breakdown[] = 'County: ' . TaxJurisdiction::formatRate($model->county_rate);
                    if ($model->city_rate > 0) $breakdown[] = 'City: ' . TaxJurisdiction::formatRate($model->city_rate);
                    if ($model->special_rate > 0) $breakdown[] = 'Special: ' . TaxJurisdiction::formatRate($model->special_rate);
                    return '<small>' . implode('<br>', $breakdown) . '</small>';
                },
                'options' => ['style' => 'width: 200px;'],
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
                'contentOptions' => ['class' => 'text-center'],
                'buttons' => [
                    'view' => function ($url, $model, $key) {
                        return Html::a('<i class="fas fa-eye"></i>', $url, [
                            'title' => Yii::t('app', 'View'),
                            'class' => 'btn btn-sm btn-outline-info mx-1',
                        ]);
                    },
                    'update' => function ($url, $model, $key) {
                        return Html::a('<i class="fas fa-edit"></i>', $url, [
                            'title' => Yii::t('app', 'Update'),
                            'class' => 'btn btn-sm btn-outline-primary mx-1',
                        ]);
                    },
                    'delete' => function ($url, $model, $key) {
                        return Html::a('<i class="fas fa-trash"></i>', $url, [
                            'title' => Yii::t('app', 'Delete'),
                            'class' => 'btn btn-sm btn-outline-danger mx-1',
                            'data' => [
                                'confirm' => Yii::t('app', 'Are you sure you want to delete this tax jurisdiction?'),
                                'method' => 'post',
                            ],
                        ]);
                    },
                ],
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>

<script>
$(document).ready(function() {
    // Bulk actions
    $('.bulk-checkbox, #selection-all').on('change', function() {
        updateBulkControls();
    });
    
    $('#bulk-operation').on('change', function() {
        updateBulkControls();
    });
    
    function updateBulkControls() {
        const selected = $('.bulk-checkbox:checked').length;
        const operation = $('#bulk-operation').val();
        
        $('#selected-count').text(selected);
        $('#bulk-submit').prop('disabled', selected === 0 || operation === '');
        
        // Update hidden input for selected IDs
        const ids = [];
        $('.bulk-checkbox:checked').each(function() {
            ids.push($(this).val());
        });
        
        // Remove existing hidden inputs
        $('#bulk-form input[name="ids[]"]').remove();
        
        // Add new hidden inputs
        ids.forEach(function(id) {
            $('#bulk-form').append('<input type="hidden" name="ids[]" value="' + id + '">');
        });
    }
    
    function confirmBulkAction() {
        const operation = $('#bulk-operation').val();
        const count = $('.bulk-checkbox:checked').length;
        
        if (operation === 'delete') {
            return confirm('<?= Yii::t('app', 'Are you sure you want to delete {count} selected jurisdictions?', ['count' => '']) ?>'.replace('', count));
        }
        
        return confirm('<?= Yii::t('app', 'Are you sure you want to {operation} {count} selected jurisdictions?', ['operation' => '', 'count' => '']) ?>'.replace('', operation).replace('', count));
    }
    
    // ZIP code lookup
    $('#lookup-btn').on('click', function() {
        const zipCode = $('#lookup-zip').val().trim();
        if (!zipCode) return;
        
        $.get('<?= \yii\helpers\Url::to(['lookup']) ?>', {zipCode: zipCode})
            .done(function(response) {
                if (response.success) {
                    const data = response.data;
                    $('#lookup-result').html(`
                        <div class="alert alert-success">
                            <h6><strong>${data.zip_code}</strong> - ${data.tax_region_name || (data.city_name + ', ' + data.county_name)}</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <strong><?= Yii::t('app', 'Combined Rate') ?>:</strong> ${data.combined_rate}%<br>
                                    <strong><?= Yii::t('app', 'State') ?>:</strong> ${data.state_code} (${data.state_rate}%)<br>
                                    <strong><?= Yii::t('app', 'County Rate') ?>:</strong> ${data.county_rate}%
                                </div>
                                <div class="col-md-6">
                                    <strong><?= Yii::t('app', 'City Rate') ?>:</strong> ${data.city_rate}%<br>
                                    <strong><?= Yii::t('app', 'Special Rate') ?>:</strong> ${data.special_rate}%<br>
                                    <strong><?= Yii::t('app', 'Effective Date') ?>:</strong> ${data.effective_date}
                                </div>
                            </div>
                        </div>
                    `);
                } else {
                    $('#lookup-result').html(`
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            ${response.message}
                        </div>
                    `);
                }
            })
            .fail(function() {
                $('#lookup-result').html(`
                    <div class="alert alert-danger">
                        <i class="fas fa-times mr-2"></i>
                        <?= Yii::t('app', 'Error occurred while looking up tax rate') ?>
                    </div>
                `);
            });
    });
    
    // Enter key for lookup
    $('#lookup-zip').on('keypress', function(e) {
        if (e.which === 13) {
            $('#lookup-btn').click();
        }
    });
});

// Make confirmBulkAction global
window.confirmBulkAction = function() {
    const operation = $('#bulk-operation').val();
    const count = $('.bulk-checkbox:checked').length;
    
    if (operation === 'delete') {
        return confirm('<?= Yii::t('app', 'Are you sure you want to delete') ?> ' + count + ' <?= Yii::t('app', 'selected jurisdictions?') ?>');
    }
    
    return confirm('<?= Yii::t('app', 'Are you sure you want to') ?> ' + operation + ' ' + count + ' <?= Yii::t('app', 'selected jurisdictions?') ?>');
};
</script>

<style>
.btn-group .btn {
    margin-right: 5px;
}

.grid-view .table td {
    vertical-align: middle;
}

.alert {
    border-radius: 0.5rem;
}

.card {
    border: none;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border-radius: 0.5rem;
}

.badge {
    font-size: 0.85em;
}
</style>