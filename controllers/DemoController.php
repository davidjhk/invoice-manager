<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

/**
 * DemoController handles demo user restrictions and actions
 */
class DemoController extends Controller
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
                            return Yii::$app->user->identity->isDemo();
                        }
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'reset-demo-data' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Demo dashboard with restrictions notice
     * @return mixed
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Reset demo data to initial state
     * @return Response
     */
    public function actionResetDemoData()
    {
        if (!Yii::$app->user->identity->isDemo()) {
            return $this->redirect(['site/index']);
        }

        // Reset demo user's data to initial state
        $user = Yii::$app->user->identity;
        
        // Delete all companies owned by demo user
        foreach ($user->companies as $company) {
            // Delete related invoices, estimates, customers, products
            $company->delete();
        }
        
        // Create a fresh demo company
        $company = new \app\models\Company();
        $company->company_name = 'Demo Company Ltd.';
        $company->company_address = '123 Demo Street, Demo City, DC 12345';
        $company->company_phone = '+1 (555) 123-4567';
        $company->company_email = 'demo@democompany.com';
        $company->sender_email = 'demo@democompany.com';
        $company->sender_name = 'Demo Company';
        $company->currency = 'USD';
        $company->tax_rate = 10.00;
        $company->invoice_prefix = 'DEMO';
        $company->estimate_prefix = 'DEMO-EST';
        $company->due_date_days = 30;
        $company->estimate_validity_days = 30;
        $company->user_id = $user->id;
        $company->save();
        
        // Set as current company
        Yii::$app->session->set('current_company_id', $company->id);
        
        Yii::$app->session->setFlash('success', 'Demo data has been reset successfully!');
        return $this->redirect(['site/index']);
    }
}