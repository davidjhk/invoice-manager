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
    'existingItems' => $existingItems,
    'isNewRecord' => $model->isNewRecord,
    'selectedCustomerId' => $model->isNewRecord ? null : $model->customer_id,
]);

?>

<div class="estimate-form">

	<?php $form = ActiveForm::begin([
        'id' => 'estimate-form',
    ]); ?>

	<div class="row">
		<div class="col-lg-8">
			<div class="card card-default">
				<div class="card-header">
					<h3 class="card-title">ESTIMATE</h3>
				</div>
				<div class="card-body">
					<div class="row">
						<div class="col-md-6">
							<?= $form->field($model, 'customer_id')->dropDownList(
                                ArrayHelper::map($customers, 'id', 'customer_name'),
                                [
                                    'prompt' => 'Select Customer',
                                    'id' => 'customer-select',
                                ]
                            )->label('Customer') ?>

							<div id="customer-details" class="mb-3">
								<label class="form-label">Customer Details</label>
								<div id="bill-to-address" class="border p-2 bg-light min-h-100 rounded">
									<?php if (!empty($model->bill_to_address)): ?>
									<?= nl2br(Html::encode($model->bill_to_address)) ?>
									<?php else: ?>
									<span class="text-muted">Select a customer to see details.</span>
									<?php endif; ?>
								</div>
								<?= $form->field($model, 'bill_to_address')->hiddenInput(['id' => 'estimate-bill_to_address'])->label(false) ?>
							</div>
							<?= Html::button('Edit Customer', [
                                'class' => 'btn btn-outline-primary btn-sm',
                                'id' => 'edit-customer-btn'
                            ]) ?>
						</div>
						<div class="col-md-6">
							<?= $form->field($model, 'ship_to_address')->textarea([
                                'rows' => 4,
                                'placeholder' => "Shipping address (if different from billing)"
                            ])->label('Ship To') ?>
							<?= Html::button('Clear Shipping Info', [
                                'class' => 'btn btn-link btn-sm p-0',
                                'id' => 'remove-shipping-btn'
                            ]) ?>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="col-lg-4">
			<div class="card card-default">
				<div class="card-header">
					<h3 class="card-title">Details</h3>
				</div>
				<div class="card-body">
					<?= $form->field($model, 'estimate_number')->textInput(['maxlength' => true]) ?>
					<?= $form->field($model, 'terms')->dropDownList([
                        'Net 15' => 'Net 15',
                        'Net 30' => 'Net 30',
                        'Net 60' => 'Net 60',
                        'Due on receipt' => 'Due on receipt'
                    ], ['prompt' => 'Select terms']) ?>
					<?= $form->field($model, 'estimate_date')->input('date') ?>
					<?= $form->field($model, 'expiry_date')->input('date') ?>
				</div>
			</div>
		</div>
	</div>

	<div class="card card-default mt-4">
		<div class="card-header">
			<h3 class="card-title">Items</h3>
		</div>
		<div class="card-body">
			<div class="table-responsive">
				<table class="table table-bordered" id="items-table">
					<thead class="thead-light">
						<tr>
							<th style="width: 5%;">#</th>
							<th style="width: 25%;">Product/Service</th>
							<th style="width: 35%;">Description</th>
							<th style="width: 10%;" class="text-right">Qty</th>
							<th style="width: 10%;" class="text-right">Rate</th>
							<th style="width: 10%;" class="text-right">Amount</th>
							<th style="width: 5%;" class="text-center">Tax</th>
							<th style="width: 5%;"></th>
						</tr>
					</thead>
					<tbody id="items-tbody">
						<!-- Items will be added dynamically by JavaScript -->
					</tbody>
				</table>
			</div>
			<?= Html::button('Add Item', ['class' => 'btn btn-outline-primary', 'id' => 'add-item-btn']) ?>
			<?= Html::button('Clear All', ['class' => 'btn btn-outline-danger', 'id' => 'clear-lines-btn']) ?>
		</div>
	</div>

	<div class="row mt-4">
		<div class="col-lg-6">
			<div class="card card-default">
				<div class="card-header">
					<h3 class="card-title">Notes</h3>
				</div>
				<div class="card-body">
					<?= $form->field($model, 'customer_notes')->textarea(['rows' => 3, 'placeholder' => 'e.g. This estimate is valid for 30 days.'])->label('Note to Customer') ?>
					<?= $form->field($model, 'memo')->textarea(['rows' => 3, 'placeholder' => 'Internal notes for your team.'])->label('Memo (Internal)') ?>
				</div>
			</div>
		</div>

		<div class="col-lg-6">
			<div class="card card-default">
				<div class="card-header">
					<h3 class="card-title">Totals</h3>
				</div>
				<div class="card-body">
					<div class="totals-grid">
						<span>Subtotal</span>
						<span id="subtotal-display" class="text-right">$0.00</span>

						<span>Discount</span>
						<div class="text-right d-flex justify-content-end align-items-center">
							<?= $form->field($model, 'discount_value', ['options' => ['class' => 'mb-0 mr-1'], 'template' => '{input}'])->textInput(['id' => 'discount-input', 'class' => 'form-control form-control-sm text-right', 'style' => 'width: 60px;', 'placeholder' => '0']) ?>
							<?= $form->field($model, 'discount_type', ['options' => ['class' => 'mb-0'], 'template' => '{input}'])->dropDownList(['percentage' => '%', 'fixed' => '$'], ['id' => 'discount-type', 'class' => 'form-control form-control-sm', 'style' => 'width: 50px;']) ?>
							<span id="discount-display" class="ml-2">-$0.00</span>
						</div>

						<span>Taxable Subtotal</span>
						<span id="taxable-subtotal-display" class="text-right">$0.00</span>

						<span>Sales Tax</span>
						<span id="tax-display" class="text-right">$0.00</span>
					</div>

					<hr>

					<div class="totals-grid font-weight-bold h5">
						<span>Total</span>
						<span id="total-display" class="text-right">$0.00</span>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="form-group mt-4">
		<?= Html::submitButton($model->isNewRecord ? 'Create Estimate' : 'Update Estimate', ['class' => 'btn btn-success btn-lg']) ?>
		<?= Html::a('Cancel', ['index'], ['class' => 'btn btn-secondary btn-lg']) ?>
	</div>

	<?php ActiveForm::end(); ?>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
	// --- CONFIGURATION ---
	const config = window.estimateConfig || {};
	const customerDataUrl = config.customerDataUrl;
	const productSearchUrl = config.productSearchUrl;
	const customerUpdateUrl = config.customerUpdateUrl;
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

		const taxAmount = 0; // Tax is not typically applied on estimates
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

	// --- UTILITY FUNCTIONS ---
	function formatDate(date) {
		return date.toISOString().split('T')[0];
	}

	function formatCurrency(amount) {
		return new Intl.NumberFormat('en-US', {
			style: 'currency',
			currency: 'USD'
		}).format(amount);
	}
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