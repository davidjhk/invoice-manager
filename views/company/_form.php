<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\State;
use app\components\PdfTemplateManager;

/** @var yii\web\View $this */
/** @var app\models\Company $model */
/** @var yii\widgets\ActiveForm $form */
/** @var string $mode 'create' or 'settings' */
/** @var bool $isCompactMode */

$mode = $mode ?? 'settings';
$isSettings = $mode === 'settings';
$isCreate = $mode === 'create';
$isCompactMode = $isCompactMode ?? false;

// collapse-helper.js is loaded via AppAsset
?>

<div class="row">
	<!-- Company Information -->
	<div class="col-lg-6">
		<div class="card mb-4">
			<div class="card-header">
				<h5 class="card-title mb-0">
					<i class="fas fa-building mr-2"></i><?= Yii::t('app/company', 'Company Information') ?>
				</h5>
			</div>
			<div class="card-body">
				<?= $form->field($model, 'company_name')->textInput([
                    'maxlength' => true,
                    'placeholder' => $isCreate ? Yii::t('app/company', 'Enter company name') : null
                ]) ?>

				<?= $form->field($model, 'company_address')->textarea([
                    'rows' => 4,
                    'placeholder' => $isCreate ? Yii::t('app/company', 'Enter company address') : null
                ]) ?>

				<div class="row">
					<div class="col-md-6">
						<?= $form->field($model, 'city')->textInput([
                            'maxlength' => true,
                            'placeholder' => $isCreate ? Yii::t('app/company', 'Enter city') : null
                        ]) ?>
					</div>
					<div class="col-md-3">
						<?= $form->field($model, 'state')->dropDownList(
                            State::getUsStateList(), 
                            ['prompt' => Yii::t('app/company', 'Select State')]
                        ) ?>
					</div>
					<div class="col-md-3">
						<?= $form->field($model, 'zip_code')->textInput([
                            'maxlength' => true,
                            'placeholder' => $isCreate ? '12345' : null
                        ]) ?>
					</div>
				</div>

				<div class="row">
					<div class="col-md-6">
						<?= $form->field($model, 'company_phone')->textInput([
                            'maxlength' => true,
                            'placeholder' => $isCreate ? Yii::t('app/company', 'Enter phone number') : null
                        ]) ?>
					</div>
					<div class="col-md-6">
						<?= $form->field($model, 'company_email')->input('email', [
                            'maxlength' => true,
                            'placeholder' => $isCreate ? Yii::t('app/company', 'Enter company email') : null
                        ]) ?>
					</div>
				</div>
			</div>
		</div>
		<!-- Language Settings -->
		<div class="card mb-4">
			<div class="card-header p-2" style="cursor: pointer;" data-custom-collapse="true"
				data-target="#language-settings-collapse" aria-expanded="false">
				<h5 class="card-title mb-0 d-flex justify-content-between align-items-center">
					<span><i class="fas fa-language mr-2"></i><?= Yii::t('app/company', 'Language Settings') ?></span>
					<i class="fas fa-chevron-down collapse-icon"></i>
				</h5>
			</div>
			<div class="collapse" id="language-settings-collapse">
				<div class="card-body">
					<div class="form-group">
						<?= $form->field($model, 'language')->dropDownList(
							\app\models\Company::getLanguageOptions(),
							[
								'prompt' => Yii::t('app/company', 'Select your preferred language for the interface'),
								'class' => 'form-control'
							]
						)->label(Yii::t('app/company', 'Interface Language')) ?>
						<small class="form-text text-muted">
							<?= $isSettings 
								? Yii::t('app/company', 'Changing the language will update the interface immediately after saving settings.')
								: Yii::t('app/company', 'This will be your default interface language.') ?>
						</small>
					</div>

					<div class="alert alert-info">
						<small>
							<strong><?= Yii::t('app', 'Available Languages') ?>:</strong>
							<br>• <?= Yii::t('app/company', 'English') ?> (English)
							<br>• <?= Yii::t('app/company', 'Spanish') ?> (Español)
							<br>• <?= Yii::t('app/company', 'Korean') ?> (한국어)
							<br>• <?= Yii::t('app/company', 'Chinese (Simplified)') ?> (简체중문)
							<br>• <?= Yii::t('app/company', 'Chinese (Traditional)') ?> (繁體中文)
						</small>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="col-lg-6">
		<!-- Company Logo -->
		<div class="card mb-4">
			<div class="card-header">
				<h5 class="card-title mb-0">
					<span><i class="fas fa-image mr-2"></i><?= Yii::t('app/company', 'Company Logo') ?></span>
				</h5>
			</div>
			<div id="company-logo-collapse">
				<div class="card-body">
					<?php if ($isSettings && $model->hasLogo()): ?>
					<div class="current-logo mb-3">
						<label class="form-label font-weight-bold"><?= Yii::t('app/company', 'Current Logo') ?></label>
						<div class="logo-preview">
							<img src="<?= $model->getLogoUrl() ?>" alt="Company Logo" class="img-thumbnail"
								style="max-height: 150px;">
							<div class="mt-2">
								<small class="text-muted"><?= Yii::t('app/company', 'Filename') ?>:
									<?= Html::encode($model->logo_filename) ?></small>
								<br>
								<?= Html::button('<i class="fas fa-trash mr-1"></i>' . Yii::t('app/company', $isCompactMode ? '' : 'Delete Logo'), [
                                    'class' => 'btn btn-outline-danger btn-sm mt-1',
                                    'id' => 'delete-logo-btn',
                                    'encode' => false,
                                    'title' => $isCompactMode ? Yii::t('app/company', 'Delete Logo') : '',
                                    'data-toggle' => $isCompactMode ? 'tooltip' : ''
                                ]) ?>
							</div>
						</div>
					</div>
					<?php endif; ?>

					<div class="form-group">
						<label class="form-label font-weight-bold">
							<?= $isSettings ? Yii::t('app/company', 'Upload New Logo') : Yii::t('app/company', 'Upload Logo') ?>
						</label>

						<label for="logo-upload" class="logo-upload-area" id="logo-upload-area">
							<div class="upload-content">
								<i class="fas fa-cloud-upload-alt upload-icon"></i>
								<div class="upload-text">
									<strong><?= Yii::t('app/company', 'Click to upload') ?></strong>
									<?= Yii::t('app/company', 'or drag and drop') ?>
								</div>
								<div class="upload-hint">
									<?= Yii::t('app/company', 'PNG, JPG, JPEG, GIF up to 2MB') ?>
								</div>
							</div>
							<?= Html::activeFileInput($model, 'logo_upload', [
                                'class' => 'file-input',
                                'accept' => 'image/*',
                                'id' => 'logo-upload'
                            ]) ?>
						</label>
					</div>

					<div id="logo-preview" class="mt-3" style="display: none;">
						<label class="form-label"><?= Yii::t('app/company', 'Preview') ?></label>
						<div>
							<img id="logo-preview-img" src="#" alt="Logo Preview" class="img-thumbnail"
								style="max-height: 150px;">
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Invoice Settings -->
<div class="card mb-4">
	<div class="card-header p-2" style="cursor: pointer;" data-custom-collapse="true"
		data-target="#invoice-settings-collapse" aria-expanded="<?= $isCreate ? 'true' : 'false' ?>">
		<h5 class="card-title mb-0 d-flex justify-content-between align-items-center">
			<span><i class="fas fa-file-invoice mr-2"></i><?= Yii::t('app/company', 'Invoice Settings') ?></span>
			<i class="fas fa-chevron-down collapse-icon <?= $isCreate ? 'rotated' : '' ?>"></i>
		</h5>
	</div>
	<div class="collapse <?= $isCreate ? 'show' : '' ?>" id="invoice-settings-collapse">
		<div class="card-body">
			<div class="row">
				<div class="col-md-3">
					<?= $form->field($model, 'tax_rate')->textInput([
                        'type' => 'number',
                        'min' => 0,
                        'max' => 100,
                        'step' => 0.01,
                        'placeholder' => $isCreate ? Yii::t('app/company', '10.00') : null,
                        'append' => $isSettings ? '%' : null
                    ]) ?>
				</div>
				<div class="col-md-3">
					<?= $form->field($model, 'currency')->dropDownList([
                        'USD' => 'USD ($)',
                        'EUR' => 'EUR (€)',
                        'GBP' => 'GBP (£)',
                        'KRW' => 'KRW (₩)',
                    ], ['prompt' => Yii::t('app/company', 'Select Currency')]) ?>
				</div>
				<div class="col-md-3">
					<?= $form->field($model, 'invoice_prefix')->textInput([
                        'maxlength' => true,
                        'placeholder' => Yii::t('app/company', 'INV')
                    ]) ?>
				</div>
				<div class="col-md-3">
					<?= $form->field($model, 'estimate_prefix')->textInput([
                        'maxlength' => true,
                        'placeholder' => Yii::t('app/company', 'EST')
                    ]) ?>
				</div>
				<div class="col-md-3">
					<?= $form->field($model, 'due_date_days')->textInput([
                        'type' => 'number',
                        'min' => 1,
                        'max' => 365,
                        'placeholder' => $isCreate ? '30' : null
                    ]) ?>
				</div>
				<div class="col-md-3">
					<?= $form->field($model, 'estimate_validity_days')->textInput([
                        'type' => 'number',
                        'min' => 1,
                        'max' => 365,
                        'placeholder' => $isCreate ? '30' : null
                    ]) ?>
				</div>
			</div>

			<?php if ($isSettings): ?>
			<div class="alert alert-light">
				<div class="row">
					<div class="col-md-3">
						<small><strong><?= Yii::t('app/company', 'Next Invoice Number') ?>:</strong> <span
								id="next-invoice-number"><?= $model->generateInvoiceNumber() ?></span></small>
					</div>
					<div class="col-md-3">
						<small><strong><?= Yii::t('app/company', 'Next Estimate Number') ?>:</strong> <span
								id="next-estimate-number"><?= $model->generateEstimateNumber() ?></span></small>
					</div>
					<div class="col-md-3">
						<small><strong><?= Yii::t('app/company', 'Default Due Date') ?>:</strong>
							<?= Yii::$app->formatter->asDate($model->getDefaultDueDate()) ?></small>
					</div>
					<div class="col-md-3">
						<small><strong><?= Yii::t('app/company', 'Default Estimate Expiry') ?>:</strong>
							<?= Yii::$app->formatter->asDate($model->getDefaultExpiryDate()) ?></small>
					</div>
				</div>
			</div>
			<?php endif; ?>
		</div>
	</div>
