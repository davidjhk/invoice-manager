<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\TaxJurisdiction;

/* @var $this yii\web\View */
/* @var $model app\models\TaxJurisdiction */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="tax-jurisdiction-form">

    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-map-marker-alt mr-2"></i><?= Yii::t('app', 'Location Information') ?></h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <?= $form->field($model, 'zip_code')->textInput(['maxlength' => true, 'placeholder' => '90210']) ?>
                        </div>
                        <div class="col-md-4">
                            <?= $form->field($model, 'state_code')->textInput(['maxlength' => true, 'placeholder' => 'CA', 'style' => 'text-transform: uppercase;']) ?>
                        </div>
                        <div class="col-md-4">
                            <?= $form->field($model, 'state_name')->textInput(['maxlength' => true, 'placeholder' => 'California']) ?>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($model, 'county_name')->textInput(['maxlength' => true, 'placeholder' => 'Los Angeles']) ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'city_name')->textInput(['maxlength' => true, 'placeholder' => 'Beverly Hills']) ?>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-8">
                            <?= $form->field($model, 'tax_region_name')->textInput(['maxlength' => true, 'placeholder' => 'Beverly Hills Tax Region']) ?>
                        </div>
                        <div class="col-md-4">
                            <?= $form->field($model, 'jurisdiction_code')->textInput(['maxlength' => true, 'placeholder' => 'BH001']) ?>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-8">
                            <?= $form->field($model, 'tax_authority')->textInput(['maxlength' => true, 'placeholder' => 'California Department of Tax and Fee Administration']) ?>
                        </div>
                        <div class="col-md-4">
                            <?= $form->field($model, 'estimated_population')->textInput(['type' => 'number', 'placeholder' => '34000']) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-calculator mr-2"></i><?= Yii::t('app', 'Tax Rates') ?></h5>
                </div>
                <div class="card-body">
                    <?= $form->field($model, 'state_rate')->textInput([
                        'type' => 'number', 
                        'step' => '0.0001', 
                        'min' => '0', 
                        'max' => '99.9999',
                        'placeholder' => '6.0000'
                    ])->hint('Enter as percentage (e.g., 6.25 for 6.25%)') ?>
                    
                    <?= $form->field($model, 'county_rate')->textInput([
                        'type' => 'number', 
                        'step' => '0.0001', 
                        'min' => '0', 
                        'max' => '99.9999',
                        'placeholder' => '1.0000'
                    ]) ?>
                    
                    <?= $form->field($model, 'city_rate')->textInput([
                        'type' => 'number', 
                        'step' => '0.0001', 
                        'min' => '0', 
                        'max' => '99.9999',
                        'placeholder' => '2.5000'
                    ]) ?>
                    
                    <?= $form->field($model, 'special_rate')->textInput([
                        'type' => 'number', 
                        'step' => '0.0001', 
                        'min' => '0', 
                        'max' => '99.9999',
                        'placeholder' => '0.0000'
                    ]) ?>
                    
                    <hr>
                    
                    <?= $form->field($model, 'combined_rate')->textInput([
                        'type' => 'number', 
                        'step' => '0.0001', 
                        'min' => '0', 
                        'max' => '99.9999',
                        'placeholder' => '9.5000',
                        'id' => 'combined-rate'
                    ])->hint('Will be calculated automatically from individual rates') ?>
                    
                    <button type="button" class="btn btn-sm btn-outline-primary" id="calculate-combined">
                        <i class="fas fa-calculator mr-2"></i><?= Yii::t('app', 'Calculate Combined Rate') ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-cog mr-2"></i><?= Yii::t('app', 'Data Information') ?></h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <?= $form->field($model, 'data_source')->dropDownList(
                                TaxJurisdiction::getDataSourceOptions(),
                                ['prompt' => Yii::t('app', 'Select Data Source')]
                            ) ?>
                        </div>
                        <div class="col-md-4">
                            <?= $form->field($model, 'effective_date')->textInput(['type' => 'date']) ?>
                        </div>
                        <div class="col-md-4">
                            <?= $form->field($model, 'expiry_date')->textInput(['type' => 'date']) ?>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-3">
                            <?= $form->field($model, 'data_year')->textInput(['type' => 'number', 'min' => '2020', 'max' => '2030']) ?>
                        </div>
                        <div class="col-md-3">
                            <?= $form->field($model, 'data_month')->textInput(['type' => 'number', 'min' => '1', 'max' => '12']) ?>
                        </div>
                        <div class="col-md-3">
                            <?= $form->field($model, 'last_verified')->textInput(['type' => 'date']) ?>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="form-label">&nbsp;</label>
                                <div class="custom-control custom-switch">
                                    <?= $form->field($model, 'is_active')->checkbox([
                                        'class' => 'custom-control-input',
                                        'id' => 'is-active-switch'
                                    ])->label(Yii::t('app', 'Is Active'), [
                                        'class' => 'custom-control-label',
                                        'for' => 'is-active-switch'
                                    ]) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <?= $form->field($model, 'notes')->textarea(['rows' => 3, 'placeholder' => Yii::t('app', 'Additional notes about this tax jurisdiction...')]) ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle mr-2"></i><?= Yii::t('app', 'Help') ?></h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h6><?= Yii::t('app', 'Tax Rate Entry Tips') ?>:</h6>
                        <ul class="mb-0 small">
                            <li><?= Yii::t('app', 'Enter rates as percentages (e.g., 8.25 for 8.25%)') ?></li>
                            <li><?= Yii::t('app', 'Combined rate should equal sum of all individual rates') ?></li>
                            <li><?= Yii::t('app', 'Use effective date for when rate becomes active') ?></li>
                            <li><?= Yii::t('app', 'Set expiry date if rate has known end date') ?></li>
                        </ul>
                    </div>
                    
                    <div class="alert alert-warning">
                        <h6><?= Yii::t('app', 'Data Source') ?>:</h6>
                        <p class="mb-0 small">
                            <?= Yii::t('app', 'Always verify tax rates with official sources before using in production') ?>.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="form-group mt-4">
        <?= Html::submitButton('<i class="fas fa-save mr-2"></i>' . Yii::t('app', 'Save Tax Jurisdiction'), [
            'class' => 'btn btn-success btn-lg'
        ]) ?>
        <?= Html::a('<i class="fas fa-times mr-2"></i>' . Yii::t('app', 'Cancel'), ['index'], [
            'class' => 'btn btn-secondary btn-lg ml-2'
        ]) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<script>
