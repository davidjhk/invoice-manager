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
							<?= $form->field($model, 'city')->textInput([
                                'maxlength' => true,
                                'placeholder' => 'Enter city'
                            ]) ?>
						</div>
						<div class="col-md-3">
							<?= $form->field($model, 'state')->dropDownList([
                                '' => 'Select State',
                                'AL' => 'Alabama', 'AK' => 'Alaska', 'AZ' => 'Arizona', 'AR' => 'Arkansas',
                                'CA' => 'California', 'CO' => 'Colorado', 'CT' => 'Connecticut', 'DE' => 'Delaware',
                                'FL' => 'Florida', 'GA' => 'Georgia', 'HI' => 'Hawaii', 'ID' => 'Idaho',
                                'IL' => 'Illinois', 'IN' => 'Indiana', 'IA' => 'Iowa', 'KS' => 'Kansas',
                                'KY' => 'Kentucky', 'LA' => 'Louisiana', 'ME' => 'Maine', 'MD' => 'Maryland',
                                'MA' => 'Massachusetts', 'MI' => 'Michigan', 'MN' => 'Minnesota', 'MS' => 'Mississippi',
                                'MO' => 'Missouri', 'MT' => 'Montana', 'NE' => 'Nebraska', 'NV' => 'Nevada',
                                'NH' => 'New Hampshire', 'NJ' => 'New Jersey', 'NM' => 'New Mexico', 'NY' => 'New York',
                                'NC' => 'North Carolina', 'ND' => 'North Dakota', 'OH' => 'Ohio', 'OK' => 'Oklahoma',
                                'OR' => 'Oregon', 'PA' => 'Pennsylvania', 'RI' => 'Rhode Island', 'SC' => 'South Carolina',
                                'SD' => 'South Dakota', 'TN' => 'Tennessee', 'TX' => 'Texas', 'UT' => 'Utah',
                                'VT' => 'Vermont', 'VA' => 'Virginia', 'WA' => 'Washington', 'WV' => 'West Virginia',
                                'WI' => 'Wisconsin', 'WY' => 'Wyoming'
                            ]) ?>
						</div>
						<div class="col-md-3">
							<?= $form->field($model, 'zip_code')->textInput([
                                'maxlength' => true,
                                'placeholder' => '12345'
                            ]) ?>
						</div>
					</div>

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
				<div class="card-header p-2" style="cursor: pointer;" data-toggle="collapse" data-target="#customer-help-collapse" aria-expanded="false">
					<h6 class="card-title mb-0 d-flex justify-content-between align-items-center">
						<span><i class="fas fa-question-circle mr-2"></i>Customer Help</span>
						<i class="fas fa-chevron-down collapse-icon"></i>
					</h6>
				</div>
				<div class="collapse" id="customer-help-collapse">
					<div class="card-body py-2">
						<div class="alert alert-info py-2 mb-0">
							<small>
								<strong>Customer Name:</strong> Required field for identification.<br>
								<strong>Email:</strong> Used for sending invoices and communication.<br>
								<strong>Phone:</strong> Primary contact number for quick communication.<br>
								<strong>Fax:</strong> Fax number for document transmission.<br>
								<strong>Mobile:</strong> Mobile phone number for urgent communication.<br>
								<strong>Contact Name:</strong> Primary contact person name.<br>
								<strong>Addresses:</strong> Customer, billing, and shipping addresses for invoices.
							</small>
						</div>
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

    // Auto-format phone number using common utility
    PhoneFormatter.initPhoneFormattingJQuery('#customer-customer_phone, #customer-customer_fax, #customer-customer_mobile');
    
    // Collapse functionality is handled by collapse-helper.js
");
?>