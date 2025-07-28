<?php

/** @var yii\web\View $this */
/** @var app\models\Company[] $companies */

use yii\bootstrap4\Html;
use yii\bootstrap4\ActiveForm;

$this->title = Yii::t('app/company', 'Select Company');
$this->params['breadcrumbs'][] = $this->title;

// JavaScript variables for translations
$this->registerJs("
    var translations = {
        error: " . json_encode(Yii::t('app', 'Error')) . ",
        unknownError: " . json_encode(Yii::t('app', 'Unknown error')) . ",
        requestFailed: " . json_encode(Yii::t('app', 'Request failed')) . ",
        status: " . json_encode(Yii::t('app', 'Status')) . ",
        select: " . json_encode(Yii::t('app/company', 'Select')) . ",
        indexUrl: " . json_encode(\yii\helpers\Url::to(['site/index'])) . "
    };
", \yii\web\View::POS_HEAD);
?>

<div class="company-select">
	<h1><?= Html::encode($this->title) ?></h1>

	<p class="lead"><?= Yii::t('app/company', 'Please select a company to continue') ?>:</p>

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
					<?= Html::a(Yii::t('app/company', 'Select'), ['company/set-current', 'id' => $company->id], [
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
				<h5 class="card-title"><?= Yii::t('app/company', 'Create New Company') ?></h5>
				<p class="card-text text-muted">
					<?= Yii::t('app/company', 'Start managing invoices for a new business or client') ?>.
					<br><small class="text-info">
						<?= Yii::t('app/company', 'You can create {count} more companies', ['count' => Yii::$app->user->identity->getRemainingCompanySlots()]) ?>.
					</small>
				</p>
				<?= Html::a(Yii::t('app/company', 'Add New Company'), ['company/create'], [
                    'class' => 'btn btn-success btn-lg'
                ]) ?>
			</div>
		</div>
	</div>
	<?php else: ?>
	<div class="mt-4">
		<div class="card">
			<div class="card-body text-center">
				<h5 class="card-title"><?= Yii::t('app/company', 'Company Limit Reached') ?></h5>
				<p class="card-text text-muted">
					<?= Yii::t('app/company', 'You have reached your maximum number of companies ({max})', ['max' => Yii::$app->user->identity->max_companies]) ?>.
					<br><?= Yii::t('app/company', 'Please upgrade your account or contact support to create more companies') ?>.
				</p>
				<button class="btn btn-secondary btn-lg" disabled><?= Yii::t('app/company', 'Add New Company') ?></button>
			</div>
		</div>
	</div>
	<?php endif; ?>
</div>

<?php
use Yii;
use yii\helpers\Url;
use yii\web\JsExpression;
$user_id = Yii::$app->user->isGuest ? Yii::$app->user->id : '';

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
            btn.prop('disabled', true).text('<?= Yii::t('app/company', 'Selecting...') ?>');
            
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
                    // Save the selected company ID to local storage with user-specific key
                    if (typeof(Storage) !== 'undefined') {
                        var userId = '{$user_id}';
                        var selectedCompanyKey = 'selectedCompanyId_' + userId;
                        localStorage.setItem(selectedCompanyKey, companyId);
                        console.log('Saved companyId to localStorage with key: ' + selectedCompanyKey + ', value: ' + companyId);
                    } else {
                        console.log('Sorry, your browser does not support web storage.');
                    }
                    window.location.href = translations.indexUrl;
                } else {
                    alert(translations.error + ': ' + (response.message || translations.unknownError));
                    btn.prop('disabled', false).text(translations.select);
                }
            })
            .fail(function(xhr, status, error) {
                console.log('Request failed:');
                console.log('Status:', status);
                console.log('Error:', error);
                console.log('Response:', xhr.responseText);
                console.log('Status Code:', xhr.status);
                
                alert(translations.requestFailed + '. ' + translations.status + ': ' + status + ' (' + xhr.status + ')');
                btn.prop('disabled', false).text(translations.select);
            });
        });
    });
");
?>