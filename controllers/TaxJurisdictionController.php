<?php

namespace app\controllers;

use Yii;
use app\models\TaxJurisdiction;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\UploadedFile;
use yii\web\Response;

/**
 * TaxJurisdictionController implements the CRUD actions for TaxJurisdiction model.
 */
class TaxJurisdictionController extends Controller
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
                    'delete' => ['POST'],
                    'bulk-operation' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all TaxJurisdiction models.
     * @return mixed
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => TaxJurisdiction::find()->orderBy(['state_code' => SORT_ASC, 'zip_code' => SORT_ASC]),
            'pagination' => [
                'pageSize' => 50,
            ],
        ]);

        // Get filter parameters
        $stateCode = Yii::$app->request->get('state_code');
        $zipCode = Yii::$app->request->get('zip_code');
        $dataSource = Yii::$app->request->get('data_source');
        $activeOnly = Yii::$app->request->get('active_only', 1);

        $query = $dataProvider->query;

        if ($stateCode) {
            $query->andWhere(['state_code' => $stateCode]);
        }

        if ($zipCode) {
            $query->andWhere(['like', 'zip_code', $zipCode]);
        }

        if ($dataSource) {
            $query->andWhere(['data_source' => $dataSource]);
        }

        if ($activeOnly) {
            $query->andWhere(['is_active' => true]);
        }

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'stateCode' => $stateCode,
            'zipCode' => $zipCode,
            'dataSource' => $dataSource,
            'activeOnly' => $activeOnly,
        ]);
    }

    /**
     * Displays a single TaxJurisdiction model.
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
     * Creates a new TaxJurisdiction model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new TaxJurisdiction();
        $model->data_source = TaxJurisdiction::DATA_SOURCE_MANUAL;
        $model->effective_date = date('Y-m-d');
        $model->data_year = date('Y');
        $model->data_month = date('n');
        $model->is_active = true;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->addFlash('success', 'Tax jurisdiction created successfully.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing TaxJurisdiction model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->addFlash('success', 'Tax jurisdiction updated successfully.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing TaxJurisdiction model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        Yii::$app->session->addFlash('success', 'Tax jurisdiction deleted successfully.');

        return $this->redirect(['index']);
    }

    /**
     * Import tax jurisdictions from CSV file
     * @return mixed
     */
    public function actionImportCsv()
    {
        if (Yii::$app->request->isPost) {
            $uploadedFile = UploadedFile::getInstanceByName('csv_file');
            $dataSource = Yii::$app->request->post('data_source', TaxJurisdiction::DATA_SOURCE_IMPORT);
            $replaceExisting = Yii::$app->request->post('replace_existing', false);

            if ($uploadedFile) {
                $tempPath = $uploadedFile->tempName;
                
                try {
                    $csvData = [];
                    $handle = fopen($tempPath, 'r');
                    
                    if (!$handle) {
                        throw new \Exception('Could not open CSV file for reading');
                    }
                    
                    $headers = fgetcsv($handle, 0, ',', '"', '\\'); // Read header row
                    
                    if (!$headers) {
                        fclose($handle);
                        throw new \Exception('Invalid CSV file format - no headers found');
                    }


                    $rowCount = 0;
                    $skippedRows = 0;
                    while (($row = fgetcsv($handle, 0, ',', '"', '\\')) !== false && $rowCount < 10000) { // Limit to 10k rows for safety
                        if (count($row) === count($headers)) {
                            $csvData[] = array_combine($headers, $row);
                            $rowCount++;
                        } else {
                            $skippedRows++;
                        }
                    }
                    fclose($handle);

                    if ($skippedRows > 0) {
                        Yii::$app->session->addFlash('warning', "Skipped $skippedRows rows due to column count mismatch.");
                    }

                    if (empty($csvData)) {
                        throw new \Exception("No valid data rows found in CSV file. Total rows processed: $rowCount, Skipped: $skippedRows");
                    }

                    // If replacing existing, deactivate old rates from this source
                    if ($replaceExisting) {
                        TaxJurisdiction::updateAll(
                            ['is_active' => false], 
                            ['data_source' => $dataSource]
                        );
                    }

                    $results = TaxJurisdiction::importFromCsv($csvData, $dataSource);

                    $message = "Import completed! Imported: {$results['imported']}, Updated: {$results['updated']}";
                    if (!empty($results['errors'])) {
                        $message .= ". Errors: " . count($results['errors']);
                        Yii::$app->session->addFlash('warning', 'Some errors occurred during import. Check the error log.');
                    }

                    Yii::$app->session->addFlash('success', $message);

                } catch (\Exception $e) {
                    Yii::$app->session->addFlash('error', 'Import failed: ' . $e->getMessage());
                }
            } else {
                Yii::$app->session->addFlash('error', 'Please select a CSV file to upload.');
            }

            return $this->redirect(['index']);
        }

        return $this->render('import-csv');
    }

    /**
     * Export tax jurisdictions to CSV
     * @return Response
     */
    public function actionExportCsv()
    {
        $stateCode = Yii::$app->request->get('state_code');
        $activeOnly = Yii::$app->request->get('active_only', 1);

        $query = TaxJurisdiction::find()->orderBy(['state_code' => SORT_ASC, 'zip_code' => SORT_ASC]);

        if ($stateCode) {
            $query->andWhere(['state_code' => $stateCode]);
        }

        if ($activeOnly) {
            $query->andWhere(['is_active' => true]);
        }

        $jurisdictions = $query->all();

        $filename = 'tax_jurisdictions_' . date('Y-m-d_H-i-s') . '.csv';

        Yii::$app->response->format = Response::FORMAT_RAW;
        Yii::$app->response->headers->add('Content-Type', 'text/csv');
        Yii::$app->response->headers->add('Content-Disposition', "attachment; filename=\"{$filename}\"");

        $output = fopen('php://output', 'w');

        // Write header row
        fputcsv($output, [
            'zip_code', 'state_code', 'state_name', 'county_name', 'city_name', 
            'tax_region_name', 'combined_rate', 'state_rate', 'county_rate', 
            'city_rate', 'special_rate', 'estimated_population', 'effective_date',
            'data_source', 'is_active'
        ]);

        // Write data rows
        foreach ($jurisdictions as $jurisdiction) {
            fputcsv($output, [
                $jurisdiction->zip_code,
                $jurisdiction->state_code,
                $jurisdiction->state_name,
                $jurisdiction->county_name,
                $jurisdiction->city_name,
                $jurisdiction->tax_region_name,
                $jurisdiction->combined_rate,
                $jurisdiction->state_rate,
                $jurisdiction->county_rate,
                $jurisdiction->city_rate,
                $jurisdiction->special_rate,
                $jurisdiction->estimated_population,
                $jurisdiction->effective_date,
                $jurisdiction->data_source,
                $jurisdiction->is_active ? 'Y' : 'N',
            ]);
        }

        fclose($output);
        return Yii::$app->response;
    }

    /**
     * Lookup tax rate for a ZIP code (AJAX endpoint)
     * @param string $zipCode
     * @return Response
     */
    public function actionLookup($zipCode)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $jurisdiction = TaxJurisdiction::findByZipCode($zipCode);

        if ($jurisdiction) {
            return [
                'success' => true,
                'data' => [
                    'zip_code' => $jurisdiction->zip_code,
                    'state_code' => $jurisdiction->state_code,
                    'state_name' => $jurisdiction->state_name,
                    'county_name' => $jurisdiction->county_name,
                    'city_name' => $jurisdiction->city_name,
                    'tax_region_name' => $jurisdiction->tax_region_name,
                    'state_rate' => $jurisdiction->state_rate,
                    'county_rate' => $jurisdiction->county_rate,
                    'city_rate' => $jurisdiction->city_rate,
                    'special_rate' => $jurisdiction->special_rate,
                    'combined_rate' => $jurisdiction->combined_rate,
                    'effective_date' => $jurisdiction->effective_date,
                    'data_source' => $jurisdiction->data_source,
                ]
            ];
        } else {
            return [
                'success' => false,
                'message' => 'No tax jurisdiction found for ZIP code: ' . $zipCode
            ];
        }
    }

    /**
     * Statistics dashboard
     * @return mixed
     */
    public function actionStats()
    {
        $stats = TaxJurisdiction::getStatistics();

        // Get breakdown by state
        $stateBreakdown = TaxJurisdiction::find()
            ->select(['state_code', 'COUNT(*) as count'])
            ->where(['is_active' => true])
            ->groupBy('state_code')
            ->orderBy(['count' => SORT_DESC])
            ->asArray()
            ->all();

        // Get breakdown by data source
        $sourceBreakdown = TaxJurisdiction::find()
            ->select(['data_source', 'COUNT(*) as count'])
            ->where(['is_active' => true])
            ->groupBy('data_source')
            ->asArray()
            ->all();

        // Get recent updates
        $recentUpdates = TaxJurisdiction::find()
            ->where(['is_active' => true])
            ->orderBy(['updated_at' => SORT_DESC])
            ->limit(10)
            ->all();

        return $this->render('stats', [
            'stats' => $stats,
            'stateBreakdown' => $stateBreakdown,
            'sourceBreakdown' => $sourceBreakdown,
            'recentUpdates' => $recentUpdates,
        ]);
    }

    /**
     * Bulk operations
     * @return mixed
     */
    public function actionBulkOperation()
    {
        if (Yii::$app->request->isPost) {
            $operation = Yii::$app->request->post('operation');
            $ids = Yii::$app->request->post('ids', []);

            if (empty($ids)) {
                Yii::$app->session->addFlash('error', 'Please select at least one jurisdiction.');
                return $this->redirect(['index']);
            }

            $count = 0;
            switch ($operation) {
                case 'activate':
                    $count = TaxJurisdiction::updateAll(['is_active' => true], ['id' => $ids]);
                    Yii::$app->session->addFlash('success', "Activated {$count} jurisdictions.");
                    break;

                case 'deactivate':
                    $count = TaxJurisdiction::updateAll(['is_active' => false], ['id' => $ids]);
                    Yii::$app->session->addFlash('success', "Deactivated {$count} jurisdictions.");
                    break;

                case 'delete':
                    $count = TaxJurisdiction::deleteAll(['id' => $ids]);
                    Yii::$app->session->addFlash('success', "Deleted {$count} jurisdictions.");
                    break;

                case 'verify':
                    $count = TaxJurisdiction::updateAll(['last_verified' => date('Y-m-d')], ['id' => $ids]);
                    Yii::$app->session->addFlash('success', "Verified {$count} jurisdictions.");
                    break;

                default:
                    Yii::$app->session->addFlash('error', 'Invalid operation selected.');
            }
        }

        return $this->redirect(['index']);
    }

    /**
     * Finds the TaxJurisdiction model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return TaxJurisdiction the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = TaxJurisdiction::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}