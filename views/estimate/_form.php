<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use app\models\State;

/** @var yii\web\View $this */
/** @var app\models\Estimate $model */
/** @var app\models\Company $company */
/** @var app\models\Customer[] $customers */
/** @var yii\widgets\ActiveForm $form */

// Prepare data for JavaScript
$customerDataUrl = Url::to(['/customer/get-data']);
$productSearchUrl = Url::to(['/product/search']);
$customerUpdateUrl = Url::to(['/customer/update']);
$customerCreateUrl = Url::to(['/customer/create-ajax']);

$existingItems = [];
if (!$model->isNewRecord) {
    try {
        $items = $model->estimateItems;
        if ($items) {
            foreach ($items as $item) {
                $existingItems[] = [
                    'product_service_name' => $item->product_service_name ?? '',
                    'product_id' => $item->product_id ?? '',
                    'description' => $item->description ?? '',
                    'quantity' => $item->quantity ?? 1,
                    'rate' => $item->rate ?? 0,
                    'is_taxable' => $item->is_taxable ? true : false
                ];
            }
        }
    } catch (Exception $e) {
        // In case of error, keep existingItems empty
    }
}

$this->registerJsVar('estimateConfig', [
    'customerDataUrl' => $customerDataUrl,
    'productSearchUrl' => $productSearchUrl,
    'customerUpdateUrl' => $customerUpdateUrl,
    'customerCreateUrl' => $customerCreateUrl,
    'existingItems' => $existingItems,
    'isNewRecord' => $model->isNewRecord,
    'selectedCustomerId' => $model->isNewRecord ? null : $model->customer_id,
]);

?>

