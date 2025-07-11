<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\Product;

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
		<!-- Left Column -->
		<div class="col-lg-8">

			<!-- Basic Information -->
			<div class="card mb-4">
				<div class="card-header">
					<h5 class="card-title mb-0">
						<i class="fas fa-info-circle mr-2"></i>Basic Information
					</h5>
				</div>
				<div class="card-body">
					<div class="row">
						<div class="col-md-6">
							<?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
						</div>
						<div class="col-md-6">
							<?= $form->field($model, 'type')->dropDownList(Product::getTypeOptions(), ['prompt' => 'Select Type']) ?>
						</div>
					</div>

					<div class="row">
						<div class="col-md-6">
							<?= $form->field($model, 'category')->dropDownList(Product::getCategoryOptions(), ['prompt' => 'Select Category']) ?>
						</div>
						<div class="col-md-6">
							<?= $form->field($model, 'sku')->textInput(['maxlength' => true]) ?>
						</div>
					</div>

					<?= $form->field($model, 'description')->textarea(['rows' => 3]) ?>
				</div>
			</div>

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
							<?= $form->field($model, 'unit')->dropDownList(Product::getUnitOptions(), ['prompt' => 'Select Unit']) ?>
						</div>
						<div class="col-md-4">
							<?= $form->field($model, 'price')->textInput([
                                'type' => 'number',
                                'min' => 0,
                                'step' => 0.01,
                                'id' => 'price-input'
                            ]) ?>
						</div>
						<div class="col-md-4">
							<?= $form->field($model, 'cost')->textInput([
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
							This product/service is taxable
						</label>
					</div>

					<div class="form-check mb-3">
						<?= $form->field($model, 'is_active')->checkbox([
                            'class' => 'form-check-input',
                            'label' => false,
                        ]) ?>
						<label class="form-check-label" for="product-is_active">
							Product is active
						</label>
					</div>
				</div>
			</div>

			<!-- Company Information -->
			<div class="card mb-4">
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
			<div class="card mb-4">
				<div class="card-header">
					<h6 class="card-title mb-0">
						<i class="fas fa-question-circle mr-2"></i>Product Help
					</h6>
				</div>
				<div class="card-body">
					<div class="alert alert-info">
						<small>
							<strong>Name:</strong> Product or service name for identification.<br><br>
							<strong>Type:</strong> Product or Service classification.<br><br>
							<strong>Category:</strong> Group products for better organization.<br><br>
							<strong>SKU:</strong> Stock Keeping Unit for inventory tracking.<br><br>
							<strong>Price:</strong> Selling price charged to customers.<br><br>
							<strong>Cost:</strong> Your cost for profit margin calculation.<br><br>
							<strong>Taxable:</strong> Whether sales tax applies to this item.
						</small>
					</div>
				</div>
			</div>

		</div>
	</div>

	<!-- Form Actions -->
	<div class="form-group mt-4">
		<?= Html::submitButton($model->isNewRecord ? 'Create Product' : 'Update Product', [
            'class' => 'btn btn-success'
        ]) ?>
		<?= Html::a('Cancel', $model->isNewRecord ? ['index'] : ['view', 'id' => $model->id], [
            'class' => 'btn btn-secondary'
        ]) ?>
	</div>

	<?php ActiveForm::end(); ?>

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
");
?>