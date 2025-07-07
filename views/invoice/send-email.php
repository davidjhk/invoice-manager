<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\models\Invoice $model */

$this->title = 'Send Invoice Email: ' . $model->invoice_number;
$this->params['breadcrumbs'][] = ['label' => 'Invoices', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->invoice_number, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Send Email';
?>

<div class="invoice-send-email">

	<div class="d-flex justify-content-between align-items-center mb-4">
		<h1><?= Html::encode($this->title) ?></h1>
		<?= Html::a('Back to Invoice', ['view', 'id' => $model->id], ['class' => 'btn btn-secondary']) ?>
	</div>

	<div class="row">
		<div class="col-lg-8">
			<div class="card">
				<div class="card-header">
					<h5 class="card-title mb-0">
						<i class="fas fa-envelope mr-2"></i>Email Details
					</h5>
				</div>
				<div class="card-body">

					<?php $form = ActiveForm::begin([
                        'id' => 'send-email-form',
                        'options' => ['class' => 'needs-validation', 'novalidate' => true],
                        'fieldConfig' => [
                            'options' => ['class' => 'form-group'],
                            'inputOptions' => ['class' => 'form-control'],
                            'labelOptions' => ['class' => 'form-label font-weight-bold'],
                        ],
                    ]); ?>

					<div class="form-group">
						<label class="form-label font-weight-bold">To</label>
						<?= Html::textInput('recipient_email', $model->customer->customer_email, [
                            'class' => 'form-control',
                            'required' => true,
                            'type' => 'email'
                        ]) ?>
						<small class="form-text text-muted">Customer's email address</small>
					</div>

					<div class="form-group">
						<label class="form-label font-weight-bold">Subject</label>
						<?= Html::textInput('subject', "Invoice {$model->invoice_number} from {$model->company->company_name}", [
                            'class' => 'form-control',
                            'required' => true
                        ]) ?>
					</div>

					<div class="form-group">
						<label class="form-label font-weight-bold">Message</label>
						<?php
                        $defaultMessage = "Dear {$model->customer->customer_name},

Thank you for your business! Please find attached invoice #{$model->invoice_number} for the amount of {$model->formatAmount($model->total_amount)}.

Invoice Payment Guide:
- Amount Due: {$model->formatAmount($model->total_amount)}
- Due Date: " . ($model->due_date ? date('F j, Y', strtotime($model->due_date)) : 'Upon receipt') . "
- Pay to the order of: {$model->company->company_name}
- Please send payment to: {$model->company->company_address}

Please process this invoice according to the payment terms. If you have any questions regarding this invoice, please don't hesitate to contact us.

Best regards,
{$model->company->company_name}";
                        ?>
						<?= Html::textarea('message', $defaultMessage, [
                            'class' => 'form-control',
                            'rows' => 12,
                            'required' => true
                        ]) ?>
					</div>

					<div class="form-group">
						<div class="form-check">
							<?= Html::checkbox('attach_pdf', true, [
                                'class' => 'form-check-input',
                                'id' => 'attach-pdf'
                            ]) ?>
							<label class="form-check-label" for="attach-pdf">
								Attach PDF invoice
							</label>
						</div>
					</div>

					<div class="form-group">
						<?= Html::submitButton('<i class="fas fa-paper-plane mr-2"></i>Send Email', [
                            'class' => 'btn btn-success btn-lg',
                            'id' => 'send-email-btn'
                        ]) ?>
						<?= Html::a('Cancel', ['view', 'id' => $model->id], ['class' => 'btn btn-secondary']) ?>
					</div>

					<?php ActiveForm::end(); ?>

				</div>
			</div>
		</div>

		<div class="col-lg-4">
			<!-- Invoice Summary -->
			<div class="card mb-3">
				<div class="card-header">
					<h6 class="card-title mb-0">Invoice Summary</h6>
				</div>
				<div class="card-body">
					<table class="table table-sm">
						<tr>
							<td><strong>Invoice #:</strong></td>
							<td><?= Html::encode($model->invoice_number) ?></td>
						</tr>
						<tr>
							<td><strong>Customer:</strong></td>
							<td><?= Html::encode($model->customer->customer_name) ?></td>
						</tr>
						<tr>
							<td><strong>Date:</strong></td>
							<td><?= Yii::$app->formatter->asDate($model->invoice_date) ?></td>
						</tr>
						<tr>
							<td><strong>Due Date:</strong></td>
							<td><?= $model->due_date ? Yii::$app->formatter->asDate($model->due_date) : 'Not set' ?>
							</td>
						</tr>
						<tr class="table-active">
							<td><strong>Amount:</strong></td>
							<td><strong><?= $model->formatAmount($model->total_amount) ?></strong></td>
						</tr>
					</table>
				</div>
			</div>

			<!-- Email Configuration Status -->
			<div class="card">
				<div class="card-header">
					<h6 class="card-title mb-0">Email Configuration</h6>
				</div>
				<div class="card-body">
					<?php if (!empty($model->company->smtp2go_api_key)): ?>
					<div class="alert alert-success">
						<i class="fas fa-check-circle mr-2"></i>
						SMTP2GO configured
					</div>
					<?php else: ?>
					<div class="alert alert-warning">
						<i class="fas fa-exclamation-triangle mr-2"></i>
						SMTP2GO not configured.
						<?= Html::a('Configure now', ['/company/settings'], ['class' => 'alert-link']) ?>
					</div>
					<?php endif; ?>

					<div class="form-group">
						<label class="form-label">From:</label>
						<div class="text-muted">
							<?= Html::encode($model->company->sender_email ?: $model->company->company_email) ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

</div>

<?php
$sendEmailUrl = \yii\helpers\Url::to(['send-email-ajax', 'id' => $model->id]);

$this->registerJs("
    let isSubmitting = false;
    
    $('#send-email-form').off('submit.sendEmail').on('submit.sendEmail', function(e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        
        // Prevent double submission
        if (isSubmitting) {
            return false;
        }
        
        isSubmitting = true;
        const btn = $('#send-email-btn');
        const originalText = btn.html();
        
        btn.prop('disabled', true).html('<i class=\"fas fa-spinner fa-spin mr-2\"></i>Sending...');
        
        const formData = {
            recipient_email: $('input[name=\"recipient_email\"]').val(),
            subject: $('input[name=\"subject\"]').val(),
            message: $('textarea[name=\"message\"]').val(),
            attach_pdf: $('#attach-pdf').prop('checked') ? 1 : 0
        };
        
        $.post('{$sendEmailUrl}', formData)
        .done(function(response) {
            if (response.success) {
                alert('Email sent successfully!');
                window.location.href = '" . \yii\helpers\Url::to(['view', 'id' => $model->id]) . "';
            } else {
                alert('Failed to send email: ' + response.message);
                isSubmitting = false;
            }
        })
        .fail(function() {
            alert('An error occurred while sending the email. Please try again.');
            isSubmitting = false;
        })
        .always(function() {
            btn.prop('disabled', false).html(originalText);
        });
    });
    
    // Prevent double-click on button
    $('#send-email-btn').off('click.sendEmail').on('click.sendEmail', function(e) {
        if (isSubmitting) {
            e.preventDefault();
            e.stopImmediatePropagation();
            return false;
        }
    });
");
?>