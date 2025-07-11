<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\SignupForm;
use app\models\PasswordResetRequestForm;
use app\models\ResetPasswordForm;
use app\models\ChangePasswordForm;
use app\models\User;
use app\models\Company;
use app\models\AdminSettings;
use yii\base\InvalidArgumentException;
use yii\web\BadRequestHttpException;
use yii\httpclient\Client;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout', 'index', 'check-auth', 'invoice-app', 'change-password'],
                'rules' => [
                    [
                        'actions' => ['logout', 'index', 'check-auth', 'invoice-app', 'change-password'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Signup action.
     *
     * @return Response|string
     */
    public function actionSignup()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }
        
        // Check if signup is allowed
        if (!AdminSettings::isSignupAllowed()) {
            Yii::$app->session->setFlash('error', 'New user registration is currently disabled.');
            return $this->redirect(['site/login']);
        }

        $model = new SignupForm();
        if ($model->load(Yii::$app->request->post())) {
            $user = $model->signup();
            if ($user) {
                Yii::$app->user->login($user);
                Yii::$app->session->setFlash('success', 'Account created successfully! Welcome to ' . (Yii::$app->params['siteName'] ?? 'Invoice Manager') . '.');
                return $this->goHome();
            } else {
                Yii::$app->session->setFlash('error', 'Failed to create account. Please check the errors below.');
            }
        }

        return $this->render('signup', [
            'model' => $model,
        ]);
    }

    /**
     * Requests password reset.
     *
     * @return Response|string
     */
    public function actionRequestPasswordReset()
    {
        $model = new PasswordResetRequestForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->session->setFlash('success', 'Check your email for further instructions.');
                return $this->goHome();
            } else {
                Yii::$app->session->setFlash('error', 'Sorry, we are unable to reset password for the provided email address.');
            }
        }

        return $this->render('requestPasswordResetToken', [
            'model' => $model,
        ]);
    }

    /**
     * Resets password.
     *
     * @param string $token
     * @return Response|string
     * @throws BadRequestHttpException
     */
    public function actionResetPassword($token)
    {
        try {
            $model = new ResetPasswordForm($token);
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->resetPassword()) {
            Yii::$app->session->setFlash('success', 'New password saved.');
            return $this->redirect(['site/login']);
        }

        return $this->render('resetPassword', [
            'model' => $model,
        ]);
    }

    /**
     * Invoice app action.
     *
     * @return string
     */
    public function actionInvoiceApp()
    {
        $this->layout = false;
        return $this->render('invoice-app');
    }

    /**
     * Check authentication status for AJAX requests.
     *
     * @return Response
     */
    public function actionCheckAuth()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return [
            'authenticated' => !Yii::$app->user->isGuest,
            'user' => Yii::$app->user->isGuest ? null : [
                'id' => Yii::$app->user->id,
                'username' => Yii::$app->user->identity->username,
            ]
        ];
    }

    /**
     * Google Login action
     *
     * @return Response
     */
    public function actionGoogleLogin()
    {
        $clientId = Yii::$app->params['googleClientId'] ?? null;
        $clientSecret = Yii::$app->params['googleClientSecret'] ?? null;
        
        if (!$clientId || !$clientSecret) {
            Yii::$app->session->setFlash('error', 'Google authentication is not configured.');
            return $this->redirect(['site/login']);
        }

        $code = Yii::$app->request->get('code');
        $state = Yii::$app->request->get('state');
        $error = Yii::$app->request->get('error');

        if ($error) {
            Yii::$app->session->setFlash('error', 'Google authentication failed: ' . $error);
            return $this->redirect(['site/login']);
        }

        if (!$code) {
            // Redirect to Google OAuth
            $redirectUri = Yii::$app->urlManager->createAbsoluteUrl(['site/google-login']);
            $scope = 'openid email profile';
            $state = Yii::$app->security->generateRandomString(32);
            Yii::$app->session->set('google_oauth_state', $state);
            
            $googleAuthUrl = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
                'client_id' => $clientId,
                'redirect_uri' => $redirectUri,
                'scope' => $scope,
                'response_type' => 'code',
                'state' => $state,
                'access_type' => 'offline',
                'prompt' => 'select_account'
            ]);
            
            return $this->redirect($googleAuthUrl);
        }

        // Verify state parameter
        $savedState = Yii::$app->session->get('google_oauth_state');
        if (!$savedState || $savedState !== $state) {
            Yii::$app->session->setFlash('error', 'Invalid state parameter.');
            return $this->redirect(['site/login']);
        }

        // Exchange code for access token
        $client = new Client();
        $redirectUri = Yii::$app->urlManager->createAbsoluteUrl(['site/google-login']);
        
        try {
            $response = $client->createRequest()
                ->setMethod('POST')
                ->setUrl('https://oauth2.googleapis.com/token')
                ->setData([
                    'client_id' => $clientId,
                    'client_secret' => $clientSecret,
                    'redirect_uri' => $redirectUri,
                    'grant_type' => 'authorization_code',
                    'code' => $code,
                ])
                ->send();
            
            if (!$response->isOk) {
                throw new \Exception('Failed to exchange code for token');
            }
            
            $tokenData = $response->data;
            $accessToken = $tokenData['access_token'];
            
            // Get user info from Google
            $userResponse = $client->createRequest()
                ->setMethod('GET')
                ->setUrl('https://www.googleapis.com/oauth2/v2/userinfo')
                ->addHeaders(['Authorization' => 'Bearer ' . $accessToken])
                ->send();
            
            if (!$userResponse->isOk) {
                throw new \Exception('Failed to get user info from Google');
            }
            
            $googleUser = $userResponse->data;
            
            // Check if user exists
            $user = User::findByGoogleId($googleUser['id']);
            if (!$user) {
                // Check if user exists by email
                $user = User::findByEmail($googleUser['email']);
                if ($user) {
                    // Link Google account to existing user
                    $user->google_id = $googleUser['id'];
                    $user->avatar_url = $googleUser['picture'] ?? null;
                    $user->updateFromGoogle($googleUser);
                } else {
                    // Create new user
                    $user = User::createFromGoogle($googleUser);
                    if ($user) {
                        // Create a default company for the user
                        $company = new Company();
                        $company->company_name = ($googleUser['name'] ?? 'User') . "'s Company";
                        $company->company_email = $googleUser['email'];
                        $company->sender_email = $googleUser['email'];
                        $company->sender_name = $googleUser['name'] ?? 'User';
                        $company->user_id = $user->id;
                        $company->save();
                    }
                }
            } else {
                // Update existing Google user
                $user->updateFromGoogle($googleUser);
            }
            
            if ($user) {
                // Login user
                Yii::$app->user->login($user, 3600 * 24 * 30); // 30 days
                Yii::$app->session->setFlash('success', 'Welcome back, ' . $user->getDisplayName() . '!');
                return $this->goHome();
            } else {
                Yii::$app->session->setFlash('error', 'Failed to create or update user account.');
                return $this->redirect(['site/login']);
            }
            
        } catch (\Exception $e) {
            Yii::error('Google OAuth error: ' . $e->getMessage(), 'app');
            Yii::$app->session->setFlash('error', 'Google authentication failed. Please try again.');
            return $this->redirect(['site/login']);
        }
    }

    /**
     * Change password action.
     *
     * @return Response|string
     */
    public function actionChangePassword()
    {
        $model = new ChangePasswordForm(Yii::$app->user->identity);
        
        if ($model->load(Yii::$app->request->post()) && $model->changePassword()) {
            Yii::$app->session->setFlash('success', 'Password changed successfully.');
            return $this->redirect(['site/index']);
        }
        
        return $this->render('change-password', [
            'model' => $model,
        ]);
    }
}