$(document).ready(function() {
    // Auto-calculate combined rate
    $('#calculate-combined').on('click', function() {
        const stateRate = parseFloat($('#taxjurisdiction-state_rate').val()) || 0;
        const countyRate = parseFloat($('#taxjurisdiction-county_rate').val()) || 0;
        const cityRate = parseFloat($('#taxjurisdiction-city_rate').val()) || 0;
        const specialRate = parseFloat($('#taxjurisdiction-special_rate').val()) || 0;
        
        const combinedRate = stateRate + countyRate + cityRate + specialRate;
        $('#taxjurisdiction-combined_rate').val(combinedRate.toFixed(4));
    });
    
    // Auto-calculate when individual rates change
    $('#taxjurisdiction-state_rate, #taxjurisdiction-county_rate, #taxjurisdiction-city_rate, #taxjurisdiction-special_rate').on('input', function() {
        // Auto-calculate with a small delay
        setTimeout(function() {
            $('#calculate-combined').click();
        }, 500);
    });
    
    // Auto-fill current year/month
    if (!$('#taxjurisdiction-data_year').val()) {
        $('#taxjurisdiction-data_year').val(new Date().getFullYear());
    }
    
    if (!$('#taxjurisdiction-data_month').val()) {
        $('#taxjurisdiction-data_month').val(new Date().getMonth() + 1);
    }
    
    // Auto-fill effective date if empty
    if (!$('#taxjurisdiction-effective_date').val()) {
        const today = new Date().toISOString().split('T')[0];
        $('#taxjurisdiction-effective_date').val(today);
    }
    
    // Transform state code to uppercase
    $('#taxjurisdiction-state_code').on('input', function() {
        this.value = this.value.toUpperCase();
    });
});
</script>

<style>
.card {
    border: none;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border-radius: 0.5rem;
}

.alert {
    border-radius: 0.5rem;
}

.custom-control-label {
    padding-top: 0.375rem;
}

.form-group .hint-block {
    font-size: 0.875em;
    color: #6c757d;
}

#calculate-combined {
    width: 100%;
}
</style>