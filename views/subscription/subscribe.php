<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\models\Plan;

/** @var yii\web\View $this */
/** @var array $plans */

$this->title = 'Choose a Plan';
$this->params['breadcrumbs'][] = ['label' => 'Subscriptions', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="subscription-subscribe">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php if (empty($plans)): ?>
        <div class="alert alert-info">
            <p>No plans are currently available.</p>
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($plans as $plan): ?>
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><?= Html::encode($plan->name) ?></h3>
                        </div>
                        <div class="card-body">
                            <div class="plan-price">
                                <h2><?= $plan->getFormattedPrice() ?><small class="text-muted">/month</small></h2>
                            </div>
                            
                            <div class="plan-description">
                                <p><?= Html::encode($plan->description) ?></p>
                            </div>
                            
                            <div class="plan-features">
                                <ul class="list-unstyled">
                                    <?php foreach ($plan->getFeaturesArray() as $feature => $value): ?>
                                        <li>
                                            <i class="fas fa-check text-success"></i>
                                            <strong><?= Html::encode(ucfirst(str_replace('_', ' ', $feature))) ?>:</strong>
                                            <?= Html::encode($value) ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                        <div class="card-footer">
                            <div class="d-flex justify-content-between">
                                <?= Html::a('Pay with Stripe', ['stripe-checkout', 'planId' => $plan->id], [
                                    'class' => 'btn btn-primary',
                                    'data' => [
                                        'confirm' => 'This will redirect you to Stripe for payment processing.',
                                    ],
                                ]) ?>
                                
                                <?= Html::a('Pay with PayPal', ['paypal-checkout', 'planId' => $plan->id], [
                                    'class' => 'btn btn-info',
                                    'data' => [
                                        'confirm' => 'This will redirect you to PayPal for payment processing.',
                                    ],
                                ]) ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<style>
.plan-price {
    text-align: center;
    margin: 20px 0;
    padding: 15px;
    background-color: #f8f9fa;
    border-radius: 5px;
}

.plan-features ul {
    margin: 0;
    padding: 0;
}

.plan-features li {
    margin-bottom: 10px;
    padding: 5px;
}
</style>
