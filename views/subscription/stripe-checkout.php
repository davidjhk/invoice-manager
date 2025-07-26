<?php

/* @var $this yii\web\View */
/* @var $plan app\models\Plan */
/* @var $stripePublicKey string */
/* @var $checkoutSession array */

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
                            <i class="fas fa-credit-card mr-2"></i>
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

                        <!-- Stripe Checkout -->
                        <div class="checkout-section">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle mr-2"></i>
                                <?= Yii::t('app', 'You will be redirected to Stripe\'s secure payment page to complete your subscription.') ?>
                            </div>

                            <div class="checkout-actions text-center">
                                <button id="checkout-button" class="btn btn-primary btn-lg">
                                    <i class="fas fa-credit-card mr-2"></i>
                                    <?= Yii::t('app', 'Subscribe with Stripe') ?>
                                </button>
                                
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
                                    <p class="small text-muted"><?= Yii::t('app', 'PCI Compliant') ?></p>
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

<!-- Stripe JavaScript -->
<script src="https://js.stripe.com/v3/"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Stripe
    const stripe = Stripe('<?= $stripePublicKey ?>');
    
    // Handle checkout button click
    document.getElementById('checkout-button').addEventListener('click', function() {
        // Show loading state
        this.disabled = true;
        this.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i><?= Yii::t('app', 'Loading...') ?>';
        
        // Redirect to Stripe Checkout
        <?php if (isset($checkoutSession['url'])): ?>
        window.location.href = '<?= $checkoutSession['url'] ?>';
        <?php else: ?>
        // Fallback: Create checkout session via AJAX
        fetch('<?= Url::to(['subscription/create-checkout-session']) ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': '<?= Yii::$app->request->csrfToken ?>'
            },
            body: JSON.stringify({
                planId: <?= $plan->id ?>
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.checkoutUrl) {
                window.location.href = data.checkoutUrl;
            } else {
                alert('<?= Yii::t('app', 'Failed to create checkout session. Please try again.') ?>');
                // Reset button
                this.disabled = false;
                this.innerHTML = '<i class="fas fa-credit-card mr-2"></i><?= Yii::t('app', 'Subscribe with Stripe') ?>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('<?= Yii::t('app', 'An error occurred. Please try again.') ?>');
            // Reset button
            this.disabled = false;
            this.innerHTML = '<i class="fas fa-credit-card mr-2"></i><?= Yii::t('app', 'Subscribe with Stripe') ?>';
        });
        <?php endif; ?>
    });
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
    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
    color: white;
    border-radius: 1rem 1rem 0 0 !important;
    padding: 1.5rem;
}

.plan-summary {
    background: #f8fafc;
    border-radius: 0.5rem;
    padding: 1.5rem;
    border-left: 4px solid #4f46e5;
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

.btn-primary {
    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
    border: none;
    border-radius: 0.5rem;
    padding: 1rem 2rem;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(79, 70, 229, 0.3);
}

.payment-security {
    border-top: 1px solid #e2e8f0;
    padding-top: 1.5rem;
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