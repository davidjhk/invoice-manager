<?php

/* @var $this yii\web\View */
/* @var $currentSubscription app\models\UserSubscription */
/* @var $newPlan app\models\Plan */
/* @var $upgradeData array */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = Yii::t('app', 'Upgrade to {planName}', ['planName' => $newPlan->name]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'My Account'), 'url' => ['my-account']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="upgrade-preview">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">
                            <i class="fas fa-arrow-up mr-2"></i>
                            <?= Html::encode($this->title) ?>
                        </h4>
                    </div>
                    <div class="card-body">
                        <!-- Current vs New Plan Comparison -->
                        <div class="plan-comparison mb-4">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="current-plan">
                                        <h5 class="text-muted">
                                            <i class="fas fa-circle mr-2"></i>
                                            <?= Yii::t('app', 'Current Plan') ?>
                                        </h5>
                                        <div class="plan-card">
                                            <h4><?= Html::encode($currentSubscription->plan->name) ?></h4>
                                            <p class="plan-price"><?= $currentSubscription->plan->getFormattedPrice() ?>/month</p>
                                            <p class="text-muted"><?= Html::encode($currentSubscription->plan->description) ?></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="new-plan">
                                        <h5 class="text-primary">
                                            <i class="fas fa-arrow-right mr-2"></i>
                                            <?= Yii::t('app', 'Upgrading to') ?>
                                        </h5>
                                        <div class="plan-card highlighted">
                                            <h4><?= Html::encode($newPlan->name) ?></h4>
                                            <p class="plan-price text-primary"><?= $newPlan->getFormattedPrice() ?>/month</p>
                                            <p class="text-muted"><?= Html::encode($newPlan->description) ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Billing Summary -->
                        <div class="billing-summary mb-4">
                            <h5><i class="fas fa-calculator mr-2"></i><?= Yii::t('app', 'Billing Summary') ?></h5>
                            <div class="card bg-light">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <h6><?= Yii::t('app', 'Prorated Upgrade Calculation') ?></h6>
                                            <ul class="list-unstyled mb-0">
                                                <li class="d-flex justify-content-between">
                                                    <span><?= Yii::t('app', 'Days used in current period') ?>:</span>
                                                    <span><?= $upgradeData['used_days'] ?> <?= Yii::t('app', 'days') ?></span>
                                                </li>
                                                <li class="d-flex justify-content-between">
                                                    <span><?= Yii::t('app', 'Days remaining in current period') ?>:</span>
                                                    <span><?= $upgradeData['remaining_days'] ?> <?= Yii::t('app', 'days') ?></span>
                                                </li>
                                                <li class="d-flex justify-content-between">
                                                    <span><?= Yii::t('app', 'Credit from unused time') ?>:</span>
                                                    <span class="text-success">-$<?= number_format($upgradeData['credit_applied'], 2) ?></span>
                                                </li>
                                                <li class="d-flex justify-content-between">
                                                    <span><?= Yii::t('app', 'New plan monthly cost') ?>:</span>
                                                    <span>$<?= number_format($upgradeData['new_plan_cost'], 2) ?></span>
                                                </li>
                                                <hr>
                                                <li class="d-flex justify-content-between font-weight-bold">
                                                    <span><?= Yii::t('app', 'Upgrade charge today') ?>:</span>
                                                    <span class="text-primary">$<?= number_format($upgradeData['upgrade_charge'], 2) ?></span>
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="col-md-4 text-center">
                                            <div class="upgrade-amount">
                                                <h3 class="text-primary">$<?= number_format($upgradeData['upgrade_charge'], 2) ?></h3>
                                                <p class="text-muted"><?= Yii::t('app', 'Due today') ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Important Information -->
                        <div class="upgrade-info mb-4">
                            <div class="alert alert-info">
                                <h6><i class="fas fa-info-circle mr-2"></i><?= Yii::t('app', 'Important Information') ?></h6>
                                <ul class="mb-0">
                                    <li><?= Yii::t('app', 'Your new billing cycle will start today with the upgraded plan.') ?></li>
                                    <li><?= Yii::t('app', 'You will receive a credit for the unused time on your current plan.') ?></li>
                                    <li><?= Yii::t('app', 'Future billing will be at the new plan rate of {price}/month.', ['price' => $newPlan->getFormattedPrice()]) ?></li>
                                    <li><?= Yii::t('app', 'You can downgrade or cancel at any time from your account settings.') ?></li>
                                </ul>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="upgrade-actions text-center">
                            <?= Html::beginForm(['confirm-upgrade', 'planId' => $newPlan->id], 'post', ['class' => 'd-inline']) ?>
                                <?= Html::submitButton(
                                    '<i class="fas fa-arrow-up mr-2"></i>' . Yii::t('app', 'Confirm Upgrade'),
                                    ['class' => 'btn btn-primary btn-lg mr-3']
                                ) ?>
                            <?= Html::endForm() ?>
                            
                            <?= Html::a(
                                '<i class="fas fa-times mr-2"></i>' . Yii::t('app', 'Cancel'),
                                ['my-account'],
                                ['class' => 'btn btn-secondary btn-lg']
                            ) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.upgrade-preview {
    padding: 2rem 0;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
}

.card {
    border: none;
    border-radius: 1rem;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

.card-header {
    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
    color: white;
    border-radius: 1rem 1rem 0 0 !important;
    padding: 1.5rem;
}

.plan-card {
    background: #f8fafc;
    border: 2px solid #e2e8f0;
    border-radius: 0.5rem;
    padding: 1.5rem;
    text-align: center;
}

.plan-card.highlighted {
    border-color: #4f46e5;
    background: linear-gradient(135deg, rgba(79, 70, 229, 0.1) 0%, rgba(124, 58, 237, 0.1) 100%);
}

.plan-price {
    font-size: 1.5rem;
    font-weight: 700;
    margin: 0.5rem 0;
}

.billing-summary .card {
    box-shadow: none;
    border: 1px solid #dee2e6;
}

.upgrade-amount {
    background: rgba(79, 70, 229, 0.1);
    border-radius: 0.5rem;
    padding: 1rem;
}

.btn-primary {
    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
    border: none;
    border-radius: 0.5rem;
    padding: 0.75rem 2rem;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(79, 70, 229, 0.3);
}

.btn-secondary {
    border: 2px solid #6c757d;
    border-radius: 0.5rem;
    padding: 0.75rem 2rem;
    font-weight: 600;
}

@media (max-width: 768px) {
    .upgrade-preview {
        padding: 1rem 0;
    }
    
    .upgrade-actions .btn {
        display: block;
        width: 100%;
        margin-bottom: 1rem;
    }
}
</style>