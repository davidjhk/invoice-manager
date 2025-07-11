<?php

/** @var yii\web\View $this */
/** @var app\models\Company[] $companies */

use yii\bootstrap4\Html;
use yii\bootstrap4\ActiveForm;

$this->title = 'Select Company';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="company-select">
	<h1><?= Html::encode($this->title) ?></h1>

	<p class="lead">Please select a company to continue:</p>

	<div class="row">
		<?php foreach ($companies as $company): ?>
		<div class="col-md-6 col-lg-4 mb-4">
			<div class="card">
				<div class="card-body text-center">
					<?php if ($company->hasLogo()): ?>
					<img src="<?= $company->getLogoUrl() ?>" alt="<?= Html::encode($company->company_name) ?>"
						class="img-fluid mb-3" style="max-height: 80px;">
					<?php endif; ?>
					<h5 class="card-title"><?= Html::encode($company->company_name) ?></h5>
					<p class="card-text text-muted">
						<?= Html::encode($company->company_email) ?><br>
						<?= Html::encode($company->company_phone) ?>
					</p>
					<?= Html::a('Select', ['company/set-current', 'id' => $company->id], [
                            'class' => 'btn btn-primary btn-select-company',
                            'data-company-id' => $company->id,
                            'data-company-name' => $company->company_name,
                        ]) ?>
				</div>
			</div>
		</div>
		<?php endforeach; ?>
	</div>

	<?php if (Yii::$app->user->identity->canCreateMoreCompanies()): ?>
	<div class="mt-4">
		<div class="card">
			<div class="card-body text-center">
				<h5 class="card-title">Create New Company</h5>
				<p class="card-text text-muted">
					Start managing invoices for a new business or client.
					<br><small class="text-info">
						You can create <?= Yii::$app->user->identity->getRemainingCompanySlots() ?> more companies.
					</small>
				</p>
				<?= Html::a('Add New Company', ['company/create'], [
                    'class' => 'btn btn-success btn-lg'
                ]) ?>
			</div>
		</div>
	</div>
	<?php else: ?>
	<div class="mt-4">
		<div class="card">
			<div class="card-body text-center">
				<h5 class="card-title">Company Limit Reached</h5>
				<p class="card-text text-muted">
					You have reached your maximum number of companies (<?= Yii::$app->user->identity->max_companies ?>).
					<br>Please upgrade your account or contact support to create more companies.
				</p>
				<button class="btn btn-secondary btn-lg" disabled>Add New Company</button>
			</div>
		</div>
	</div>
	<?php endif; ?>
</div>

<?php
use yii\helpers\Url;
use yii\web\JsExpression;

$this->registerJs("
    $(document).ready(function() {
        // Check if jQuery is loaded
        if (typeof jQuery === 'undefined') {
            console.error('jQuery is not loaded!');
            return;
        }
        
        console.log('jQuery version:', jQuery.fn.jquery);
        
        $('.btn-select-company').on('click', function(e) {
            e.preventDefault();
            
            var btn = $(this);
            var companyId = btn.data('company-id');
            
            console.log('Button clicked, Company ID:', companyId);
            
            // Disable button during request
            btn.prop('disabled', true).text('Selecting...');
            
            // Simple data without CSRF (disabled in controller for AJAX)
            var postData = {
                'id': companyId
            };
            
            console.log('Sending data:', postData);
            
            $.post({
                url: '" . Url::to(['company/set-current']) . "',
                data: postData,
                dataType: 'json',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .done(function(response) {
                console.log('Success response:', response);
                if (response && response.success) {
                    window.location.href = '" . Url::to(['site/index']) . "';
                } else {
                    alert('Error: ' + (response.message || 'Unknown error'));
                    btn.prop('disabled', false).text('Select');
                }
            })
            .fail(function(xhr, status, error) {
                console.log('Request failed:');
                console.log('Status:', status);
                console.log('Error:', error);
                console.log('Response:', xhr.responseText);
                console.log('Status Code:', xhr.status);
                
                alert('Request failed. Status: ' + status + ' (' + xhr.status + ')');
                btn.prop('disabled', false).text('Select');
            });
        });
    });
");
?>