<?php

namespace app\components;

use Yii;
use yii\base\Component;
use yii\base\Exception;

/**
 * StripePayment component for handling Stripe payment operations
 */
class StripePayment extends Component
{
    /**
     * @var string Stripe secret key
     */
    public $secretKey;

    /**
     * @var string Stripe publishable key
     */
    public $publishableKey;

    /**
     * @var string Stripe webhook secret
     */
    public $webhookSecret;

    /**
     * Initialize the component
     */
    public function init()
    {
        parent::init();
        
        // Load keys from params if not set
        if (empty($this->secretKey)) {
            $this->secretKey = Yii::$app->params['stripe.secretKey'] ?? null;
        }
        
        if (empty($this->publishableKey)) {
            $this->publishableKey = Yii::$app->params['stripe.publishableKey'] ?? null;
        }
        
        if (empty($this->webhookSecret)) {
            $this->webhookSecret = Yii::$app->params['stripe.webhookSecret'] ?? null;
        }
        
        // Set Stripe secret key
        if ($this->secretKey) {
            \Stripe\Stripe::setApiKey($this->secretKey);
        }
    }

    /**
     * Create a Stripe checkout session
     * 
     * @param array $items List of items to charge
     * @param string $successUrl URL to redirect to on successful payment
     * @param string $cancelUrl URL to redirect to on cancelled payment
     * @param array $options Additional options for the session
     * @return \Stripe\Checkout\Session
     * @throws Exception
     */
    public function createCheckoutSession($items, $successUrl, $cancelUrl, $options = [])
    {
        if (!$this->secretKey) {
            throw new Exception('Stripe secret key is not configured');
        }

        try {
            $lineItems = [];
            foreach ($items as $item) {
                $lineItems[] = [
                    'price_data' => [
                        'currency' => $item['currency'] ?? 'usd',
                        'product_data' => [
                            'name' => $item['name'],
                            'description' => $item['description'] ?? '',
                        ],
                        'unit_amount' => intval($item['amount'] * 100), // Convert to cents
                    ],
                    'quantity' => $item['quantity'] ?? 1,
                ];
            }

            $sessionOptions = array_merge([
                'payment_method_types' => ['card'],
                'line_items' => $lineItems,
                'mode' => 'payment',
                'success_url' => $successUrl,
                'cancel_url' => $cancelUrl,
            ], $options);

            return \Stripe\Checkout\Session::create($sessionOptions);
        } catch (\Exception $e) {
            throw new Exception('Failed to create Stripe checkout session: ' . $e->getMessage());
        }
    }

    /**
     * Create a Stripe subscription
     * 
     * @param string $customerId Stripe customer ID
     * @param string $priceId Stripe price ID
     * @param array $options Additional options for the subscription
     * @return \Stripe\Subscription
     * @throws Exception
     */
    public function createSubscription($customerId, $priceId, $options = [])
    {
        if (!$this->secretKey) {
            throw new Exception('Stripe secret key is not configured');
        }

        try {
            $subscriptionOptions = array_merge([
                'customer' => $customerId,
                'items' => [
                    ['price' => $priceId],
                ],
                'payment_behavior' => 'default_incomplete',
                'payment_settings' => ['save_default_payment_method' => 'on_subscription'],
                'expand' => ['latest_invoice.payment_intent'],
            ], $options);

            return \Stripe\Subscription::create($subscriptionOptions);
        } catch (\Exception $e) {
            throw new Exception('Failed to create Stripe subscription: ' . $e->getMessage());
        }
    }

    /**
     * Cancel a Stripe subscription
     * 
     * @param string $subscriptionId Stripe subscription ID
     * @return \Stripe\Subscription
     * @throws Exception
     */
    public function cancelSubscription($subscriptionId)
    {
        if (!$this->secretKey) {
            throw new Exception('Stripe secret key is not configured');
        }

        try {
            $subscription = \Stripe\Subscription::retrieve($subscriptionId);
            return $subscription->cancel();
        } catch (\Exception $e) {
            throw new Exception('Failed to cancel Stripe subscription: ' . $e->getMessage());
        }
    }

    /**
     * Verify a webhook signature
     * 
     * @param string $payload Webhook payload
     * @param string $signature Webhook signature
     * @return \Stripe\Event|null
     * @throws Exception
     */
    public function verifyWebhookSignature($payload, $signature)
    {
        if (!$this->webhookSecret) {
            throw new Exception('Stripe webhook secret is not configured');
        }

        try {
            return \Stripe\Webhook::constructEvent(
                $payload,
                $signature,
                $this->webhookSecret
            );
        } catch (\Exception $e) {
            throw new Exception('Failed to verify Stripe webhook signature: ' . $e->getMessage());
        }
    }

    /**
     * Get Stripe publishable key
     * 
     * @return string
     */
    public function getPublishableKey()
    {
        return $this->publishableKey;
    }
}
