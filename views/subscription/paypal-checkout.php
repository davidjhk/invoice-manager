<?php

/* @var $this yii\web\View */
/* @var $plan app\models\Plan */
/* @var $paypalClientId string */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = Yii::t('app', 'Subscribe to {planName}', ['planName' => $plan->name]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'My Account'), 'url' => ['my-account']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="subscription-checkout">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">
                            <i class="fab fa-paypal mr-2"></i>
                            <?= Html::encode($this->title) ?>
                        </h4>
                    </div>
                    <div class="card-body">
                        <!-- Plan Summary -->
                        <div class="plan-summary mb-4">
                            <div class="row">
                                <div class="col-md-8">
                                    <h5><?= Html::encode($plan->name) ?> <?= Yii::t('app', 'Plan') ?></h5>
                                    <p class="text-muted"><?= Html::encode($plan->description) ?></p>
                                    
                                    <?php if (!empty($plan->features)): ?>
                                    <div class="plan-features">
                                        <h6><?= Yii::t('app', 'Features Included') ?>:</h6>
                                        <ul class="list-unstyled">
                                            <?php foreach ($plan->getFeatures() as $feature): ?>
                                            <li><i class="fas fa-check text-success mr-2"></i><?= Html::encode($feature) ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-4 text-right">
                                    <div class="plan-price">
                                        <h3 class="text-primary"><?= $plan->getFormattedPrice() ?></h3>
                                        <small class="text-muted"><?= Yii::t('app', 'per month') ?></small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- PayPal Checkout -->
                        <div class="checkout-section">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle mr-2"></i>
                                <?= Yii::t('app', 'You will be redirected to PayPal\'s secure payment page to complete your subscription.') ?>
                            </div>

                            <div class="checkout-actions text-center">
                                <!-- PayPal Button Container -->
                                <div id="paypal-button-container"></div>
                                
                                <div class="mt-3">
                                    <?= Html::a(Yii::t('app', 'Cancel'), ['my-account'], ['class' => 'btn btn-secondary']) ?>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Security Info -->
                        <div class="payment-security mt-4">
                            <div class="row text-center">
                                <div class="col-md-4">
                                    <i class="fas fa-shield-alt text-success fa-2x mb-2"></i>
                                    <p class="small text-muted"><?= Yii::t('app', 'Secure SSL Encryption') ?></p>
                                </div>
                                <div class="col-md-4">
                                    <i class="fas fa-lock text-success fa-2x mb-2"></i>
                                    <p class="small text-muted"><?= Yii::t('app', 'PayPal Protected') ?></p>
                                </div>
                                <div class="col-md-4">
                                    <i class="fas fa-undo text-success fa-2x mb-2"></i>
                                    <p class="small text-muted"><?= Yii::t('app', 'Cancel Anytime') ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- PayPal JavaScript SDK -->
<script src="https://www.paypal.com/sdk/js?client-id=<?= $paypalClientId ?>&vault=true&intent=subscription"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Render PayPal subscription button
    paypal.Buttons({
        style: {
            layout: 'vertical',
            color: 'blue',
            shape: 'rect',
            label: 'subscribe'
        },
        createSubscription: function(data, actions) {
            return actions.subscription.create({
                'plan_id': '<?= $plan->paypal_plan_id ?? '' ?>',
                'application_context': {
                    'brand_name': '<?= Yii::$app->name ?>',
                    'user_action': 'SUBSCRIBE_NOW',
                    'payment_method': {
                        'payer_selected': 'PAYPAL',
                        'payee_preferred': 'IMMEDIATE_PAYMENT_REQUIRED'
                    },
                    'return_url': '<?= Url::to(['subscription/paypal-success'], true) ?>',
                    'cancel_url': '<?= Url::to(['subscription/paypal-cancel'], true) ?>'
                }
            });
        },
        onApprove: function(data, actions) {
            // Handle subscription approval
            fetch('<?= Url::to(['subscription/paypal-approve']) ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': '<?= Yii::$app->request->csrfToken ?>'
                },
                body: JSON.stringify({
                    subscriptionId: data.subscriptionID,
                    planId: <?= $plan->id ?>
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = '<?= Url::to(['subscription/success']) ?>';
                } else {
                    alert('<?= Yii::t('app', 'Subscription activation failed. Please contact support.') ?>');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('<?= Yii::t('app', 'An error occurred. Please try again.') ?>');
            });
        },
        onCancel: function(data) {
            // Handle subscription cancellation
            window.location.href = '<?= Url::to(['subscription/my-account']) ?>';
        },
        onError: function(err) {
            console.error('PayPal error:', err);
            alert('<?= Yii::t('app', 'PayPal error occurred. Please try again.') ?>');
        }
    }).render('#paypal-button-container');
});
</script>

<style>
.subscription-checkout {
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
    background: linear-gradient(135deg, #0070ba 0%, #003087 100%);
    color: white;
    border-radius: 1rem 1rem 0 0 !important;
    padding: 1.5rem;
}

.plan-summary {
    background: #f8fafc;
    border-radius: 0.5rem;
    padding: 1.5rem;
    border-left: 4px solid #0070ba;
}

.plan-price h3 {
    font-weight: 700;
    margin-bottom: 0;
}

.plan-features ul {
    columns: 2;
    column-gap: 2rem;
}

.plan-features li {
    margin-bottom: 0.5rem;
    break-inside: avoid;
}

.checkout-section {
    border-top: 1px solid #e2e8f0;
    padding-top: 1.5rem;
}

.payment-security {
    border-top: 1px solid #e2e8f0;
    padding-top: 1.5rem;
}

#paypal-button-container {
    max-width: 300px;
    margin: 0 auto;
}

@media (max-width: 768px) {
    .plan-features ul {
        columns: 1;
    }
    
    .subscription-checkout {
        padding: 1rem 0;
    }
}
</style>