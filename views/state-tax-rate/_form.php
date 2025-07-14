<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\web\JsExpression;

/** @var yii\web\View $this */
/** @var app\models\StateTaxRate $model */
/** @var app\models\State[] $states */
/** @var app\models\Country[] $countries */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="state-tax-rate-form">

    <?php $form = ActiveForm::begin([
        'id' => 'state-tax-rate-form',
        'options' => ['class' => 'needs-validation', 'novalidate' => true],
        'fieldConfig' => [
            'options' => ['class' => 'form-group'],
            'inputOptions' => ['class' => 'form-control'],
            'labelOptions' => ['class' => 'form-label font-weight-bold'],
        ],
    ]); ?>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-percent mr-2"></i><?= Yii::t('app', 'Tax Rate Information') ?>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($model, 'country_code')->dropDownList(
                                $countries,
                                [
                                    'prompt' => Yii::t('app', 'Select Country'),
                                    'id' => 'country-select',
                                ]
                            )->label(Yii::t('app', 'Country')) ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'state_code')->dropDownList(
                                [],
                                [
                                    'prompt' => Yii::t('app', 'Select State'),
                                    'id' => 'state-select',
                                ]
                            )->label(Yii::t('app', 'State')) ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($model, 'base_rate')->textInput([
                                'type' => 'number',
                                'step' => '0.01',
                                'min' => '0',
                                'max' => '99.99',
                                'placeholder' => '0.00'
                            ])->label(Yii::t('app', 'Base Tax Rate (%)')) ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'average_total_rate')->textInput([
                                'type' => 'number',
                                'step' => '0.01',
                                'min' => '0',
                                'max' => '99.99',
                                'placeholder' => '0.00'
                            ])->label(Yii::t('app', 'Average Total Rate (%)')) ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <?= $form->field($model, 'has_local_tax')->checkbox([
                                'id' => 'has-local-tax-checkbox'
                            ])->label(Yii::t('app', 'State has local tax jurisdictions')) ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($model, 'revenue_threshold')->textInput([
                                'type' => 'number',
                                'step' => '0.01',
                                'min' => '0',
                                'placeholder' => Yii::t('app', 'Leave empty if no threshold')
                            ])->label(Yii::t('app', 'Revenue Threshold ($)')) ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'transaction_threshold')->textInput([
                                'type' => 'number',
                                'min' => '0',
                                'placeholder' => Yii::t('app', 'Leave empty if no threshold')
                            ])->label(Yii::t('app', 'Transaction Threshold')) ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($model, 'effective_date')->textInput([
                                'type' => 'date',
                                'value' => $model->effective_date ?: date('Y-m-d')
                            ])->label(Yii::t('app', 'Effective Date')) ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'is_active')->checkbox()->label(Yii::t('app', 'Is Active')) ?>
                        </div>
                    </div>

                    <?= $form->field($model, 'notes')->textarea([
                        'rows' => 3,
                        'placeholder' => Yii::t('app', 'Additional notes about this tax rate...')
                    ])->label(Yii::t('app', 'Notes')) ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-info">
                <div class="card-header bg-info text-white">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-info-circle mr-2"></i><?= Yii::t('app', 'Help & Guidelines') ?>
                    </h6>
                </div>
                <div class="card-body">
                    <h6><?= Yii::t('app', 'Tax Rate Fields') ?></h6>
                    <ul class="list-unstyled">
                        <li><strong><?= Yii::t('app', 'Base Rate') ?>:</strong> <?= Yii::t('app', 'State-level sales tax rate') ?></li>
                        <li><strong><?= Yii::t('app', 'Total Rate') ?>:</strong> <?= Yii::t('app', 'Includes average local taxes') ?></li>
                    </ul>

                    <h6 class="mt-3"><?= Yii::t('app', 'Economic Nexus') ?></h6>
                    <ul class="list-unstyled">
                        <li><strong><?= Yii::t('app', 'Revenue') ?>:</strong> <?= Yii::t('app', 'Annual sales threshold') ?></li>
                        <li><strong><?= Yii::t('app', 'Transactions') ?>:</strong> <?= Yii::t('app', 'Number of sales per year') ?></li>
                    </ul>

                    <div class="alert alert-warning mt-3">
                        <small>
                            <i class="fas fa-exclamation-triangle mr-1"></i>
                            <?= Yii::t('app', 'Tax rates and thresholds change frequently. Always verify with official sources.') ?>
                        </small>
                    </div>
                </div>
            </div>

            <div class="card border-success mt-3" id="tax-preview" style="display: none;">
                <div class="card-header bg-success text-white">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-calculator mr-2"></i><?= Yii::t('app', 'Tax Preview') ?>
                    </h6>
                </div>
                <div class="card-body">
                    <div class="text-center">
                        <h4 class="text-success mb-1" id="preview-base-rate">0.00%</h4>
                        <small class="text-muted"><?= Yii::t('app', 'Base Rate') ?></small>
                    </div>
                    <div class="text-center mt-3" id="preview-total-section" style="display: none;">
                        <h4 class="text-info mb-1" id="preview-total-rate">0.00%</h4>
                        <small class="text-muted"><?= Yii::t('app', 'With Local Tax') ?></small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="form-group mt-4">
        <div class="d-flex justify-content-between">
            <?= Html::a('<i class="fas fa-arrow-left mr-1"></i>' . Yii::t('app', 'Cancel'), ['index'], ['class' => 'btn btn-secondary']) ?>
            <?= Html::submitButton('<i class="fas fa-save mr-1"></i>' . Yii::t('app', 'Save Tax Rate'), ['class' => 'btn btn-success']) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?php
