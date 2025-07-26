<?php

/* @var $this yii\web\View */
/* @var $currentSubscription app\models\UserSubscription */
/* @var $newPlan app\models\Plan */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = Yii::t('app', 'Downgrade to {planName}', ['planName' => $newPlan->name]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'My Account'), 'url' => ['my-account']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="downgrade-preview">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">
                            <i class="fas fa-arrow-down mr-2"></i>
                            <?= Html::encode($this->title) ?>
                        </h4>
                    </div>
                    <div class="card-body">
                        <!-- Current vs New Plan Comparison -->
                        <div class="plan-comparison mb-4">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="current-plan">
                                        <h5 class="text-primary">
                                            <i class="fas fa-circle mr-2"></i>
                                            <?= Yii::t('app', 'Current Plan') ?>
                                        </h5>
                                        <div class="plan-card current">
                                            <h4><?= Html::encode($currentSubscription->plan->name) ?></h4>
                                            <p class="plan-price"><?= $currentSubscription->plan->getFormattedPrice() ?>/month</p>
                                            <p class="text-muted"><?= Html::encode($currentSubscription->plan->description) ?></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="new-plan">
                                        <h5 class="text-warning">
                                            <i class="fas fa-arrow-down mr-2"></i>
                                            <?= Yii::t('app', 'Downgrading to') ?>
                                        </h5>
                                        <div class="plan-card">
                                            <h4><?= Html::encode($newPlan->name) ?></h4>
                                            <p class="plan-price text-warning"><?= $newPlan->getFormattedPrice() ?>/month</p>
                                            <p class="text-muted"><?= Html::encode($newPlan->description) ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Downgrade Schedule -->
                        <div class="downgrade-schedule mb-4">
                            <h5><i class="fas fa-calendar mr-2"></i><?= Yii::t('app', 'Downgrade Schedule') ?></h5>
                            <div class="card bg-light">
                                <div class="card-body">
                                    <div class="timeline">
                                        <div class="timeline-item active">
                                            <div class="timeline-marker"></div>
                                            <div class="timeline-content">
                                                <h6><?= Yii::t('app', 'Today') ?></h6>
                                                <p class="text-muted"><?= Yii::t('app', 'Downgrade is scheduled') ?></p>
                                                <small><?= Yii::$app->formatter->asDate('now') ?></small>
                                            </div>
                                        </div>
                                        <div class="timeline-item">
                                            <div class="timeline-marker future"></div>
                                            <div class="timeline-content">
                                                <h6><?= Yii::t('app', 'Current Plan Continues') ?></h6>
                                                <p class="text-muted"><?= Yii::t('app', 'You keep all current plan benefits until the end of your billing period') ?></p>
                                                <small><?= Yii::t('app', 'Until {date}', ['date' => Yii::$app->formatter->asDate($currentSubscription->end_date)]) ?></small>
                                            </div>
                                        </div>
                                        <div class="timeline-item future">
                                            <div class="timeline-marker future"></div>
                                            <div class="timeline-content">
                                                <h6><?= Yii::t('app', 'New Plan Starts') ?></h6>
                                                <p class="text-muted"><?= Yii::t('app', 'Your subscription changes to the new plan and billing resumes at the lower rate') ?></p>
                                                <small class="text-primary"><?= Yii::$app->formatter->asDate($currentSubscription->end_date) ?></small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Billing Changes -->
                        <div class="billing-changes mb-4">
                            <h5><i class="fas fa-money-bill-wave mr-2"></i><?= Yii::t('app', 'Billing Changes') ?></h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="billing-info">
                                        <h6><?= Yii::t('app', 'Current Billing') ?></h6>
                                        <div class="amount current-amount">
                                            <span class="price"><?= $currentSubscription->plan->getFormattedPrice() ?></span>
                                            <span class="period">/month</span>
                                        </div>
                                        <p class="text-muted"><?= Yii::t('app', 'Until {date}', ['date' => Yii::$app->formatter->asDate($currentSubscription->end_date)]) ?></p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="billing-info">
                                        <h6><?= Yii::t('app', 'New Billing') ?></h6>
                                        <div class="amount new-amount">
                                            <span class="price text-success"><?= $newPlan->getFormattedPrice() ?></span>
                                            <span class="period">/month</span>
                                        </div>
                                        <p class="text-muted"><?= Yii::t('app', 'Starting {date}', ['date' => Yii::$app->formatter->asDate($currentSubscription->end_date)]) ?></p>
                                        <div class="savings">
                                            <span class="badge badge-success">
                                                <?= Yii::t('app', 'Save ${amount}/month', ['amount' => number_format($currentSubscription->plan->price - $newPlan->price, 2)]) ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Important Notice -->
                        <div class="downgrade-notice mb-4">
                            <div class="alert alert-warning">
                                <h6><i class="fas fa-exclamation-triangle mr-2"></i><?= Yii::t('app', 'Important Notice') ?></h6>
                                <ul class="mb-0">
                                    <li><?= Yii::t('app', 'Your downgrade will take effect on {date} at the end of your current billing period.', ['date' => Yii::$app->formatter->asDate($currentSubscription->end_date)]) ?></li>
                                    <li><?= Yii::t('app', 'You will continue to have access to all current plan features until then.') ?></li>
                                    <li><?= Yii::t('app', 'After the downgrade, some features may be limited based on your new plan.') ?></li>
                                    <li><?= Yii::t('app', 'You can cancel this downgrade or upgrade again at any time before the change takes effect.') ?></li>
                                </ul>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="downgrade-actions text-center">
                            <?= Html::beginForm(['confirm-downgrade', 'planId' => $newPlan->id], 'post', ['class' => 'd-inline']) ?>
                                <?= Html::submitButton(
                                    '<i class="fas fa-arrow-down mr-2"></i>' . Yii::t('app', 'Confirm Downgrade'),
                                    [
                                        'class' => 'btn btn-warning btn-lg mr-3',
                                        'onclick' => 'return confirm("' . Yii::t('app', 'Are you sure you want to downgrade your plan? This will take effect on {date}.', ['date' => Yii::$app->formatter->asDate($currentSubscription->end_date)]) . '")'
                                    ]
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
.downgrade-preview {
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
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
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

.plan-card.current {
    border-color: #4f46e5;
    background: linear-gradient(135deg, rgba(79, 70, 229, 0.1) 0%, rgba(124, 58, 237, 0.1) 100%);
}

.plan-price {
    font-size: 1.5rem;
    font-weight: 700;
    margin: 0.5rem 0;
}

.timeline {
    position: relative;
    padding-left: 3rem;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 1rem;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e2e8f0;
}

.timeline-item {
    position: relative;
    margin-bottom: 2rem;
}

.timeline-marker {
    position: absolute;
    left: -2.5rem;
    top: 0.25rem;
    width: 1rem;
    height: 1rem;
    background: #10b981;
    border-radius: 50%;
    border: 3px solid #f3f4f6;
}

.timeline-marker.future {
    background: #6b7280;
}

.timeline-item.active .timeline-marker {
    background: #4f46e5;
}

.timeline-item.future .timeline-marker {
    background: #f59e0b;
}

.billing-info {
    background: #f8fafc;
    border-radius: 0.5rem;
    padding: 1.5rem;
    text-align: center;
}

.amount {
    font-size: 2rem;
    font-weight: 700;
    margin: 1rem 0;
}

.amount .period {
    font-size: 1rem;
    color: #6b7280;
}

.savings {
    margin-top: 1rem;
}

.btn-warning {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    border: none;
    border-radius: 0.5rem;
    padding: 0.75rem 2rem;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-warning:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(245, 158, 11, 0.3);
}

.btn-secondary {
    border: 2px solid #6c757d;
    border-radius: 0.5rem;
    padding: 0.75rem 2rem;
    font-weight: 600;
}

@media (max-width: 768px) {
    .downgrade-preview {
        padding: 1rem 0;
    }
    
    .timeline {
        padding-left: 2rem;
    }
    
    .timeline-marker {
        left: -1.5rem;
    }
    
    .downgrade-actions .btn {
        display: block;
        width: 100%;
        margin-bottom: 1rem;
    }
}
</style>