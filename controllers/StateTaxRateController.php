<?php

namespace app\controllers;

use Yii;
use app\models\StateTaxRate;
use app\models\State;
use app\models\Country;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\Response;
use yii\helpers\Json;

/**
 * StateTaxRateController implements the CRUD actions for StateTaxRate model.
 */
class StateTaxRateController extends Controller
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
                            // Only allow admin users
                            return Yii::$app->user->identity && Yii::$app->user->identity->role === 'admin';
                        }
                    ],
                ],
                'denyCallback' => function () {
                    throw new \yii\web\ForbiddenHttpException(Yii::t('app', 'You are not allowed to access this page.'));
                }
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all StateTaxRate models.
     * @return mixed
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => StateTaxRate::find()->with(['state', 'country'])->orderBy(['country_code' => SORT_ASC, 'state_code' => SORT_ASC, 'effective_date' => SORT_DESC]),
            'pagination' => [
                'pageSize' => 50,
            ],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single StateTaxRate model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new StateTaxRate model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new StateTaxRate();
        $model->effective_date = date('Y-m-d');
        $model->is_active = true;
        $model->country_code = 'US';

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', Yii::t('app', 'State tax rate has been created successfully.'));
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
            'states' => $this->getStatesList(),
            'countries' => $this->getCountriesList(),
        ]);
    }

    /**
     * Updates an existing StateTaxRate model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', Yii::t('app', 'State tax rate has been updated successfully.'));
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
            'states' => $this->getStatesList(),
            'countries' => $this->getCountriesList(),
        ]);
    }

    /**
     * Deletes an existing StateTaxRate model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $model->delete();
        
        Yii::$app->session->setFlash('success', Yii::t('app', 'State tax rate has been deleted successfully.'));
        return $this->redirect(['index']);
    }

    /**
     * Toggle active status of a StateTaxRate model.
     * @param integer $id
     * @return Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionToggleActive($id)
    {
        $model = $this->findModel($id);
        $model->is_active = !$model->is_active;
        
        if ($model->save()) {
            $status = $model->is_active ? Yii::t('app', 'activated') : Yii::t('app', 'deactivated');
            Yii::$app->session->setFlash('success', Yii::t('app', 'State tax rate has been {status}.', ['status' => $status]));
        } else {
            Yii::$app->session->setFlash('error', Yii::t('app', 'Failed to update state tax rate status.'));
        }
        
        return $this->redirect(['index']);
    }

    /**
     * Bulk import tax rates from CSV or predefined data.
     * @return Response
     */
    public function actionBulkImport()
    {
        if (Yii::$app->request->isPost) {
            $importType = Yii::$app->request->post('import_type', 'default');
            
            if ($importType === 'default') {
                $this->importDefaultRates();
                Yii::$app->session->setFlash('success', Yii::t('app', 'Default US tax rates have been imported successfully.'));
            }
            
            return $this->redirect(['index']);
        }

        return $this->render('bulk-import');
    }

    /**
     * Get states by country (AJAX endpoint)
     * @param string $country_code
     * @return Response
     */
    public function actionGetStates($country_code = 'US')
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        $states = State::find()
            ->where(['country_code' => $country_code])
            ->orderBy(['state_name' => SORT_ASC])
            ->all();
        
        $result = [];
        foreach ($states as $state) {
            $result[] = [
                'value' => $state->state_code,
                'text' => $state->state_name . ' (' . $state->state_code . ')'
            ];
        }
        
        return $result;
    }

    /**
     * Finds the StateTaxRate model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return StateTaxRate the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = StateTaxRate::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }

    /**
     * Get list of states for dropdown
     * @return array
     */
    protected function getStatesList()
    {
        return State::find()
            ->select(['CONCAT(state_name, " (", state_code, ")") as name', 'state_code', 'country_code'])
            ->orderBy(['country_code' => SORT_ASC, 'state_name' => SORT_ASC])
            ->indexBy('state_code')
            ->column('name');
    }

    /**
     * Get list of countries for dropdown
     * @return array
     */
    protected function getCountriesList()
    {
        return Country::find()
            ->select(['country_name', 'country_code'])
            ->orderBy(['country_name' => SORT_ASC])
            ->indexBy('country_code')
            ->column('country_name');
    }

    /**
     * Import default US tax rates
     */
    protected function importDefaultRates()
    {
        $defaultRates = [
            ['AL', 4.00, true, 9.24, 250000, null],        // Alabama
            ['AK', 0.00, true, 1.76, 100000, null],        // Alaska
            ['AZ', 5.60, true, 8.40, 100000, null],        // Arizona  
            ['AR', 6.50, true, 9.47, 100000, 200],         // Arkansas
            ['CA', 6.00, true, 8.85, 500000, null],        // California
            ['CO', 2.90, true, 7.86, 100000, null],        // Colorado
            ['CT', 6.35, false, 6.35, 100000, 200],        // Connecticut
            ['DE', 0.00, false, 0.00, null, null],         // Delaware - No sales tax
            ['FL', 6.00, true, 7.02, 100000, null],        // Florida
            ['GA', 4.00, true, 7.31, 100000, 200],         // Georgia
            ['HI', 4.00, true, 4.44, 100000, 200],         // Hawaii
            ['ID', 6.00, true, 6.03, 100000, null],        // Idaho
            ['IL', 6.25, true, 8.64, 100000, 200],         // Illinois
            ['IN', 7.00, false, 7.00, 100000, 200],        // Indiana
            ['IA', 6.00, true, 6.94, 100000, 200],         // Iowa
            ['KS', 6.50, true, 8.68, 100000, null],        // Kansas
            ['KY', 6.00, false, 6.00, 100000, 200],        // Kentucky
            ['LA', 4.45, true, 9.56, 100000, 200],         // Louisiana
            ['ME', 5.50, false, 5.50, 100000, 200],        // Maine
            ['MD', 6.00, false, 6.00, 100000, 200],        // Maryland
            ['MA', 6.25, false, 6.25, 100000, null],       // Massachusetts
            ['MI', 6.00, false, 6.00, 100000, 200],        // Michigan
            ['MN', 6.88, true, 7.46, 100000, 200],         // Minnesota
            ['MS', 7.00, true, 7.07, 250000, null],        // Mississippi
            ['MO', 4.23, true, 8.30, 100000, null],        // Missouri
            ['MT', 0.00, false, 0.00, null, null],         // Montana - No sales tax
            ['NE', 5.50, true, 6.94, 100000, 200],         // Nebraska
            ['NV', 4.60, true, 8.23, 100000, 200],         // Nevada
            ['NH', 0.00, false, 0.00, null, null],         // New Hampshire - No sales tax
            ['NJ', 6.63, false, 6.63, 100000, 200],        // New Jersey
            ['NM', 5.13, true, 7.69, 100000, null],        // New Mexico
            ['NY', 4.00, true, 8.54, 500000, 100],         // New York
            ['NC', 4.75, true, 6.98, 100000, 200],         // North Carolina
            ['ND', 5.00, true, 6.86, 100000, null],        // North Dakota
            ['OH', 5.75, true, 7.26, 100000, 200],         // Ohio
            ['OK', 4.50, true, 9.05, 100000, null],        // Oklahoma
            ['OR', 0.00, false, 0.00, null, null],         // Oregon - No sales tax
            ['PA', 6.00, true, 6.34, 100000, null],        // Pennsylvania
            ['RI', 7.00, false, 7.00, 100000, 200],        // Rhode Island
            ['SC', 6.00, true, 7.46, 100000, null],        // South Carolina
            ['SD', 4.20, true, 6.40, 100000, 200],         // South Dakota
            ['TN', 7.00, true, 9.55, 100000, null],        // Tennessee
            ['TX', 6.25, true, 8.20, 500000, null],        // Texas
            ['UT', 4.85, true, 7.10, 100000, 200],         // Utah
            ['VT', 6.00, true, 6.24, 100000, 200],         // Vermont
            ['VA', 4.30, true, 5.75, 100000, 200],         // Virginia
            ['WA', 6.50, true, 9.23, 100000, null],        // Washington
            ['WV', 6.00, true, 6.48, 100000, 200],         // West Virginia
            ['WI', 5.00, true, 5.44, 100000, null],        // Wisconsin
            ['WY', 4.00, true, 5.36, 100000, 200],         // Wyoming
        ];

        $effectiveDate = date('Y-m-d');
        $imported = 0;

        foreach ($defaultRates as $rate) {
            // Check if rate already exists for this state/country/date
            $existing = StateTaxRate::find()
                ->where([
                    'state_code' => $rate[0],
                    'country_code' => 'US',
                    'effective_date' => $effectiveDate
                ])
                ->exists();

            if (!$existing) {
                $model = new StateTaxRate();
                $model->state_code = $rate[0];
                $model->country_code = 'US';
                $model->base_rate = $rate[1];
                $model->has_local_tax = $rate[2];
                $model->average_total_rate = $rate[3];
                $model->revenue_threshold = $rate[4];
                $model->transaction_threshold = $rate[5];
                $model->is_active = true;
                $model->effective_date = $effectiveDate;
                $model->notes = 'Imported default 2025 US state tax rates';
                
                if ($model->save()) {
                    $imported++;
                }
            }
        }

        return $imported;
    }
}