$getStatesUrl = \yii\helpers\Url::to(['get-states']);
$this->registerJs("
$(document).ready(function() {
    // Load states based on selected country
    function loadStates(countryCode, selectedState) {
        if (!countryCode) {
            $('#state-select').html('<option value=\"\">" . Yii::t('app', 'Select State') . "</option>');
            return;
        }
        
        $.getJSON('{$getStatesUrl}', {country_code: countryCode})
            .done(function(data) {
                var options = '<option value=\"\">" . Yii::t('app', 'Select State') . "</option>';
                $.each(data, function(i, state) {
                    var selected = (selectedState && state.value === selectedState) ? ' selected' : '';
                    options += '<option value=\"' + state.value + '\"' + selected + '>' + state.text + '</option>';
                });
                $('#state-select').html(options);
            })
            .fail(function() {
                $('#state-select').html('<option value=\"\">" . Yii::t('app', 'Error loading states') . "</option>');
            });
    }
    
    // Load states on country change
    $('#country-select').change(function() {
        loadStates($(this).val());
    });
    
    // Load states on page load if country is selected
    var selectedCountry = $('#country-select').val();
    var selectedState = '" . Html::encode($model->state_code) . "';
    if (selectedCountry) {
        loadStates(selectedCountry, selectedState);
    }
    
    // Update tax preview
    function updatePreview() {
        var baseRate = parseFloat($('#statetaxrate-base_rate').val()) || 0;
        var totalRate = parseFloat($('#statetaxrate-average_total_rate').val()) || 0;
        var hasLocalTax = $('#has-local-tax-checkbox').is(':checked');
        
        if (baseRate > 0 || totalRate > 0) {
            $('#preview-base-rate').text(baseRate.toFixed(2) + '%');
            
            if (hasLocalTax && totalRate > 0) {
                $('#preview-total-rate').text(totalRate.toFixed(2) + '%');
                $('#preview-total-section').show();
            } else {
                $('#preview-total-section').hide();
            }
            
            $('#tax-preview').show();
        } else {
            $('#tax-preview').hide();
        }
    }
    
    // Update preview on input change
    $('#statetaxrate-base_rate, #statetaxrate-average_total_rate, #has-local-tax-checkbox').on('input change', updatePreview);
    
    // Initialize preview
    updatePreview();
    
    // Auto-set average total rate to base rate if has_local_tax is unchecked
    $('#has-local-tax-checkbox').change(function() {
        if (!$(this).is(':checked')) {
            var baseRate = $('#statetaxrate-base_rate').val();
            if (baseRate) {
                $('#statetaxrate-average_total_rate').val(baseRate);
            }
        }
        updatePreview();
    });
});
");
?>