</div>
<?php if ($isSettings && Yii::$app->user->identity->canManageSubusers()): ?>
<div class="row">
	<!-- Subuser Management -->
	<div class="col-lg-12">
		<div class="card mb-4">
			<div class="card-header p-2" style="cursor: pointer;" data-custom-collapse="true"
				data-target="#subuser-management-collapse" aria-expanded="false">
				<h5 class="card-title mb-0 d-flex justify-content-between align-items-center">
					<span><i class="fas fa-users mr-2"></i><?= Yii::t('app/company', 'Subuser Management') ?></span>
					<i class="fas fa-chevron-down collapse-icon"></i>
				</h5>
			</div>
			<div class="collapse" id="subuser-management-collapse">
				<div class="card-body">
					<div class="d-flex justify-content-between align-items-center mb-3">
						<div>
							<h6 class="font-weight-bold mb-1">
								<i class="fas fa-user-plus mr-2"></i><?= Yii::t('app/company', 'Team Members') ?>
							</h6>
							<small class="text-muted">
								<?= Yii::t('app/company', 'Manage subusers who can access your companies') ?>
								(<?= Yii::$app->user->identity->getSubuserCount() ?>/<?= Yii::$app->user->identity->getMaxSubusersLimit() === null ? '∞' : Yii::$app->user->identity->getMaxSubusersLimit() ?>)
							</small>
						</div>
						<?php if (Yii::$app->user->identity->canCreateMoreSubusers()): ?>
						<?= Html::button('<i class="fas fa-plus mr-1"></i>' . Yii::t('app/company', 'Add Subuser'), [
							'class' => 'btn btn-primary btn-sm',
							'id' => 'add-subuser-btn',
							'data-toggle' => 'modal',
							'data-target' => '#subuser-modal',
							'encode' => false
						]) ?>
						<?php endif; ?>
					</div>

					<!-- Existing Subusers List -->
					<div id="subusers-list">
						<?php 
						$subusers = Yii::$app->user->identity->getSubusers()->where(['is_active' => true])->all();
						if (empty($subusers)):
						?>
						<div class="text-center py-4 text-muted">
							<i class="fas fa-user-friends fa-3x mb-3"></i>
							<p><?= Yii::t('app/company', 'No subusers created yet') ?></p>
							<?php if (Yii::$app->user->identity->canCreateMoreSubusers()): ?>
							<p><small><?= Yii::t('app/company', 'Click "Add Subuser" to create your first team member') ?></small>
							</p>
							<?php endif; ?>
						</div>
						<?php else: ?>
						<div class="table-responsive">
							<table class="table table-hover">
								<thead>
									<tr>
										<th><?= Yii::t('app/company', 'Name') ?></th>
										<th><?= Yii::t('app/company', 'Email') ?></th>
										<th><?= Yii::t('app/company', 'Company Access') ?></th>
										<th><?= Yii::t('app/company', 'Created') ?></th>
										<th><?= Yii::t('app/company', 'Actions') ?></th>
									</tr>
								</thead>
								<tbody>
									<?php foreach ($subusers as $subuser): ?>
									<tr>
										<td>
											<i class="fas fa-user-circle mr-2 text-primary"></i>
											<?= Html::encode($subuser->getDisplayName()) ?>
										</td>
										<td><?= Html::encode($subuser->email) ?></td>
										<td>
											<?php 
											$userCompanies = Yii::$app->user->identity->getCompanies()->where(['is_active' => true])->all();
											$accessibleCompanies = $subuser->getAccessibleCompanies()->all();
											$accessibleCompanyIds = [];
											foreach ($accessibleCompanies as $company) {
												$accessibleCompanyIds[] = $company->id;
											}
											?>
											<div class="company-access-controls" data-subuser-id="<?= $subuser->id ?>">
												<?php foreach ($userCompanies as $company): ?>
												<div class="form-check form-check-inline">
													<input class="form-check-input company-access-checkbox"
														type="checkbox"
														id="company-<?= $company->id ?>-subuser-<?= $subuser->id ?>"
														data-company-id="<?= $company->id ?>"
														data-subuser-id="<?= $subuser->id ?>"
														<?= in_array($company->id, $accessibleCompanyIds) ? 'checked' : '' ?>>
													<label class="form-check-label"
														for="company-<?= $company->id ?>-subuser-<?= $subuser->id ?>">
														<?= Html::encode($company->company_name) ?>
													</label>
												</div>
												<?php endforeach; ?>
											</div>
										</td>
										<td><?= Yii::$app->formatter->asDatetime($subuser->created_at) ?></td>
										<td>
											<?= Html::button('<i class="fas fa-edit"></i>', [
												'class' => 'btn btn-sm btn-outline-primary edit-subuser-btn',
												'data-subuser-id' => $subuser->id,
												'data-toggle' => 'tooltip',
												'title' => Yii::t('app/company', 'Edit Subuser')
											]) ?>
											<?= Html::button('<i class="fas fa-trash"></i>', [
												'class' => 'btn btn-sm btn-outline-danger delete-subuser-btn ml-1',
												'data-subuser-id' => $subuser->id,
												'data-subuser-name' => $subuser->getDisplayName(),
												'data-toggle' => 'tooltip',
												'title' => Yii::t('app/company', 'Delete Subuser')
											]) ?>
										</td>
									</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
						</div>
						<?php endif; ?>
					</div>

					<?php if (!Yii::$app->user->identity->canCreateMoreSubusers() && Yii::$app->user->identity->getMaxSubusersLimit() > 0): ?>
					<div class="alert alert-warning mt-3">
						<i class="fas fa-exclamation-triangle mr-2"></i>
						<?= Yii::t('app/company', 'You have reached the maximum number of subusers for your plan') ?>
						(<?= Yii::$app->user->identity->getMaxSubusersLimit() ?>)
					</div>
					<?php elseif (Yii::$app->user->identity->getMaxSubusersLimit() === 0 && !Yii::$app->user->identity->isAdmin()): ?>
					<div class="alert alert-info mt-3">
						<i class="fas fa-info-circle mr-2"></i>
						<?= Yii::t('app/company', 'Upgrade to Pro plan to create subusers') ?>
					</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
