<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Customer $model */
/** @var app\models\Company $company */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="customer-form">

	<?php $form = ActiveForm::begin([
        'id' => 'customer-form',
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
						<i class="fas fa-user mr-2"></i>Customer Information
					</h5>
				</div>
				<div class="card-body">
					<div class="row">
						<div class="col-md-6">
							<?= $form->field($model, 'customer_name')->textInput([
                                'maxlength' => true,
                                'required' => true,
                                'placeholder' => 'Enter customer name'
                            ]) ?>
						</div>
						<div class="col-md-6">
							<?= $form->field($model, 'customer_email')->input('email', [
                                'maxlength' => true,
                                'placeholder' => 'customer@example.com'
                            ]) ?>
						</div>
					</div>

					<div class="row">
						<div class="col-md-4">
							<?= $form->field($model, 'customer_phone')->textInput([
                                'maxlength' => true,
                                'placeholder' => '+1 (555) 123-4567'
                            ]) ?>
						</div>
						<div class="col-md-4">
							<?= $form->field($model, 'customer_fax')->textInput([
                                'maxlength' => true,
                                'placeholder' => '+1 (555) 123-4567'
                            ]) ?>
						</div>
						<div class="col-md-4">
							<?= $form->field($model, 'customer_mobile')->textInput([
                                'maxlength' => true,
                                'placeholder' => '+1 (555) 123-4567'
                            ]) ?>
						</div>
					</div>

					<div class="row">
						<div class="col-md-6">
							<?= $form->field($model, 'contact_name')->textInput([
                                'maxlength' => true,
                                'placeholder' => 'Enter contact person name'
                            ]) ?>
						</div>
						<div class="col-md-6">
							<!-- Space for future field -->
						</div>
					</div>

					<div class="row">
						<div class="col-md-6">
							<?= $form->field($model, 'payment_terms')->dropDownList([
                                'Net 15' => 'Net 15',
                                'Net 30' => 'Net 30',
                                'Net 60' => 'Net 60',
                                'Due on receipt' => 'Due on receipt',
                                'Cash on delivery' => 'Cash on delivery'
                            ], [
                                'prompt' => 'Select payment terms'
                            ]) ?>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label class="form-label font-weight-bold">Status</label>
								<div class="form-check mt-2">
									<?= Html::activeCheckbox($model, 'is_active', [
                                        'class' => 'form-check-input',
                                        'label' => 'Active Customer',
                                        'labelOptions' => ['class' => 'form-check-label']
                                    ]) ?>
								</div>
							</div>
						</div>
					</div>

					<?= $form->field($model, 'customer_address')->textarea([
                        'rows' => 3,
                        'placeholder' => 'Enter customer address (optional)'
                    ]) ?>

					<div class="row">
						<div class="col-md-6">
							<?= $form->field($model, 'billing_address')->textarea([
                                'rows' => 3,
                                'placeholder' => 'Enter billing address (optional)'
                            ]) ?>
						</div>
						<div class="col-md-6">
							<?= $form->field($model, 'shipping_address')->textarea([
                                'rows' => 3,
                                'placeholder' => 'Enter shipping address (optional)'
                            ]) ?>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="col-lg-4">
			<!-- Help Information -->
			<div class="card">
				<div class="card-header">
					<h6 class="card-title mb-0">
						<i class="fas fa-info-circle mr-2"></i>Customer Information
					</h6>
				</div>
				<div class="card-body">
					<div class="alert alert-info">
						<small>
							<strong>Customer Name:</strong> Required field for identification.<br><br>
							<strong>Email:</strong> Used for sending invoices and communication.<br><br>
							<strong>Phone:</strong> Primary contact number for quick communication.<br><br>
							<strong>Fax:</strong> Fax number for document transmission.<br><br>
							<strong>Mobile:</strong> Mobile phone number for urgent communication.<br><br>
							<strong>Contact Name:</strong> Primary contact person name.<br><br>
							<strong>Addresses:</strong> Customer, billing, and shipping addresses for invoices.
						</small>
					</div>
				</div>
			</div>

			<!-- Quick Actions -->
			<?php if (!$model->isNewRecord): ?>
			<div class="card mt-3">
				<div class="card-header">
					<h6 class="card-title mb-0">Quick Actions</h6>
				</div>
				<div class="card-body">
					<div class="d-grid gap-2">
						<?= Html::a('View Customer', ['view', 'id' => $model->id], [
                            'class' => 'btn btn-outline-primary btn-sm mb-2'
                        ]) ?>

						<?= Html::a('Create Invoice', ['/invoice/create', 'customer_id' => $model->id], [
                            'class' => 'btn btn-outline-success btn-sm mb-2'
                        ]) ?>

						<?php if ($model->customer_email): ?>
						<?= Html::a('Send Email', 'mailto:' . $model->customer_email, [
                                'class' => 'btn btn-outline-info btn-sm'
                            ]) ?>
						<?php endif; ?>
					</div>
				</div>
			</div>
			<?php endif; ?>
		</div>
	</div>

	<div class="form-group mt-4">
		<?= Html::submitButton($model->isNewRecord ? 'Create Customer' : 'Update Customer', [
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
    $('#customer-form').on('submit', function(e) {
        const form = this;
        if (!form.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
        }
        form.classList.add('was-validated');
    });

    // Auto-format phone number
    $('#customer-customer_phone, #customer-customer_fax, #customer-customer_mobile').on('input', function() {
        let value = this.value.replace(/\D/g, '');
        
        // Limit to 11 digits maximum (1 + 10 digits for US numbers)
        if (value.length > 11) {
            value = value.substring(0, 11);
        }
        
        if (value.length === 11 && value.charAt(0) === '1') {
            // Format as +1 (XXX) XXX-XXXX
            value = '+1 (' + value.substring(1, 4) + ') ' + value.substring(4, 7) + '-' + value.substring(7, 11);
        } else if (value.length === 10) {
            // Format as (XXX) XXX-XXXX
            value = '(' + value.substring(0, 3) + ') ' + value.substring(3, 6) + '-' + value.substring(6, 10);
        } else if (value.length > 6) {
            // Partial formatting XXX-XXXX
            value = value.substring(0, 3) + '-' + value.substring(3);
        } else if (value.length > 3) {
            // Partial formatting XXX-
            value = value.substring(0, 3) + '-' + value.substring(3);
        }
        
        this.value = value;
    });
");
?>