<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\Product;
use app\models\ProductCategory;

/** @var yii\web\View $this */
/** @var app\models\Product $model */
/** @var app\models\Company $company */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="product-form">

	<?php $form = ActiveForm::begin([
        'id' => 'product-form',
        'options' => ['class' => 'needs-validation', 'novalidate' => true],
        'fieldConfig' => [
            'options' => ['class' => 'form-group'],
            'inputOptions' => ['class' => 'form-control'],
            'labelOptions' => ['class' => 'form-label font-weight-bold'],
        ],
    ]); ?>

	<div class="row">
		<div class="col-lg-12">

			<!-- Basic Information -->
			<div class="card mb-4">
				<div class="card-header">
					<h5 class="card-title mb-0">
						<i class="fas fa-info-circle mr-2"></i><?= Yii::t('app/product', 'Product Information') ?>
					</h5>
				</div>
				<div class="card-body">
					<div class="row">
						<div class="col-md-6">
							<?= $form->field($model, 'name')->label(Yii::t('app/product', 'Name'))->textInput(['maxlength' => true]) ?>
						</div>
						<div class="col-md-6">
							<?= $form->field($model, 'type')->label(Yii::t('app/product', 'Type'))->dropDownList(Product::getTypeOptions(), ['prompt' => Yii::t('app', 'Select Type')]) ?>
						</div>
					</div>

					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<?= Html::label(Yii::t('app/product', 'Category'), 'product-category_id', ['class' => 'form-label font-weight-bold']) ?>
								<div class="input-group">
									<?= Html::dropDownList('Product[category_id]', $model->category_id, ProductCategory::getCategoryOptions($company->id), [
										'class' => 'form-control',
										'prompt' => Yii::t('app', 'Select Category'),
										'id' => 'product-category_id'
									]) ?>
									<div class="input-group-append">
										<button type="button" class="btn btn-outline-secondary" data-toggle="modal"
											data-target="#categoryModal" title="Add New Category">
											<i class="fas fa-plus"></i>
										</button>
										<a href="<?= \yii\helpers\Url::to(['/category/index']) ?>"
											class="btn btn-outline-info" title="Manage Categories">
											<i class="fas fa-cog"></i>
										</a>
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<?= $form->field($model, 'sku')->label(Yii::t('app/product', 'SKU'))->textInput(['maxlength' => true]) ?>
						</div>
					</div>

					<?= $form->field($model, 'description')->label(Yii::t('app/product', 'Description'))->textarea(['rows' => 3]) ?>
				</div>
			</div>
		</div>
		<!-- Left Column -->
		<div class="col-lg-8">

			<!-- Pricing Information -->
			<div class="card mb-4">
				<div class="card-header">
					<h5 class="card-title mb-0">
						<i class="fas fa-dollar-sign mr-2"></i>Pricing & Cost
					</h5>
				</div>
				<div class="card-body">
					<div class="row">
						<div class="col-md-4">
							<?= $form->field($model, 'unit')->label(Yii::t('app/product', 'Unit'))->dropDownList(Product::getUnitOptions(), ['prompt' => Yii::t('app', 'Select Unit')]) ?>
						</div>
						<div class="col-md-4">
							<?= $form->field($model, 'price')->label(Yii::t('app/product', 'Price'))->textInput([
                                'type' => 'number',
                                'min' => 0,
                                'step' => 0.01,
                                'id' => 'price-input'
                            ]) ?>
						</div>
						<div class="col-md-4">
							<?= $form->field($model, 'cost')->label(Yii::t('app/product', 'Cost'))->textInput([
                                'type' => 'number',
                                'min' => 0,
                                'step' => 0.01,
                                'id' => 'cost-input'
                            ]) ?>
						</div>
					</div>

					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label class="form-label">Profit Margin</label>
								<div class="input-group">
									<input type="text" class="form-control" id="margin-display" readonly>
									<div class="input-group-append">
										<span class="input-group-text">%</span>
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label class="form-label">Profit Amount</label>
								<div class="input-group">
									<div class="input-group-prepend">
										<span class="input-group-text">$</span>
									</div>
									<input type="text" class="form-control" id="profit-display" readonly>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

		</div>

		<!-- Right Column -->
		<div class="col-lg-4">

			<!-- Settings -->
			<div class="card mb-4">
				<div class="card-header">
					<h5 class="card-title mb-0">
						<i class="fas fa-cog mr-2"></i>Settings
					</h5>
				</div>
				<div class="card-body">
					<div class="form-check mb-3">
						<?= $form->field($model, 'is_taxable')->checkbox([
                            'class' => 'form-check-input',
                            'label' => false,
                        ]) ?>
						<label class="form-check-label" for="product-is_taxable">
							<?= Yii::t('app/product', 'This product is subject to tax') ?>
						</label>
					</div>

					<div class="form-check mb-3">
						<?= $form->field($model, 'is_active')->checkbox([
                            'class' => 'form-check-input',
                            'label' => false,
                        ]) ?>
						<label class="form-check-label" for="product-is_active">
							<?= Yii::t('app/product', 'Active') ?>
						</label>
					</div>
				</div>
			</div>

			<!-- Company Information -->
			<div class="card mb-4" style="display: none;">
				<div class="card-header">
					<h5 class="card-title mb-0">
						<i class="fas fa-building mr-2"></i>Company
					</h5>
				</div>
				<div class="card-body">
					<div class="form-group">
						<label class="form-label">Company</label>
						<input type="text" class="form-control" value="<?= Html::encode($company->company_name) ?>"
							readonly>
					</div>
				</div>
			</div>

			<!-- Help Information -->
			<div class="card mb-4" style="display: none;">
				<div class="card-header p-2" style="cursor: pointer;" data-custom-collapse="true"
					data-target="#product-help-collapse" aria-expanded="false">
					<h6 class="card-title mb-0 d-flex justify-content-between align-items-center">
						<span><i class="fas fa-question-circle mr-2"></i>Product Help</span>
						<i class="fas fa-chevron-down collapse-icon"></i>
					</h6>
				</div>
				<div class="collapse" id="product-help-collapse">
					<div class="card-body py-2">
						<div class="alert alert-info py-2 mb-0">
							<small>
								<strong>Name:</strong> Product or service name for identification.<br>
								<strong>Type:</strong> Product or Service classification.<br>
								<strong>Category:</strong> Select from categories or add new ones using the +
								button.<br>
								<strong>SKU:</strong> Stock Keeping Unit for inventory tracking.<br>
								<strong>Price:</strong> Selling price charged to customers.<br>
								<strong>Cost:</strong> Your cost for profit margin calculation.<br>
								<strong>Taxable:</strong> Whether sales tax applies to this item.
							</small>
						</div>
					</div>
				</div>
			</div>

		</div>
	</div>

	<!-- Form Actions -->
	<div class="form-group mt-4">
		<?= Html::submitButton($model->isNewRecord ? Yii::t('app/product', 'Create Product') : Yii::t('app/product', 'Update Product'), [
            'class' => 'btn btn-success'
        ]) ?>
		<?= Html::a(Yii::t('app/product', 'Cancel'), $model->isNewRecord ? ['index'] : ['view', 'id' => $model->id], [
            'class' => 'btn btn-secondary'
        ]) ?>
	</div>

	<?php ActiveForm::end(); ?>

