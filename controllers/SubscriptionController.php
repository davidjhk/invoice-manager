<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;
use app\models\Plan;
use app\models\UserSubscription;
use app\models\User;

class SubscriptionController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['webhook-stripe', 'webhook-paypal'],
                    ],
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'cancel' => ['POST'],
                    'create-checkout-session' => ['POST'],
                    'paypal-approve' => ['POST'],
                    'webhook-stripe' => ['POST'],
                    'webhook-paypal' => ['POST'],
                    'confirm-upgrade' => ['POST'],
                    'confirm-downgrade' => ['POST'],
                    'cancel-scheduled-change' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function beforeAction($action)
    {
        // Disable CSRF validation for webhook actions
        if (in_array($action->id, ['webhook-stripe', 'webhook-paypal'])) {
            $this->enableCsrfValidation = false;
        }
        
        return parent::beforeAction($action);
    }

    /**
     * Display subscription plans
     *
     * @return string
     */
    public function actionIndex()
    {
        $plans = Plan::getActivePlans()->all();
        $currentSubscription = UserSubscription::getActiveSubscription(Yii::$app->user->id);

        return $this->render('index', [
            'plans' => $plans,
            'currentSubscription' => $currentSubscription,
        ]);
    }

    /**
     * Subscribe to a plan
     *
     * @param int $planId
     * @param string $paymentMethod
     * @return Response|string
     */
    public function actionSubscribe($planId, $paymentMethod = 'stripe')
    {
        $plan = Plan::findOne($planId);
        if (!$plan || !$plan->is_active) {
            throw new NotFoundHttpException('Plan not found.');
        }

        $user = Yii::$app->user->identity;
        $currentSubscription = UserSubscription::getActiveSubscription($user->id);

        // Check if user already has an active subscription
        if ($currentSubscription && $currentSubscription->isActive()) {
            Yii::$app->session->setFlash('error', Yii::t('app', 'You already have an active subscription.'));
            return $this->redirect(['index']);
        }

        if ($paymentMethod === 'stripe') {
            return $this->processStripeSubscription($plan, $user);
        } elseif ($paymentMethod === 'paypal') {
            Yii::$app->session->setFlash('error', 'PayPal payment is currently unavailable. Please use credit card payment.');
            return $this->redirect(['index']);
        } else {
            throw new BadRequestHttpException('Invalid payment method.');
        }
    }

    /**
     * Upgrade subscription to a higher plan
     *
     * @param int $planId
     * @return Response|string
     */
    public function actionUpgrade($planId)
    {
        $plan = Plan::findOne($planId);
        if (!$plan || !$plan->is_active) {
            throw new NotFoundHttpException('Plan not found.');
        }

        $user = Yii::$app->user->identity;
        $currentSubscription = UserSubscription::getActiveSubscription($user->id);

        if (!$currentSubscription || !$currentSubscription->canBeUpgraded()) {
            Yii::$app->session->setFlash('error', Yii::t('app', 'Cannot upgrade subscription.'));
            return $this->redirect(['my-account']);
        }

        // Check if the new plan is actually an upgrade
        if ($plan->price <= $currentSubscription->plan->price) {
            Yii::$app->session->setFlash('error', Yii::t('app', 'You can only upgrade to a higher plan.'));
            return $this->redirect(['my-account']);
        }

        // Calculate prorated upgrade cost
        $upgradeData = $this->calculateUpgradeProration($currentSubscription, $plan);

        return $this->render('upgrade-preview', [
            'currentSubscription' => $currentSubscription,
            'newPlan' => $plan,
            'upgradeData' => $upgradeData,
        ]);
    }

    /**
     * Downgrade subscription to a lower plan
     */
    public function actionDowngrade($planId)
    {
        $plan = Plan::findOne($planId);
        if (!$plan || !$plan->is_active) {
            throw new NotFoundHttpException('Plan not found.');
        }

        $user = Yii::$app->user->identity;
        $currentSubscription = UserSubscription::getActiveSubscription($user->id);

        if (!$currentSubscription || !$currentSubscription->isActive()) {
            Yii::$app->session->setFlash('error', Yii::t('app', 'Cannot downgrade subscription.'));
            return $this->redirect(['my-account']);
        }

        if ($plan->price >= $currentSubscription->plan->price) {
            Yii::$app->session->setFlash('error', Yii::t('app', 'You can only downgrade to a lower plan.'));
            return $this->redirect(['my-account']);
        }

        return $this->render('downgrade-preview', [
            'currentSubscription' => $currentSubscription,
            'newPlan' => $plan,
        ]);
    }

    /**
     * Confirm upgrade and process payment
     */
    public function actionConfirmUpgrade($planId)
    {
        if (!Yii::$app->request->isPost) {
            throw new BadRequestHttpException('Invalid request method.');
        }

        $plan = Plan::findOne($planId);
        if (!$plan || !$plan->is_active) {
            throw new NotFoundHttpException('Plan not found.');
        }

        $user = Yii::$app->user->identity;
        $currentSubscription = UserSubscription::getActiveSubscription($user->id);

        if (!$currentSubscription || !$currentSubscription->canBeUpgraded()) {
            Yii::$app->session->setFlash('error', Yii::t('app', 'Cannot upgrade subscription.'));
            return $this->redirect(['my-account']);
        }

        try {
            // Calculate prorated upgrade cost
            $upgradeData = $this->calculateUpgradeProration($currentSubscription, $plan);

            // Process upgrade based on payment method
            if ($currentSubscription->payment_method === UserSubscription::PAYMENT_METHOD_STRIPE) {
                return $this->processStripeUpgrade($currentSubscription, $plan, $upgradeData);
            } else {
                // For non-Stripe payments, update subscription immediately
                return $this->processDirectUpgrade($currentSubscription, $plan, $upgradeData);
            }

        } catch (\Exception $e) {
            Yii::error('Upgrade error: ' . $e->getMessage(), 'subscription');
            Yii::$app->session->setFlash('error', Yii::t('app', 'Failed to process upgrade.'));
            return $this->redirect(['my-account']);
        }
    }

    /**
     * Confirm downgrade
     */
    public function actionConfirmDowngrade($planId)
    {
        if (!Yii::$app->request->isPost) {
            throw new BadRequestHttpException('Invalid request method.');
        }

        $plan = Plan::findOne($planId);
        if (!$plan || !$plan->is_active) {
            throw new NotFoundHttpException('Plan not found.');
        }

        $user = Yii::$app->user->identity;
        $currentSubscription = UserSubscription::getActiveSubscription($user->id);

        if (!$currentSubscription || !$currentSubscription->isActive()) {
            Yii::$app->session->setFlash('error', Yii::t('app', 'Cannot downgrade subscription.'));
            return $this->redirect(['my-account']);
        }

        try {
            // Schedule downgrade for end of current period
            $currentSubscription->scheduled_plan_id = $plan->id;
            $currentSubscription->scheduled_change_date = $currentSubscription->end_date;
            
            if ($currentSubscription->save()) {
                Yii::$app->session->setFlash('success', Yii::t('app', 'Downgrade scheduled successfully. Your new plan will take effect on {date}.', [
                    'date' => Yii::$app->formatter->asDate($currentSubscription->end_date)
                ]));
            } else {
                Yii::$app->session->setFlash('error', Yii::t('app', 'Failed to schedule downgrade.'));
            }

            return $this->redirect(['my-account']);

        } catch (\Exception $e) {
            Yii::error('Downgrade error: ' . $e->getMessage(), 'subscription');
            Yii::$app->session->setFlash('error', Yii::t('app', 'Failed to schedule downgrade.'));
            return $this->redirect(['my-account']);
        }
    }

    /**
     * Cancel scheduled plan change
     */
    public function actionCancelScheduledChange()
    {
        if (!Yii::$app->request->isPost) {
            throw new BadRequestHttpException('Invalid request method.');
        }

        $user = Yii::$app->user->identity;
        $currentSubscription = UserSubscription::getActiveSubscription($user->id);

        if (!$currentSubscription || !$currentSubscription->scheduled_plan_id) {
            Yii::$app->session->setFlash('error', Yii::t('app', 'No scheduled plan change found.'));
            return $this->redirect(['my-account']);
        }

        try {
            $currentSubscription->scheduled_plan_id = null;
            $currentSubscription->scheduled_change_date = null;
            
            if ($currentSubscription->save()) {
                Yii::$app->session->setFlash('success', Yii::t('app', 'Scheduled plan change has been cancelled.'));
            } else {
                Yii::$app->session->setFlash('error', Yii::t('app', 'Failed to cancel scheduled plan change.'));
            }

            return $this->redirect(['my-account']);

        } catch (\Exception $e) {
            Yii::error('Cancel scheduled change error: ' . $e->getMessage(), 'subscription');
            Yii::$app->session->setFlash('error', Yii::t('app', 'Failed to cancel scheduled plan change.'));
            return $this->redirect(['my-account']);
        }
    }

    /**
     * Cancel subscription
     *
     * @return Response
     */
    public function actionCancel()
    {
        $user = Yii::$app->user->identity;
        $subscription = UserSubscription::getActiveSubscription($user->id);

        if (!$subscription || !$subscription->canBeCancelled()) {
            Yii::$app->session->setFlash('error', Yii::t('app', 'Cannot cancel subscription.'));
            return $this->redirect(['my-account']);
        }

        if ($subscription->payment_method === UserSubscription::PAYMENT_METHOD_STRIPE) {
            $success = $this->cancelStripeSubscription($subscription);
        } elseif ($subscription->payment_method === UserSubscription::PAYMENT_METHOD_PAYPAL) {
            $success = $this->cancelPayPalSubscription($subscription);
        } else {
            $success = false;
        }

        if ($success) {
            Yii::$app->session->setFlash('success', Yii::t('app', 'Subscription has been cancelled successfully.'));
        } else {
            Yii::$app->session->setFlash('error', Yii::t('app', 'Failed to cancel subscription. Please try again.'));
        }

        return $this->redirect(['my-account']);
    }

    /**
     * My Account page
     *
     * @return string
     */
    public function actionMyAccount()
    {
        $user = Yii::$app->user->identity;
        $subscription = UserSubscription::getActiveSubscription($user->id);
        $availablePlans = Plan::getActivePlans()->all();

        return $this->render('my-account', [
            'user' => $user,
            'subscription' => $subscription,
            'availablePlans' => $availablePlans,
        ]);
    }

    /**
     * Stripe webhook handler
     *
     * @return Response
     */
    public function actionWebhookStripe()
    {
        $this->layout = false;
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $payload = Yii::$app->request->rawBody;
            $sig_header = Yii::$app->request->headers->get('Stripe-Signature');
            
            // Process Stripe webhook
            $this->processStripeWebhook($payload, $sig_header);
            
            return ['status' => 'success'];
        } catch (\Exception $e) {
            Yii::error('Stripe webhook error: ' . $e->getMessage(), 'subscription');
            Yii::$app->response->statusCode = 400;
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Create Stripe checkout session
     *
     * @return Response
     */
    public function actionCreateCheckoutSession()
    {
        $this->layout = false;
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!Yii::$app->request->isPost) {
            Yii::$app->response->statusCode = 405;
            return ['error' => 'Method not allowed'];
        }

        try {
            $data = json_decode(Yii::$app->request->rawBody, true);
            $planId = $data['planId'] ?? null;

            if (!$planId) {
                Yii::$app->response->statusCode = 400;
                return ['error' => 'Plan ID is required'];
            }

            $plan = Plan::findOne($planId);
            if (!$plan || !$plan->is_active) {
                Yii::$app->response->statusCode = 404;
                return ['error' => 'Plan not found'];
            }

            $user = Yii::$app->user->identity;
            if (!$user) {
                Yii::$app->response->statusCode = 401;
                return ['error' => 'Authentication required'];
            }

            // Get Stripe configuration
            $stripeSecretKey = Yii::$app->params['stripe']['secretKey'] ?? '';
            
            if (empty($stripeSecretKey)) {
                Yii::$app->response->statusCode = 500;
                return ['error' => 'Stripe not configured'];
            }

            // Initialize Stripe
            \Stripe\Stripe::setApiKey($stripeSecretKey);

            try {
                // Create Stripe checkout session
                $checkoutSession = \Stripe\Checkout\Session::create([
                    'payment_method_types' => ['card'],
                    'mode' => 'subscription',
                    'line_items' => [[
                        'price_data' => [
                            'currency' => 'usd',
                            'product_data' => [
                                'name' => $plan->name,
                                'description' => $plan->description,
                            ],
                            'unit_amount' => (int)($plan->price * 100), // Stripe uses cents
                            'recurring' => [
                                'interval' => 'month',
                            ],
                        ],
                        'quantity' => 1,
                    ]],
                    'customer_email' => $user->email,
                    'metadata' => [
                        'user_id' => $user->id,
                        'plan_id' => $plan->id,
                    ],
                    'success_url' => Yii::$app->urlManager->createAbsoluteUrl(['subscription/stripe-success']) . '?session_id={CHECKOUT_SESSION_ID}',
                    'cancel_url' => Yii::$app->urlManager->createAbsoluteUrl(['subscription/my-account']),
                ]);

                return [
                    'success' => true,
                    'checkoutUrl' => $checkoutSession->url,
                ];

            } catch (\Stripe\Exception\ApiErrorException $e) {
                Yii::error('Stripe API error: ' . $e->getMessage(), 'subscription');
                Yii::$app->response->statusCode = 500;
                return ['error' => 'Payment processing error: ' . $e->getMessage()];
            }

        } catch (\Exception $e) {
            Yii::error('Stripe checkout session error: ' . $e->getMessage(), 'subscription');
            Yii::$app->response->statusCode = 500;
            return ['error' => 'Internal server error'];
        }
    }

    /**
     * PayPal webhook handler
     *
     * @return Response
     */
    public function actionWebhookPaypal()
    {
        $this->layout = false;
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $payload = Yii::$app->request->rawBody;
            
            // Process PayPal webhook
            $this->processPayPalWebhook($payload);
            
            return ['status' => 'success'];
        } catch (\Exception $e) {
            Yii::error('PayPal webhook error: ' . $e->getMessage(), 'subscription');
            Yii::$app->response->statusCode = 400;
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Handle PayPal subscription approval
     *
     * @return Response
     */
    public function actionPaypalApprove()
    {
        $this->layout = false;
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!Yii::$app->request->isPost) {
            Yii::$app->response->statusCode = 405;
            return ['error' => 'Method not allowed'];
        }

        try {
            $data = json_decode(Yii::$app->request->rawBody, true);
            $subscriptionId = $data['subscriptionId'] ?? null;
            $planId = $data['planId'] ?? null;

            if (!$subscriptionId || !$planId) {
                Yii::$app->response->statusCode = 400;
                return ['error' => 'Subscription ID and Plan ID are required'];
            }

            $plan = Plan::findOne($planId);
            if (!$plan || !$plan->is_active) {
                Yii::$app->response->statusCode = 404;
                return ['error' => 'Plan not found'];
            }

            $user = Yii::$app->user->identity;
            if (!$user) {
                Yii::$app->response->statusCode = 401;
                return ['error' => 'Authentication required'];
            }

            // TODO: Implement actual PayPal subscription approval
            // For now, return a placeholder response
            return [
                'success' => true,
                'message' => 'PayPal integration not yet implemented'
            ];

        } catch (\Exception $e) {
            Yii::error('PayPal approval error: ' . $e->getMessage(), 'subscription');
            Yii::$app->response->statusCode = 500;
            return ['error' => 'Internal server error'];
        }
    }

    /**
     * Handle PayPal subscription success
     *
     * @return Response|string
     */
    public function actionPaypalSuccess()
    {
        // Handle PayPal subscription success redirect
        Yii::$app->session->setFlash('success', Yii::t('app', 'Subscription completed successfully!'));
        return $this->redirect(['success']);
    }

    /**
     * Handle PayPal subscription cancellation
     *
     * @return Response|string
     */
    public function actionPaypalCancel()
    {
        // Handle PayPal subscription cancellation redirect
        Yii::$app->session->setFlash('info', Yii::t('app', 'Subscription was cancelled.'));
        return $this->redirect(['my-account']);
    }

    /**
     * Handle Stripe subscription success
     *
     * @return Response|string
     */
    public function actionStripeSuccess()
    {
        $sessionId = Yii::$app->request->get('session_id');
        
        if (!$sessionId) {
            Yii::$app->session->setFlash('error', Yii::t('app', 'Invalid session.'));
            return $this->redirect(['my-account']);
        }

        try {
            // Initialize Stripe
            $stripeSecretKey = Yii::$app->params['stripe']['secretKey'] ?? '';
            if (empty($stripeSecretKey)) {
                throw new \Exception('Stripe secret key not configured');
            }
            
            \Stripe\Stripe::setApiKey($stripeSecretKey);

            // Retrieve the checkout session
            $session = \Stripe\Checkout\Session::retrieve($sessionId);
            
            Yii::info('Stripe session retrieved: ' . json_encode([
                'session_id' => $sessionId,
                'payment_status' => $session->payment_status,
                'subscription' => $session->subscription,
                'metadata' => $session->metadata->toArray()
            ]), 'subscription');
            
            if ($session->payment_status === 'paid') {
                $userId = $session->metadata->user_id ?? null;
                $planId = $session->metadata->plan_id ?? null;
                
                if ($userId && $planId) {
                    // Create subscription record
                    $plan = Plan::findOne($planId);
                    $user = User::findOne($userId);
                    
                    if ($plan && $user) {
                        // Check if user already has an active subscription
                        $existingSubscription = UserSubscription::getActiveSubscription($user->id);
                        if ($existingSubscription) {
                            Yii::$app->session->setFlash('warning', Yii::t('app', 'You already have an active subscription.'));
                        } else {
                            $subscription = new UserSubscription();
                            $subscription->user_id = $user->id;
                            $subscription->plan_id = $plan->id;
                            $subscription->status = UserSubscription::STATUS_ACTIVE;
                            $subscription->payment_method = UserSubscription::PAYMENT_METHOD_STRIPE;
                            $subscription->stripe_subscription_id = $session->subscription;
                            $subscription->start_date = date('Y-m-d');
                            $subscription->end_date = date('Y-m-d', strtotime('+1 month'));
                            $subscription->next_billing_date = date('Y-m-d', strtotime('+1 month'));
                            $subscription->is_recurring = true;
                            
                            if ($subscription->save()) {
                                Yii::$app->session->setFlash('success', Yii::t('app', 'Subscription completed successfully!'));
                                Yii::info('Subscription created successfully for user ' . $user->id, 'subscription');
                            } else {
                                $errors = implode(', ', $subscription->getFirstErrors());
                                Yii::error('Subscription save failed: ' . $errors, 'subscription');
                                Yii::$app->session->setFlash('error', Yii::t('app', 'Failed to save subscription: {errors}', ['errors' => $errors]));
                            }
                        }
                    } else {
                        $error = 'Plan or user not found. Plan ID: ' . $planId . ', User ID: ' . $userId;
                        Yii::error($error, 'subscription');
                        Yii::$app->session->setFlash('error', Yii::t('app', 'Invalid plan or user data.'));
                    }
                } else {
                    $error = 'Missing metadata. User ID: ' . $userId . ', Plan ID: ' . $planId;
                    Yii::error($error, 'subscription');
                    Yii::$app->session->setFlash('error', Yii::t('app', 'Missing subscription information.'));
                }
            } else {
                Yii::error('Payment not completed. Status: ' . $session->payment_status, 'subscription');
                Yii::$app->session->setFlash('error', Yii::t('app', 'Payment was not completed.'));
            }
            
            return $this->redirect(['success']);
            
        } catch (\Exception $e) {
            Yii::error('Stripe success handling error: ' . $e->getMessage(), 'subscription');
            Yii::$app->session->setFlash('error', Yii::t('app', 'An error occurred while processing your subscription.'));
            return $this->redirect(['my-account']);
        }
    }

    /**
     * Subscription success page
     *
     * @return string
     */
    public function actionSuccess()
    {
        $user = Yii::$app->user->identity;
        $subscription = UserSubscription::getActiveSubscription($user->id);
        
        return $this->render('success', [
            'subscription' => $subscription,
        ]);
    }

    /**
     * Calculate prorated upgrade cost
     */
    protected function calculateUpgradeProration($currentSubscription, $newPlan)
    {
        $currentPlan = $currentSubscription->plan;
        $today = new \DateTime();
        $periodEnd = new \DateTime($currentSubscription->end_date);
        
        // Calculate remaining days in current period
        $totalDays = 30; // Assume 30 days per month
        $remainingDays = max(0, $periodEnd->diff($today)->days);
        $usedDays = $totalDays - $remainingDays;
        
        // Calculate prorated amounts
        $currentPlanDailyRate = $currentPlan->price / $totalDays;
        $newPlanDailyRate = $newPlan->price / $totalDays;
        
        $remainingCurrentPlanValue = $remainingDays * $currentPlanDailyRate;
        $newPlanCost = $newPlan->price;
        $upgradeCharge = $newPlanCost - $remainingCurrentPlanValue;
        
        return [
            'current_plan' => $currentPlan,
            'new_plan' => $newPlan,
            'remaining_days' => $remainingDays,
            'used_days' => $usedDays,
            'remaining_value' => $remainingCurrentPlanValue,
            'new_plan_cost' => $newPlanCost,
            'upgrade_charge' => max(0, $upgradeCharge),
            'credit_applied' => $remainingCurrentPlanValue,
        ];
    }

    /**
     * Process direct upgrade (non-Stripe)
     */
    protected function processDirectUpgrade($currentSubscription, $newPlan, $upgradeData)
    {
        try {
            // Update subscription immediately
            $currentSubscription->plan_id = $newPlan->id;
            $currentSubscription->start_date = date('Y-m-d');
            $currentSubscription->end_date = date('Y-m-d', strtotime('+1 month'));
            $currentSubscription->next_billing_date = date('Y-m-d', strtotime('+1 month'));
            
            if ($currentSubscription->save()) {
                Yii::$app->session->setFlash('success', Yii::t('app', 'Plan upgraded successfully! Your new billing cycle starts today.'));
            } else {
                Yii::$app->session->setFlash('error', Yii::t('app', 'Failed to upgrade plan.'));
            }
            
            return $this->redirect(['my-account']);
            
        } catch (\Exception $e) {
            Yii::error('Direct upgrade error: ' . $e->getMessage(), 'subscription');
            throw $e;
        }
    }

    /**
     * Process Stripe subscription
     *
     * @param Plan $plan
     * @param User $user
     * @return Response|string
     */
    protected function processStripeSubscription($plan, $user)
    {
        // Get Stripe configuration
        $stripePublicKey = Yii::$app->params['stripe']['publishableKey'] ?? '';
        $stripeSecretKey = Yii::$app->params['stripe']['secretKey'] ?? '';
        
        if (empty($stripePublicKey) || empty($stripeSecretKey)) {
            Yii::$app->session->setFlash('error', Yii::t('app', 'Stripe payment is not configured. Please contact administrator.'));
            return $this->redirect(['index']);
        }

        // This method will be implemented with actual Stripe integration
        // For now, return a placeholder view
        return $this->render('stripe-checkout', [
            'plan' => $plan,
            'user' => $user,
            'stripePublicKey' => $stripePublicKey,
            'checkoutSession' => null, // Will be implemented later
        ]);
    }

    /**
     * Process PayPal subscription
     *
     * @param Plan $plan
     * @param User $user
     * @return Response|string
     */
    protected function processPayPalSubscription($plan, $user)
    {
        // Get PayPal configuration
        $paypalClientId = Yii::$app->params['paypal']['clientId'] ?? '';
        $paypalClientSecret = Yii::$app->params['paypal']['clientSecret'] ?? '';
        
        if (empty($paypalClientId) || empty($paypalClientSecret)) {
            Yii::$app->session->setFlash('error', Yii::t('app', 'PayPal payment is not configured. Please contact administrator.'));
            return $this->redirect(['index']);
        }

        // This method will be implemented with actual PayPal integration
        // For now, return a placeholder view
        return $this->render('paypal-checkout', [
            'plan' => $plan,
            'user' => $user,
            'paypalClientId' => $paypalClientId,
        ]);
    }

    /**
     * Process Stripe upgrade
     *
     * @param UserSubscription $subscription
     * @param Plan $newPlan
     * @return Response
     */
    protected function processStripeUpgrade($subscription, $newPlan, $upgradeData)
    {
        try {
            // Get Stripe configuration
            $stripeSecretKey = Yii::$app->params['stripe']['secretKey'] ?? '';
            \Stripe\Stripe::setApiKey($stripeSecretKey);

            // Create payment intent for the upgrade charge
            if ($upgradeData['upgrade_charge'] > 0) {
                $paymentIntent = \Stripe\PaymentIntent::create([
                    'amount' => (int)($upgradeData['upgrade_charge'] * 100), // Convert to cents
                    'currency' => 'usd',
                    'metadata' => [
                        'type' => 'upgrade',
                        'user_id' => $subscription->user_id,
                        'old_plan_id' => $subscription->plan_id,
                        'new_plan_id' => $newPlan->id,
                        'subscription_id' => $subscription->id,
                    ],
                    'description' => "Plan upgrade from {$subscription->plan->name} to {$newPlan->name}",
                ]);

                return $this->render('upgrade-payment', [
                    'currentSubscription' => $subscription,
                    'newPlan' => $newPlan,
                    'upgradeData' => $upgradeData,
                    'paymentIntent' => $paymentIntent,
                    'stripePublicKey' => Yii::$app->params['stripe']['publishableKey'] ?? '',
                ]);
            } else {
                // No additional charge needed, upgrade immediately
                return $this->processDirectUpgrade($subscription, $newPlan, $upgradeData);
            }

        } catch (\Exception $e) {
            Yii::error('Stripe upgrade error: ' . $e->getMessage(), 'subscription');
            throw $e;
        }
    }

    /**
     * Process PayPal upgrade
     *
     * @param UserSubscription $subscription
     * @param Plan $newPlan
     * @return Response
     */
    protected function processPayPalUpgrade($subscription, $newPlan)
    {
        // This will be implemented with actual PayPal API calls
        Yii::$app->session->setFlash('info', Yii::t('app', 'PayPal upgrade functionality will be implemented.'));
        return $this->redirect(['my-account']);
    }

    /**
     * Cancel Stripe subscription
     *
     * @param UserSubscription $subscription
     * @return bool
     */
    protected function cancelStripeSubscription($subscription)
    {
        // This will be implemented with actual Stripe API calls
        return $subscription->cancel();
    }

    /**
     * Cancel PayPal subscription
     *
     * @param UserSubscription $subscription
     * @return bool
     */
    protected function cancelPayPalSubscription($subscription)
    {
        // This will be implemented with actual PayPal API calls
        return $subscription->cancel();
    }

    /**
     * Process Stripe webhook
     *
     * @param string $payload
     * @param string $signature
     */
    protected function processStripeWebhook($payload, $signature)
    {
        // This will be implemented with actual Stripe webhook handling
        // Handle events like subscription updates, payments, cancellations, etc.
    }

    /**
     * Process PayPal webhook
     *
     * @param string $payload
     */
    protected function processPayPalWebhook($payload)
    {
        // This will be implemented with actual PayPal webhook handling
        // Handle events like subscription updates, payments, cancellations, etc.
    }
}