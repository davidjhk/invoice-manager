<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\models\Invoice $model */
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
        $items = $model->invoiceItems;
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

$this->registerJsVar('invoiceConfig', [
    'customerDataUrl' => $customerDataUrl,
    'productSearchUrl' => $productSearchUrl,
    'customerUpdateUrl' => $customerUpdateUrl,
    'customerCreateUrl' => $customerCreateUrl,
    'existingItems' => $existingItems,
    'isNewRecord' => $model->isNewRecord,
    'selectedCustomerId' => $model->isNewRecord ? null : $model->customer_id,
]);

?>

<div class="invoice-form">

	<?php $form = ActiveForm::begin([
        'id' => 'invoice-form',
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
						<i class="fas fa-file-invoice mr-2"></i>INVOICE
					</h5>
				</div>
				<div class="card-body">
					<div class="row">
						<div class="col-md-6">
							<?= $form->field($model, 'customer_id')->dropDownList(
                                ArrayHelper::map($customers, 'id', 'customer_name'),
                                [
                                    'prompt' => Yii::t('app/invoice', 'Select Customer'),
                                    'id' => 'customer-select',
                                ]
                            )->label(Yii::t('app/invoice', 'Customer')) ?>
							
							<div class="d-flex align-items-center mb-3">
								<small class="text-muted mr-2"><?= Yii::t('app/invoice', 'Customer not in list?') ?></small>
								<?= Html::button(Yii::t('app/invoice', 'Add New Customer'), [
									'class' => 'btn btn-outline-success btn-sm',
									'id' => 'add-customer-btn',
									'data-toggle' => 'modal',
									'data-target' => '#addCustomerModal'
								]) ?>
							</div>

							<div id="customer-details" class="mb-3">
								<label class="form-label"><?= Yii::t('app/invoice', 'Customer Information') ?></label>
								<div id="bill-to-address" class="border p-2 bg-light min-h-100 rounded">
									<?php if (!empty($model->bill_to_address)): ?>
									<?= nl2br(Html::encode($model->bill_to_address)) ?>
									<?php else: ?>
									<span class="text-muted"><?= Yii::t('app/invoice', 'Select Customer') ?>.</span>
									<?php endif; ?>
								</div>
								<?= $form->field($model, 'bill_to_address')->hiddenInput(['id' => 'invoice-bill_to_address'])->label(false) ?>
							</div>
							<?= Html::button(Yii::t('app/invoice', 'Edit'), [
                                'class' => 'btn btn-outline-primary btn-sm',
                                'id' => 'edit-customer-btn'
                            ]) ?>
						</div>
						<div class="col-md-6">
							<?= $form->field($model, 'ship_to_address')->textarea([
                                'rows' => 4,
                                'placeholder' => Yii::t('app/invoice', 'Shipping address (if different from billing)')
                            ])->label(Yii::t('app/invoice', 'Ship To')) ?>
							<?= Html::button(Yii::t('app/invoice', 'Clear Shipping Info'), [
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
						<i class="fas fa-info-circle mr-2"></i><?= Yii::t('app/invoice', 'Invoice Details') ?>
					</h5>
				</div>
				<div class="card-body">
					<div class="row">
						<div class="col-md-6">
							<?= $form->field($model, 'invoice_number')->textInput(['maxlength' => true])->label(Yii::t('app/invoice', 'Invoice Number')) ?>
							<?= $form->field($model, 'invoice_date')->input('date')->label(Yii::t('app/invoice', 'Invoice Date')) ?>
						</div>
						<div class="col-md-6">
							<?= $form->field($model, 'terms')->dropDownList([
                                'Net 15' => 'Net 15',
                                'Net 30' => 'Net 30',
                                'Net 60' => 'Net 60',
                                'Due on receipt' => 'Due on receipt'
                            ], ['prompt' => Yii::t('app/invoice', 'Select terms')]) ?>
							<?= $form->field($model, 'due_date')->input('date')->label(Yii::t('app/invoice', 'Due Date')) ?>
						</div>
					</div>
				</div>
			</div>

			<!-- Help Information -->
			<div class="card mt-3">
				<div class="card-header p-2" style="cursor: pointer;" data-toggle="collapse" data-target="#invoice-help-collapse" aria-expanded="false">
					<h6 class="card-title mb-0 d-flex justify-content-between align-items-center">
						<span><i class="fas fa-question-circle mr-2"></i><?= Yii::t('app/invoice', 'Invoice Help') ?></span>
						<i class="fas fa-chevron-down collapse-icon"></i>
					</h6>
				</div>
				<div class="collapse" id="invoice-help-collapse">
					<div class="card-body py-2">
						<div class="alert alert-info py-2 mb-0">
							<small>
								<strong><?= Yii::t('app/invoice', 'Invoice Number') ?>:</strong> <?= Yii::t('app/invoice', 'Unique identifier for this invoice.') ?><br>
								<strong><?= Yii::t('app/invoice', 'Terms') ?>:</strong> <?= Yii::t('app/invoice', 'Payment terms that determine due date.') ?><br>
								<strong><?= Yii::t('app/invoice', 'Invoice Date') ?>:</strong> <?= Yii::t('app/invoice', 'Date when invoice is issued.') ?><br>
								<strong><?= Yii::t('app/invoice', 'Due Date') ?>:</strong> <?= Yii::t('app/invoice', 'Payment deadline automatically calculated from terms.') ?><br>
								<strong><?= Yii::t('app/invoice', 'Items') ?>:</strong> <?= Yii::t('app/invoice', 'Add products/services with quantity and rate.') ?><br>
								<strong><?= Yii::t('app/invoice', 'Tax') ?>:</strong> <?= Yii::t('app/invoice', 'Check items that are taxable for automatic calculation.') ?>
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
				<i class="fas fa-list mr-2"></i><?= Yii::t('app/invoice', 'Items') ?>
			</h5>
		</div>
		<div class="card-body">
			<div class="table-responsive">
				<table class="table table-bordered" id="items-table">
					<thead class="thead-light">
						<tr>
							<th style="width: 5%;">#</th>
							<th style="width: 25%;"><?= Yii::t('app/invoice', 'Product/Service') ?></th>
							<th style="width: 35%;"><?= Yii::t('app/invoice', 'Description') ?></th>
							<th style="width: 10%;" class="text-right"><?= Yii::t('app/invoice', 'Quantity') ?></th>
							<th style="width: 10%;" class="text-right"><?= Yii::t('app/invoice', 'Price') ?></th>
							<th style="width: 10%;" class="text-right"><?= Yii::t('app/invoice', 'Amount') ?></th>
							<th style="width: 5%;" class="text-center"><?= Yii::t('app/invoice', 'Tax') ?></th>
							<th style="width: 5%;"></th>
						</tr>
					</thead>
					<tbody id="items-tbody">
						<!-- Items will be added dynamically by JavaScript -->
					</tbody>
				</table>
			</div>
			<?= Html::button(Yii::t('app/invoice', 'Add Item'), ['class' => 'btn btn-outline-primary', 'id' => 'add-item-btn']) ?>
			<?= Html::button(Yii::t('app/invoice', 'Clear All'), ['class' => 'btn btn-outline-danger', 'id' => 'clear-lines-btn']) ?>
		</div>
	</div>

	<div class="row mt-4">
		<div class="col-lg-6">
			<div class="card card-default">
				<div class="card-header">
					<h5 class="card-title mb-0">
						<i class="fas fa-comment mr-2"></i><?= Yii::t('app/invoice', 'Notes & Payment') ?>
					</h5>
				</div>
				<div class="card-body">
					<?= $form->field($model, 'customer_notes')->textarea(['rows' => 3, 'placeholder' => Yii::t('app/invoice', 'Thank you for your business.')])->label(Yii::t('app/invoice', 'Note to Customer')) ?>
					<?= $form->field($model, 'payment_instructions')->textarea(['rows' => 3, 'placeholder' => Yii::t('app/invoice', 'e.g. Bank transfer details')])->label(Yii::t('app/invoice', 'Payment Instructions')) ?>
				</div>
			</div>
		</div>

		<div class="col-lg-6">
			<div class="card card-default">
				<div class="card-header">
					<h5 class="card-title mb-0">
						<i class="fas fa-calculator mr-2"></i><?= Yii::t('app/invoice', 'Total') ?>
					</h5>
				</div>
				<div class="card-body">
					<div class="totals-grid">
						<span><?= Yii::t('app/invoice', 'Subtotal') ?></span>
						<span id="subtotal-display" class="text-right">$0.00</span>

						<span><?= Yii::t('app/invoice', 'Discount') ?></span>
						<div class="text-right d-flex justify-content-end align-items-center">
							<?= $form->field($model, 'discount_value', ['options' => ['class' => 'mb-0 mr-1'], 'template' => '{input}'])->textInput(['id' => 'discount-input', 'class' => 'form-control form-control-sm text-right', 'style' => 'width: 60px;', 'placeholder' => '0']) ?>
							<?= $form->field($model, 'discount_type', ['options' => ['class' => 'mb-0'], 'template' => '{input}'])->dropDownList(['percentage' => '%', 'fixed' => '$'], ['id' => 'discount-type', 'class' => 'form-control form-control-sm', 'style' => 'width: 50px;']) ?>
							<span id="discount-display" class="ml-2">-$0.00</span>
						</div>

						<span><?= Yii::t('app/invoice', 'Taxable Subtotal') ?></span>
						<span id="taxable-subtotal-display" class="text-right">$0.00</span>

						<span><?= Yii::t('app/invoice', 'Sales Tax') ?></span>
						<div class="text-right d-flex justify-content-end align-items-center">
							<select class="form-control form-control-sm mr-2" id="tax-rate-select"
								style="width: 120px;">
								<option value="auto">Auto</option>
								<option value="0">0%</option>
								<option value="5">5%</option>
								<option value="10">10%</option>
							</select>
							<span id="tax-display">$0.00</span>
						</div>
					</div>

					<hr>

					<div class="totals-grid font-weight-bold h5">
						<span><?= Yii::t('app/invoice', 'Total') ?></span>
						<span id="total-display" class="text-right">$0.00</span>
						<span><?= Yii::t('app/invoice', 'Deposit') ?></span>
						<span id="deposit-display" class="text-right">$0.00</span>
						<span><?= Yii::t('app/invoice', 'Balance Due') ?></span>
						<span id="balance-display" class="text-right">$0.00</span>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="form-group mt-4">
		<?= Html::submitButton($model->isNewRecord ? Yii::t('app/invoice', 'Create Invoice') : Yii::t('app/invoice', 'Update Invoice'), ['class' => 'btn btn-success']) ?>
		<?= Html::a(Yii::t('app/invoice', 'Cancel'), ['index'], ['class' => 'btn btn-secondary']) ?>
	</div>

	<?php ActiveForm::end(); ?>

</div>

<!-- Add Customer Modal -->
<div class="modal fade" id="addCustomerModal" tabindex="-1" role="dialog" aria-labelledby="addCustomerModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="addCustomerModalLabel"><?= Yii::t('app/invoice', 'Add New Customer') ?></h5>
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
	const form = document.getElementById('invoice-form');
	form.addEventListener('submit', function(e) {
		if (!form.checkValidity()) {
			e.preventDefault();
			e.stopPropagation();
		}
		form.classList.add('was-validated');
	});

	// --- CONFIGURATION ---
	const config = window.invoiceConfig || {};
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
	const termsSelect = document.getElementById('invoice-terms');
	const editCustomerBtn = document.getElementById('edit-customer-btn');
	const removeShippingBtn = document.getElementById('remove-shipping-btn');
	const addCustomerBtn = document.getElementById('add-customer-btn');
	const saveCustomerBtn = document.getElementById('save-customer-btn');

	// --- INITIALIZATION ---
	initializePage();

	function initializePage() {
		if (existingItems.length > 0) {
			existingItems.forEach(item => addInvoiceItem(item));
			calculateTotals();
		} else {
			addInvoiceItem();
		}

		if (!isNewRecord && selectedCustomerId) {
			loadCustomerData(selectedCustomerId);
		}

		addEventListeners();
	}

	// --- EVENT LISTENERS ---
	function addEventListeners() {
		addItemBtn.addEventListener('click', () => addInvoiceItem());

		clearLinesBtn.addEventListener('click', () => {
			if (confirm('Are you sure you want to clear all lines?')) {
				itemsTbody.innerHTML = '';
				addInvoiceItem(); // Add a fresh row
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
		document.getElementById('tax-rate-select').addEventListener('change', calculateTotals);

		termsSelect.addEventListener('change', (e) => {
			if (e.target.value) updateDueDateFromTerms(e.target.value);
		});

		// Add event listener for invoice date changes
		document.getElementById('invoice-invoice_date').addEventListener('change', (e) => {
			const selectedTerms = termsSelect.value;
			if (selectedTerms) {
				updateDueDateFromTerms(selectedTerms);
			}
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
			document.getElementById('invoice-ship_to_address').value = '';
		});

		// Add Customer Modal Event Listeners
		saveCustomerBtn.addEventListener('click', handleSaveCustomer);
		
		// Handle modal cleanup
		$('#addCustomerModal').on('hidden.bs.modal', function () {
			$('.modal-backdrop').remove();
			$('body').removeClass('modal-open');
			$('body').css('padding-right', '');
		});
		
	}

	// --- CORE FUNCTIONS ---
	function addInvoiceItem(item = {}) {
		const rowIndex = itemsTbody.rows.length;
		const newRow = document.createElement('tr');

		newRow.innerHTML = `
            <td class="align-middle text-center">${rowIndex + 1}</td>
            <td>
                <input type="text" class="form-control product-input" name="InvoiceItem[${rowIndex}][product_service_name]" placeholder="Product or Service" value="${item.product_service_name || ''}">
                <input type="hidden" class="product-id-input" name="InvoiceItem[${rowIndex}][product_id]" value="${item.product_id || ''}">
            </td>
            <td><textarea class="form-control description-input" name="InvoiceItem[${rowIndex}][description]" rows="3" placeholder="Description">${item.description || ''}</textarea></td>
            <td><input type="number" class="form-control quantity-input text-right" name="InvoiceItem[${rowIndex}][quantity]" value="${item.quantity || 1}" min="0" step="1"></td>
            <td><input type="number" class="form-control rate-input text-right" name="InvoiceItem[${rowIndex}][rate]" value="${item.rate || '0.00'}" min="0" step="0.01"></td>
            <td class="align-middle text-right amount-display">$0.00</td>
            <td class="align-middle text-center"><input type="checkbox" class="form-check-input tax-checkbox" name="InvoiceItem[${rowIndex}][is_taxable]" value="1" ${item.is_taxable === false ? '' : 'checked'}></td>
            <td class="align-middle text-center"><button type="button" class="btn btn-sm btn-outline-danger remove-item-btn"><i class="fas fa-trash"></i></button></td>
        `;

		itemsTbody.appendChild(newRow);
		calculateRowAmount(newRow);

		// Initialize autocomplete for the newly added product input
		const newProductInput = newRow.querySelector('.product-input');
		if (newProductInput) {
			initializeProductAutocomplete($(newProductInput));
		}
	}

	function updateRowNumbers() {
		itemsTbody.querySelectorAll('tr').forEach((row, index) => {
			row.querySelector('td:first-child').textContent = index + 1;
			row.querySelectorAll('[name^="InvoiceItem"]').forEach(input => {
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

		const taxRateVal = document.getElementById('tax-rate-select').value;
		const taxRate = taxRateVal === 'auto' ? 0 : parseFloat(taxRateVal) || 0;
		const taxAmount = taxableSubtotal * (taxRate / 100);

		const total = subtotalAfterDiscount + taxAmount;
		const deposit = 0; // Placeholder for deposit logic
		const balance = total - deposit;

		document.getElementById('subtotal-display').textContent = formatCurrency(subtotal);
		document.getElementById('discount-display').textContent = `-${formatCurrency(discountAmount)}`;
		document.getElementById('taxable-subtotal-display').textContent = formatCurrency(taxableSubtotal);
		document.getElementById('tax-display').textContent = formatCurrency(taxAmount);
		document.getElementById('total-display').textContent = formatCurrency(total);
		document.getElementById('deposit-display').textContent = formatCurrency(deposit);
		document.getElementById('balance-display').textContent = formatCurrency(balance);
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

				document.getElementById('invoice-bill_to_address').value = [
					customer.customer_name,
					customer.customer_address,
					customer.customer_phone ? `Phone: ${customer.customer_phone}` : '',
					customer.customer_email ? `Email: ${customer.customer_email}` : ''
				].filter(Boolean).join('\n');

				document.getElementById('invoice-ship_to_address').value = customer.customer_address || '';
				if (customer.payment_terms) {
					termsSelect.value = customer.payment_terms;
					updateDueDateFromTerms(customer.payment_terms);
				}
			}
		} catch (error) {
			console.error('Failed to load customer data:', error);
			document.getElementById('bill-to-address').innerHTML =
				'<span class="text-danger">Error loading customer data.</span>';
		}
	}

	function updateDueDateFromTerms(terms) {
		let invoiceDateStr = document.getElementById('invoice-invoice_date').value;
		if (!invoiceDateStr) {
			const today = new Date();
			invoiceDateStr = formatDate(today);
			document.getElementById('invoice-invoice_date').value = invoiceDateStr;
		}

		const invoiceDate = new Date(invoiceDateStr);
		const dueDate = new Date(invoiceDate);
		let daysToAdd = 0;

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
		}

		if (daysToAdd > 0) {
			dueDate.setDate(dueDate.getDate() + daysToAdd);
		}

		document.getElementById('invoice-due_date').value = formatDate(dueDate);
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
		const formData = new FormData(form);
		
		// Add CSRF token
		formData.append('<?= Yii::$app->request->csrfParam ?>', '<?= Yii::$app->request->csrfToken ?>');
		
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
				setTimeout(() => {
					alert('<?= Yii::t('app/customer', 'Customer added successfully!') ?>');
				}, 500);
			} else {
				console.error('Customer creation failed:', data);
				let errorMessage = data.message || '<?= Yii::t('app/customer', 'Failed to add customer. Please try again.') ?>';
				if (data.errors) {
					errorMessage += '\nErrors: ' + JSON.stringify(data.errors);
				}
				alert(errorMessage);
			}
		} catch (error) {
			console.error('Error adding customer:', error);
			alert('<?= Yii::t('app/customer', 'Error adding customer. Please try again.') ?>');
		} finally {
			saveCustomerBtn.disabled = false;
			saveCustomerBtn.textContent = '<?= Yii::t('app/customer', 'Add Customer') ?>';
		}
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