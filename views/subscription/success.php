<?php

/* @var $this yii\web\View */
/* @var $subscription app\models\UserSubscription */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = Yii::t('app', 'Subscription Successful');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'My Account'), 'url' => ['my-account']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="subscription-success">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body text-center">
                        <!-- Success Icon -->
                        <div class="success-icon mb-4">
                            <i class="fas fa-check-circle text-success"></i>
                        </div>

                        <!-- Success Message -->
                        <h1 class="success-title"><?= Yii::t('app', 'Subscription Successful!') ?></h1>
                        <p class="success-message">
                            <?= Yii::t('app', 'Thank you for subscribing! Your payment has been processed successfully.') ?>
                        </p>

                        <?php if (isset($subscription)): ?>
                        <!-- Subscription Details -->
                        <div class="subscription-details mt-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h5 class="card-title"><?= Yii::t('app', 'Subscription Details') ?></h5>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p><strong><?= Yii::t('app', 'Plan') ?>:</strong> <?= Html::encode($subscription->plan->name) ?></p>
                                            <p><strong><?= Yii::t('app', 'Status') ?>:</strong> 
                                                <span class="badge badge-success"><?= Html::encode(ucfirst($subscription->status)) ?></span>
                                            </p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong><?= Yii::t('app', 'Next Billing Date') ?>:</strong> 
                                                <?= Yii::$app->formatter->asDate($subscription->next_billing_date) ?>
                                            </p>
                                            <p><strong><?= Yii::t('app', 'Amount') ?>:</strong> 
                                                <?= $subscription->plan->getFormattedPrice() ?>/month
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- What's Next -->
                        <div class="whats-next mt-4">
                            <h5><?= Yii::t('app', 'What\'s Next?') ?></h5>
                            <div class="row text-left">
                                <div class="col-md-4">
                                    <div class="next-step">
                                        <i class="fas fa-envelope text-primary fa-2x mb-2"></i>
                                        <h6><?= Yii::t('app', 'Check Your Email') ?></h6>
                                        <p class="small text-muted">
                                            <?= Yii::t('app', 'We\'ve sent a confirmation email with your subscription details.') ?>
                                        </p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="next-step">
                                        <i class="fas fa-cogs text-primary fa-2x mb-2"></i>
                                        <h6><?= Yii::t('app', 'Setup Your Account') ?></h6>
                                        <p class="small text-muted">
                                            <?= Yii::t('app', 'Configure your company settings and start creating invoices.') ?>
                                        </p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="next-step">
                                        <i class="fas fa-question-circle text-primary fa-2x mb-2"></i>
                                        <h6><?= Yii::t('app', 'Need Help?') ?></h6>
                                        <p class="small text-muted">
                                            <?= Yii::t('app', 'Check our documentation or contact support for assistance.') ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="action-buttons mt-4">
                            <?= Html::a(
                                '<i class="fas fa-tachometer-alt mr-2"></i>' . Yii::t('app', 'Go to Dashboard'),
                                ['/site/index'],
                                ['class' => 'btn btn-primary btn-lg mr-3']
                            ) ?>
                            
                            <?= Html::a(
                                '<i class="fas fa-user mr-2"></i>' . Yii::t('app', 'My Account'),
                                ['my-account'],
                                ['class' => 'btn btn-outline-primary btn-lg']
                            ) ?>
                        </div>

                        <!-- Support Info -->
                        <div class="support-info mt-4 pt-4 border-top">
                            <p class="small text-muted">
                                <?= Yii::t('app', 'Questions about your subscription?') ?>
                                <?= Html::a(Yii::t('app', 'Contact Support'), '#', ['class' => 'text-primary']) ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.subscription-success {
    padding: 2rem 0;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
}

.card {
    border: none;
    border-radius: 1rem;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

.success-icon {
    font-size: 5rem;
    margin-bottom: 1rem;
}

.success-title {
    color: #28a745;
    font-weight: 700;
    margin-bottom: 1rem;
}

.success-message {
    font-size: 1.1rem;
    color: #6c757d;
    margin-bottom: 2rem;
}

.subscription-details .card {
    box-shadow: none;
    border: 1px solid #dee2e6;
}

.next-step {
    text-align: center;
    padding: 1rem;
}

.next-step h6 {
    color: #495057;
    font-weight: 600;
    margin-top: 0.5rem;
}

.btn-primary {
    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
    border: none;
    border-radius: 0.5rem;
    padding: 0.75rem 1.5rem;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(79, 70, 229, 0.3);
}

.btn-outline-primary {
    border: 2px solid #4f46e5;
    color: #4f46e5;
    border-radius: 0.5rem;
    padding: 0.75rem 1.5rem;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-outline-primary:hover {
    background: #4f46e5;
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(79, 70, 229, 0.3);
}

@media (max-width: 768px) {
    .subscription-success {
        padding: 1rem 0;
    }
    
    .success-icon {
        font-size: 3rem;
    }
    
    .action-buttons .btn {
        display: block;
        width: 100%;
        margin-bottom: 1rem;
    }
    
    .action-buttons .btn:last-child {
        margin-bottom: 0;
    }
}
</style>