<div class="estimate-form">

	<?php $form = ActiveForm::begin([
        'id' => 'estimate-form',
        'options' => ['class' => 'needs-validation', 'novalidate' => true],
        'fieldConfig' => [
            'options' => ['class' => 'form-group'],
            'inputOptions' => ['class' => 'form-control'],
            'labelOptions' => ['class' => 'form-label font-weight-bold'],
        ],
    ]); ?>

	<div class="row">
		<div class="col-lg-7">
			<div class="card card-default">
				<div class="card-header" style="display: none;">
					<h5 class="card-title mb-0">
						<i class="fas fa-file-invoice mr-2"></i><?= strtoupper(Yii::t('app/estimate', 'Estimate')) ?>
					</h5>
				</div>
				<div class="card-body">
					<div class="row">
						<div class="col-md-12">
							<?= $form->field($model, 'customer_id')->dropDownList(
                                ArrayHelper::map($customers, 'id', 'customer_name'),
                                [
                                    'prompt' => Yii::t('app/estimate', 'Select Customer'),
                                    'id' => 'customer-select',
                                ]
                            )->label(Yii::t('app/estimate', 'Customer')) ?>

							<div class="d-flex align-items-center mb-3">
								<small
									class="text-muted mr-2"><?= Yii::t('app/estimate', 'Customer not in list?') ?></small>
								<?= Html::button(Yii::t('app/estimate', 'Add New Customer'), [
									'class' => 'btn btn-outline-success btn-sm',
									'id' => 'add-customer-btn',
									'data-toggle' => 'modal',
									'data-target' => '#addCustomerModal'
								]) ?>
							</div>
						</div>
						<div class="col-md-6">
							<div id="customer-details" class="mb-3">
								<label class="form-label font-weight-bold"><?= Yii::t('app/estimate', 'Customer Information') ?></label>
								<div id="bill-to-address" class="border p-2 bg-light min-h-100 rounded">
									<?php if (!empty($model->bill_to_address)): ?>
									<?= nl2br(Html::encode($model->bill_to_address)) ?>
									<?php else: ?>
									<span class="text-muted"><?= Yii::t('app/estimate', 'Select Customer') ?>.</span>
									<?php endif; ?>
								</div>
								<?= $form->field($model, 'bill_to_address')->hiddenInput(['id' => 'estimate-bill_to_address'])->label(false) ?>
							</div>
							<?= Html::button(Yii::t('app/estimate', 'Edit'), [
                                'class' => 'btn btn-outline-primary btn-sm',
                                'id' => 'edit-customer-btn'
                            ]) ?>
						</div>
						<div class="col-md-6">
							<?= $form->field($model, 'ship_to_address')->textarea([
                                'rows' => 4,
                                'placeholder' => Yii::t('app/estimate', 'Shipping address (if different from billing)')
                            ])->label(Yii::t('app/estimate', 'Ship To')) ?>

							<?= Html::button(Yii::t('app/estimate', 'Clear Shipping Info'), [
                                'class' => 'btn btn-link btn-sm p-0',
                                'id' => 'remove-shipping-btn'
                            ]) ?>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="col-lg-5">
			<div class="card card-default">
				<div class="card-header" style="display: none;">
					<h5 class="card-title mb-0">
						<i class="fas fa-info-circle mr-2"></i><?= Yii::t('app/estimate', 'Estimate Details') ?>
					</h5>
				</div>
				<div class="card-body">
					<div class="row">
						<div class="col-md-6">
							<?= $form->field($model, 'estimate_number')->textInput(['maxlength' => true])->label(Yii::t('app/estimate', 'Estimate Number')) ?>
							<?= $form->field($model, 'estimate_date')->input('date')->label(Yii::t('app/estimate', 'Estimate Date')) ?>
						</div>
						<div class="col-md-6">
							<?= $form->field($model, 'terms')->dropDownList([
                                'Net 15' => 'Net 15',
                                'Net 30' => 'Net 30',
                                'Net 60' => 'Net 60',
                                'Due on receipt' => 'Due on receipt'
                            ], ['prompt' => Yii::t('app/estimate', 'Select terms')]) ?>
							<?= $form->field($model, 'expiry_date')->input('date')->label(Yii::t('app/estimate', 'Expiry Date')) ?>
						</div>
					</div>
				</div>
			</div>

			<!-- Help Information -->
			<div class="card mt-3" style="display: none;">
				<div class="card-header p-2" style="cursor: pointer;" data-toggle="collapse"
					data-target="#estimate-help-collapse" aria-expanded="false">
					<h6 class="card-title mb-0 d-flex justify-content-between align-items-center">
						<span><i
								class="fas fa-question-circle mr-2"></i><?= Yii::t('app/estimate', 'Estimate Help') ?></span>
						<i class="fas fa-chevron-down collapse-icon"></i>
					</h6>
				</div>
				<div class="collapse" id="estimate-help-collapse">
					<div class="card-body py-2">
						<div class="alert alert-info py-2 mb-0">
							<small>
								<strong><?= Yii::t('app/estimate', 'Estimate Number') ?>:</strong>
								<?= Yii::t('app/estimate', 'Unique identifier for this estimate.') ?><br>
								<strong><?= Yii::t('app/estimate', 'Terms') ?>:</strong>
								<?= Yii::t('app/estimate', 'Terms and conditions for this estimate.') ?><br>
								<strong><?= Yii::t('app/estimate', 'Estimate Date') ?>:</strong>
								<?= Yii::t('app/estimate', 'Date when estimate is issued.') ?><br>
								<strong><?= Yii::t('app/estimate', 'Expiry Date') ?>:</strong>
								<?= Yii::t('app/estimate', 'Expiry date for this estimate.') ?><br>
								<strong><?= Yii::t('app/estimate', 'Items') ?>:</strong>
								<?= Yii::t('app/estimate', 'Add products/services with quantity and rate.') ?><br>
								<strong><?= Yii::t('app/estimate', 'Convert to Invoice') ?>:</strong>
								<?= Yii::t('app/estimate', 'Convert approved estimates to invoices.') ?>
							</small>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="card card-default mt-4">
		<div class="card-header" style="display: none;">
			<h5 class="card-title mb-0">
				<i class="fas fa-list mr-2"></i><?= Yii::t('app/estimate', 'Items') ?>
			</h5>
		</div>
		<div class="card-body">
			<div class="table-responsive">
				<table class="table table-bordered" id="items-table">
					<thead class="thead-light">
						<tr>
							<th style="width: 5%;">#</th>
							<th style="width: 25%;"><?= Yii::t('app/estimate', 'Product/Service') ?></th>
							<th style="width: 35%;"><?= Yii::t('app/estimate', 'Description') ?></th>
							<th style="width: 10%;" class="text-right"><?= Yii::t('app/estimate', 'Quantity') ?></th>
							<th style="width: 10%;" class="text-right"><?= Yii::t('app/estimate', 'Price') ?></th>
							<th style="width: 10%;" class="text-right"><?= Yii::t('app/estimate', 'Amount') ?></th>
							<th style="width: 5%;" class="text-center"><?= Yii::t('app/estimate', 'Tax') ?></th>
							<th style="width: 5%;"></th>
						</tr>
					</thead>
					<tbody id="items-tbody">
						<!-- Items will be added dynamically by JavaScript -->
					</tbody>
				</table>
			</div>
			<?= Html::button(Yii::t('app/estimate', 'Add Item'), ['class' => 'btn btn-outline-primary', 'id' => 'add-item-btn']) ?>
			<?= Html::button(Yii::t('app/estimate', 'Clear All'), ['class' => 'btn btn-outline-danger', 'id' => 'clear-lines-btn']) ?>
		</div>
	</div>

	<div class="row mt-4">
		<div class="col-lg-6">
			<div class="card card-default">
				<div class="card-header" style="display: none;">
					<h5 class="card-title mb-0">
						<i class="fas fa-comment mr-2"></i><?= Yii::t('app/estimate', 'Notes') ?>
					</h5>
				</div>
				<div class="card-body">
					<?= $form->field($model, 'customer_notes')->textarea(['rows' => 3, 'placeholder' => Yii::t('app/estimate', 'e.g. This estimate is valid for 30 days.')])->label(Yii::t('app/estimate', 'Note to Customer')) ?>
					<?= $form->field($model, 'memo')->textarea(['rows' => 3, 'placeholder' => Yii::t('app/estimate', 'Internal notes for your team.')])->label(Yii::t('app/estimate', 'Memo (Internal)')) ?>
				</div>
			</div>
		</div>

		<div class="col-lg-6">
			<div class="card card-default">
				<div class="card-header" style="display: none;">
					<h5 class="card-title mb-0">
						<i class="fas fa-calculator mr-2"></i><?= Yii::t('app/estimate', 'Total') ?>
					</h5>
				</div>
				<div class="card-body">
					<div class="totals-grid">
						<span><?= Yii::t('app/estimate', 'Subtotal') ?></span>
						<span id="subtotal-display" class="text-right">$0.00</span>

						<span><?= Yii::t('app/estimate', 'Discount') ?></span>
						<div class="text-right d-flex justify-content-end align-items-center">
							<?= $form->field($model, 'discount_value', ['options' => ['class' => 'mb-0 mr-1'], 'template' => '{input}'])->textInput(['id' => 'discount-input', 'class' => 'form-control form-control-sm text-right', 'style' => 'width: 60px;', 'placeholder' => '0']) ?>
							<?= $form->field($model, 'discount_type', ['options' => ['class' => 'mb-0'], 'template' => '{input}'])->dropDownList(['percentage' => '%', 'fixed' => '$'], ['id' => 'discount-type', 'class' => 'form-control form-control-sm', 'style' => 'width: 50px;']) ?>
							<span id="discount-display" class="ml-2">-$0.00</span>
						</div>

						<span><?= Yii::t('app/estimate', 'Taxable Subtotal') ?></span>
						<span id="taxable-subtotal-display" class="text-right">$0.00</span>

						<span><?= Yii::t('invoice', 'Tax Calculation') ?></span>
						<div class="text-right">
							<?php if ($model->hasAttribute('tax_calculation_mode')): ?>
							<?= $form->field($model, 'tax_calculation_mode', ['options' => ['class' => 'mb-2'], 'template' => '{input}'])->dropDownList(
									\app\models\Estimate::getTaxCalculationModeOptions(),
									['id' => 'tax-calculation-mode', 'class' => 'form-control form-control-sm']
								) ?>
							<?php else: ?>
							<select id="tax-calculation-mode" class="form-control form-control-sm mb-2">
								<option value="manual" selected><?= Yii::t('invoice', 'Manual Input') ?></option>
								<option value="automatic"><?= Yii::t('invoice', 'Automatic Calculation') ?></option>
							</select>
							<?php endif; ?>
						</div>

						<span><?= Yii::t('app/estimate', 'Shipping Fee') ?></span>
						<div class="text-right d-flex justify-content-end align-items-center">
							<?= $form->field($model, 'shipping_fee', ['options' => ['class' => 'mb-0'], 'template' => '{input}'])->textInput(['id' => 'shipping-fee-input', 'class' => 'form-control form-control-sm text-right', 'style' => 'width: 120px;', 'placeholder' => '0.00', 'type' => 'number', 'step' => '0.01', 'min' => '0']) ?>
							<span id="shipping-fee-display" class="ml-2">$0.00</span>
						</div>

						<span><?= Yii::t('app/estimate', 'Sales Tax') ?></span>
						<div class="text-right d-flex justify-content-end align-items-center">
							<input type="number" class="form-control form-control-sm mr-2" id="tax-rate-input"
								style="width: 120px;" min="0" max="100" step="0.01"
								value="<?= ($model->hasAttribute('tax_calculation_mode') && $model->tax_calculation_mode === \app\models\Estimate::TAX_MODE_AUTOMATIC) ? ($model->hasAttribute('auto_calculated_tax_rate') ? ($model->auto_calculated_tax_rate ?? 0) : 0) : ($company->tax_rate ?? 0) ?>"
								placeholder="0.00">
							<span class="mr-2">%</span>
							<span id="tax-display">$0.00</span>
							<button type="button" class="btn btn-outline-primary btn-sm ml-2" id="calculate-tax-btn"
								style="display: none;">
								<i class="fas fa-calculator"></i> <?= Yii::t('invoice', 'Calculate') ?>
							</button>
						</div>
					</div>

					<hr>

					<div class="totals-grid font-weight-bold h5">
						<span><?= Yii::t('app/estimate', 'Total') ?></span>
						<span id="total-display" class="text-right">$0.00</span>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Hidden fields for calculation values -->
	<?= $form->field($model, 'tax_rate')->hiddenInput(['id' => 'tax-rate-hidden'])->label(false) ?>

	<div class="form-group mt-4">
		<?= Html::submitButton($model->isNewRecord ? Yii::t('app/estimate', 'Create Estimate') : Yii::t('app/estimate', 'Update Estimate'), ['class' => 'btn btn-success']) ?>
		<?= Html::a(Yii::t('app/estimate', 'Cancel'), ['index'], ['class' => 'btn btn-secondary']) ?>
	</div>

	<?php ActiveForm::end(); ?>

