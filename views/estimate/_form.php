<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

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
				<div class="card-header">
					<h5 class="card-title mb-0">
						<i class="fas fa-file-invoice mr-2"></i><?= strtoupper(Yii::t('app/estimate', 'Estimate')) ?>
					</h5>
				</div>
				<div class="card-body">
					<div class="row">
						<div class="col-md-6">
							<?= $form->field($model, 'customer_id')->dropDownList(
                                ArrayHelper::map($customers, 'id', 'customer_name'),
                                [
                                    'prompt' => Yii::t('app/estimate', 'Select Customer'),
                                    'id' => 'customer-select',
                                ]
                            )->label(Yii::t('app/estimate', 'Customer')) ?>
							
							<div class="d-flex align-items-center mb-3">
								<small class="text-muted mr-2"><?= Yii::t('app/estimate', 'Customer not in list?') ?></small>
								<?= Html::button(Yii::t('app/estimate', 'Add New Customer'), [
									'class' => 'btn btn-outline-success btn-sm',
									'id' => 'add-customer-btn',
									'data-toggle' => 'modal',
									'data-target' => '#addCustomerModal'
								]) ?>
							</div>

							<div id="customer-details" class="mb-3">
								<label class="form-label"><?= Yii::t('app/estimate', 'Customer Information') ?></label>
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
				<div class="card-header">
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
			<div class="card mt-3">
				<div class="card-header p-2" style="cursor: pointer;" data-toggle="collapse" data-target="#estimate-help-collapse" aria-expanded="false">
					<h6 class="card-title mb-0 d-flex justify-content-between align-items-center">
						<span><i class="fas fa-question-circle mr-2"></i><?= Yii::t('app/estimate', 'Estimate Help') ?></span>
						<i class="fas fa-chevron-down collapse-icon"></i>
					</h6>
				</div>
				<div class="collapse" id="estimate-help-collapse">
					<div class="card-body py-2">
						<div class="alert alert-info py-2 mb-0">
							<small>
								<strong><?= Yii::t('app/estimate', 'Estimate Number') ?>:</strong> <?= Yii::t('app/estimate', 'Unique identifier for this estimate.') ?><br>
								<strong><?= Yii::t('app/estimate', 'Terms') ?>:</strong> <?= Yii::t('app/estimate', 'Terms and conditions for this estimate.') ?><br>
								<strong><?= Yii::t('app/estimate', 'Estimate Date') ?>:</strong> <?= Yii::t('app/estimate', 'Date when estimate is issued.') ?><br>
								<strong><?= Yii::t('app/estimate', 'Expiry Date') ?>:</strong> <?= Yii::t('app/estimate', 'Expiry date for this estimate.') ?><br>
								<strong><?= Yii::t('app/estimate', 'Items') ?>:</strong> <?= Yii::t('app/estimate', 'Add products/services with quantity and rate.') ?><br>
								<strong><?= Yii::t('app/estimate', 'Convert to Invoice') ?>:</strong> <?= Yii::t('app/estimate', 'Convert approved estimates to invoices.') ?>
							</small>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="card card-default mt-4">
		<div class="card-header">
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
				<div class="card-header">
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
				<div class="card-header">
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

						<span><?= Yii::t('app/estimate', 'Sales Tax') ?></span>
						<div class="text-right d-flex justify-content-end align-items-center">
							<input type="number" class="form-control form-control-sm mr-2" id="tax-rate-input"
								style="width: 120px;" min="0" max="100" step="0.01" 
								value="<?= $company->tax_rate ?? 0 ?>" placeholder="0.00">
							<span class="mr-2">%</span>
							<span id="tax-display">$0.00</span>
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
<div class="modal fade" id="addCustomerModal" tabindex="-1" role="dialog" aria-labelledby="addCustomerModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="addCustomerModalLabel"><?= Yii::t('app/estimate', 'Add New Customer') ?></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<form id="add-customer-form">
					<div class="form-group">
						<label for="new-customer-name"><?= Yii::t('app/customer', 'Customer Name') ?> <span class="text-danger">*</span></label>
						<input type="text" class="form-control" id="new-customer-name" name="customer_name" required>
					</div>
					<div class="form-group">
						<label for="new-customer-email"><?= Yii::t('app/customer', 'Email') ?></label>
						<input type="email" class="form-control" id="new-customer-email" name="customer_email">
					</div>
					<div class="form-group">
						<label for="new-customer-phone"><?= Yii::t('app/customer', 'Phone') ?></label>
						<input type="tel" class="form-control" id="new-customer-phone" name="customer_phone" placeholder="e.g. +1 (555) 123-4567" pattern="[\+\-\s\(\)\d\.\#\*]*">
					</div>
					<div class="form-group">
						<label for="new-customer-address"><?= Yii::t('app/customer', 'Address') ?></label>
						<textarea class="form-control" id="new-customer-address" name="customer_address" rows="3"></textarea>
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
				<button type="button" class="btn btn-secondary" data-dismiss="modal"><?= Yii::t('app', 'Cancel') ?></button>
				<button type="button" class="btn btn-success" id="save-customer-btn"><?= Yii::t('app/customer', 'Add Customer') ?></button>
			</div>
		</div>
	</div>
