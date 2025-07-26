<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */

$this->title = Yii::t('app', 'Bulk Import Tax Rates');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Admin'), 'url' => ['/admin/index']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'State Tax Rates'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

// Check if user can use import functionality
if (!Yii::$app->user->identity->canUseImport()) {
	echo '<div class="alert alert-warning">';
	echo '<h4><i class="fas fa-lock mr-2"></i>' . Yii::t('app', 'Feature Not Available') . '</h4>';
	echo '<p>' . Yii::t('app', 'Import functionality is only available for Pro plan users. Please upgrade your plan to access this feature.') . '</p>';
	echo Html::a('<i class="fas fa-arrow-up mr-2"></i>' . Yii::t('app', 'Upgrade Plan'), ['/subscription/my-account'], ['class' => 'btn btn-primary']);
	echo '</div>';
	return;
}
?>
<div class="state-tax-rate-bulk-import">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?= Html::encode($this->title) ?></h1>
        <?= Html::a('<i class="fas fa-list mr-1"></i>' . Yii::t('app', 'Back to List'), ['index'], ['class' => 'btn btn-secondary']) ?>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-upload mr-2"></i><?= Yii::t('app', 'Import Options') ?>
                    </h5>
                </div>
                <div class="card-body">
                    <?php $form = ActiveForm::begin([
                        'action' => ['bulk-import'],
                        'method' => 'post',
                        'options' => ['class' => 'needs-validation', 'novalidate' => true],
                    ]); ?>

                    <div class="form-group">
                        <label class="form-label font-weight-bold"><?= Yii::t('app', 'Import Type') ?></label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="import_type" id="import_default" value="default" checked>
                            <label class="form-check-label" for="import_default">
                                <strong><?= Yii::t('app', 'Default US State Tax Rates (2025)') ?></strong>
                                <div class="text-muted"><?= Yii::t('app', 'Import current US state tax rates with economic nexus thresholds') ?></div>
                            </label>
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle mr-2"></i><?= Yii::t('app', 'What will be imported:') ?></h6>
                        <ul class="mb-0">
                            <li><?= Yii::t('app', 'Base state tax rates for all 50 US states') ?></li>
                            <li><?= Yii::t('app', 'Average total rates including local taxes') ?></li>
                            <li><?= Yii::t('app', 'Economic nexus revenue thresholds') ?></li>
                            <li><?= Yii::t('app', 'Transaction thresholds where applicable') ?></li>
                            <li><?= Yii::t('app', 'Local tax jurisdiction information') ?></li>
                        </ul>
                    </div>

                    <div class="alert alert-warning">
                        <h6><i class="fas fa-exclamation-triangle mr-2"></i><?= Yii::t('app', 'Important Notes:') ?></h6>
                        <ul class="mb-0">
                            <li><?= Yii::t('app', 'Existing rates for the same state/country/date will be skipped') ?></li>
                            <li><?= Yii::t('app', 'This will not overwrite your existing tax rates') ?></li>
                            <li><?= Yii::t('app', 'Tax rates are effective from today\'s date') ?></li>
                            <li><?= Yii::t('app', 'Always verify rates with official sources before using') ?></li>
                        </ul>
                    </div>

                    <div class="form-group">
                        <?= Html::submitButton('<i class="fas fa-download mr-1"></i>' . Yii::t('app', 'Import Tax Rates'), [
                            'class' => 'btn btn-success btn-lg',
                            'data-confirm' => Yii::t('app', 'Are you sure you want to import the default tax rates? This action cannot be undone.')
                        ]) ?>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-info">
                <div class="card-header bg-info text-white">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-table mr-2"></i><?= Yii::t('app', 'Sample Data Preview') ?>
                    </h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th><?= Yii::t('app', 'State') ?></th>
                                <th><?= Yii::t('app', 'Base') ?></th>
                                <th><?= Yii::t('app', 'Total') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>CA</td>
                                <td>6.00%</td>
                                <td>8.85%</td>
                            </tr>
                            <tr>
                                <td>NY</td>
                                <td>4.00%</td>
                                <td>8.54%</td>
                            </tr>
                            <tr>
                                <td>TX</td>
                                <td>6.25%</td>
                                <td>8.20%</td>
                            </tr>
                            <tr>
                                <td>FL</td>
                                <td>6.00%</td>
                                <td>7.02%</td>
                            </tr>
                            <tr>
                                <td>DE</td>
                                <td>0.00%</td>
                                <td>0.00%</td>
                            </tr>
                        </tbody>
                    </table>
                    <small class="text-muted"><?= Yii::t('app', '* Sample of states that will be imported') ?></small>
                </div>
            </div>

            <div class="card border-warning mt-3">
                <div class="card-header bg-warning text-dark">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-exclamation-triangle mr-2"></i><?= Yii::t('app', 'Disclaimer') ?>
                    </h6>
                </div>
                <div class="card-body">
                    <p class="card-text small">
                        <?= Yii::t('app', 'These rates are for reference only and may not reflect the most current tax laws. Always consult with tax professionals and verify rates with official state sources before making tax calculations.') ?>
                    </p>
                    <p class="card-text small mb-0">
                        <strong><?= Yii::t('app', 'Data sources:') ?></strong> State revenue departments, Tax Foundation, and other public sources as of 2025.
                    </p>
                </div>
            </div>

            <div class="card border-success mt-3">
                <div class="card-header bg-success text-white">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-lightbulb mr-2"></i><?= Yii::t('app', 'Pro Tips') ?>
                    </h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled small mb-0">
                        <li><i class="fas fa-check text-success mr-2"></i><?= Yii::t('app', 'Import once to get started quickly') ?></li>
                        <li><i class="fas fa-check text-success mr-2"></i><?= Yii::t('app', 'Update individual rates as needed') ?></li>
                        <li><i class="fas fa-check text-success mr-2"></i><?= Yii::t('app', 'Set effective dates for rate changes') ?></li>
                        <li><i class="fas fa-check text-success mr-2"></i><?= Yii::t('app', 'Use notes field for documentation') ?></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

</div>