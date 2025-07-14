<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\StateTaxRate $model */

$this->title = $model->getDisplayName();
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Admin'), 'url' => ['/admin/index']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'State Tax Rates'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="state-tax-rate-view">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?= Html::encode($this->title) ?></h1>
        <div>
            <?= Html::a('<i class="fas fa-list mr-1"></i>' . Yii::t('app', 'Back to List'), ['index'], ['class' => 'btn btn-secondary mr-2']) ?>
            <?= Html::a('<i class="fas fa-edit mr-1"></i>' . Yii::t('app', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary mr-2']) ?>
            <?= Html::a('<i class="fas fa-trash mr-1"></i>' . Yii::t('app', 'Delete'), ['delete', 'id' => $model->id], [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
                    'method' => 'post',
                ],
            ]) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-percent mr-2"></i><?= Yii::t('app', 'Tax Rate Details') ?>
                    </h5>
                </div>
                <div class="card-body">
                    <?= DetailView::widget([
                        'model' => $model,
                        'options' => ['class' => 'table table-striped detail-view'],
                        'attributes' => [
                            [
                                'attribute' => 'state_code',
                                'label' => Yii::t('app', 'State'),
                                'format' => 'raw',
                                'value' => function ($model) {
                                    $stateName = $model->state ? $model->state->state_name : $model->state_code;
                                    return Html::tag('span', $stateName . ' (' . $model->state_code . ')', [
                                        'class' => 'badge badge-secondary badge-lg'
                                    ]);
                                },
                            ],
                            [
                                'attribute' => 'country_code',
                                'label' => Yii::t('app', 'Country'),
                                'format' => 'raw',
                                'value' => function ($model) {
                                    $countryName = $model->country ? $model->country->country_name : $model->country_code;
                                    return Html::tag('span', $countryName . ' (' . $model->country_code . ')', [
                                        'class' => 'badge badge-primary badge-lg'
                                    ]);
                                },
                            ],
                            [
                                'attribute' => 'base_rate',
                                'label' => Yii::t('app', 'Base Tax Rate'),
                                'format' => 'raw',
                                'value' => function ($model) {
                                    $color = $model->base_rate > 0 ? 'success' : 'secondary';
                                    return Html::tag('h4', number_format($model->base_rate, 2) . '%', [
                                        'class' => "text-{$color} mb-0"
                                    ]);
                                },
                            ],
                            [
                                'attribute' => 'has_local_tax',
                                'label' => Yii::t('app', 'Has Local Tax'),
                                'format' => 'raw',
                                'value' => function ($model) {
                                    if ($model->has_local_tax) {
                                        return Html::tag('span', '<i class="fas fa-check mr-2"></i>' . Yii::t('app', 'Yes'), [
                                            'class' => 'badge badge-success badge-lg'
                                        ]);
                                    } else {
                                        return Html::tag('span', '<i class="fas fa-times mr-2"></i>' . Yii::t('app', 'No'), [
                                            'class' => 'badge badge-danger badge-lg'
                                        ]);
                                    }
                                },
                            ],
                            [
                                'attribute' => 'average_total_rate',
                                'label' => Yii::t('app', 'Average Total Rate'),
                                'format' => 'raw',
                                'value' => function ($model) {
                                    if (!$model->has_local_tax) {
                                        return Html::tag('span', 'N/A', ['class' => 'text-muted']);
                                    }
                                    return Html::tag('h4', number_format($model->average_total_rate, 2) . '%', [
                                        'class' => 'text-info mb-0'
                                    ]);
                                },
                            ],
                            [
                                'attribute' => 'revenue_threshold',
                                'label' => Yii::t('app', 'Revenue Threshold'),
                                'format' => 'raw',
                                'value' => function ($model) {
                                    if (!$model->revenue_threshold) {
                                        return Html::tag('span', Yii::t('app', 'No threshold'), ['class' => 'text-muted']);
                                    }
                                    return Html::tag('strong', '$' . number_format($model->revenue_threshold), [
                                        'class' => 'text-warning'
                                    ]);
                                },
                            ],
                            [
                                'attribute' => 'transaction_threshold',
                                'label' => Yii::t('app', 'Transaction Threshold'),
                                'format' => 'raw',
                                'value' => function ($model) {
                                    if (!$model->transaction_threshold) {
                                        return Html::tag('span', Yii::t('app', 'No threshold'), ['class' => 'text-muted']);
                                    }
                                    return Html::tag('strong', number_format($model->transaction_threshold) . ' ' . Yii::t('app', 'transactions'), [
                                        'class' => 'text-warning'
                                    ]);
                                },
                            ],
                            [
                                'attribute' => 'effective_date',
                                'label' => Yii::t('app', 'Effective Date'),
                                'format' => 'date',
                            ],
                            [
                                'attribute' => 'is_active',
                                'label' => Yii::t('app', 'Status'),
                                'format' => 'raw',
                                'value' => function ($model) {
                                    if ($model->is_active) {
                                        return Html::tag('span', '<i class="fas fa-check mr-2"></i>' . Yii::t('app', 'Active'), [
                                            'class' => 'badge badge-success badge-lg'
                                        ]);
                                    } else {
                                        return Html::tag('span', '<i class="fas fa-times mr-2"></i>' . Yii::t('app', 'Inactive'), [
                                            'class' => 'badge badge-danger badge-lg'
                                        ]);
                                    }
                                },
                            ],
                            'notes:ntext',
                            'created_at:datetime',
                            'updated_at:datetime',
                        ],
                    ]) ?>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card border-info">
                <div class="card-header bg-info text-white">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-calculator mr-2"></i><?= Yii::t('app', 'Tax Calculator') ?>
                    </h6>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label><?= Yii::t('app', 'Taxable Amount') ?></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">$</span>
                            </div>
                            <input type="number" class="form-control" id="taxable-amount" placeholder="0.00" step="0.01">
                        </div>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input type="checkbox" class="form-check-input" id="include-local-tax" <?= $model->has_local_tax ? 'checked' : 'disabled' ?>>
                        <label class="form-check-label" for="include-local-tax">
                            <?= Yii::t('app', 'Include Local Tax') ?>
                        </label>
                    </div>
                    
                    <div class="calculation-results" style="display: none;">
                        <div class="alert alert-info">
                            <strong><?= Yii::t('app', 'Tax Rate Used') ?>:</strong> <span id="rate-used"></span><br>
                            <strong><?= Yii::t('app', 'Tax Amount') ?>:</strong> $<span id="tax-amount">0.00</span><br>
                            <strong><?= Yii::t('app', 'Total Amount') ?>:</strong> $<span id="total-amount">0.00</span>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($model->revenue_threshold || $model->transaction_threshold): ?>
            <div class="card border-warning mt-3">
                <div class="card-header bg-warning text-dark">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-exclamation-triangle mr-2"></i><?= Yii::t('app', 'Economic Nexus') ?>
                    </h6>
                </div>
                <div class="card-body">
                    <p class="card-text"><?= Yii::t('app', 'This state has economic nexus thresholds:') ?></p>
                    <ul class="list-unstyled">
                        <?php if ($model->revenue_threshold): ?>
                        <li><i class="fas fa-dollar-sign text-success mr-2"></i><?= Yii::t('app', 'Revenue: ${threshold}', ['threshold' => number_format($model->revenue_threshold)]) ?></li>
                        <?php endif; ?>
                        <?php if ($model->transaction_threshold): ?>
                        <li><i class="fas fa-shopping-cart text-info mr-2"></i><?= Yii::t('app', 'Transactions: {threshold}', ['threshold' => number_format($model->transaction_threshold)]) ?></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<?php
$baseRate = $model->base_rate;
$totalRate = $model->average_total_rate;
$hasLocalTax = $model->has_local_tax ? 'true' : 'false';

$this->registerJs("
$(document).ready(function() {
    var baseRate = {$baseRate};
    var totalRate = {$totalRate};
    var hasLocalTax = {$hasLocalTax};
    
    function calculateTax() {
        var amount = parseFloat($('#taxable-amount').val()) || 0;
        var useLocalTax = $('#include-local-tax').is(':checked') && hasLocalTax;
        
        if (amount > 0) {
            var rate = useLocalTax ? totalRate : baseRate;
            var taxAmount = (amount * rate / 100);
            var totalAmount = amount + taxAmount;
            
            $('#rate-used').text(rate.toFixed(2) + '%' + (useLocalTax ? ' (with local)' : ' (base only)'));
            $('#tax-amount').text(taxAmount.toFixed(2));
            $('#total-amount').text(totalAmount.toFixed(2));
            $('.calculation-results').show();
        } else {
            $('.calculation-results').hide();
        }
    }
    
    $('#taxable-amount, #include-local-tax').on('input change', calculateTax);
});
");
?>