</div>

<!-- Category Modal -->
<div class="modal fade" id="categoryModal" tabindex="-1" role="dialog" aria-labelledby="categoryModalLabel"
	aria-hidden="true" data-backdrop="true" data-keyboard="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="categoryModalLabel">
					<i class="fas fa-tag mr-2"></i>Add New Category
				</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<form id="categoryForm">
					<input type="hidden" id="modal-company-id" value="<?= $company->id ?>">

					<div class="form-group">
						<label for="modal-category-name" class="form-label font-weight-bold">Category Name</label>
						<input type="text" class="form-control" id="modal-category-name" required maxlength="100"
							placeholder="Enter category name">
					</div>

					<div class="form-group">
						<label for="modal-category-description" class="form-label font-weight-bold">Description
							(Optional)</label>
						<textarea class="form-control" id="modal-category-description" rows="2"
							placeholder="Optional description for this category"></textarea>
					</div>

					<div class="form-check">
						<input type="checkbox" class="form-check-input" id="modal-category-active" checked>
						<label class="form-check-label" for="modal-category-active">
							Category is active
						</label>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-outline-secondary" data-dismiss="modal">
					<i class="fas fa-times mr-1"></i>Cancel
				</button>
				<button type="button" class="btn btn-success" id="saveCategoryBtn">
					<i class="fas fa-plus mr-1"></i>Add Category
				</button>
			</div>
		</div>
	</div>