</div>

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
		document.getElementById('tax-rate-input').addEventListener('input', calculateTotals);

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
            <td><textarea class="form-control description-input" name="EstimateItem[${rowIndex}][description]" rows="1" placeholder="Description">${item.description || ''}</textarea></td>
            <td><input type="number" class="form-control quantity-input text-right" name="EstimateItem[${rowIndex}][quantity]" value="${item.quantity || 1}" min="0" step="1"></td>
            <td><input type="number" class="form-control rate-input text-right" name="EstimateItem[${rowIndex}][rate]" value="${item.rate || '0.00'}" min="0" step="0.01"></td>
            <td class="align-middle text-right amount-display">$0.00</td>
            <td class="align-middle text-center"><input type="checkbox" class="form-check-input tax-checkbox" name="EstimateItem[${rowIndex}][is_taxable]" value="1" ${item.is_taxable === false ? '' : 'checked'}></td>
            <td class="align-middle text-center"><button type="button" class="btn btn-sm btn-outline-danger remove-item-btn"><i class="fas fa-trash"></i></button></td>
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

		const subtotalAfterDiscount = subtotal - discountAmount;
		const taxableRatio = subtotal > 0 ? taxableAmount / subtotal : 0;
		const taxableSubtotal = taxableAmount - (discountAmount * taxableRatio);

		const taxRateVal = document.getElementById('tax-rate-input').value;
		const taxRate = parseFloat(taxRateVal) || 0;
		const taxAmount = taxableSubtotal * (taxRate / 100);
		
		// Update hidden tax_rate field
		document.getElementById('tax-rate-hidden').value = taxRate;
		
		const total = subtotalAfterDiscount + taxAmount;

		document.getElementById('subtotal-display').textContent = formatCurrency(subtotal);
		document.getElementById('discount-display').textContent = `-${formatCurrency(discountAmount)}`;
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
				if (customer.customer_address) billToHtml +=
					`<br>${customer.customer_address.replace(/\n/g, '<br>')}`;
				if (customer.customer_phone) billToHtml += `<br>Phone: ${customer.customer_phone}`;
				if (customer.customer_email) billToHtml += `<br>Email: ${customer.customer_email}`;
				document.getElementById('bill-to-address').innerHTML = billToHtml;

				document.getElementById('estimate-bill_to_address').value = [
					customer.customer_name,
					customer.customer_address,
					customer.customer_phone ? `Phone: ${customer.customer_phone}` : '',
					customer.customer_email ? `Email: ${customer.customer_email}` : ''
				].filter(Boolean).join('\n');

				document.getElementById('estimate-ship_to_address').value = customer.customer_address || '';
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
				NotificationUtils.showSuccess('<?= Yii::t('app/customer', 'Customer added successfully!') ?>', 500);
			} else {
				const errorMessage = data.message || '<?= Yii::t('app/customer', 'Failed to add customer. Please try again.') ?>';
				NotificationUtils.showError(errorMessage, data.errors);
			}
		} catch (error) {
			NotificationUtils.showError('<?= Yii::t('app/customer', 'Error adding customer. Please try again.') ?>');
		} finally {
			saveCustomerBtn.disabled = false;
			saveCustomerBtn.textContent = '<?= Yii::t('app/customer', 'Add Customer') ?>';
		}
	}

	// --- UTILITY FUNCTIONS ---
	function formatDate(date) {
		return DateUtils.formatDateForInput(date);
	}

	function formatCurrency(amount) {
		return CurrencyUtils.formatCurrency(amount);
	}

	// Collapse functionality is handled by collapse-helper.js
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
</style>