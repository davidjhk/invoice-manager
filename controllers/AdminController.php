<?php

namespace app\controllers;

use Yii;
use app\models\User;
use app\models\AdminSettings;
use app\models\ChangePasswordForm;
use app\models\Plan;
use app\models\UserSubscription;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\data\ActiveDataProvider;
use yii\web\Response;

/**
 * AdminController implements the CRUD actions for admin functions.
 */
class AdminController extends Controller
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
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            return Yii::$app->user->identity->isAdmin();
                        }
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete-user' => ['POST'],
                    'toggle-user-status' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Admin dashboard
     * @return mixed
     */
    public function actionIndex()
    {
        $stats = [
            'totalUsers' => User::find()->count(),
            'activeUsers' => User::find()->where(['is_active' => 1])->count(),
            'inactiveUsers' => User::find()->where(['is_active' => 0])->count(),
            'adminUsers' => User::find()->where(['role' => 'admin'])->count(),
            'planCount' => Plan::find()->count(),
            'activeSubscriptions' => UserSubscription::find()->where(['status' => 'active'])->count(),
        ];

        return $this->render('index', [
            'stats' => $stats,
        ]);
    }

    /**
     * Admin settings management
     * @return mixed
     */
    public function actionSettings()
    {
        // Check if admin settings table exists
        if (!$this->isAdminSettingsTableExists()) {
            Yii::$app->session->setFlash('error', 'Admin settings table is missing. Please run the migration: "php yii migrate"');
            
            return $this->render('settings-error', [
                'error' => 'Admin settings table does not exist. Please run migrations to create the table.',
                'suggestions' => [
                    'Run migration command: php yii migrate',
                    'Check database connection in config/db-local.php',
                    'Verify table prefix settings in database configuration',
                ]
            ]);
        }

        try {
            $settings = AdminSettings::find()->all();
            
            if (Yii::$app->request->isPost) {
                $post = Yii::$app->request->post();
                
                foreach ($settings as $setting) {
                    // Handle checkbox settings (allow_signup, site_maintenance, email_notifications, backup_enabled)
                    if (in_array($setting->setting_key, ['allow_signup', 'site_maintenance', 'email_notifications', 'backup_enabled'])) {
                        // For checkboxes, set to 1 if checked, 0 if not checked
                        $setting->setting_value = isset($post[$setting->setting_key]) ? '1' : '0';
                        $setting->save();
                    } elseif (isset($post[$setting->setting_key])) {
                        // For other settings, only update if present in POST
                        $setting->setting_value = $post[$setting->setting_key];
                        $setting->save();
                    }
                }
                
                // Handle AI model setting separately
                if (isset($post['ai_model'])) {
                    AdminSettings::setAiModel($post['ai_model']);
                }
                
                Yii::$app->session->setFlash('success', 'Settings updated successfully.');
                return $this->redirect(['settings']);
            }

            return $this->render('settings', [
                'settings' => $settings,
                'aiModels' => AdminSettings::getAvailableAiModels(),
                'currentAiModel' => AdminSettings::getAiModel(),
            ]);
        } catch (\Exception $e) {
            // Log the specific error for debugging
            Yii::error('Admin settings error: ' . $e->getMessage(), __METHOD__);
            
            Yii::$app->session->setFlash('error', 'Error accessing admin settings: ' . $e->getMessage());
            
            return $this->render('settings-error', [
                'error' => $e->getMessage(),
                'suggestions' => [
                    'Check database connection',
                    'Verify admin_settings table exists with correct name',
                    'Run migrations if needed: php yii migrate',
                ]
            ]);
        }
    }

    /**
     * User management
     * @return mixed
     */
    public function actionUsers()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => User::find()->orderBy(['created_at' => SORT_DESC]),
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $this->render('users', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Create a new user
     * @return mixed
     */
    public function actionCreateUser()
    {
        $model = new User();
        $model->scenario = 'create';

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $model->setPassword($model->password);
            $model->generateAuthKey();
            $model->is_active = 1;
            $model->created_at = date('Y-m-d H:i:s');
            
            if ($model->save(false)) {
                Yii::$app->session->setFlash('success', 'User created successfully.');
                return $this->redirect(['users']);
            }
        }

        return $this->render('create-user', [
            'model' => $model,
        ]);
    }

    /**
     * Update user
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdateUser($id)
    {
        $model = $this->findUser($id);
        $model->scenario = 'update';

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if (!empty($model->password)) {
                $model->setPassword($model->password);
            }
            
            if ($model->save(false)) {
                Yii::$app->session->setFlash('success', 'User updated successfully.');
                return $this->redirect(['users']);
            }
        }

        // Clear password field for security
        $model->password = '';

        return $this->render('update-user', [
            'model' => $model,
        ]);
    }

    /**
     * Delete user
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDeleteUser($id)
    {
        $model = $this->findUser($id);
        
        // Prevent deleting yourself
        if ($model->id === Yii::$app->user->id) {
            Yii::$app->session->setFlash('error', 'You cannot delete your own account.');
            return $this->redirect(['users']);
        }
        
        $model->delete();
        Yii::$app->session->setFlash('success', 'User deleted successfully.');
        
        return $this->redirect(['users']);
    }

    /**
     * Toggle user active status
     * @param integer $id
     * @return Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionToggleUserStatus($id)
    {
        $model = $this->findUser($id);
        
        // Prevent deactivating yourself
        if ($model->id === Yii::$app->user->id) {
            Yii::$app->session->setFlash('error', 'You cannot deactivate your own account.');
            return $this->redirect(['users']);
        }
        
        $model->is_active = $model->is_active ? 0 : 1;
        $model->save(false);
        
        $status = $model->is_active ? 'activated' : 'deactivated';
        Yii::$app->session->setFlash('success', "User {$status} successfully.");
        
        return $this->redirect(['users']);
    }

    /**
     * Reset user password
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionResetUserPassword($id)
    {
        $model = $this->findUser($id);
        
        if (Yii::$app->request->isPost) {
            $newPassword = Yii::$app->request->post('newPassword');
            $confirmPassword = Yii::$app->request->post('confirmPassword');
            
            if (empty($newPassword) || strlen($newPassword) < 6) {
                Yii::$app->session->setFlash('error', 'Password must be at least 6 characters long.');
            } elseif ($newPassword !== $confirmPassword) {
                Yii::$app->session->setFlash('error', 'Passwords do not match.');
            } else {
                $model->setPassword($newPassword);
                $model->removePasswordResetToken();
                
                if ($model->save(false)) {
                    Yii::$app->session->setFlash('success', 'Password reset successfully.');
                    return $this->redirect(['users']);
                }
            }
        }

        return $this->render('reset-user-password', [
            'model' => $model,
        ]);
    }

    /**
     * Check if admin settings table exists
     * @return bool
     */
    protected function isAdminSettingsTableExists()
    {
        try {
            $tableName = AdminSettings::tableName();
            
            // Remove Yii2 table name wrapper if present
            if (strpos($tableName, '{{%') === 0) {
                $tableName = str_replace(['{{%', '}}'], '', $tableName);
                // Apply table prefix if configured
                $tablePrefix = Yii::$app->db->tablePrefix;
                if ($tablePrefix) {
                    $tableName = $tablePrefix . $tableName;
                }
            }
            
            // Check if table exists in database schema
            $schema = Yii::$app->db->schema->getTableSchema($tableName, true);
            return $schema !== null;
        } catch (\Exception $e) {
            // If any error occurs during table check, assume table doesn't exist
            Yii::error('Error checking admin settings table existence: ' . $e->getMessage(), __METHOD__);
            return false;
        }
    }

    /**
     * Finds the User model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return User the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findUser($id)
    {
        if (($model = User::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested user does not exist.');
    }
}