</div>

<?php
// JavaScript for margin calculation
$this->registerJs("
    function calculateMargin() {
        var price = parseFloat($('#price-input').val()) || 0;
        var cost = parseFloat($('#cost-input').val()) || 0;
        
        var profit = price - cost;
        var margin = price > 0 ? (profit / price) * 100 : 0;
        
        $('#margin-display').val(margin.toFixed(2));
        $('#profit-display').val(profit.toFixed(2));
        
        // Update margin color
        var marginClass = 'text-dark';
        if (margin > 50) {
            marginClass = 'text-success';
        } else if (margin > 20) {
            marginClass = 'text-warning';
        } else if (margin < 0) {
            marginClass = 'text-danger';
        }
        
        $('#margin-display').removeClass('text-success text-warning text-danger text-dark').addClass(marginClass);
    }
    
    // Calculate margin on price/cost change
    $('#price-input, #cost-input').on('input', calculateMargin);
    
    // Calculate initial margin
    calculateMargin();
    
    // Category Modal functionality
    $('#saveCategoryBtn').on('click', function() {
        var name = $('#modal-category-name').val().trim();
        var description = $('#modal-category-description').val().trim();
        var isActive = $('#modal-category-active').is(':checked');
        var companyId = $('#modal-company-id').val();
        
        if (!name) {
            alert('Please enter a category name.');
            return;
        }
        
        // Disable button and show loading
        var button = $(this);
        var originalText = button.html();
        button.html('<i class=\"fas fa-spinner fa-spin mr-1\"></i>Adding...').prop('disabled', true);
        
        $.ajax({
            url: '" . \yii\helpers\Url::to(['/category/create-ajax']) . "',
            type: 'POST',
            data: {
                'ProductCategory[name]': name,
                'ProductCategory[description]': description,
                'ProductCategory[is_active]': isActive ? 1 : 0,
                'ProductCategory[company_id]': companyId,
                '_csrf': $('meta[name=csrf-token]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    // Add new option to dropdown
                    var option = new Option(response.category.name, response.category.id, false, true);
                    $('#product-category_id').append(option);
                    
                    // Clear form
                    $('#modal-category-name').val('');
                    $('#modal-category-description').val('');
                    $('#modal-category-active').prop('checked', true);
                    
                    // Close modal properly
                    $('#categoryModal').modal('hide');
                    
                    // Force remove backdrop and modal-open class
                    setTimeout(function() {
                        $('.modal-backdrop').remove();
                        $('body').removeClass('modal-open');
                        $('body').css('padding-right', '');
                    }, 300);
                    
                    // Show success message
                    alert('Category \"' + response.category.name + '\" has been added successfully!');
                } else {
                    alert('Error: ' + (response.message || 'Failed to create category.'));
                }
            },
            error: function() {
                alert('Error: Failed to create category. Please try again.');
            },
            complete: function() {
                // Restore button
                button.html(originalText).prop('disabled', false);
            }
        });
    });
    
    // Clear modal when it's closed and ensure backdrop is removed
    $('#categoryModal').on('hidden.bs.modal', function() {
        $('#modal-category-name').val('');
        $('#modal-category-description').val('');
        $('#modal-category-active').prop('checked', true);
        
        // Ensure backdrop is completely removed
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open');
        $('body').css('padding-right', '');
    });
    
    // Handle modal close button clicks
    $('#categoryModal .close, #categoryModal [data-dismiss=\"modal\"]').on('click', function() {
        $('#categoryModal').modal('hide');
        setTimeout(function() {
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open');
            $('body').css('padding-right', '');
        }, 300);
    });
    
    // Initialize tooltips
    $('[title]').tooltip();
    
    // Collapse functionality is handled by collapse-helper.js
");

// Add CSS to ensure modal backdrop is properly handled
$this->registerCss("
    /* Ensure modal backdrop is properly removed */
    .modal-backdrop {
        z-index: 1040;
    }
    
    .modal {
        z-index: 1050;
    }
    
    /* Force remove any lingering backdrop styles */
    body.modal-open {
        overflow: hidden;
    }
");
?>