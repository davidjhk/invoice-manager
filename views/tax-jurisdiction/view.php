<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\TaxJurisdiction */

$this->title = $model->tax_region_name ?: ($model->zip_code . ' - ' . $model->state_code);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Admin Panel'), 'url' => ['/admin/index']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Tax Management'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="tax-jurisdiction-view">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?= Html::encode($this->title) ?></h1>
        <div class="action-buttons">
            <?= Html::a('<i class="fas fa-edit mr-2"></i>' . Yii::t('app', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
            <?= Html::a('<i class="fas fa-trash mr-2"></i>' . Yii::t('app', 'Delete'), ['delete', 'id' => $model->id], [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => Yii::t('app', 'Are you sure you want to delete this tax jurisdiction?'),
                    'method' => 'post',
                ],
            ]) ?>
            <?= Html::a('<i class="fas fa-list mr-2"></i>' . Yii::t('app', 'Back to List'), ['index'], ['class' => 'btn btn-outline-secondary']) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle mr-2"></i><?= Yii::t('app', 'Tax Jurisdiction Details') ?></h5>
                </div>
                <div class="card-body">
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            'zip_code',
                            'state_code',
                            'state_name',
                            'county_name',
                            'city_name',
                            'tax_region_name',
                            'tax_authority',
                            'jurisdiction_code',
                            [
                                'attribute' => 'estimated_population',
                                'format' => 'integer',
                            ],
                            [
                                'attribute' => 'data_source',
                                'value' => function ($model) {
                                    $sources = \app\models\TaxJurisdiction::getDataSourceOptions();
                                    return $sources[$model->data_source] ?? $model->data_source;
                                }
                            ],
                            'effective_date:date',
                            'expiry_date:date',
                            'last_verified:date',
                            [
                                'attribute' => 'is_active',
                                'format' => 'html',
                                'value' => function ($model) {
                                    if ($model->is_active) {
                                        return '<span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i>' . Yii::t('app', 'Active') . '</span>';
                                    } else {
                                        return '<span class="badge badge-secondary"><i class="fas fa-times-circle mr-1"></i>' . Yii::t('app', 'Inactive') . '</span>';
                                    }
                                }
                            ],
                            [
                                'attribute' => 'data_year',
                                'format' => 'integer',
                            ],
                            [
                                'attribute' => 'data_month',
                                'format' => 'integer',
                            ],
                            'notes:ntext',
                            'created_at:datetime',
                            'updated_at:datetime',
                        ],
                    ]) ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <!-- Tax Rates Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-calculator mr-2"></i><?= Yii::t('app', 'Tax Rates') ?></h5>
                </div>
                <div class="card-body">
                    <?php 
                    $stateRateInfo = $model->getStateTaxRateInfo();
                    $actualStateRate = $stateRateInfo['actual_base_rate'];
                    $actualRateWithLocal = $stateRateInfo['actual_rate_with_local'];
                    $storedStateRate = $model->state_rate;
                    $hasMismatch = $stateRateInfo['has_mismatch'];
                    ?>
                    
                    <div class="tax-rates">
                        <div class="rate-item mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="rate-label">
                                    <?= Yii::t('app', 'State Rate') ?> 
                                    <small class="text-muted">(<?= Yii::t('app', 'Used in calculations') ?>)</small>:
                                </span>
                                <span class="rate-value badge badge-primary"><?= \app\models\TaxJurisdiction::formatRate($actualStateRate) ?></span>
                            </div>
                            <?php if ($hasMismatch): ?>
                            <div class="mt-1">
                                <small class="text-warning">
                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                    <?= Yii::t('app', 'Stored rate differs') ?>: <?= \app\models\TaxJurisdiction::formatRate($storedStateRate) ?>
                                </small>
                            </div>
                            <?php endif; ?>
                            <?php if ($actualRateWithLocal !== $actualStateRate): ?>
                            <div class="mt-1">
                                <small class="text-info">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    <?= Yii::t('app', 'With local tax') ?>: <?= \app\models\TaxJurisdiction::formatRate($actualRateWithLocal) ?>
                                </small>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="rate-item mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="rate-label"><?= Yii::t('app', 'County Rate') ?>:</span>
                                <span class="rate-value badge badge-info"><?= \app\models\TaxJurisdiction::formatRate($model->county_rate) ?></span>
                            </div>
                        </div>
                        
                        <div class="rate-item mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="rate-label"><?= Yii::t('app', 'City Rate') ?>:</span>
                                <span class="rate-value badge badge-warning"><?= \app\models\TaxJurisdiction::formatRate($model->city_rate) ?></span>
                            </div>
                        </div>
                        
                        <div class="rate-item mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="rate-label"><?= Yii::t('app', 'Special Rate') ?>:</span>
                                <span class="rate-value badge badge-secondary"><?= \app\models\TaxJurisdiction::formatRate($model->special_rate) ?></span>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="rate-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="rate-label font-weight-bold"><?= Yii::t('app', 'Combined Rate') ?>:</span>
                                <span class="rate-value badge badge-success badge-lg"><?= \app\models\TaxJurisdiction::formatRate($model->combined_rate) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Status Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-flag mr-2"></i><?= Yii::t('app', 'Status Information') ?></h5>
                </div>
                <div class="card-body">
                    <?php if ($model->isExpired()): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            <strong><?= Yii::t('app', 'Expired') ?>:</strong> <?= Yii::t('app', 'This jurisdiction has expired on {date}', ['date' => $model->expiry_date]) ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($model->needsVerification()): ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-clock mr-2"></i>
                            <strong><?= Yii::t('app', 'Needs Verification') ?>:</strong> 
                            <?php if ($model->last_verified): ?>
                                <?= Yii::t('app', 'Last verified on {date}', ['date' => $model->last_verified]) ?>
                            <?php else: ?>
                                <?= Yii::t('app', 'Never verified') ?>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle mr-2"></i>
                            <strong><?= Yii::t('app', 'Up to Date') ?>:</strong> <?= Yii::t('app', 'Last verified on {date}', ['date' => $model->last_verified]) ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!$model->is_active): ?>
                        <div class="alert alert-secondary">
                            <i class="fas fa-pause-circle mr-2"></i>
                            <strong><?= Yii::t('app', 'Inactive') ?>:</strong> <?= Yii::t('app', 'This jurisdiction is currently inactive') ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Quick Actions Card -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-bolt mr-2"></i><?= Yii::t('app', 'Quick Actions') ?></h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <?php if (!$model->is_active): ?>
                            <?= Html::a('<i class="fas fa-play mr-2"></i>' . Yii::t('app', 'Activate'), ['bulk-operation'], [
                                'class' => 'btn btn-success btn-sm',
                                'data' => ['method' => 'post'],
                                'data-params' => ['operation' => 'activate', 'ids' => [$model->id]]
                            ]) ?>
                        <?php else: ?>
                            <?= Html::a('<i class="fas fa-pause mr-2"></i>' . Yii::t('app', 'Deactivate'), ['bulk-operation'], [
                                'class' => 'btn btn-warning btn-sm',
                                'data' => ['method' => 'post'],
                                'data-params' => ['operation' => 'deactivate', 'ids' => [$model->id]]
                            ]) ?>
                        <?php endif; ?>
                        
                        <?= Html::a('<i class="fas fa-check mr-2"></i>' . Yii::t('app', 'Mark as Verified'), ['bulk-operation'], [
                            'class' => 'btn btn-info btn-sm',
                            'data' => ['method' => 'post'],
                            'data-params' => ['operation' => 'verify', 'ids' => [$model->id]]
                        ]) ?>
                        
                        <?= Html::a('<i class="fas fa-copy mr-2"></i>' . Yii::t('app', 'Duplicate'), ['create', 'copy_from' => $model->id], [
                            'class' => 'btn btn-outline-primary btn-sm'
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<style>
.rate-item .rate-label {
    font-weight: 500;
}

.rate-item .rate-value {
    font-size: 0.9em;
    min-width: 60px;
    text-align: center;
}

.badge-lg {
    font-size: 1em;
    padding: 0.5em 0.75em;
}

.card {
    border: none;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border-radius: 0.5rem;
}

.alert {
    border-radius: 0.5rem;
}

.d-grid.gap-2 > * {
    margin-bottom: 0.5rem;
}
</style>