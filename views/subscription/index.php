<?php

/** @var yii\web\View $this */
/** @var app\models\Plan[] $plans */
/** @var app\models\UserSubscription|null $currentSubscription */

use yii\helpers\Html;

$this->title = Yii::t('app', 'Subscription Plans');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="subscription-plans">
	<div class="text-center mb-5">
		<h1 class="display-4"><?= Yii::t('app', 'Choose Your Plan') ?></h1>
		<p class="lead text-muted"><?= Yii::t('app', 'Select the perfect plan for your business needs') ?></p>
	</div>

	<?php if ($currentSubscription && $currentSubscription->isActive()): ?>
	<div class="alert alert-info" role="alert">
		<i class="fas fa-info-circle"></i>
		<?= Yii::t('app', 'You are currently subscribed to the {planName} plan.', [
                'planName' => Html::encode($currentSubscription->plan->name)
            ]) ?>
		<?= Html::a(Yii::t('app', 'Manage subscription'), ['/subscription/my-account'], ['class' => 'alert-link']) ?>
	</div>
	<?php endif; ?>

	<div class="row justify-content-center">
		<!-- Paid Plans -->
		<?php foreach ($plans as $plan): ?>
		<div class="col-lg-4 col-md-6 mb-4">
			<div
				class="card plan-card h-100 <?= ($currentSubscription && $currentSubscription->plan_id == $plan->id) ? 'border-primary' : '' ?>">
				<div
					class="card-header text-center <?= $plan->name === 'Pro' ? 'bg-primary text-white' : 'bg-light' ?>">
					<h4 class="my-0 font-weight-normal"><?= Html::encode($plan->name) ?></h4>
					<?php if ($plan->name === 'Pro'): ?>
					<small class="badge badge-light"><?= Yii::t('app', 'Most Popular') ?></small>
					<?php endif; ?>
				</div>
				<div class="card-body text-center">
					<h1 class="card-title pricing-card-title">
						<?= $plan->getFormattedPrice() ?> <small class="text-muted">/month</small>
					</h1>
					<p class="text-muted"><?= Html::encode($plan->description) ?></p>

					<?php
                        $features = $plan->getFeaturesArray();
                        ?>
					<ul class="list-unstyled mt-3 mb-4">
						<?php if (isset($features['invoices'])): ?>
						<li><i class="fas fa-check text-success"></i>
							<?= Yii::t('app', '{count} invoices', ['count' => $features['invoices']]) ?></li>
						<?php endif; ?>
						<?php if (isset($features['customers'])): ?>
						<li><i class="fas fa-check text-success"></i>
							<?= Yii::t('app', '{count} customers', ['count' => $features['customers']]) ?></li>
						<?php endif; ?>
						<?php if (isset($features['users'])): ?>
						<li><i class="fas fa-check text-success"></i>
							<?= Yii::t('app', 'Up to {count} companies', ['count' => $features['users']]) ?></li>
						<?php endif; ?>
						<?php if (isset($features['storage'])): ?>
						<li><i class="fas fa-check text-success"></i>
							<?= Yii::t('app', '{size} storage', ['size' => $features['storage']]) ?></li>
						<?php endif; ?>
						<?php if (isset($features['support'])): ?>
						<li><i class="fas fa-check text-success"></i> <?= Html::encode($features['support']) ?></li>
						<?php endif; ?>
						<?php if (isset($features['api_access'])): ?>
						<li><i class="fas fa-check text-success"></i> <?= Yii::t('app', 'API access') ?></li>
						<?php endif; ?>
						<?php if (isset($features['custom_templates'])): ?>
						<li><i class="fas fa-check text-success"></i> <?= Yii::t('app', 'Custom templates') ?></li>
						<?php endif; ?>
					</ul>

					<?php if ($currentSubscription && $currentSubscription->plan_id == $plan->id && $currentSubscription->isActive()): ?>
					<span class="badge badge-primary"><?= Yii::t('app', 'Current Plan') ?></span>
					<?php elseif ($currentSubscription && $currentSubscription->isActive() && $plan->price > $currentSubscription->plan->price): ?>
					<?= Html::a(
                                Yii::t('app', 'Upgrade'),
                                ['/subscription/upgrade', 'planId' => $plan->id],
                                ['class' => 'btn btn-primary']
                            ) ?>
					<?php elseif (!$currentSubscription || !$currentSubscription->isActive()): ?>
					<?= Html::a(
                                '<i class="fas fa-credit-card"></i> ' . Yii::t('app', 'Subscribe with Credit Card'),
                                ['/subscription/subscribe', 'planId' => $plan->id, 'paymentMethod' => 'stripe'],
                                ['class' => 'btn btn-primary btn-block']
                            ) ?>
					<?php else: ?>
					<span class="text-muted"><?= Yii::t('app', 'Contact support for plan changes') ?></span>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php endforeach; ?>
	</div>

</div>

<style>
.plan-card {
	border-radius: 15px;
	box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
	transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.plan-card:hover {
	transform: translateY(-5px);
	box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.pricing-card-title {
	font-size: 2.5rem;
}

.plan-card .list-unstyled li {
	padding: 0.5rem 0;
}

.btn-group-vertical .btn {
	border-radius: 0.375rem;
}

.accordion .card {
	border: 1px solid #e3e6f0;
	margin-bottom: 1rem;
}

.accordion .card-header {
	background-color: #f8f9fc;
	border-bottom: 1px solid #e3e6f0;
}

.accordion .btn-link {
	color: #5a5c69;
	text-decoration: none;
	font-weight: 600;
}

.accordion .btn-link:hover {
	color: #3a3b45;
}
</style>