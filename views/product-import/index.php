<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var string $step */
/** @var array $previewData */
/** @var array $errors */
/** @var app\models\Company $company */

$this->title = Yii::t('app', 'Import Products');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Products'), 'url' => ['product/index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="product-import-index">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?= Html::encode($this->title) ?></h1>
        <div>
            <?= Html::a('<i class="fas fa-download mr-1"></i>' . Yii::t('app', 'Download Template'), 
                ['download-template'], 
                ['class' => 'btn btn-outline-primary', 'encode' => false]
            ) ?>
            <?= Html::a('<i class="fas fa-arrow-left mr-1"></i>' . Yii::t('app', 'Back to Products'), 
                ['product/index'], 
                ['class' => 'btn btn-secondary', 'encode' => false]
            ) ?>
        </div>
    </div>

    <!-- Pro Plan Badge -->
    <div class="alert alert-info mb-4">
        <i class="fas fa-crown mr-2 text-warning"></i>
        <strong><?= Yii::t('app', 'Pro Feature') ?></strong> - 
        <?= Yii::t('app', 'Product import functionality is available for Pro plan subscribers.') ?>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <h4><?= Yii::t('app', 'Import Errors') ?></h4>
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                    <li><?= Html::encode($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($step === 'upload'): ?>
        <!-- Step 1: Upload CSV File -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-upload mr-2"></i>
                    <?= Yii::t('app', 'Step 1: Upload CSV File') ?>
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <?php $form = ActiveForm::begin([
                            'options' => ['enctype' => 'multipart/form-data'],
                            'id' => 'import-form'
                        ]); ?>

                        <div class="form-group">
                            <label for="csvFile"><?= Yii::t('app', 'Choose CSV File') ?></label>
                            <input type="file" class="form-control-file" id="csvFile" name="csvFile" accept=".csv" required>
                            <small class="form-text text-muted">
                                <?= Yii::t('app', 'Maximum file size: 5MB. Only CSV files are allowed.') ?>
                            </small>
                        </div>

                        <div class="form-group">
                            <?= Html::submitButton('<i class="fas fa-eye mr-1"></i>' . Yii::t('app', 'Preview Import'), [
                                'class' => 'btn btn-primary',
                                'encode' => false
                            ]) ?>
                        </div>

                        <?php ActiveForm::end(); ?>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h5 class="card-title"><?= Yii::t('app', 'CSV Format Requirements') ?></h5>
                                <ul class="small mb-3">
                                    <li><?= Yii::t('app', 'First row must contain column headers') ?></li>
                                    <li><?= Yii::t('app', 'Product name is required') ?></li>
                                    <li><?= Yii::t('app', 'Use comma (,) as delimiter') ?></li>
                                    <li><?= Yii::t('app', 'UTF-8 encoding recommended') ?></li>
                                </ul>
                                <p class="card-text">
                                    <strong><?= Yii::t('app', 'Supported columns:') ?></strong><br>
                                    <small class="text-muted">
                                        name*, description, type, category, sku, unit, price, cost, is_taxable
                                    </small>
                                </p>
                                <p class="card-text">
                                    <strong><?= Yii::t('app', 'Product types:') ?></strong><br>
                                    <small class="text-muted">service, product, non_inventory</small>
                                </p>
                                <p class="card-text">
                                    <small class="text-muted">* <?= Yii::t('app', 'Required field') ?></small>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <?php elseif ($step === 'preview' && $previewData): ?>
        <!-- Step 2: Preview Data -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-eye mr-2"></i>
                    <?= Yii::t('app', 'Step 2: Preview Import Data') ?>
                </h3>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle mr-2"></i>
                    <?= Yii::t('app', 'Found {total} rows to import. Showing first 10 rows for preview.', [
                        'total' => $previewData['total_rows']
                    ]) ?>
                </div>

                <div class="table-responsive mb-4">
                    <table class="table table-bordered table-sm">
                        <thead class="thead-light">
                            <tr>
                                <?php foreach ($previewData['headers'] as $header): ?>
                                    <th><?= Html::encode($header) ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($previewData['data'] as $row): ?>
                                <tr>
                                    <?php foreach ($previewData['headers'] as $header): ?>
                                        <td><?= Html::encode($row[$header] ?? '') ?></td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between">
                    <?= Html::a('<i class="fas fa-arrow-left mr-1"></i>' . Yii::t('app', 'Back'), 
                        ['index'], 
                        ['class' => 'btn btn-secondary', 'encode' => false]
                    ) ?>
                    
                    <?php $form = ActiveForm::begin(); ?>
                    <?= Html::hiddenInput('confirm_import', '1') ?>
                    <?= Html::submitButton('<i class="fas fa-check mr-1"></i>' . Yii::t('app', 'Confirm Import'), [
                        'class' => 'btn btn-success',
                        'encode' => false,
                        'data-confirm' => Yii::t('app', 'Are you sure you want to import {count} products?', [
                            'count' => $previewData['total_rows']
                        ])
                    ]) ?>
                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Help Section -->
    <div class="card mt-4">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-question-circle mr-2"></i>
                <?= Yii::t('app', 'Import Guidelines') ?>
            </h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h5><?= Yii::t('app', 'Before You Import') ?></h5>
                    <ul>
                        <li><?= Yii::t('app', 'Download the template file to see the correct format') ?></li>
                        <li><?= Yii::t('app', 'Ensure your CSV file uses UTF-8 encoding') ?></li>
                        <li><?= Yii::t('app', 'Product name is the only required field') ?></li>
                        <li><?= Yii::t('app', 'Categories will be created automatically if they don\'t exist') ?></li>
                        <li><?= Yii::t('app', 'Duplicate SKUs will be rejected') ?></li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h5><?= Yii::t('app', 'Field Mappings') ?></h5>
                    <ul>
                        <li><strong>name</strong>: <?= Yii::t('app', 'Product or service name (required)') ?></li>
                        <li><strong>type</strong>: <?= Yii::t('app', 'service, product, or non_inventory') ?></li>
                        <li><strong>category</strong>: <?= Yii::t('app', 'Category name (auto-created if new)') ?></li>
                        <li><strong>price</strong>: <?= Yii::t('app', 'Selling price (decimal number)') ?></li>
                        <li><strong>cost</strong>: <?= Yii::t('app', 'Cost basis (decimal number)') ?></li>
                        <li><strong>is_taxable</strong>: <?= Yii::t('app', '1 or 0, true/false, yes/no') ?></li>
                    </ul>
                </div>
            </div>
            
            <div class="row mt-4">
                <div class="col-12">
                    <h5><?= Yii::t('app', 'Common Units') ?></h5>
                    <p class="text-muted small">
                        each, hour, piece, box, kg, lb, meter, foot, sqft, sqm, month, year
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$this->registerCss("
    .card {
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
        border: none;
    }
    
    .table th {
        font-size: 12px;
        white-space: nowrap;
    }
    
    .table td {
        font-size: 12px;
        max-width: 150px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    
    .alert {
        border-radius: 8px;
    }
");
?>