</div>
<?php endif; ?>

<!-- Display Settings (Left) and Email Settings (Right) -->
<div class="row">
	<!-- Display Settings -->
	<div class="col-lg-6">
		<div class="card mb-4">
			<div class="card-header p-2" style="cursor: pointer;" data-custom-collapse="true"
				data-target="#display-settings-collapse" aria-expanded="false">
				<h5 class="card-title mb-0 d-flex justify-content-between align-items-center">
					<span><i
							class="fas fa-palette mr-2"></i><?= Yii::t('app/company', 'Display & PDF Settings') ?></span>
					<i class="fas fa-chevron-down collapse-icon"></i>
				</h5>
			</div>
			<div class="collapse" id="display-settings-collapse">
				<div class="card-body">
					<div class="form-group">
						<label class="font-weight-bold"><?= Yii::t('app/company', 'Dark Mode') ?></label>
						<div class="custom-control custom-switch">
							<input type="hidden" name="Company[dark_mode]" value="0">
							<input type="checkbox" class="custom-control-input" id="dark-mode-switch"
								name="Company[dark_mode]" value="1" <?= $model->dark_mode ? 'checked' : '' ?>>
							<label class="custom-control-label" for="dark-mode-switch">
								<?= Yii::t('app/company', 'Enable dark mode theme') ?>
							</label>
						</div>
						<small class="form-text text-muted">
							<?= Yii::t('app/company', 'Enable dark mode theme for your company\'s interface') ?>
						</small>
					</div>

					<div class="form-group">
						<label class="font-weight-bold"><?= Yii::t('app/company', 'Compact Mode') ?></label>
						<div class="custom-control custom-switch">
							<input type="hidden" name="Company[compact_mode]" value="0">
							<input type="checkbox" class="custom-control-input" id="compact-mode-switch"
								name="Company[compact_mode]" value="1" <?= $model->compact_mode ? 'checked' : '' ?>>
							<label class="custom-control-label" for="compact-mode-switch">
								<?= Yii::t('app/company', 'Enable compact menu display') ?>
							</label>
						</div>
						<small class="form-text text-muted">
							<?= Yii::t('app/company', 'Hide menu text in the top bar and show icons only') ?>
						</small>
					</div>

					<?php if (Yii::$app->user->identity && Yii::$app->user->identity->canUseCustomTemplates()): ?>
					<div class="form-group">
						<?= $form->field($model, 'pdf_template')->dropDownList(
							PdfTemplateManager::getTemplateOptions(),
							[
								'id' => 'pdf-template-select',
								'class' => 'form-control',
								'onchange' => 'updateCompanyTemplatePreview(this.value)'
							]
						)->label(Yii::t('invoice', 'Choose Template'), ['class' => 'font-weight-bold']) ?>
						<div class="row">
							<div class="col-md-12">
								<div id="company-template-preview" class="border rounded mt-2"
									style="min-height: 300px; background: #f8f9fa;">
									<div class="text-center p-4">
										<i class="fas fa-file-pdf fa-3x text-muted mb-2"></i>
										<div class="text-muted"><?= Yii::t('invoice', 'Template Preview') ?></div>
									</div>
								</div>
							</div>
						</div>
						<small class="form-text text-muted">
							<?= Yii::t('invoice', 'Select a template for your invoice PDF') ?>
						</small>
					</div>
					<?php else: ?>
					<div class="form-group">
						<label class="font-weight-bold"><?= Yii::t('invoice', 'Choose Template') ?></label>
						<div class="alert alert-info">
							<i class="fas fa-info-circle mr-2"></i>
							<?= Yii::t('app', 'Template selection is available for Pro plan subscribers.') ?>
							<?= Yii::t('app', 'You are currently using the Classic template.') ?>
							<br>
							<?= Html::a(
								'<i class="fas fa-arrow-up mr-1"></i>' . Yii::t('app', 'Upgrade Plan'),
								['/subscription/my-account'],
								['class' => 'btn btn-primary btn-sm mt-2', 'encode' => false]
							) ?>
						</div>
						<?= Html::activeHiddenInput($model, 'pdf_template', ['value' => 'classic']) ?>
					</div>
					<?php endif; ?>

					<div class="form-group">
						<label class="font-weight-bold"><?= Yii::t('app/company', 'Use CJK Fonts for PDF') ?></label>
						<div class="custom-control custom-switch">
							<input type="hidden" name="Company[use_cjk_font]" value="0">
							<input type="checkbox" class="custom-control-input" id="cjk-font-switch"
								name="Company[use_cjk_font]" value="1" <?= $model->use_cjk_font ? 'checked' : '' ?>>
							<label class="custom-control-label" for="cjk-font-switch">
								<?= Yii::t('app/company', 'Use CJK fonts for PDF generation') ?>
							</label>
						</div>
						<small class="form-text text-muted">
							<?= Yii::t('app/company', 'Use CJK Description') ?>
						</small>
					</div>

					<div class="form-group">
						<label class="font-weight-bold"><?= Yii::t('app/company', 'Hide PDF Footer') ?></label>
						<div class="custom-control custom-switch">
							<input type="hidden" name="Company[hide_footer]" value="0">
							<input type="checkbox" class="custom-control-input" id="hide-footer-switch"
								name="Company[hide_footer]" value="1" <?= $model->hide_footer ? 'checked' : '' ?>>
							<label class="custom-control-label" for="hide-footer-switch">
								<?= Yii::t('app/company', 'Hide footer text in PDF documents') ?>
							</label>
						</div>
						<small class="form-text text-muted">
							<?= Yii::t('app/company', 'Hide the "Generated by..." text in PDF documents. Page numbers will still be shown.') ?>
						</small>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="col-lg-6">
		<!-- Email Settings -->
		<div class="card mb-4">
			<div class="card-header p-2" style="cursor: pointer;" data-custom-collapse="true"
				data-target="#email-settings-collapse" aria-expanded="false">
				<h5 class="card-title mb-0 d-flex justify-content-between align-items-center">
					<span><i class="fas fa-envelope mr-2"></i><?= Yii::t('app/company', 'Email Settings') ?></span>
					<i class="fas fa-chevron-down collapse-icon"></i>
				</h5>
			</div>
			<div class="collapse" id="email-settings-collapse">
				<div class="card-body">
					<?php if ($isSettings): ?>
					<div class="d-flex justify-content-between align-items-center mb-3">
						<h6 class="font-weight-bold mb-0">
							<i class="fas fa-cog mr-2"></i><?= Yii::t('app/company', 'Email Configuration') ?>
						</h6>
						<?php 
							$hasSmtpKey = !empty($model->smtp2go_api_key);
						?>
						<?= Html::button('<i class="fas fa-envelope mr-1"></i>' . Yii::t('app/company', $isCompactMode ? '' : 'Test Email'), [
                            'class' => 'btn ' . ($hasSmtpKey ? 'btn-outline-primary' : 'btn-outline-secondary') . ' btn-sm',
                            'id' => 'test-email-btn',
                            'data-toggle' => $hasSmtpKey ? 'modal' : ($isCompactMode ? 'tooltip' : null),
                            'data-target' => $hasSmtpKey ? '#test-email-modal' : null,
                            'disabled' => !$hasSmtpKey,
                            'title' => $hasSmtpKey ? ($isCompactMode ? Yii::t('app/company', 'Test Email') : null) : Yii::t('app/company', 'Please configure SMTP2GO API Key first'),
                            'encode' => false
                        ]) ?>
					</div>
					<?php endif; ?>

					<div class="form-group">
						<?= Html::activeLabel($model, 'smtp2go_api_key', ['class' => 'control-label']) ?>
						<div class="input-group">
							<?= Html::activePasswordInput($model, 'smtp2go_api_key', [
								'class' => 'form-control',
								'maxlength' => true,
								'placeholder' => $isCreate 
									? Yii::t('app/company', 'Enter your SMTP2GO API key (optional)')
									: Yii::t('app/company', 'Enter your SMTP2GO API key'),
								'id' => 'smtp2go-api-key-input'
							]) ?>
							<div class="input-group-append">
								<?= Html::button('<i class="fas fa-check"></i> ' . Yii::t('app/company', $isCompactMode ? '' : 'Apply'), [
									'class' => 'btn btn-outline-success btn-sm',
									'id' => 'apply-smtp-key-btn',
									'title' => $isCompactMode ? Yii::t('app/company', 'Apply SMTP2GO API Key') : Yii::t('app/company', 'Apply SMTP2GO API Key'),
									'encode' => false,
									'data-toggle' => $isCompactMode ? 'tooltip' : ''
								]) ?>
							</div>
						</div>
						<?= Html::error($model, 'smtp2go_api_key', ['class' => 'help-block']) ?>
					</div>

					<?= $form->field($model, 'sender_email')->input('email', [
                        'maxlength' => true,
                        'placeholder' => Yii::t('app/company', 'noreply@yourcompany.com')
                    ]) ?>

					<?= $form->field($model, 'sender_name')->textInput([
                        'maxlength' => true,
                        'placeholder' => Yii::t('app/company', 'Your Company Name')
                    ]) ?>

					<?= $form->field($model, 'bcc_email')->input('email', [
                        'maxlength' => true,
                        'placeholder' => $isCreate 
                            ? Yii::t('app/company', 'admin@yourcompany.com (optional)')
                            : Yii::t('app/company', 'admin@yourcompany.com')
                    ]) ?>

					<div class="alert alert-info">
						<small>
							<i class="fas fa-info-circle mr-1"></i>
							<?php if ($isCreate): ?>
							<?= Yii::t('app/company', 'Email settings are optional but recommended for sending invoices') ?>.
							<a href="https://www.smtp2go.com"
								target="_blank"><?= Yii::t('app/company', 'Get your SMTP2GO API key here') ?></a>.
							<?php else: ?>
							To use email functionality, you need to configure SMTP2GO API.
							<a href="https://www.smtp2go.com" target="_blank">Get your API key here</a>.
							<br><br>
							<strong>Email Configuration:</strong>
							<br>• <strong>Sender Email:</strong> The email address that will appear as "From" in sent
							emails
							<br>• <strong>Sender Name:</strong> The name that will appear as sender (defaults to company
							name)
							<br>• <strong>BCC Email:</strong> Email address to receive blind carbon copy of all sent
							emails
							<?php endif; ?>
						</small>
					</div>
				</div>
			</div>
		</div>
	</div>

