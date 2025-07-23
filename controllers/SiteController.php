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
                'only' => ['logout', 'index', 'check-auth',  'change-password', 'change-language', 'toggle-theme'],
                'rules' => [
                    [
                        'actions' => ['change-language'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['logout', 'index', 'check-auth', 'change-password', 'toggle-theme'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                    'toggle-theme' => ['post'],
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
            if (Yii::$app->user->identity->isDemo()) {
                return $this->redirect(['/demo/index']);
            }
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
        $redirectUri = Yii::$app->urlManager->createAbsoluteUrl(['site/google-login']);
        
        try {
            // Use cURL for HTTP requests (fallback when yii2-httpclient is not available)
            $tokenData = $this->makeHttpRequest('https://oauth2.googleapis.com/token', [
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'redirect_uri' => $redirectUri,
                'grant_type' => 'authorization_code',
                'code' => $code,
            ], 'POST');
            
            if (!$tokenData || !isset($tokenData['access_token'])) {
                throw new \Exception('Failed to exchange code for token');
            }
            
            $accessToken = $tokenData['access_token'];
            
            // Get user info from Google
            $googleUser = $this->makeHttpRequest('https://www.googleapis.com/oauth2/v2/userinfo', [], 'GET', [
                'Authorization: Bearer ' . $accessToken
            ]);
            
            if (!$googleUser || !isset($googleUser['email'])) {
                throw new \Exception('Failed to get user info from Google');
            }
            
            // Check if user exists by Google ID first
            $user = User::findByGoogleId($googleUser['id']);
            
            if (!$user) {
                // Check if user exists by email (existing user wants to use Google SSO)
                $user = User::findByEmail($googleUser['email']);
                
                if ($user) {
                    // Link Google account to existing user
                    $user->google_id = $googleUser['id'];
                    $user->avatar_url = $googleUser['picture'] ?? null;
                    $user->login_type = User::LOGIN_TYPE_GOOGLE;
                    if (!$user->updateFromGoogle($googleUser)) {
                        Yii::error('Failed to update existing user with Google profile: ' . json_encode($user->errors), 'app');
                    }
                } else {
                    // No existing user found - deny access
                    Yii::$app->session->setFlash('error', Yii::t('app', 'No account found with this email address. Please contact administrator to create an account first.'));
                    return $this->redirect(['site/login']);
                }
            } else {
                // Update existing Google user
                if (!$user->updateFromGoogle($googleUser)) {
                    Yii::error('Failed to update existing Google user: ' . json_encode($user->errors), 'app');
                }
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
            Yii::error('Google OAuth error: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString(), 'app');
            
            // Show more detailed error in development mode
            if (YII_ENV_DEV) {
                Yii::$app->session->setFlash('error', 'Google authentication failed: ' . $e->getMessage());
            } else {
                Yii::$app->session->setFlash('error', 'Google authentication failed. Please try again.');
            }
            return $this->redirect(['site/login']);
        }
    }

    /**
     * Make HTTP request using cURL
     *
     * @param string $url
     * @param array $data
     * @param string $method
     * @param array $headers
     * @return array|null
     */
    private function makeHttpRequest($url, $data = [], $method = 'GET', $headers = [])
    {
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_USERAGENT => 'Invoice Manager OAuth Client/1.0',
        ]);
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if (!empty($data)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            }
        }
        
        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            Yii::error('cURL error: ' . $error, 'app');
            return null;
        }
        
        if ($httpCode >= 400) {
            Yii::error('HTTP error: ' . $httpCode . ' | Response: ' . $response, 'app');
            return null;
        }
        
        $decoded = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            Yii::error('JSON decode error: ' . json_last_error_msg() . ' | Response: ' . $response, 'app');
            return null;
        }
        
        return $decoded;
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

    /**
     * Change language action
     * 
     * @param string $language
     * @return Response
     */
    public function actionChangeLanguage($language = 'en-US')
    {
        // Validate language
        $allowedLanguages = ['en-US', 'es-ES', 'ko-KR', 'zh-CN', 'zh-TW'];
        if (!in_array($language, $allowedLanguages)) {
            $language = 'en-US';
        }
        
        // Set session language
        Yii::$app->session->set('language', $language);
        
        // Update user's company language preference if logged in
        if (!Yii::$app->user->isGuest) {
            $user = Yii::$app->user->identity;
            $companyId = Yii::$app->session->get('current_company_id');
            
            if ($companyId) {
                $company = Company::findOne(['id' => $companyId, 'user_id' => $user->id]);
                if ($company) {
                    $company->language = $language;
                    $company->save(false, ['language']);
                }
            }
        }
        
        // Set application language
        Yii::$app->language = $language;
        
        // Return JSON response for AJAX requests
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['success' => true, 'language' => $language];
        }
        
        // Redirect back to previous page or home
        $referrer = Yii::$app->request->referrer;
        if ($referrer && strpos($referrer, Yii::$app->request->hostInfo) === 0) {
            return $this->redirect($referrer);
        }
        
        return $this->goHome();
    }

    /**
     * Toggle theme action.
     *
     * @return Response|array
     */
    public function actionToggleTheme()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (Yii::$app->user->isGuest) {
            return ['success' => false, 'message' => 'User not logged in'];
        }

        $mode = Yii::$app->request->post('mode', 'light');
        $darkMode = ($mode === 'dark') ? 1 : 0;

        $user = Yii::$app->user->identity;
        $companyId = Yii::$app->session->get('current_company_id');

        if (!$companyId) {
            return ['success' => false, 'message' => 'No company selected'];
        }

        $company = Company::findOne(['id' => $companyId, 'user_id' => $user->id]);
        if (!$company) {
            return ['success' => false, 'message' => 'Company not found'];
        }

        $company->dark_mode = $darkMode;
        if ($company->save(false, ['dark_mode'])) {
            return ['success' => true, 'mode' => $mode, 'dark_mode' => $darkMode];
        } else {
            return ['success' => false, 'message' => 'Failed to save theme preference'];
        }
    }
}