</div>

<!-- Add Customer Modal -->
<div class="modal fade" id="addCustomerModal" tabindex="-1" role="dialog" aria-labelledby="addCustomerModalLabel"
	aria-hidden="true">
	<div class="modal-dialog modal-dialog-wide" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="addCustomerModalLabel"><?= Yii::t('app/estimate', 'Add New Customer') ?>
				</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<form id="add-customer-form">
					<div class="form-group">
						<label for="new-customer-name"><?= Yii::t('app/customer', 'Customer Name') ?> <span
								class="text-danger">*</span></label>
						<input type="text" class="form-control" id="new-customer-name" name="customer_name" required>
					</div>
					<div class="form-group">
						<label for="new-customer-email"><?= Yii::t('app/customer', 'Email') ?></label>
						<input type="email" class="form-control" id="new-customer-email" name="customer_email">
					</div>
					<div class="form-group">
						<label for="new-customer-phone"><?= Yii::t('app/customer', 'Phone') ?></label>
						<input type="tel" class="form-control" id="new-customer-phone" name="customer_phone"
							placeholder="e.g. +1 (555) 123-4567" pattern="[\+\-\s\(\)\d\.\#\*]*">
					</div>
					<div class="form-group">
						<label for="new-customer-address"><?= Yii::t('app/customer', 'Address') ?></label>
						<textarea class="form-control" id="new-customer-address" name="customer_address"
							rows="2"></textarea>
					</div>
					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label for="new-customer-city"><?= Yii::t('app/customer', 'City') ?></label>
								<input type="text" class="form-control" id="new-customer-city" name="city">
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<label for="new-customer-state"><?= Yii::t('app/customer', 'State') ?></label>
								<?= Html::dropDownList('state', '', State::getUsStateList(), [
									'class' => 'form-control',
									'id' => 'new-customer-state',
									'prompt' => Yii::t('app/customer', 'Select State')
								]) ?>
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<label for="new-customer-zip"><?= Yii::t('app/customer', 'ZIP Code') ?></label>
								<input type="text" class="form-control" id="new-customer-zip" name="zip_code"
									placeholder="12345">
							</div>
						</div>
					</div>
					<div class="form-group">
						<label for="new-customer-terms"><?= Yii::t('app/customer', 'Payment Terms') ?></label>
						<select class="form-control" id="new-customer-terms" name="payment_terms">
							<option value=""><?= Yii::t('app/customer', 'Select terms') ?></option>
							<option value="Net 15">Net 15</option>
							<option value="Net 30">Net 30</option>
							<option value="Net 60">Net 60</option>
							<option value="Due on receipt">Due on receipt</option>
						</select>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary"
					data-dismiss="modal"><?= Yii::t('app', 'Cancel') ?></button>
				<button type="button" class="btn btn-success"
					id="save-customer-btn"><?= Yii::t('app/customer', 'Add Customer') ?></button>
			</div>
		</div>
	</div>
</div>