</div>


<?php
// Register all CSS styles
$this->registerCss("
    /* Collapse functionality styles */
    .collapse {
        display: none;
        transition: all 0.3s ease;
    }
    
    .collapse.show {
        display: block;
    }
    
    .collapse-icon {
        transition: transform 0.3s ease;
    }
    
    .collapse-icon.rotated {
        transform: rotate(180deg);
    }
    
    [data-custom-collapse] {
        transition: background-color 0.3s ease;
    }
    
    [data-custom-collapse]:hover {
        background-color: rgba(0,0,0,0.05);
    }

    /* Custom switch styling to ensure proper display */
    .custom-switch {
        padding-left: 2.25rem;
        position: relative;
    }

    .custom-switch .custom-control-input {
        position: absolute;
        left: 0;
        z-index: -1;
        width: 1rem;
        height: 1.25rem;
        opacity: 0;
    }

    .custom-switch .custom-control-label {
        position: relative;
        margin-bottom: 0;
        vertical-align: top;
    }

    .custom-switch .custom-control-label::before {
        position: absolute;
        top: 0.25rem;
        left: -2.25rem;
        width: 1.75rem;
        height: 1rem;
        pointer-events: none;
        content: '';
        background-color: #adb5bd;
        border: #adb5bd;
        border-radius: 0.5rem;
        transition: background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }

    .custom-switch .custom-control-label::after {
        position: absolute;
        top: calc(0.25rem + 2px);
        left: calc(-2.25rem + 2px);
        width: calc(1rem - 4px);
        height: calc(1rem - 4px);
        content: '';
        background-color: #fff;
        border-radius: 0.5rem;
        transition: transform 0.15s ease-in-out;
    }

    .custom-switch .custom-control-input:checked~.custom-control-label::before {
        background-color: #007bff;
        border-color: #007bff;
    }

    .custom-switch .custom-control-input:checked~.custom-control-label::after {
        transform: translateX(0.75rem);
    }

    .custom-switch .custom-control-input:focus~.custom-control-label::before {
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }

    .custom-switch .custom-control-input:focus:not(:checked)~.custom-control-label::before {
        border-color: #80bdff;
    }

    .custom-switch .custom-control-input:not(:disabled):active~.custom-control-label::before {
        background-color: #b3d7ff;
        border-color: #b3d7ff;
    }

    .custom-switch .custom-control-input:disabled~.custom-control-label,
    .custom-switch .custom-control-input:disabled~.custom-control-label::before {
        color: #6c757d;
    }

    .custom-switch .custom-control-input:disabled~.custom-control-label::before {
        background-color: #e9ecef;
    }

    /* Modern Logo Upload Area */
    .logo-upload-area {
        position: relative;
        border: 2px dashed #e5e7eb;
        border-radius: 12px;
        padding: 2rem;
        text-align: center;
        background: #f8fafc;
        transition: all 0.3s ease;
        cursor: pointer;
        min-height: 120px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .logo-upload-area:hover {
        border-color: #6366f1;
        background: #f0f9ff;
    }
    
    .logo-upload-area.dragover {
        border-color: #6366f1;
        background: #e0e7ff;
        transform: scale(1.02);
    }
    
    .upload-content {
        /* pointer-events: none; - Removed to allow clicks */
        cursor: pointer;
    }
    
    .upload-icon {
        font-size: 2rem;
        color: #6b7280;
        margin-bottom: 0.5rem;
        display: block;
    }
    
    .upload-text {
        font-size: 1rem;
        color: #374151;
        margin-bottom: 0.25rem;
    }
    
    .upload-hint {
        font-size: 0.875rem;
        color: #6b7280;
    }
    
    .file-input {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        cursor: pointer;
        z-index: 10;
        background: transparent;
    }
    
    /* Dark Mode Support */
    body.dark-mode .logo-upload-area {
        border-color: #374151;
        background: #1f2937;
    }
    
    body.dark-mode .logo-upload-area:hover {
        border-color: #60a5fa;
        background: #1e293b;
    }
    
    body.dark-mode .logo-upload-area.dragover {
        border-color: #60a5fa;
        background: #334155;
    }
    
    body.dark-mode .upload-icon {
        color: #9ca3af;
    }
    
    body.dark-mode .upload-text {
        color: #f3f4f6;
    }
    
    body.dark-mode .upload-hint {
        color: #9ca3af;
    }
    .modal.show .modal-dialog{
        border-radius: 12px;
    }		
");
?>

<?php if ($isSettings && (!isset($isReadOnly) || !$isReadOnly)): ?>
<!-- Submit buttons for settings mode -->
<div class="form-group">
	<div class="row align-items-center">
		<div class="col-md-6">
			<?= Html::submitButton('<i class="fas fa-save mr-1"></i>' . Yii::t('app/company', $isCompactMode ? 'Save' : 'Save Settings'), [
                'class' => 'btn btn-success btn-block',
                'id' => 'save-settings-btn',
                'encode' => false,
                'title' => $isCompactMode ? Yii::t('app/company', 'Save Settings') : '',
                'data-toggle' => $isCompactMode ? 'tooltip' : ''
            ]) ?>
		</div>
		<div class="col-md-6 d-flex justify-content-end">
			<?= Html::button('<i class="fas fa-undo mr-1"></i>' . Yii::t('app/company', $isCompactMode ? '' : 'Reset to Defaults'), [
                'class' => 'btn btn-outline-warning mobile-hidden',
                'id' => 'reset-defaults-btn',
                'data-confirm' => Yii::t('app/company', 'Are you sure you want to reset all settings to default values?'),
                'encode' => false,
                'title' => $isCompactMode ? Yii::t('app/company', 'Reset to Defaults') : '',
                'data-toggle' => $isCompactMode ? 'tooltip' : ''
            ]) ?>
			<?= Html::a('<i class="fas fa-download mr-1"></i>' . Yii::t('app/company', $isCompactMode ? '' : 'Export Backup'), ['/company/backup'], [
                'class' => 'btn btn-outline-info ml-2 mobile-hidden',
                'target' => '_blank',
                'encode' => false,
                'title' => $isCompactMode ? Yii::t('app/company', 'Export Backup') : '',
                'data-toggle' => $isCompactMode ? 'tooltip' : ''
            ]) ?>
		</div>
	</div>
</div>
<?php endif; ?>

<?php
// Always register JavaScript for debugging
$deleteLogoUrl = \yii\helpers\Url::to(['/company/delete-logo']);
$csrfToken = Yii::$app->request->csrfToken;
// Use json_encode to safely pass strings to JavaScript
$confirmDeleteMsg = json_encode(Yii::t('app/company', 'Are you sure you want to delete the company logo?'));
$noLogoMsg = json_encode(Yii::t('app/company', 'No logo uploaded.'));
$successDeleteMsg = json_encode(Yii::t('app/company', 'Logo deleted successfully.'));
$failDeleteMsg = json_encode(Yii::t('app/company', 'Failed to delete logo.'));
$errorMsg = json_encode(Yii::t('app/company', 'An unexpected error occurred while deleting the logo.'));

// Debug: Check if we're in settings mode
echo "<!-- Debug: isSettings = " . ($isSettings ? 'true' : 'false') . " -->";
?>

<script>
document.addEventListener("DOMContentLoaded", function() {
	console.log("Company form JavaScript loaded (mode: <?= $mode ?>)");

	// --- Logo Upload Preview ---
	const uploadArea = document.getElementById("logo-upload-area");
	const fileInput = document.getElementById("logo-upload");
	const previewContainer = document.getElementById("logo-preview");
	const previewImg = document.getElementById("logo-preview-img");

	console.log("Logo upload elements found:");
	console.log("- Upload area:", uploadArea ? "Yes" : "No");
	console.log("- File input:", fileInput ? "Yes" : "No");
	console.log("- Preview container:", previewContainer ? "Yes" : "No");
	console.log("- Preview img:", previewImg ? "Yes" : "No");

	function handleFile(file) {
		console.log("Handling file:", file ? file.name : "No file");
		if (file) {
			// Validate file type
			const allowedTypes = ["image/png", "image/jpeg", "image/jpg", "image/gif"];
			if (!allowedTypes.includes(file.type)) {
				alert("Please select a valid image file (PNG, JPG, JPEG, or GIF)");
				return;
			}

			// Validate file size (2MB limit)
			if (file.size > 2 * 1024 * 1024) {
				alert("File size must be less than 2MB");
				return;
			}

			const reader = new FileReader();
			reader.onload = function(e) {
				console.log("File loaded, showing preview");
				if (previewImg && previewContainer) {
					previewImg.src = e.target.result;
					previewContainer.style.display = "block";

					// Update upload area text if it exists
					if (uploadArea) {
						const uploadText = uploadArea.querySelector(".upload-text");
						const uploadIcon = uploadArea.querySelector(".upload-icon");
						if (uploadText) {
							uploadText.innerHTML = "<strong>File selected:</strong> " + file.name;
						}
						if (uploadIcon) {
							uploadIcon.className = "fas fa-check-circle upload-icon";
							uploadIcon.style.color = "#10b981";
						}
					}
				}
			};
			reader.onerror = function() {
				console.error("Error reading file");
				alert("Error reading file. Please try again.");
			};
			reader.readAsDataURL(file);
		} else {
			console.log("No file, hiding preview");
			if (previewContainer) {
				previewContainer.style.display = "none";
			}
			// Reset upload area
			if (uploadArea) {
				const uploadText = uploadArea.querySelector(".upload-text");
				const uploadIcon = uploadArea.querySelector(".upload-icon");
				if (uploadText) {
					uploadText.innerHTML = "<strong>Click to upload</strong> or drag and drop";
				}
				if (uploadIcon) {
					uploadIcon.className = "fas fa-cloud-upload-alt upload-icon";
					uploadIcon.style.color = "#6b7280";
				}
			}
		}
	}

	// File input change event
	if (fileInput) {
		console.log("Adding change event to file input");
		fileInput.addEventListener("change", function(e) {
			console.log("File input changed, files:", e.target.files);
			if (e.target.files && e.target.files.length > 0) {
				handleFile(e.target.files[0]);
			} else {
				console.log("No files selected");
				handleFile(null);
			}
		});
	}

	// Label automatically handles clicks to the file input, no manual click handling needed
	if (uploadArea && fileInput) {
		console.log("Upload area (label) and file input ready");

		// Just add debugging to confirm clicks are working
		uploadArea.addEventListener("click", function(e) {
			console.log("Label clicked - browser will handle file input automatically");
		});

		// Drag and drop functionality
		["dragover", "dragleave", "drop"].forEach(eventName => {
			uploadArea.addEventListener(eventName, function(e) {
				e.preventDefault();
				e.stopPropagation();

				if (eventName === "dragover") {
					uploadArea.classList.add("dragover");
					console.log("Drag over");
				} else if (eventName === "dragleave" || eventName === "drop") {
					uploadArea.classList.remove("dragover");
					console.log("Drag leave/drop");
				}

				if (eventName === "drop") {
					const files = e.dataTransfer.files;
					console.log("Files dropped:", files.length);
					if (files.length > 0) {
						fileInput.files = files;
						handleFile(files[0]);
					}
				}
			});
		});
	}

	// --- Delete Logo ---
	const deleteLogoBtn = document.getElementById("delete-logo-btn");
	console.log("Delete logo button found:", deleteLogoBtn ? "Yes" : "No");

	if (deleteLogoBtn) {
		deleteLogoBtn.addEventListener("click", function(e) {
			console.log("Delete logo button clicked!");
			e.preventDefault();

			if (!confirm(<?= $confirmDeleteMsg ?>)) {
				return;
			}

			fetch("<?= $deleteLogoUrl ?>", {
					method: "POST",
					headers: {
						"Content-Type": "application/json",
						"X-Requested-With": "XMLHttpRequest",
						"X-CSRF-Token": "<?= $csrfToken ?>"
					}
				})
				.then(response => response.json())
				.then(data => {
					if (data.success) {
						alert(<?= $successDeleteMsg ?>);
						location.reload();
					} else {
						alert("Error: " + (data.message || <?= $failDeleteMsg ?>));
					}
				})
				.catch(error => {
					console.error("Fetch error:", error);
					alert(<?= $errorMsg ?>);
				});
		});
	}

	// SMTP2GO API Key Apply functionality
	const applySmtpKeyBtn = document.getElementById('apply-smtp-key-btn');
	const smtpKeyInput = document.getElementById('smtp2go-api-key-input');
	const testEmailBtn = document.getElementById('test-email-btn');

	// Function to update Test Email button state
	function updateTestEmailButton() {
		const hasApiKey = smtpKeyInput && smtpKeyInput.value.trim() !== '';

		if (testEmailBtn) {
			if (hasApiKey) {
				testEmailBtn.className = 'btn btn-outline-primary btn-sm';
				testEmailBtn.disabled = false;
				testEmailBtn.setAttribute('data-toggle', 'modal');
				testEmailBtn.setAttribute('data-target', '#test-email-modal');
				testEmailBtn.removeAttribute('title');
			} else {
				testEmailBtn.className = 'btn btn-outline-secondary btn-sm';
				testEmailBtn.disabled = true;
				testEmailBtn.removeAttribute('data-toggle');
				testEmailBtn.removeAttribute('data-target');
				testEmailBtn.setAttribute('title',
					'<?= Yii::t('app/company', 'Please configure SMTP2GO API Key first') ?>');
			}
		}
	}

	// Update Test Email button when SMTP key input changes
	if (smtpKeyInput) {
		smtpKeyInput.addEventListener('input', updateTestEmailButton);
		smtpKeyInput.addEventListener('change', updateTestEmailButton);
	}

	if (applySmtpKeyBtn && smtpKeyInput) {
		applySmtpKeyBtn.addEventListener('click', function() {
			const apiKey = smtpKeyInput.value.trim();

			if (!apiKey) {
				alert('<?= Yii::t('app/company', 'Please enter an API key') ?>');
				return;
			}

			// Show loading state
			const originalText = applySmtpKeyBtn.innerHTML;
			applySmtpKeyBtn.innerHTML =
				'<i class="fas fa-spinner fa-spin"></i> <?= Yii::t('app/company', 'Applying...') ?>';
			applySmtpKeyBtn.disabled = true;

			// Prepare form data
			const formData = new FormData();
			formData.append('Company[smtp2go_api_key]', apiKey);
			formData.append('<?= Yii::$app->request->csrfParam ?>',
				'<?= Yii::$app->request->csrfToken ?>');

			// Send AJAX request
			fetch('<?= \yii\helpers\Url::to(['company/update-settings']) ?>', {
					method: 'POST',
					body: formData
				})
				.then(response => response.json())
				.then(data => {
					if (data.success) {
						// Show success state
						applySmtpKeyBtn.innerHTML =
							'<i class="fas fa-check"></i> <?= Yii::t('app/company', 'Applied!') ?>';
						applySmtpKeyBtn.className = 'btn btn-success btn-sm';

						// Update Test Email button state
						updateTestEmailButton();

						// Show success message
						alert(
							'<?= Yii::t('app/company', 'SMTP2GO API Key applied successfully!') ?>'
						);

						// Reset button after 2 seconds
						setTimeout(() => {
							applySmtpKeyBtn.innerHTML = originalText;
							applySmtpKeyBtn.className = 'btn btn-outline-success btn-sm';
							applySmtpKeyBtn.disabled = false;
						}, 2000);
					} else {
						// Show error
						alert('<?= Yii::t('app/company', 'Error') ?>: ' + (data.message ||
							'<?= Yii::t('app/company', 'Failed to apply API key') ?>'));

						// Reset button
						applySmtpKeyBtn.innerHTML = originalText;
						applySmtpKeyBtn.disabled = false;
					}
				})
				.catch(error => {
					console.error('SMTP Key Apply Error:', error);
					alert('<?= Yii::t('app/company', 'Network error occurred') ?>');

					// Reset button
					applySmtpKeyBtn.innerHTML = originalText;
					applySmtpKeyBtn.disabled = false;
				});
		});
	}

	// Subuser Management
	const subuserModal = document.getElementById('subuser-modal');
	const subuserForm = document.getElementById('subuser-form');
	const subuserModalTitle = document.getElementById('subuser-modal-title');
	const saveSubuserBtn = document.getElementById('save-subuser-btn');
	const passwordFields = document.getElementById('password-fields');

	// Add Subuser button
	const addSubuserBtn = document.getElementById('add-subuser-btn');
	if (addSubuserBtn) {
		addSubuserBtn.addEventListener('click', function() {
			resetSubuserModal();
			subuserModalTitle.textContent = '<?= Yii::t('app/company', 'Add Subuser') ?>';
			saveSubuserBtn.textContent = '<?= Yii::t('app/company', 'Create Subuser') ?>';
			if (passwordFields) {
				passwordFields.style.display = 'block';
				// Make password required for new subuser
				document.getElementById('subuser-password').required = true;
			}
		});
	}

	// Edit Subuser buttons
	const editSubuserBtns = document.querySelectorAll('.edit-subuser-btn');
	editSubuserBtns.forEach(btn => {
		btn.addEventListener('click', function() {
			const subuserId = this.getAttribute('data-subuser-id');
			loadSubuserData(subuserId);
		});
	});

	// Delete Subusr buttons
	const deleteSubuserBtns = document.querySelectorAll('.delete-subuser-btn');
	deleteSubuserBtns.forEach(btn => {
		btn.addEventListener('click', function() {
			const subuserId = this.getAttribute('data-subuser-id');
			const subuserName = this.getAttribute('data-subuser-name');
			deleteSubuser(subuserId, subuserName);
		});
	});

	// Subuser Form submission
	if (subuserForm) {
		subuserForm.addEventListener('submit', function(e) {
			e.preventDefault();
			saveSubuser();
		});
	}

	function resetSubuserModal() {
		subuserForm.reset();
		document.getElementById('subuser-id').value = '';
		const resultDiv = document.getElementById('subuser-result');
		if (resultDiv) {
			resultDiv.style.display = 'none';
		}
		// Reset password field required attribute
		const passwordField = document.getElementById('subuser-password');
		if (passwordField) {
			passwordField.required = false;
		}
	}

	function loadSubuserData(subuserId) {
		// Show loading
		subuserModalTitle.textContent = '<?= Yii::t('app/company', 'Loading...') ?>';

		fetch('<?= \yii\helpers\Url::to(['company/get-subuser']) ?>?id=' + subuserId, {
				method: 'GET',
				headers: {
					'X-Requested-With': 'XMLHttpRequest'
				}
			})
			.then(response => response.json())
			.then(data => {
				if (data.success) {
					// Populate form
					document.getElementById('subuser-id').value = data.subuser.id;
					document.getElementById('subuser-full-name').value = data.subuser.full_name || '';
					document.getElementById('subuser-username').value = data.subuser.username || '';
					document.getElementById('subuser-email').value = data.subuser.email || '';
					document.getElementById('subuser-company').value = data.subuser.company_id || '';

					// Update modal
					subuserModalTitle.textContent = '<?= Yii::t('app/company', 'Edit Subuser') ?>';
					saveSubuserBtn.textContent = '<?= Yii::t('app/company', 'Update Subuser') ?>';

					// Hide password field for editing and remove required attribute
					if (passwordFields) {
						passwordFields.style.display = 'none';
						document.getElementById('subuser-password').required = false;
					}

					// Show modal
					$(subuserModal).modal('show');
				} else {
					alert('<?= Yii::t('app/company', 'Error loading subuser data') ?>');
				}
			})
			.catch(error => {
				console.error('Error:', error);
				alert('<?= Yii::t('app/company', 'Network error occurred') ?>');
			});
	}

	function saveSubuser() {
		const formData = new FormData(subuserForm);
		formData.append('<?= Yii::$app->request->csrfParam ?>', '<?= Yii::$app->request->csrfToken ?>');

		// Show loading
		const originalText = saveSubuserBtn.textContent;
		saveSubuserBtn.innerHTML =
			'<i class="fas fa-spinner fa-spin"></i> <?= Yii::t('app/company', 'Saving...') ?>';
		saveSubuserBtn.disabled = true;

		const isEdit = document.getElementById('subuser-id').value !== '';
		const url = isEdit ? '<?= \yii\helpers\Url::to(['company/update-subuser']) ?>' :
			'<?= \yii\helpers\Url::to(['company/create-subuser']) ?>';

		fetch(url, {
				method: 'POST',
				body: formData,
				headers: {
					'X-Requested-With': 'XMLHttpRequest'
				}
			})
			.then(response => response.json())
			.then(data => {
				const resultDiv = document.getElementById('subuser-result');
				resultDiv.style.display = 'block';

				if (data.success) {
					resultDiv.className = 'alert alert-success';
					resultDiv.innerHTML = '<i class="fas fa-check-circle mr-2"></i>' + data.message;

					// Close modal and reload page after success
					setTimeout(() => {
						$(subuserModal).modal('hide');
						location.reload();
					}, 1500);
				} else {
					resultDiv.className = 'alert alert-danger';
					resultDiv.innerHTML = '<i class="fas fa-exclamation-circle mr-2"></i>' + data.message;
					if (data.errors) {
						resultDiv.innerHTML += '<br><small>' + Object.values(data.errors).join('<br>') +
							'</small>';
					}
				}

				// Reset button
				saveSubuserBtn.innerHTML = originalText;
				saveSubuserBtn.disabled = false;
			})
			.catch(error => {
				console.error('Error:', error);

				const resultDiv = document.getElementById('subuser-result');
				resultDiv.style.display = 'block';
				resultDiv.className = 'alert alert-danger';
				resultDiv.innerHTML =
					'<i class="fas fa-exclamation-circle mr-2"></i><?= Yii::t('app/company', 'Network error occurred') ?>';

				// Reset button
				saveSubuserBtn.innerHTML = originalText;
				saveSubuserBtn.disabled = false;
			});
	}

	function deleteSubuser(subuserId, subuserName) {
		if (!confirm('<?= Yii::t('app/company', 'Are you sure you want to delete') ?> "' + subuserName +
				'"?')) {
			return;
		}

		const formData = new FormData();
		formData.append('id', subuserId);
		formData.append('<?= Yii::$app->request->csrfParam ?>', '<?= Yii::$app->request->csrfToken ?>');

		fetch('<?= \yii\helpers\Url::to(['company/delete-subuser']) ?>', {
				method: 'POST',
				body: formData,
				headers: {
					'X-Requested-With': 'XMLHttpRequest'
				}
			})
			.then(response => response.json())
			.then(data => {
				if (data.success) {
					alert(data.message);
					location.reload();
				} else {
					alert('<?= Yii::t('app/company', 'Error') ?>: ' + data.message);
				}
			})
			.catch(error => {
				console.error('Error:', error);
				alert('<?= Yii::t('app/company', 'Network error occurred') ?>');
			});
	}

	// Company access checkbox handling
	const companyAccessCheckboxes = document.querySelectorAll('.company-access-checkbox');
	companyAccessCheckboxes.forEach(checkbox => {
		checkbox.addEventListener('change', function() {
			const subuserId = this.getAttribute('data-subuser-id');
			const companyId = this.getAttribute('data-company-id');
			const isGranting = this.checked;

			// Disable checkbox during request
			this.disabled = true;

			const url = isGranting ?
				'<?= \yii\helpers\Url::to(['company/grant-company-access']) ?>' :
				'<?= \yii\helpers\Url::to(['company/revoke-company-access']) ?>';

			const formData = new FormData();
			formData.append('subuser_id', subuserId);
			formData.append('company_id', companyId);
			formData.append('<?= Yii::$app->request->csrfParam ?>',
				'<?= Yii::$app->request->csrfToken ?>');

			fetch(url, {
					method: 'POST',
					body: formData,
					headers: {
						'X-Requested-With': 'XMLHttpRequest'
					}
				})
				.then(response => response.json())
				.then(data => {
					if (data.success) {
						// Show success message briefly
						const label = this.nextElementSibling;
						const originalText = label.textContent;
						label.innerHTML = isGranting ?
							'<span class="text-success">' + originalText + ' ✓</span>' :
							'<span class="text-danger">' + originalText + ' ✗</span>';

						setTimeout(() => {
							label.textContent = originalText;
						}, 2000);
					} else {
						// Revert checkbox state on error
						this.checked = !isGranting;
						alert('<?= Yii::t('app/company', 'Error') ?>: ' + data.message);
					}

					// Re-enable checkbox
					this.disabled = false;
				})
				.catch(error => {
					console.error('Error:', error);
					// Revert checkbox state on error
					this.checked = !isGranting;
					this.disabled = false;
					alert('<?= Yii::t('app/company', 'Network error occurred') ?>');
				});
		});
	});
});
</script>

<?php
// Add Test Email Modal for settings mode
if ($isSettings) {
    echo '

<!-- Test Email Modal -->
<div class="modal fade" id="test-email-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">' . Yii::t('app/company', 'Test Email Configuration') . '</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="test-email-form">
                    <div class="form-group">
                        <label for="test-email">' . Yii::t('app/company', 'Email Address') . '</label>
                        <input type="email" class="form-control" id="test-email" required
                            placeholder="' . Yii::t('app/company', 'Enter email to send test message') . '">
                    </div>
                    <div id="test-email-result" class="mt-3" style="display: none;"></div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                    data-dismiss="modal">' . Yii::t('app', 'Close') . '</button>
                <button type="button" class="btn btn-primary"
                    id="send-test-email">' . Yii::t('app/company', 'Send Test Email') . '</button>
            </div>
        </div>
    </div>
</div>';
}

// Add Subuser Modal for settings mode
if ($isSettings && Yii::$app->user->identity->canManageSubusers()) {
    echo '

<!-- Subuser Modal -->
<div class="modal fade" id="subuser-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="subuser-modal-title">' . Yii::t('app/company', 'Add Subuser') . '</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="subuser-form">
                <div class="modal-body">
                    <input type="hidden" id="subuser-id" name="subuser_id">
                    
                    <div class="form-group">
                        <label for="subuser-full-name">' . Yii::t('app/company', 'Full Name') . ' <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="subuser-full-name" name="full_name" required
                            placeholder="' . Yii::t('app/company', 'Enter full name') . '">
                    </div>
                    
                    <div class="form-group">
                        <label for="subuser-username">' . Yii::t('app/company', 'Username') . ' <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="subuser-username" name="username" required
                            placeholder="' . Yii::t('app/company', 'Enter username') . '">
                    </div>
                    
                    <div class="form-group">
                        <label for="subuser-email">' . Yii::t('app/company', 'Email') . ' <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="subuser-email" name="email" required
                            placeholder="' . Yii::t('app/company', 'Enter email address') . '">
                    </div>
                    
                    <div class="form-group" id="password-fields">
                        <label for="subuser-password">' . Yii::t('app/company', 'Password') . ' <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="subuser-password" name="password"
                            placeholder="' . Yii::t('app/company', 'Enter password') . '">
                    </div>
                    
                    <div class="form-group">
                        <label for="subuser-company">' . Yii::t('app/company', 'Default Company') . '</label>
                        <select class="form-control" id="subuser-company" name="company_id">';
                        
    $companies = Yii::$app->user->identity->getCompanies()->where(['is_active' => true])->all();
    echo '<option value="">' . Yii::t('app/company', 'Select a company') . '</option>';
    foreach ($companies as $company) {
        echo '<option value="' . $company->id . '">' . Html::encode($company->company_name) . '</option>';
    }
    
    echo '          </select>
                        <small class="form-text text-muted">' . Yii::t('app/company', 'Subuser will have access to all your companies') . '</small>
                    </div>
                    
                    <div id="subuser-result" class="mt-3" style="display: none;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">' . Yii::t('app', 'Cancel') . '</button>
                    <button type="submit" class="btn btn-primary" id="save-subuser-btn">' . Yii::t('app/company', 'Create Subuser') . '</button>
                </div>
            </form>
        </div>
    </div>
</div>';
}
?>