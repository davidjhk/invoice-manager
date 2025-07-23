<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\models\Customer $customer */
/** @var app\models\Invoice[] $outstandingInvoices */
/** @var app\models\Invoice $startInvoice */

$this->title = 'Receive Payment for ' . Html::encode($customer->customer_name);
$this->params['breadcrumbs'][] = ['label' => 'Invoices', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => Html::encode($startInvoice->invoice_number), 'url' => ['view', 'id' => $startInvoice->id]];
$this->params['breadcrumbs'][] = 'Receive Payment';
?>

<div class="receive-payment-form">

	<h1><?= Html::encode($this->title) ?></h1>

	<?php $form = ActiveForm::begin(); ?>

	<div class="card card-default">
		<div class="card-body">
			<div class="row">
				<div class="col-md-4">
					<div class="form-group">
						<label class="form-label">Customer</label>
						<p class="form-control-static"><strong><?= Html::encode($customer->customer_name) ?></strong>
						</p>
					</div>
				</div>
				<div class="col-md-4">
					<?= $form->field(new app\models\Payment(), 'payment_date')->textInput(['type' => 'date', 'id' => 'payment-date', 'value' => date('Y-m-d')]) ?>
				</div>
				<div class="col-md-4">
					<?= $form->field(new app\models\Payment(), 'payment_method')->dropDownList([
                        'Check' => 'Check',
                        'Cash' => 'Cash',
                        'Credit Card' => 'Credit Card',
                        'Bank Transfer' => 'Bank Transfer',
                        'Other' => 'Other',
                    ], ['id' => 'payment-method']) ?>
				</div>
			</div>
			<div class="row">
				<div class="col-md-4">
					<div class="form-group">
						<label for="total-amount-received">Amount Received</label>
						<input type="number" id="total-amount-received" class="form-control" step="0.01"
							placeholder="Enter total amount received">
					</div>
				</div>
				<div class="col-md-8">
					<?= $form->field(new app\models\Payment(), 'notes')->textInput(['id' => 'payment-notes', 'placeholder' => 'Add a note or reference...']) ?>
				</div>
			</div>
		</div>
	</div>

	<h3 class="mt-4">Outstanding Invoices</h3>

	<div class="table-responsive">
		<table class="table table-bordered" id="outstanding-invoices-table">
			<thead class="thead-light">
				<tr>
					<th style="width: 5%;"></th>
					<th>Invoice #</th>
					<th>Due Date</th>
					<th class="text-right">Original Amount</th>
					<th class="text-right">Open Balance</th>
					<th class="text-right" style="width: 15%;">Payment</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($outstandingInvoices as $invoice): ?>
				<tr>
					<td class="text-center align-middle">
						<input type="checkbox" class="invoice-checkbox" value="<?= $invoice->id ?>"
							data-balance="<?= $invoice->getRemainingBalance() ?>">
					</td>
					<td><?= Html::a(Html::encode($invoice->invoice_number), ['view', 'id' => $invoice->id], ['target' => '_blank']) ?>
					</td>
					<td><?= Yii::$app->formatter->asDate($invoice->due_date) ?></td>
					<td class="text-right"><?= $invoice->formatAmount($invoice->total_amount) ?></td>
					<td class="text-right balance-cell"><?= $invoice->formatAmount($invoice->getRemainingBalance()) ?>
					</td>
					<td>
						<input type="number" name="payments[<?= $invoice->id ?>]"
							class="form-control form-control-sm text-right payment-input" step="0.01" min="0"
							max="<?= $invoice->getRemainingBalance() ?>" disabled>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>

	<div class="row justify-content-end">
		<div class="col-md-4">
			<div class="totals-summary p-3 bg-light rounded">
				<div class="d-flex justify-content-between">
					<strong>Amount to Apply:</strong>
					<span id="amount-to-apply">$0.00</span>
				</div>
				<div class="d-flex justify-content-between">
					<strong>Amount to Credit:</strong>
					<span id="amount-to-credit" class="text-success">$0.00</span>
				</div>
			</div>
		</div>
	</div>

	<!-- Hidden form fields -->
	<input type="hidden" name="total_amount_received" id="total-amount-received-hidden" value="0">
	<input type="hidden" name="payment_date" id="payment-date-hidden" value="<?= date('Y-m-d') ?>">
	<input type="hidden" name="payment_method" id="payment-method-hidden" value="Cash">
	<input type="hidden" name="notes" id="payment-notes-hidden" value="">

	<div class="form-group mt-4">
		<?= Html::submitButton('Save Payment', ['class' => 'btn btn-success']) ?>
		<?= Html::a('Cancel', ['view', 'id' => $startInvoice->id], ['class' => 'btn btn-secondary']) ?>
	</div>

	<?php ActiveForm::end(); ?>

</div>

