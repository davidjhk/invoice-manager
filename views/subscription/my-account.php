<?php

/** @var yii\web\View $this */
/** @var app\models\User $user */
/** @var app\models\UserSubscription|null $subscription */
/** @var app\models\Plan[] $availablePlans */

use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;

$this->title = Yii::t('app', 'My Account');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="my-account">
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-user-circle"></i> <?= Yii::t('app', 'Account Information') ?></h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><?= Yii::t('app', 'Full Name') ?></label>
                                <p class="form-control-plaintext"><?= Html::encode($user->getDisplayName()) ?></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><?= Yii::t('app', 'Email Address') ?></label>
                                <p class="form-control-plaintext"><?= Html::encode($user->email) ?></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><?= Yii::t('app', 'Username') ?></label>
                                <p class="form-control-plaintext"><?= Html::encode($user->username) ?></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><?= Yii::t('app', 'Account Status') ?></label>
                                <p class="form-control-plaintext">
                                    <span class="badge badge-<?= $user->is_active ? 'success' : 'secondary' ?>">
                                        <?= $user->getStatusLabel() ?>
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Subscription Information -->
            <div class="card mt-4">
                <div class="card-header">
                    <h4><i class="fas fa-credit-card"></i> <?= Yii::t('app', 'Subscription') ?></h4>
                </div>
                <div class="card-body">
                    <?php if ($subscription && $subscription->isActive()): ?>
                        <div class="subscription-active">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><?= Yii::t('app', 'Current Plan') ?></label>
                                        <p class="form-control-plaintext">
                                            <strong><?= Html::encode($subscription->plan->name) ?></strong>
                                            <span class="text-success ml-2"><?= $subscription->plan->getFormattedPrice() ?>/month</span>
                                        </p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><?= Yii::t('app', 'Status') ?></label>
                                        <p class="form-control-plaintext">
                                            <span class="badge badge-success"><?= $subscription->getStatusLabel() ?></span>
                                        </p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><?= Yii::t('app', 'Next Billing Date') ?></label>
                                        <p class="form-control-plaintext">
                                            <?= $subscription->getFormattedNextBillingDate() ?: Yii::t('app', 'N/A') ?>
                                        </p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><?= Yii::t('app', 'Payment Method') ?></label>
                                        <p class="form-control-plaintext"><?= $subscription->getPaymentMethodLabel() ?></p>
                                    </div>
                                </div>
                            </div>

                            <!-- Scheduled Change Notice -->
                            <?php if ($subscription->scheduled_plan_id): ?>
                                <div class="alert alert-info mt-3">
                                    <h6><i class="fas fa-calendar-alt mr-2"></i><?= Yii::t('app', 'Scheduled Plan Change') ?></h6>
                                    <p class="mb-2">
                                        <?= Yii::t('app', 'Your plan will change to {planName} on {date}.', [
                                            'planName' => Html::encode($subscription->scheduledPlan->name),
                                            'date' => Yii::$app->formatter->asDate($subscription->scheduled_change_date)
                                        ]) ?>
                                    </p>
                                    <?= Html::a(
                                        '<i class="fas fa-times mr-1"></i>' . Yii::t('app', 'Cancel Scheduled Change'),
                                        ['/subscription/cancel-scheduled-change'],
                                        [
                                            'class' => 'btn btn-sm btn-outline-secondary',
                                            'data' => [
                                                'confirm' => Yii::t('app', 'Are you sure you want to cancel the scheduled plan change?'),
                                                'method' => 'post',
                                            ],
                                        ]
                                    ) ?>
                                </div>
                            <?php endif; ?>

                            <div class="subscription-actions mt-3">
                                <div class="btn-group" role="group">
                                    <?php if ($subscription->canBeCancelled()): ?>
                                        <?= Html::a(
                                            '<i class="fas fa-times"></i> ' . Yii::t('app', 'Cancel Subscription'),
                                            ['/subscription/cancel'],
                                            [
                                                'class' => 'btn btn-outline-danger',
                                                'data' => [
                                                    'confirm' => Yii::t('app', 'Are you sure you want to cancel your subscription?'),
                                                    'method' => 'post',
                                                ],
                                            ]
                                        ) ?>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <?php if (!empty($availablePlans)): ?>
                                <div class="plan-changes mt-4">
                                    <h5><i class="fas fa-exchange-alt mr-2"></i><?= Yii::t('app', 'Change Plan') ?></h5>
                                    
                                    <?php 
                                    $upgradePlans = [];
                                    $downgradePlans = [];
                                    foreach ($availablePlans as $plan) {
                                        if ($plan->price > $subscription->plan->price) {
                                            $upgradePlans[] = $plan;
                                        } elseif ($plan->price < $subscription->plan->price) {
                                            $downgradePlans[] = $plan;
                                        }
                                    }
                                    ?>
                                    
                                    <?php if (!empty($upgradePlans)): ?>
                                        <div class="upgrade-section mb-3">
                                            <h6 class="text-primary"><i class="fas fa-arrow-up mr-1"></i><?= Yii::t('app', 'Upgrade Plans') ?></h6>
                                            <div class="row">
                                                <?php foreach ($upgradePlans as $plan): ?>
                                                    <div class="col-md-6 mb-2">
                                                        <div class="plan-upgrade-card">
                                                            <div class="d-flex justify-content-between align-items-center">
                                                                <div>
                                                                    <h6 class="mb-1"><?= Html::encode($plan->name) ?></h6>
                                                                    <p class="text-primary mb-0"><?= $plan->getFormattedPrice() ?>/month</p>
                                                                </div>
                                                                <div>
                                                                    <?= Html::a(
                                                                        '<i class="fas fa-arrow-up mr-1"></i>' . Yii::t('app', 'Upgrade'),
                                                                        ['/subscription/upgrade', 'planId' => $plan->id],
                                                                        ['class' => 'btn btn-sm btn-primary']
                                                                    ) ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($downgradePlans)): ?>
                                        <div class="downgrade-section">
                                            <h6 class="text-warning"><i class="fas fa-arrow-down mr-1"></i><?= Yii::t('app', 'Downgrade Plans') ?></h6>
                                            <div class="row">
                                                <?php foreach ($downgradePlans as $plan): ?>
                                                    <div class="col-md-6 mb-2">
                                                        <div class="plan-downgrade-card">
                                                            <div class="d-flex justify-content-between align-items-center">
                                                                <div>
                                                                    <h6 class="mb-1"><?= Html::encode($plan->name) ?></h6>
                                                                    <p class="text-success mb-0"><?= $plan->getFormattedPrice() ?>/month</p>
                                                                    <small class="text-muted">
                                                                        <?= Yii::t('app', 'Save ${amount}/month', ['amount' => number_format($subscription->plan->price - $plan->price, 2)]) ?>
                                                                    </small>
                                                                </div>
                                                                <div>
                                                                    <?= Html::a(
                                                                        '<i class="fas fa-arrow-down mr-1"></i>' . Yii::t('app', 'Downgrade'),
                                                                        ['/subscription/downgrade', 'planId' => $plan->id],
                                                                        ['class' => 'btn btn-sm btn-warning']
                                                                    ) ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (empty($upgradePlans) && empty($downgradePlans)): ?>
                                        <p class="text-muted"><?= Yii::t('app', 'No other plans available for changes.') ?></p>
                                    <?php endif; ?>
                                </div>

                            <?php else: ?>
                                <div class="mt-4">
                                    <div class="row">
                                        <?php foreach ($availablePlans as $plan): ?>
                                            <?php if ($plan->price > $subscription->plan->price): ?>
                                                <div class="col-md-6 mb-3">
                                                    <div class="card border-primary">
                                                        <div class="card-body text-center">
                                                            <h5 class="card-title"><?= Html::encode($plan->name) ?></h5>
                                                            <p class="card-text"><?= Html::encode($plan->description) ?></p>
                                                            <p class="text-primary font-weight-bold"><?= $plan->getFormattedPrice() ?>/month</p>
                                                            <?= Html::a(
                                                                Yii::t('app', 'Upgrade'),
                                                                ['/subscription/upgrade', 'planId' => $plan->id],
                                                                ['class' => 'btn btn-primary btn-sm']
                                                            ) ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="subscription-inactive">
                            <div class="text-center py-4">
                                <i class="fas fa-credit-card fa-3x text-muted mb-3"></i>
                                <h5><?= Yii::t('app', 'No Active Subscription') ?></h5>
                                <p class="text-muted"><?= Yii::t('app', 'You are currently using the free tier. Upgrade to unlock premium features.') ?></p>
                                <?= Html::a(
                                    '<i class="fas fa-arrow-up"></i> ' . Yii::t('app', 'Choose a Plan'),
                                    ['/subscription/index'],
                                    ['class' => 'btn btn-primary']
                                ) ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Account Actions -->
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-cogs"></i> <?= Yii::t('app', 'Account Actions') ?></h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <?= Html::a(
                            '<i class="fas fa-key"></i> ' . Yii::t('app', 'Change Password'),
                            ['/site/change-password'],
                            ['class' => 'list-group-item list-group-item-action']
                        ) ?>
                        <?= Html::a(
                            '<i class="fas fa-building"></i> ' . Yii::t('app', 'Company Settings'),
                            ['/company/settings'],
                            ['class' => 'list-group-item list-group-item-action']
                        ) ?>
                        <?= Html::a(
                            '<i class="fas fa-file-invoice"></i> ' . Yii::t('app', 'Invoices'),
                            ['/invoice/index'],
                            ['class' => 'list-group-item list-group-item-action']
                        ) ?>
                        <?= Html::a(
                            '<i class="fas fa-file-contract"></i> ' . Yii::t('app', 'Estimates'),
                            ['/estimate/index'],
                            ['class' => 'list-group-item list-group-item-action']
                        ) ?>
                    </div>
                </div>
            </div>

            <!-- Usage Summary -->
            <?php if ($subscription && $subscription->isActive()): ?>
                <div class="card mt-4">
                    <div class="card-header">
                        <h5><i class="fas fa-chart-bar"></i> <?= Yii::t('app', 'Usage Summary') ?></h5>
                    </div>
                    <div class="card-body">
                        <?php
                        $features = $subscription->plan->getFeaturesArray();
                        $userCompanyCount = $user->getCompanyCount();
                        ?>
                        <div class="usage-item mb-3">
                            <div class="d-flex justify-content-between">
                                <span><?= Yii::t('app', 'Companies') ?></span>
                                <span class="badge badge-info"><?= $userCompanyCount ?> / <?= $features['users'] ?? 'Unlimited' ?></span>
                            </div>
                        </div>
                        <div class="usage-item mb-3">
                            <div class="d-flex justify-content-between">
                                <span><?= Yii::t('app', 'Storage') ?></span>
                                <span class="badge badge-info"><?= $features['storage'] ?? 'N/A' ?></span>
                            </div>
                        </div>
                        <div class="usage-item mb-3">
                            <div class="d-flex justify-content-between">
                                <span><?= Yii::t('app', 'Support') ?></span>
                                <span class="badge badge-info"><?= $features['support'] ?? 'Basic' ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
/* Plan change cards */
.plan-upgrade-card,
.plan-downgrade-card {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 0.5rem;
    padding: 1rem;
    transition: all 0.3s ease;
}

.plan-upgrade-card:hover {
    border-color: #4f46e5;
    background: rgba(79, 70, 229, 0.05);
}

.plan-downgrade-card:hover {
    border-color: #f59e0b;
    background: rgba(245, 158, 11, 0.05);
}

.upgrade-section h6,
.downgrade-section h6 {
    font-weight: 600;
    margin-bottom: 1rem;
}

.plan-changes {
    border-top: 1px solid #e2e8f0;
    padding-top: 1.5rem;
}
</style>