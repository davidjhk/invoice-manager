<?php

namespace app\components;

use Yii;
use yii\base\Component;
use yii\base\Exception;

/**
 * PayPalPayment component for handling PayPal payment operations
 */
class PayPalPayment extends Component
{
    /**
     * @var string PayPal client ID
     */
    public $clientId;

    /**
     * @var string PayPal secret
     */
    public $secret;

    /**
     * @var string PayPal mode (sandbox or live)
     */
    public $mode = 'sandbox';

    /**
     * @var string PayPal API base URL
     */
    private $apiUrl;

    /**
     * @var string PayPal access token
     */
    private $accessToken;

    /**
     * Initialize the component
     */
    public function init()
    {
        parent::init();
        
        // Load credentials from params if not set
        if (empty($this->clientId)) {
            $this->clientId = Yii::$app->params['paypal.clientId'] ?? null;
        }
        
        if (empty($this->secret)) {
            $this->secret = Yii::$app->params['paypal.secret'] ?? null;
        }
        
        if (empty($this->mode)) {
            $this->mode = Yii::$app->params['paypal.mode'] ?? 'sandbox';
        }
        
        // Set API URL based on mode
        $this->apiUrl = $this->mode === 'sandbox' 
            ? 'https://api.sandbox.paypal.com' 
            : 'https://api.paypal.com';
    }

    /**
     * Get PayPal access token
     * 
     * @return string
     * @throws Exception
     */
    private function getAccessToken()
    {
        if ($this->accessToken) {
            return $this->accessToken;
        }

        if (!$this->clientId || !$this->secret) {
            throw new Exception('PayPal credentials are not configured');
        }

        $auth = base64_encode($this->clientId . ':' . $this->secret);
        
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $this->apiUrl . '/v1/oauth2/token',
            CURLOPT_HTTPHEADER => [
                'Authorization: Basic ' . $auth,
                'Content-Type: application/x-www-form-urlencoded'
            ],
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => 'grant_type=client_credentials',
            CURLOPT_RETURNTRANSFER => true,
        ]);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($httpCode !== 200) {
            throw new Exception('Failed to get PayPal access token: HTTP ' . $httpCode);
        }

        $data = json_decode($response, true);
        if (!isset($data['access_token'])) {
            throw new Exception('Invalid PayPal access token response');
        }

        $this->accessToken = $data['access_token'];
        return $this->accessToken;
    }

    /**
     * Create a PayPal order
     * 
     * @param array $items List of items to charge
     * @param string $currency Currency code (e.g., USD)
     * @param string $returnUrl URL to redirect to after payment approval
     * @param string $cancelUrl URL to redirect to on cancelled payment
     * @return array Order details
     * @throws Exception
     */
    public function createOrder($items, $currency = 'USD', $returnUrl, $cancelUrl)
    {
        $accessToken = $this->getAccessToken();

        $purchaseUnits = [];
        $total = 0;
        
        foreach ($items as $item) {
            $amount = $item['amount'];
            $total += $amount;
            
            $purchaseUnits[] = [
                'reference_id' => uniqid(),
                'description' => $item['description'] ?? '',
                'amount' => [
                    'currency_code' => $currency,
                    'value' => number_format($amount, 2, '.', ''),
                ],
            ];
        }

        $data = [
            'intent' => 'CAPTURE',
            'purchase_units' => $purchaseUnits,
            'application_context' => [
                'return_url' => $returnUrl,
                'cancel_url' => $cancelUrl,
                'brand_name' => Yii::$app->params['siteName'] ?? 'Invoice Manager',
                'shipping_preference' => 'NO_SHIPPING',
                'user_action' => 'PAY_NOW',
            ]
        ];

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $this->apiUrl . '/v2/checkout/orders',
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $accessToken,
            ],
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_RETURNTRANSFER => true,
        ]);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($httpCode !== 201) {
            throw new Exception('Failed to create PayPal order: HTTP ' . $httpCode . ' - ' . $response);
        }

        return json_decode($response, true);
    }

    /**
     * Capture a PayPal order
     * 
     * @param string $orderId PayPal order ID
     * @return array Capture details
     * @throws Exception
     */
    public function captureOrder($orderId)
    {
        $accessToken = $this->getAccessToken();

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $this->apiUrl . '/v2/checkout/orders/' . $orderId . '/capture',
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $accessToken,
            ],
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
        ]);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($httpCode !== 201) {
            throw new Exception('Failed to capture PayPal order: HTTP ' . $httpCode . ' - ' . $response);
        }

        return json_decode($response, true);
    }

    /**
     * Create a PayPal subscription
     * 
     * @param array $subscriptionData Subscription details
     * @return array Subscription details
     * @throws Exception
     */
    public function createSubscription($subscriptionData)
    {
        $accessToken = $this->getAccessToken();

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $this->apiUrl . '/v1/billing/subscriptions',
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $accessToken,
            ],
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($subscriptionData),
            CURLOPT_RETURNTRANSFER => true,
        ]);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($httpCode !== 201) {
            throw new Exception('Failed to create PayPal subscription: HTTP ' . $httpCode . ' - ' . $response);
        }

        return json_decode($response, true);
    }

    /**
     * Cancel a PayPal subscription
     * 
     * @param string $subscriptionId PayPal subscription ID
     * @param string $reason Reason for cancellation
     * @return array Cancellation details
     * @throws Exception
     */
    public function cancelSubscription($subscriptionId, $reason = 'User requested cancellation')
    {
        $accessToken = $this->getAccessToken();

        $data = [
            'reason' => $reason
        ];

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $this->apiUrl . '/v1/billing/subscriptions/' . $subscriptionId . '/cancel',
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $accessToken,
            ],
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_RETURNTRANSFER => true,
        ]);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($httpCode !== 204) {
            throw new Exception('Failed to cancel PayPal subscription: HTTP ' . $httpCode . ' - ' . $response);
        }

        return ['status' => 'cancelled'];
    }

    /**
     * Get PayPal mode
     * 
     * @return string
     */
    public function getMode()
    {
        return $this->mode;
    }
}