<?php
$this->registerJs(<<<'JS'
// Payment Manager - Inline to avoid PageSpeed conflicts
(function() {
    'use strict';
    
    var PaymentManager = {
        init: function() {
            console.log('Payment Manager initializing...');
            
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', this.setupPage.bind(this));
            } else {
                this.setupPage();
            }
        },
        
        setupPage: function() {
            console.log('Setting up payment page...');
            
            var elements = this.getElements();
            if (!elements.isValid) {
                console.error('Required elements not found');
                return;
            }
            
            this.elements = elements;
            this.bindEvents();
            console.log('Payment page setup complete');
        },
        
        getElements: function() {
            var totalAmountInput = document.getElementById('total-amount-received');
            var table = document.getElementById('outstanding-invoices-table');
            var amountToApply = document.getElementById('amount-to-apply');
            var amountToCredit = document.getElementById('amount-to-credit');
            
            if (!totalAmountInput) {
                console.error('total-amount-received input not found');
                return { isValid: false };
            }
            
            if (!table) {
                console.error('outstanding-invoices-table not found');
                return { isValid: false };
            }
            
            var tbody = table.querySelector('tbody');
            if (!tbody) {
                console.error('tbody not found');
                return { isValid: false };
            }
            
            var checkboxes = tbody.querySelectorAll('.invoice-checkbox');
            var paymentInputs = tbody.querySelectorAll('.payment-input');
            
            console.log('Found', checkboxes.length, 'checkboxes and', paymentInputs.length, 'payment inputs');
            
            return {
                isValid: true,
                totalAmountInput: totalAmountInput,
                table: table,
                tbody: tbody,
                amountToApply: amountToApply,
                amountToCredit: amountToCredit,
                checkboxes: checkboxes,
                paymentInputs: paymentInputs
            };
        },
        
        bindEvents: function() {
            var self = this;
            
            this.elements.totalAmountInput.addEventListener('input', function() {
                console.log('Total amount changed:', this.value);
                self.distributePayment();
            });
            
            this.elements.checkboxes.forEach(function(checkbox) {
                checkbox.addEventListener('change', function() {
                    console.log('Checkbox changed:', this.checked, 'Invoice:', this.value);
                    var row = this.closest('tr');
                    var paymentInput = row.querySelector('.payment-input');
                    
                    if (paymentInput) {
                        paymentInput.disabled = !this.checked;
                        if (!this.checked) {
                            paymentInput.value = '';
                        }
                        self.distributePayment();
                    }
                });
            });
            
            this.elements.paymentInputs.forEach(function(input) {
                input.addEventListener('input', function() {
                    console.log('Payment input changed:', this.value);
                    self.updateTotals();
                });
            });
            
            var paymentDate = document.getElementById('payment-date');
            var paymentMethod = document.getElementById('payment-method');
            var paymentNotes = document.getElementById('payment-notes');
            
            if (paymentDate) {
                paymentDate.addEventListener('change', function() {
                    var hidden = document.getElementById('payment-date-hidden');
                    if (hidden) hidden.value = this.value;
                });
            }
            
            if (paymentMethod) {
                paymentMethod.addEventListener('change', function() {
                    var hidden = document.getElementById('payment-method-hidden');
                    if (hidden) hidden.value = this.value;
                });
            }
            
            if (paymentNotes) {
                paymentNotes.addEventListener('input', function() {
                    var hidden = document.getElementById('payment-notes-hidden');
                    if (hidden) hidden.value = this.value;
                });
            }
        },
        
        distributePayment: function() {
            console.log('Distributing payment...');
            
            var totalReceived = parseFloat(this.elements.totalAmountInput.value) || 0;
            var remainingToDistribute = totalReceived;
            
            var hiddenField = document.getElementById('total-amount-received-hidden');
            if (hiddenField) {
                hiddenField.value = totalReceived;
            }
            
            this.elements.paymentInputs.forEach(function(input) {
                input.value = '';
            });
            
            var checkedBoxes = this.elements.tbody.querySelectorAll('.invoice-checkbox:checked');
            console.log('Distributing to', checkedBoxes.length, 'checked invoices');
            
            var self = this;
            checkedBoxes.forEach(function(checkbox) {
                if (remainingToDistribute <= 0) return;
                
                var row = checkbox.closest('tr');
                var balance = parseFloat(checkbox.getAttribute('data-balance')) || 0;
                var paymentInput = row.querySelector('.payment-input');
                
                if (paymentInput && remainingToDistribute > 0) {
                    var amountToPay = Math.min(balance, remainingToDistribute);
                    paymentInput.value = amountToPay.toFixed(2);
                    remainingToDistribute -= amountToPay;
                    console.log('Applied', amountToPay, 'to invoice', checkbox.value);
                }
            });
            
            this.updateTotals();
        },
        
        updateTotals: function() {
            var totalReceived = parseFloat(this.elements.totalAmountInput.value) || 0;
            var appliedAmount = 0;
            
            this.elements.paymentInputs.forEach(function(input) {
                var paymentValue = parseFloat(input.value) || 0;
                if (paymentValue > 0) {
                    appliedAmount += paymentValue;
                }
            });
            
            var creditAmount = totalReceived - appliedAmount;
            
            console.log('Totals updated - Applied:', appliedAmount, 'Credit:', creditAmount);
            
            if (this.elements.amountToApply) {
                this.elements.amountToApply.textContent = this.formatCurrency(appliedAmount);
            }
            
            if (this.elements.amountToCredit) {
                this.elements.amountToCredit.textContent = this.formatCurrency(creditAmount > 0 ? creditAmount : 0);
            }
        },
        
        formatCurrency: function(amount) {
            try {
                return new Intl.NumberFormat('en-US', { 
                    style: 'currency', 
                    currency: 'USD' 
                }).format(amount);
            } catch (e) {
                return '$' + amount.toFixed(2);
            }
        }
    };
    
    PaymentManager.init();
    window.PaymentManager = PaymentManager;
    
})();
JS
);
?>