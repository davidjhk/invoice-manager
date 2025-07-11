<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\ProductCategory $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="category-form">

    <?php $form = ActiveForm::begin([
        'id' => 'category-form',
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
                        <i class="fas fa-tag mr-2"></i>Category Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($model, 'name')->textInput([
                                'maxlength' => true,
                                'placeholder' => 'Enter category name',
                                'required' => true
                            ]) ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'sort_order')->textInput([
                                'type' => 'number',
                                'min' => 1,
                                'placeholder' => 'Sort order (optional)'
                            ])->hint('Lower numbers appear first. Leave empty to auto-assign.') ?>
                        </div>
                    </div>

                    <?= $form->field($model, 'description')->textarea([
                        'rows' => 3,
                        'placeholder' => 'Optional description for this category'
                    ]) ?>

                    <div class="form-check">
                        <?= $form->field($model, 'is_active')->checkbox([
                            'class' => 'form-check-input',
                            'label' => false,
                        ]) ?>
                        <label class="form-check-label" for="productcategory-is_active">
                            Category is active
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Help Information -->
            <div class="card">
                <div class="card-header p-2" style="cursor: pointer;" data-custom-collapse="true" data-target="#category-help-collapse" aria-expanded="false">
                    <h6 class="card-title mb-0 d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-question-circle mr-2"></i>Category Help</span>
                        <i class="fas fa-chevron-down collapse-icon"></i>
                    </h6>
                </div>
                <div class="collapse" id="category-help-collapse">
                    <div class="card-body py-2">
                        <div class="alert alert-info py-2 mb-0">
                            <small>
                                <strong>Name:</strong> Unique category name within your company.<br>
                                <strong>Description:</strong> Optional details about this category.<br>
                                <strong>Sort Order:</strong> Controls the display order in dropdowns.<br>
                                <strong>Active:</strong> Only active categories appear in product forms.
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="form-group mt-4">
        <?= Html::submitButton($model->isNewRecord ? 'Create Category' : 'Update Category', [
            'class' => 'btn btn-success'
        ]) ?>
        <?= Html::a('Cancel', $model->isNewRecord ? ['index'] : ['view', 'id' => $model->id], [
            'class' => 'btn btn-secondary'
        ]) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?php
$this->registerJs("
    // Form validation
    $('#category-form').on('submit', function(e) {
        const form = this;
        if (!form.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
        }
        form.classList.add('was-validated');
    });
");
?>