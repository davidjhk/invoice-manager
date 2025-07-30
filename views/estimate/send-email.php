<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Estimate $model */
/** @var array $emailData */

$this->title = 'Send Estimate: ' . $model->estimate_number;
$this->params['breadcrumbs'][] = ['label' => 'Estimates', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->estimate_number, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Send Email';
?>

<div class="estimate-send-email">

	<div class="d-flex justify-content-between align-items-center mb-4">
		<h1><?= Html::encode($this->title) ?></h1>
		<?= Html::a('<i class="fas fa-arrow-left mr-2"></i>Back to Estimate', ['view', 'id' => $model->id], [
            'class' => 'btn btn-secondary'
        ]) ?>
	</div>

	<div class="row">
		<div class="col-lg-8">
			<div class="card">
				<div class="card-header">
					<h5 class="card-title mb-0">
						<i class="fas fa-envelope mr-2"></i>Email Estimate
					</h5>
				</div>
				<div class="card-body">

					<?php $form = ActiveForm::begin([
                        'id' => 'email-form',
                        'options' => ['class' => 'needs-validation', 'novalidate' => true],
                    ]); ?>

					<div class="form-group">
						<label class="form-label font-weight-bold">To <span class="text-danger">*</span></label>
						<?= Html::textInput('to', $emailData['to'], [
                            'class' => 'form-control',
                            'required' => true,
                            'placeholder' => 'customer@email.com'
                        ]) ?>
					</div>

					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label class="form-label">Cc</label>
								<?= Html::textInput('cc', $emailData['cc'], [
                                    'class' => 'form-control',
                                    'placeholder' => 'Optional'
                                ]) ?>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label class="form-label">Bcc</label>
								<?= Html::textInput('bcc', $emailData['bcc'], [
                                    'class' => 'form-control',
                                    'placeholder' => 'Optional'
                                ]) ?>
							</div>
						</div>
					</div>

					<div class="form-group">
						<label class="form-label font-weight-bold">Subject <span class="text-danger">*</span></label>
						<?= Html::textInput('subject', $emailData['subject'], [
                            'class' => 'form-control',
                            'required' => true
                        ]) ?>
					</div>

					<div class="form-group">
						<label class="form-label font-weight-bold">Message <span class="text-danger">*</span></label>
						<?= Html::textarea('message', $emailData['message'], [
                            'class' => 'form-control',
                            'rows' => 12,
                            'required' => true
                        ]) ?>
						<small class="form-text text-muted">
							You can use HTML formatting in your message.
						</small>
					</div>

					<div class="alert alert-info">
						<i class="fas fa-info-circle mr-2"></i>
						<strong>Note:</strong> The estimate PDF will be automatically attached to this email.
					</div>

					<div class="form-group">
						<?= Html::submitButton('<i class="fas fa-paper-plane mr-2"></i>Send Estimate', [
                            'class' => 'btn btn-success'
                        ]) ?>
						<?= Html::a('Cancel', ['view', 'id' => $model->id], [
                            'class' => 'btn btn-secondary'
                        ]) ?>
					</div>

					<?php ActiveForm::end(); ?>

				</div>
			</div>
		</div>

		<div class="col-lg-4">
			<!-- Estimate Summary -->
			<div class="card mb-4">
				<div class="card-header">
					<h6 class="card-title mb-0">Estimate Summary</h6>
				</div>
				<div class="card-body">
					<table class="table table-sm">
						<tr>
							<td><strong>Estimate #:</strong></td>
							<td><?= Html::encode($model->estimate_number) ?></td>
						</tr>
						<tr>
							<td><strong>Customer:</strong></td>
							<td><?= Html::encode($model->customer->customer_name) ?></td>
						</tr>
						<tr>
							<td><strong>Date:</strong></td>
							<td><?= Yii::$app->formatter->asDate($model->estimate_date) ?></td>
						</tr>
						<?php if ($model->expiry_date): ?>
						<tr>
							<td><strong>Expires:</strong></td>
							<td><?= Yii::$app->formatter->asDate($model->expiry_date) ?></td>
						</tr>
						<?php endif; ?>
						<tr>
							<td><strong>Status:</strong></td>
							<td>
								<span class="badge badge-<?= $model->getStatusClass() ?>">
									<?= $model->getStatusLabel() ?>
								</span>
							</td>
						</tr>
						<tr>
							<td><strong>Total:</strong></td>
							<td><strong><?= $model->formatAmount($model->total_amount) ?></strong></td>
						</tr>
					</table>
				</div>
			</div>

			<!-- Email Tips -->
			<div class="card">
				<div class="card-header">
					<h6 class="card-title mb-0">
						<i class="fas fa-lightbulb mr-2"></i>Email Tips
					</h6>
				</div>
				<div class="card-body">
					<ul class="list-unstyled mb-0">
						<li class="mb-2">
							<i class="fas fa-check text-success mr-2"></i>
							<small>Double-check the recipient email address</small>
						</li>
						<li class="mb-2">
							<i class="fas fa-check text-success mr-2"></i>
							<small>Personalize your message for better response</small>
						</li>
						<li class="mb-2">
							<i class="fas fa-check text-success mr-2"></i>
							<small>Include clear next steps or call-to-action</small>
						</li>
						<li class="mb-2">
							<i class="fas fa-check text-success mr-2"></i>
							<small>Mention the estimate expiry date</small>
						</li>
						<li>
							<i class="fas fa-check text-success mr-2"></i>
							<small>PDF attachment is automatically included</small>
						</li>
					</ul>
				</div>
			</div>

			<!-- Quick Actions -->
			<div class="card mt-3">
				<div class="card-header">
					<h6 class="card-title mb-0">Quick Actions</h6>
				</div>
				<div class="card-body">
					<div class="d-grid gap-2">
						<?= Html::a('<i class="fas fa-search mr-2"></i>Preview PDF', ['preview', 'id' => $model->id], [
                            'class' => 'btn btn-outline-primary btn-sm',
                            'target' => '_blank'
                        ]) ?>

						<?= Html::a('<i class="fas fa-download mr-2"></i>Download PDF', ['download-pdf', 'id' => $model->id], [
                            'class' => 'btn btn-outline-secondary btn-sm',
                            'target' => '_blank'
                        ]) ?>
					</div>
				</div>
			</div>
		</div>
	</div>

</div>

<?php
$this->registerJs("
    // Email form validation
    $('#email-form').on('submit', function(e) {
        const form = this;
        if (!form.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
        }
        form.classList.add('was-validated');
    });

    // Auto-resize message textarea
    const messageTextarea = document.querySelector('textarea[name=\"message\"]');
    if (messageTextarea) {
        messageTextarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
    }
");
?>