<?php if (Yii::$app->user->identity && Yii::$app->user->identity->canUseAiHelper()): ?>
<!-- AI Helper Modal -->
<div class="modal fade" id="aiHelperModal" tabindex="-1" role="dialog" aria-labelledby="aiHelperModalLabel"
	aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="aiHelperModalLabel">
					<i class="fas fa-robot mr-2"></i><?= Yii::t('app', 'AI Helper') ?>
				</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<!-- Input Section -->
				<div id="ai-input-section">
					<div class="form-group">
						<label for="ai-question"><?= Yii::t('app', 'Ask AI Helper') ?>:</label>
						<div class="input-group">
							<input type="text" id="ai-question" class="form-control"
								placeholder="<?= Yii::t('app', 'Enter product/service name or ask a question...') ?>"
								autofocus>
							<div class="input-group-append">
								<button type="button" class="btn btn-primary" id="ask-ai-btn">
									<i class="fas fa-paper-plane"></i>
								</button>
							</div>
						</div>
						<small class="form-text text-muted">
							<?= Yii::t('app', 'Examples: "Website development", "Consulting services", "How to write professional invoice descriptions?"') ?>
						</small>
					</div>
					<div class="form-group">
						<label for="ai-project-complexity"><?= Yii::t('app', 'Project Complexity') ?>:</label>
						<select class="form-control" id="ai-project-complexity">
							<option value="simple"><?= Yii::t('app', 'Simple Service') ?> ($100-$2,000)</option>
							<option value="small"><?= Yii::t('app', 'Small Project') ?> ($2,000-$8,000)</option>
							<option value="medium" selected><?= Yii::t('app', 'Medium Project') ?> ($8,000-$25,000)
							</option>
							<option value="complex"><?= Yii::t('app', 'Complex Project') ?> ($25,000-$50,000)</option>
						</select>
					</div>
					<div class="form-group">
						<label for="ai-response-language"><?= Yii::t('app', 'Response Language') ?>:</label>
						<select class="form-control" id="ai-response-language">
							<option value="en">English</option>
							<option value="ko" <?= (Yii::$app->language === 'ko-KR') ? 'selected' : '' ?>>한국어 (Korean)
							</option>
							<option value="es">Español (Spanish)</option>
							<option value="zh-cn">中文简体 (Chinese Simplified)</option>
							<option value="zh-tw">中文繁體 (Chinese Traditional)</option>
						</select>
					</div>
				</div>

				<!-- Results Section -->
				<div id="ai-helper-content" style="display: none;">
					<!-- AI responses will be displayed here -->
				</div>

				<!-- Pricing Recommendation Section -->
				<div id="ai-pricing-section" style="display: none;">
					<div class="card border-success mt-3">
						<div class="card-header bg-light">
							<h6 class="mb-0">
								<i class="fas fa-dollar-sign text-success mr-2"></i>
								<?= Yii::t('app', 'Recommended Price') ?>
							</h6>
						</div>
						<div class="card-body text-center">
							<div class="display-4 text-success font-weight-bold" id="recommended-price-display">
								$<span id="recommended-price-value">0</span>
							</div>
							<small class="text-muted"><?= Yii::t('app', 'USD (Market rate recommendation)') ?></small>
							<div class="mt-3">
								<button type="button" class="btn btn-success" id="add-price-to-rate-btn">
									<i class="fas fa-plus mr-2"></i><?= Yii::t('app', 'Add to Rate') ?>
								</button>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary"
					data-dismiss="modal"><?= Yii::t('app', 'Close') ?></button>
			</div>
		</div>
	</div>
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
	// --- FORM VALIDATION ---
	const form = document.getElementById('estimate-form');
	form.addEventListener('submit', function(e) {
		if (!form.checkValidity()) {
			e.preventDefault();
			e.stopPropagation();
		}
		form.classList.add('was-validated');
	});

	// --- CONFIGURATION ---
	const config = window.estimateConfig || {};
	const customerDataUrl = config.customerDataUrl;
	const productSearchUrl = config.productSearchUrl;
	const customerUpdateUrl = config.customerUpdateUrl;
	const customerCreateUrl = config.customerCreateUrl;
	const existingItems = config.existingItems || [];
	const isNewRecord = config.isNewRecord;
	const selectedCustomerId = config.selectedCustomerId;

	// --- DOM ELEMENTS ---
	const addItemBtn = document.getElementById('add-item-btn');
	const clearLinesBtn = document.getElementById('clear-lines-btn');
	const customerSelect = document.getElementById('customer-select');
	const itemsTbody = document.getElementById('items-tbody');
	const discountInput = document.getElementById('discount-input');
	const discountType = document.getElementById('discount-type');
	const termsSelect = document.getElementById('estimate-terms');
	const editCustomerBtn = document.getElementById('edit-customer-btn');
	const removeShippingBtn = document.getElementById('remove-shipping-btn');
	const estimateDateInput = document.getElementById('estimate-estimate_date');
	const addCustomerBtn = document.getElementById('add-customer-btn');
	const saveCustomerBtn = document.getElementById('save-customer-btn');

	// --- INITIALIZATION ---
	initializePage();

	function initializePage() {
		if (existingItems.length > 0) {
			existingItems.forEach(item => addEstimateItem(item));
			calculateTotals();
		} else {
			addEstimateItem();
		}

		if (!isNewRecord && selectedCustomerId) {
			loadCustomerData(selectedCustomerId);
		}

		// Set initial expiry date if estimate_date is present
		if (estimateDateInput.value) {
			updateExpiryDateFromTerms(termsSelect.value);
		}

		addEventListeners();
		initializeTaxMode();
	}

	// --- EVENT LISTENERS ---
	function addEventListeners() {
		addItemBtn.addEventListener('click', () => addEstimateItem());

		clearLinesBtn.addEventListener('click', () => {
			if (confirm('Are you sure you want to clear all lines?')) {
				itemsTbody.innerHTML = '';
				addEstimateItem();
				calculateTotals();
			}
		});

		customerSelect.addEventListener('change', (e) => {
			if (e.target.value) loadCustomerData(e.target.value);
		});

		itemsTbody.addEventListener('input', (e) => {
			if (e.target.classList.contains('quantity-input') || e.target.classList.contains(
					'rate-input')) {
				const row = e.target.closest('tr');
				if (row) calculateRowAmount(row);
				calculateTotals();
			}
		});

		itemsTbody.addEventListener('change', (e) => {
			if (e.target.classList.contains('tax-checkbox')) {
				calculateTotals();
			}
		});

		itemsTbody.addEventListener('click', (e) => {
			const removeBtn = e.target.closest('.remove-item-btn');
			if (removeBtn) {
				removeBtn.closest('tr').remove();
				updateRowNumbers();
				calculateTotals();
			}
		});

		discountInput.addEventListener('input', calculateTotals);
		discountType.addEventListener('change', calculateTotals);
		document.getElementById('shipping-fee-input').addEventListener('input', calculateTotals);
		document.getElementById('tax-rate-input').addEventListener('input', calculateTotals);
		document.getElementById('tax-calculation-mode').addEventListener('change', handleTaxModeChange);
		document.getElementById('calculate-tax-btn').addEventListener('click', calculateAutomaticTax);

		termsSelect.addEventListener('change', (e) => {
			if (e.target.value) updateExpiryDateFromTerms(e.target.value);
		});

		estimateDateInput.addEventListener('change', () => {
			updateExpiryDateFromTerms(termsSelect.value);
		});

		editCustomerBtn.addEventListener('click', () => {
			const customerId = customerSelect.value;
			if (customerId) {
				window.open(`${customerUpdateUrl}?id=${customerId}`, '_blank');
			} else {
				alert('Please select a customer first.');
			}
		});

		removeShippingBtn.addEventListener('click', () => {
			document.getElementById('estimate-ship_to_address').value = '';
		});

		// Add Customer Modal Event Listeners
		saveCustomerBtn.addEventListener('click', handleSaveCustomer);

		// Handle modal cleanup
		ModalUtils.setupModalCleanup('#addCustomerModal');

		// Auto-format phone number in modal
		PhoneFormatter.initPhoneFormattingJQuery('#new-customer-phone');

		$(itemsTbody).on('focus', '.product-input:not(.ui-autocomplete-input)', function() {
			initializeProductAutocomplete($(this));
		});
	}

	// --- CORE FUNCTIONS ---
	function addEstimateItem(item = {}) {
		const rowIndex = itemsTbody.rows.length;
		const newRow = document.createElement('tr');

		newRow.innerHTML = `
            <td class="align-middle text-center">${rowIndex + 1}</td>
            <td>
                <input type="text" class="form-control product-input" name="EstimateItem[${rowIndex}][product_service_name]" placeholder="Product or Service" value="${item.product_service_name || ''}">
                <input type="hidden" class="product-id-input" name="EstimateItem[${rowIndex}][product_id]" value="${item.product_id || ''}">
            </td>
            <td>
                <div class="input-group">
                    <textarea class="form-control description-input" name="EstimateItem[${rowIndex}][description]" rows="4" placeholder="Description">${item.description || ''}</textarea>
                    <?php if (Yii::$app->user->identity && Yii::$app->user->identity->canUseAiHelper()): ?>
                    <div class="input-group-append">
                        <button type="button" class="btn btn-outline-info ai-helper-btn" title="<?= Yii::t('app', 'AI Helper') ?>" data-row-index="${rowIndex}">
                            <i class="fas fa-robot"></i>
                        </button>
                    </div>
                    <?php endif; ?>
                </div>
            </td>
            <td><input type="number" class="form-control quantity-input text-right" name="EstimateItem[${rowIndex}][quantity]" value="${item.quantity || 1}" min="0" step="1"></td>
            <td><input type="number" class="form-control rate-input text-right" name="EstimateItem[${rowIndex}][rate]" value="${item.rate || '0.00'}" min="0" step="0.01"></td>
            <td class="text-right amount-display">$0.00</td>
            <td class="text-center"><input type="checkbox" class="form-check-input tax-checkbox" name="EstimateItem[${rowIndex}][is_taxable]" value="1" ${item.is_taxable === false ? '' : 'checked'}></td>
            <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger remove-item-btn"><i class="fas fa-trash"></i></button></td>
        `;

		itemsTbody.appendChild(newRow);
		calculateRowAmount(newRow);
	}

	function updateRowNumbers() {
		itemsTbody.querySelectorAll('tr').forEach((row, index) => {
			row.querySelector('td:first-child').textContent = index + 1;
			row.querySelectorAll('[name^="EstimateItem"]').forEach(input => {
				input.name = input.name.replace(/\[\d+\]/, `[${index}]`);
			});
		});
	}

	function calculateRowAmount(row) {
		const quantity = parseFloat(row.querySelector('.quantity-input').value) || 0;
		const rate = parseFloat(row.querySelector('.rate-input').value) || 0;
		const amount = quantity * rate;
		row.querySelector('.amount-display').textContent = formatCurrency(amount);
	}

	function calculateTotals() {
		let subtotal = 0;
		let taxableAmount = 0;

		itemsTbody.querySelectorAll('tr').forEach(row => {
			const quantity = parseFloat(row.querySelector('.quantity-input').value) || 0;
			const rate = parseFloat(row.querySelector('.rate-input').value) || 0;
			const amount = quantity * rate;
			subtotal += amount;
			if (row.querySelector('.tax-checkbox').checked) {
				taxableAmount += amount;
			}
		});

		const discountValue = parseFloat(discountInput.value) || 0;
		const discountIsPercentage = discountType.value === 'percentage';
		const discountAmount = discountIsPercentage ? subtotal * (discountValue / 100) : discountValue;

		const shippingFeeInput = document.getElementById('shipping-fee-input');
		const shippingFee = parseFloat(shippingFeeInput.value) || 0;

		const subtotalAfterDiscount = subtotal - discountAmount;
		const taxableRatio = subtotal > 0 ? taxableAmount / subtotal : 0;
		const taxableSubtotal = taxableAmount - (discountAmount * taxableRatio);

		const taxRateVal = document.getElementById('tax-rate-input').value;
		const taxRate = parseFloat(taxRateVal) || 0;
		const taxAmount = taxableSubtotal * (taxRate / 100);

		// Update hidden tax_rate field
		document.getElementById('tax-rate-hidden').value = taxRate;

		const total = subtotalAfterDiscount + taxAmount + shippingFee;

		document.getElementById('subtotal-display').textContent = formatCurrency(subtotal);
		document.getElementById('discount-display').textContent = `-${formatCurrency(discountAmount)}`;
		document.getElementById('shipping-fee-display').textContent = formatCurrency(shippingFee);
		document.getElementById('taxable-subtotal-display').textContent = formatCurrency(taxableSubtotal);
		document.getElementById('tax-display').textContent = formatCurrency(taxAmount);
		document.getElementById('total-display').textContent = formatCurrency(total);
	}

	async function loadCustomerData(customerId) {
		try {
			const response = await fetch(`${customerDataUrl}?id=${customerId}`);
			if (!response.ok) throw new Error('Network response was not ok.');
			const data = await response.json();

			if (data.success) {
				const customer = data.customer;
				let billToHtml = `<strong>${customer.customer_name}</strong>`;

				// Build address with new structure
				let addressParts = [];
				if (customer.customer_address) addressParts.push(customer.customer_address);

				let locationParts = [];
				if (customer.city) locationParts.push(customer.city);
				if (customer.state) locationParts.push(customer.state);
				if (customer.zip_code) locationParts.push(customer.zip_code);

				if (locationParts.length > 0) {
					addressParts.push(locationParts.join(', '));
				}

				if (addressParts.length > 0) {
					billToHtml += `<br>${addressParts.join('<br>')}`;
				}

				if (customer.customer_phone) billToHtml += `<br>Phone: ${customer.customer_phone}`;
				if (customer.customer_email) billToHtml += `<br>Email: ${customer.customer_email}`;
				document.getElementById('bill-to-address').innerHTML = billToHtml;

				// Build text version for hidden field
				let textAddress = [customer.customer_name];
				if (addressParts.length > 0) {
					textAddress = textAddress.concat(addressParts);
				}
				if (customer.customer_phone) textAddress.push(`Phone: ${customer.customer_phone}`);
				if (customer.customer_email) textAddress.push(`Email: ${customer.customer_email}`);

				document.getElementById('estimate-bill_to_address').value = textAddress.filter(Boolean)
					.join('\n');

				// Build ship-to address with integrated city/state/zip
				let shipToAddress = customer.customer_address || '';
				if (locationParts.length > 0) {
					if (shipToAddress) shipToAddress += '\n';
					shipToAddress += locationParts.join(', ');
				}
				document.getElementById('estimate-ship_to_address').value = shipToAddress;


				if (customer.payment_terms) {
					termsSelect.value = customer.payment_terms;
					updateExpiryDateFromTerms(customer.payment_terms);
				}
			}
		} catch (error) {
			console.error('Failed to load customer data:', error);
			document.getElementById('bill-to-address').innerHTML =
				'<span class="text-danger">Error loading customer data.</span>';
		}
	}

	function updateExpiryDateFromTerms(terms) {
		let estimateDateStr = document.getElementById('estimate-estimate_date').value;
		if (!estimateDateStr) {
			const today = new Date();
			estimateDateStr = formatDate(today);
			document.getElementById('estimate-estimate_date').value = estimateDateStr;
		}

		const estimateDate = new Date(estimateDateStr);
		const expiryDate = new Date(estimateDate);
		let daysToAdd = 30; // Default expiry

		switch (terms) {
			case 'Net 15':
				daysToAdd = 15;
				break;
			case 'Net 30':
				daysToAdd = 30;
				break;
			case 'Net 60':
				daysToAdd = 60;
				break;
			case 'Due on receipt':
				daysToAdd = 7;
				break;
		}

		expiryDate.setDate(expiryDate.getDate() + daysToAdd);
		document.getElementById('estimate-expiry_date').value = formatDate(expiryDate);
	}

	function initializeProductAutocomplete(element) {
		element.autocomplete({
			source: (request, response) => {
				$.ajax({
					url: productSearchUrl,
					data: {
						term: request.term
					},
					dataType: 'json',
					success: (data) => {
						if (data.length === 0) {
							response([{
								label: 'No products found - Add manually',
								value: request.term,
								product: null
							}]);
						} else {
							response(data.map(item => ({
								label: `${item.name} (${item.sku || 'N/A'}) - ${formatCurrency(item.price)}`,
								value: item.name,
								product: item
							})));
						}
					},
					error: () => {
						response([{
							label: 'Error loading products',
							value: request.term,
							product: null
						}]);
					}
				});
			},
			minLength: 1,
			select: (event, ui) => {
				const row = $(event.target).closest('tr');
				if (ui.item.product) {
					const product = ui.item.product;
					row.find('.product-id-input').val(product.id);
					row.find('.description-input').val(product.description);
					row.find('.rate-input').val(product.price);
					row.find('.tax-checkbox').prop('checked', product.is_taxable);
					calculateRowAmount(row[0]);
					calculateTotals();
				}
				$(event.target).val(ui.item.value);
				return false;
			}
		});
	}

	// --- CUSTOMER MANAGEMENT FUNCTIONS ---
	async function handleSaveCustomer() {
		const form = document.getElementById('add-customer-form');

		// Create FormData with CSRF token using common utility
		const formData = AjaxUtils.prepareFormData(form);

		// Basic validation
		const customerName = formData.get('customer_name');
		if (!customerName || customerName.trim() === '') {
			alert('<?= Yii::t('app/customer', 'Customer name is required.') ?>');
			return;
		}

		saveCustomerBtn.disabled = true;
		saveCustomerBtn.textContent = '<?= Yii::t('app', 'Saving...') ?>';

		try {
			const response = await fetch(customerCreateUrl, {
				method: 'POST',
				body: formData,
				headers: {
					'X-Requested-With': 'XMLHttpRequest',
					'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')
				}
			});

			const data = await response.json();

			if (data.success) {
				// Add new customer to dropdown
				const option = new Option(data.customer.customer_name, data.customer.id);
				customerSelect.add(option);

				// Select the new customer
				customerSelect.value = data.customer.id;

				// Load customer data
				await loadCustomerData(data.customer.id);

				// Close modal and reset form
				$('#addCustomerModal').modal('hide');
				form.reset();

				// Show success message
				NotificationUtils.showSuccess(
					'<?= Yii::t('app/customer', 'Customer added successfully!') ?>', 500);
			} else {
				const errorMessage = data.message ||
					'<?= Yii::t('app/customer', 'Failed to add customer. Please try again.') ?>';
				NotificationUtils.showError(errorMessage, data.errors);
			}
		} catch (error) {
			NotificationUtils.showError(
				'<?= Yii::t('app/customer', 'Error adding customer. Please try again.') ?>');
		} finally {
			saveCustomerBtn.disabled = false;
			saveCustomerBtn.textContent = '<?= Yii::t('app/customer', 'Add Customer') ?>';
		}
	}

	// --- TAX CALCULATION FUNCTIONS ---
	function handleTaxModeChange() {
		const mode = document.getElementById('tax-calculation-mode').value;
		const taxRateInput = document.getElementById('tax-rate-input');
		const calculateBtn = document.getElementById('calculate-tax-btn');

		if (mode === 'automatic') {
			calculateBtn.style.display = 'inline-block';
			taxRateInput.readOnly = true;
			taxRateInput.classList.add('bg-light');
			calculateAutomaticTax();
		} else {
			calculateBtn.style.display = 'none';
			taxRateInput.readOnly = false;
			taxRateInput.classList.remove('bg-light');
			// Set to company default tax rate
			taxRateInput.value = <?= $company->tax_rate ?? 0 ?>;
			calculateTotals();
		}
	}

	async function calculateAutomaticTax() {
		const customerId = customerSelect.value;
		if (!customerId) {
			alert(
				'<?= Yii::t('invoice', 'Please select a customer first to calculate automatic tax.') ?>'
			);
			return;
		}

		const calculateBtn = document.getElementById('calculate-tax-btn');
		const originalText = calculateBtn.innerHTML;
		calculateBtn.innerHTML =
			'<i class="fas fa-spinner fa-spin"></i> <?= Yii::t('invoice', 'Calculating...') ?>';
		calculateBtn.disabled = true;

		try {
			const response = await fetch('<?= Url::to(['/estimate/calculate-tax']) ?>', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')
				},
				body: JSON.stringify({
					customer_id: customerId,
					company_id: <?= $company->id ?? 'null' ?>
				})
			});

			const data = await response.json();

			if (data.success) {
				document.getElementById('tax-rate-input').value = data.tax_rate || 0;
				calculateTotals();

				// Show appropriate message based on message type
				if (data.message_type === 'warning') {
					NotificationUtils.showWarning(data.message);
				} else if (data.message_type === 'info') {
					NotificationUtils.showInfo(data.message);
				}
				// No message for normal success to avoid cluttering the UI
			} else {
				NotificationUtils.showError(data.message ||
					'<?= Yii::t('invoice', 'Failed to calculate automatic tax.') ?>');
				// Fallback to company default or provided fallback rate
				const fallbackRate = data.fallback_rate !== undefined ? data.fallback_rate :
					<?= $company->tax_rate ?? 0 ?>;
				document.getElementById('tax-rate-input').value = fallbackRate;
				calculateTotals();
			}
		} catch (error) {
			console.error('Tax calculation error:', error);
			NotificationUtils.showError(
				'<?= Yii::t('invoice', 'Error calculating tax. Using company default.') ?>');
			// Fallback to company default
			document.getElementById('tax-rate-input').value = <?= $company->tax_rate ?? 0 ?>;
			calculateTotals();
		} finally {
			calculateBtn.innerHTML = originalText;
			calculateBtn.disabled = false;
		}
	}

	// Initialize tax mode on page load
	function initializeTaxMode() {
		handleTaxModeChange();
	}

	// --- UTILITY FUNCTIONS ---
	function formatDate(date) {
		return DateUtils.formatDateForInput(date);
	}

	function formatCurrency(amount) {
		return CurrencyUtils.formatCurrency(amount);
	}

	// Collapse functionality is handled by collapse-helper.js

	<?php if (Yii::$app->user->identity && Yii::$app->user->identity->canUseAiHelper()): ?>
	// --- AI HELPER FUNCTIONALITY ---
	let currentAiRow = null;

	// Make useSuggestion globally accessible to fix JavaScript error
	window.useSuggestion = function(description) {
		if (currentAiRow) {
			const descriptionInput = currentAiRow.querySelector('.description-input');
			descriptionInput.value = description;
			$('#aiHelperModal').modal('hide');
		}
	};

	// Make useSuggestionWithPrice globally accessible
	window.useSuggestionWithPrice = function(description) {
		if (currentAiRow) {
			const descriptionInput = currentAiRow.querySelector('.description-input');
			const rateInput = currentAiRow.querySelector('.rate-input');

			// Update description
			descriptionInput.value = description;

			// Update price if available
			const priceValueElement = document.getElementById('recommended-price-value');
			if (priceValueElement && priceValueElement.textContent) {
				const recommendedPrice = parseFloat(priceValueElement.textContent) || 0;
				if (recommendedPrice > 0) {
					rateInput.value = recommendedPrice.toFixed(2);
					calculateRowAmount(currentAiRow);
					calculateTotals();
				}
			}

			$('#aiHelperModal').modal('hide');
		}
	};

	function initAiHelper() {
		// Add event listeners for AI helper buttons
		itemsTbody.addEventListener('click', (e) => {
			if (e.target.closest('.ai-helper-btn')) {
				const btn = e.target.closest('.ai-helper-btn');
				const row = btn.closest('tr');
				const rowIndex = btn.getAttribute('data-row-index');

				// Store current row information globally
				currentAiRow = row;
				window.currentAiHelperRowIndex = parseInt(rowIndex);

				showAiHelper(row);
			}
		});

		// Ask AI button click handler
		document.getElementById('ask-ai-btn')?.addEventListener('click', () => {
			const questionInput = document.getElementById('ai-question');
			const question = questionInput.value.trim();

			if (!question) {
				alert('<?= Yii::t('app', 'Please enter a question or product name') ?>');
				return;
			}

			askAiQuestion(question);
		});

		// Enter key handler for question input
		document.getElementById('ai-question')?.addEventListener('keypress', (e) => {
			if (e.key === 'Enter') {
				document.getElementById('ask-ai-btn').click();
			}
		});
	}

	function showAiHelper(row) {
		currentAiRow = row;
		const productInput = row.querySelector('.product-input');
		const productName = productInput.value.trim();

		// Show modal
		$('#aiHelperModal').modal('show');

		// Show input section and hide content
		document.getElementById('ai-input-section').style.display = 'block';
		document.getElementById('ai-helper-content').style.display = 'none';
		document.getElementById('ai-pricing-section').style.display = 'none';

		// Pre-fill question input with product name if available
		const questionInput = document.getElementById('ai-question');
		if (productName) {
			questionInput.value = productName;
			questionInput.placeholder =
				'<?= Yii::t('app', 'Enter product/service name or ask a question...') ?>';
		} else {
			questionInput.value = '';
			questionInput.placeholder =
				'<?= Yii::t('app', 'Enter product/service name or ask a question...') ?>';
		}

		// Focus on input
		setTimeout(() => questionInput.focus(), 100);
	}

	async function askAiQuestion(question) {
		try {
			// Show loading in content area
			const content = document.getElementById('ai-helper-content');
			content.style.display = 'block';
			content.innerHTML = `
				<div class="ai-loading text-center">
					<i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
					<p class="mt-2"><?= Yii::t('app', 'Thinking...') ?></p>
				</div>
			`;

			// Hide input section
			document.getElementById('ai-input-section').style.display = 'none';

			const customerId = customerSelect.value || '';
			const businessType = '<?= $company->industry ?? '' ?>';
			const responseLanguage = document.getElementById('ai-response-language').value;
			const projectComplexity = document.getElementById('ai-project-complexity').value;

			// Language mapping for AI prompts
			const languageNames = {
				'en': 'English',
				'ko': 'Korean',
				'es': 'Spanish',
				'zh-cn': 'Chinese Simplified',
				'zh-tw': 'Chinese Traditional'
			};

			// Create work scope prompt with language specification
			const workScopePrompt = `Based on the keywords or service "${question}", generate a professional work scope description suitable for an estimate. 

Requirements:
- Respond in ${languageNames[responseLanguage]} language
- Format as a concise work scope for billing purposes
- Include key deliverables and services
- Professional business language
- 2-3 sentences maximum
- Suitable for ${businessType ? businessType + ' business' : 'business'} context
- Output format MUST be in bullet point format with line breaks
- Each major point should be on a separate line starting with a bullet (•) or dash (-)

Keywords/Service: ${question}

Please provide only the work scope description in ${languageNames[responseLanguage]}.`;

			const response = await fetch('<?= Url::to(['/ai-helper/answer-question']) ?>', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded',
					'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')
				},
				body: new URLSearchParams({
					question: workScopePrompt,
					customer_id: customerId,
					business_type: businessType,
					response_language: responseLanguage,
					project_complexity: projectComplexity
				})
			});

			const data = await response.json();

			if (data.success && data.answer) {
				displayAnswer(data.answer, question, responseLanguage);

				// Display pricing recommendation if available
				if (data.recommended_price && data.recommended_price > 0) {
					displayPricingRecommendation(data.recommended_price);
				}
			} else {
				let errorMessage = data.error || '<?= Yii::t('app', 'Unable to generate answer') ?>';
				if (data.debug) {
					console.warn('AI Helper Debug Info:', data.debug);
				}

				// Show more helpful error message with debug info if available
				if (data.debug && data.debug.api_configured === false) {
					errorMessage =
						'<?= Yii::t('app', 'AI Helper is not configured. Please check settings.') ?>';
				}

				displayError(errorMessage);
			}
		} catch (error) {
			console.error('AI Helper Network Error:', error);
			displayError('<?= Yii::t('app', 'Network error. Please try again.') ?>');
		}
	}

	function displayAnswer(answer, originalQuestion, responseLanguage) {
		const content = document.getElementById('ai-helper-content');

		// Language flags for better UX
		const languageFlags = {
			'en': '🇺🇸',
			'ko': '🇰🇷',
			'es': '🇪🇸',
			'zh-cn': '🇨🇳',
			'zh-tw': '🇹🇼'
		};

		const languageNames = {
			'en': 'English',
			'ko': '한국어',
			'es': 'Español',
			'zh-cn': '中文简体',
			'zh-tw': '中文繁體'
		};

		let html = `
			<h6 class="mb-3">
				<i class="fas fa-robot text-warning mr-2"></i>
				<?= Yii::t('app', 'Work Scope Generated') ?>
				<span class="badge badge-info ml-2">${languageFlags[responseLanguage]} ${languageNames[responseLanguage]}</span>
			</h6>
			<div class="card mb-3">
				<div class="card-body">
					<h6 class="card-subtitle mb-2 text-muted"><?= Yii::t('app', 'Input Keywords') ?>:</h6>
					<p class="card-text"><em>"${escapeHtml(originalQuestion)}"</em></p>
					<h6 class="card-subtitle mb-2 text-muted"><?= Yii::t('app', 'Work Scope Description') ?>:</h6>
					<div class="p-3 bg-light rounded">
						<p class="card-text mb-0 font-weight-medium">${escapeHtml(answer)}</p>
					</div>
				</div>
			</div>
			<div class="text-center mb-3">
				<div class="btn-group mb-2" role="group">
					<button type="button" class="btn btn-success" id="add-to-description-btn">
						<i class="fas fa-file-text mr-1"></i><?= Yii::t('app', 'Add to Description') ?>
					</button>
					<button type="button" class="btn btn-primary" id="add-to-description-and-price-btn" style="display: none;">
						<i class="fas fa-layer-group mr-1"></i><?= Yii::t('app', 'Add to Description & Price') ?>
					</button>
				</div>
				<br>
				<button type="button" class="btn btn-outline-secondary" onclick="showNewQuestion()">
					<i class="fas fa-redo mr-1"></i><?= Yii::t('app', 'Generate Another') ?>
				</button>
			</div>
			<div class="mt-3 pt-3 border-top">
				<small class="text-muted">
					<i class="fas fa-info-circle mr-1"></i>
					<?= Yii::t('app', 'AI-generated work scope. Review and customize as needed for your estimate.') ?>
				</small>
			</div>
		`;

		content.innerHTML = html;

		// Add event listener for "Add to Description" button
		const addToDescBtn = document.getElementById('add-to-description-btn');
		if (addToDescBtn) {
			addToDescBtn.addEventListener('click', function() {
				useSuggestion(answer);
			});
		}

		// Add event listener for "Add to Description & Price" button
		const addToDescAndPriceBtn = document.getElementById('add-to-description-and-price-btn');
		if (addToDescAndPriceBtn) {
			addToDescAndPriceBtn.addEventListener('click', function() {
				useSuggestionWithPrice(answer);
			});
		}
	}

	window.showNewQuestion = function() {
		document.getElementById('ai-input-section').style.display = 'block';
		document.getElementById('ai-helper-content').style.display = 'none';
		document.getElementById('ai-pricing-section').style.display = 'none';

		// Hide the "Add to Description & Price" button
		const addToDescAndPriceBtn = document.getElementById('add-to-description-and-price-btn');
		if (addToDescAndPriceBtn) {
			addToDescAndPriceBtn.style.display = 'none';
		}

		document.getElementById('ai-question').value = '';
		document.getElementById('ai-question').focus();
	};

	function displayPricingRecommendation(price) {
		const pricingSection = document.getElementById('ai-pricing-section');
		const priceValueElement = document.getElementById('recommended-price-value');

		// Format price to 2 decimal places
		const formattedPrice = parseFloat(price).toFixed(2);
		priceValueElement.textContent = formattedPrice;

		// Show the pricing section
		pricingSection.style.display = 'block';

		// Show the "Add to Description & Price" button when pricing is available
		const addToDescAndPriceBtn = document.getElementById('add-to-description-and-price-btn');
		if (addToDescAndPriceBtn) {
			addToDescAndPriceBtn.style.display = 'inline-block';
		}

		// Add event listener for "Add to Rate" button
		const addPriceBtn = document.getElementById('add-price-to-rate-btn');
		addPriceBtn.onclick = function() {
			addPricingToRate(formattedPrice);
		};
	}

	function addPricingToRate(price) {
		// Get the currently active row index (stored when AI Helper button was clicked)
		const currentRowIndex = window.currentAiHelperRowIndex;

		if (currentRowIndex !== undefined) {
			// Find the rate input for the current row (estimate uses same naming)
			const rateInput = document.querySelector(`input[name="EstimateItem[${currentRowIndex}][rate]"]`);

			if (rateInput) {
				rateInput.value = price;

				// Trigger change event to update calculations
				rateInput.dispatchEvent(new Event('input', {
					bubbles: true
				}));

				// Show success feedback
				const addPriceBtn = document.getElementById('add-price-to-rate-btn');
				const originalText = addPriceBtn.innerHTML;
				addPriceBtn.innerHTML = '<i class="fas fa-check mr-2"></i><?= Yii::t('app', 'Added!') ?>';
				addPriceBtn.classList.remove('btn-success');
				addPriceBtn.classList.add('btn-outline-success');
				addPriceBtn.disabled = true;

				// Reset button after 2 seconds
				setTimeout(() => {
					addPriceBtn.innerHTML = originalText;
					addPriceBtn.classList.remove('btn-outline-success');
					addPriceBtn.classList.add('btn-success');
					addPriceBtn.disabled = false;
				}, 2000);

				// Close modal after adding
				setTimeout(() => {
					$('#aiHelperModal').modal('hide');
				}, 1500);
			}
		}
	}

	function displayError(errorMessage) {
		const content = document.getElementById('ai-helper-content');
		content.innerHTML = `
			<div class="alert alert-warning">
				<i class="fas fa-exclamation-triangle mr-2"></i>
				${escapeHtml(errorMessage)}
			</div>
			<p class="text-center">
				<button type="button" class="btn btn-outline-primary" onclick="showNewQuestion()">
					<i class="fas fa-redo mr-1"></i><?= Yii::t('app', 'Try Again') ?>
				</button>
			</p>
		`;
	}

	function escapeHtml(text) {
		const div = document.createElement('div');
		div.textContent = text;
		return div.innerHTML;
	}

	// Initialize AI Helper
	initAiHelper();
	<?php endif; ?>
});
</script>
<style>
.min-h-100 {
	min-height: 100px;
}

.totals-grid {
	display: grid;
	grid-template-columns: 1fr auto;
	gap: 0.5rem 1rem;
	align-items: center;
}

.form-check-input {
	float: none;
	margin-left: 0;
}

/* Modal width adjustment */
.modal-dialog-wide {
	max-width: calc(600px + 100px);
	/* Default modal-lg is 600px, adding 100px */
}

@media (min-width: 768px) {
	.modal-dialog-wide {
		max-width: calc(600px + 100px);
		margin: 1.75rem auto;
	}
}

@media (min-width: 992px) {
	.modal-dialog-wide {
		max-width: calc(800px + 100px);
		/* Bootstrap modal-xl is 800px, adding 100px */
	}
}

/* AI Helper Styles */
.ai-helper-btn {
	min-height: 100%;
	border-top-left-radius: 0;
	border-bottom-left-radius: 0;
}

.ai-suggestion {
	cursor: pointer;
	padding: 0.5rem;
	border: 1px solid #dee2e6;
	border-radius: 0.25rem;
	margin-bottom: 0.5rem;
	transition: all 0.2s;
}

.ai-suggestion:hover {
	background-color: #f8f9fa;
	border-color: #007bff;
}

.ai-loading {
	text-align: center;
	padding: 2rem;
}
</style>