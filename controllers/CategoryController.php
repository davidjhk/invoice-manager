<?php

namespace app\controllers;

use Yii;
use app\models\ProductCategory;
use app\models\User;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\Response;

/**
 * CategoryController implements the CRUD actions for ProductCategory model.
 */
class CategoryController extends Controller
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
                    ],
                ],
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
     * Lists all ProductCategory models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $user = Yii::$app->user->identity;
        $companyId = $user instanceof User ? $user->getCompanyId() : null;

        if (!$companyId) {
            throw new NotFoundHttpException('Company not found.');
        }

        $dataProvider = new ActiveDataProvider([
            'query' => ProductCategory::find()
                ->where(['company_id' => $companyId])
                ->orderBy('sort_order ASC, name ASC'),
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single ProductCategory model.
     *
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new ProductCategory model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new ProductCategory();
        
        $user = Yii::$app->user->identity;
        $companyId = $user instanceof User ? $user->getCompanyId() : null;

        if (!$companyId) {
            throw new NotFoundHttpException('Company not found.');
        }

        $model->company_id = $companyId;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Category created successfully.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing ProductCategory model.
     * If update is successful, the browser will be redirected to the 'view' page.
     *
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Category updated successfully.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing ProductCategory model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     *
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        
        if ($model->canDelete()) {
            $model->delete();
            Yii::$app->session->setFlash('success', 'Category deleted successfully.');
        } else {
            Yii::$app->session->setFlash('error', 'Cannot delete category that is assigned to products.');
        }

        return $this->redirect(['index']);
    }

    /**
     * Update sort order via AJAX
     *
     * @return array
     */
    public function actionUpdateSort()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        $user = Yii::$app->user->identity;
        $companyId = $user instanceof User ? $user->getCompanyId() : null;

        if (!$companyId) {
            return ['success' => false, 'message' => 'Company not found.'];
        }

        $sortData = Yii::$app->request->post('sort');
        
        if (!$sortData) {
            return ['success' => false, 'message' => 'No sort data provided.'];
        }

        $transaction = Yii::$app->db->beginTransaction();
        
        try {
            foreach ($sortData as $index => $categoryId) {
                $category = ProductCategory::findOne([
                    'id' => $categoryId,
                    'company_id' => $companyId
                ]);
                
                if ($category) {
                    $category->sort_order = $index + 1;
                    $category->save(false);
                }
            }
            
            $transaction->commit();
            return ['success' => true, 'message' => 'Sort order updated successfully.'];
            
        } catch (\Exception $e) {
            $transaction->rollBack();
            return ['success' => false, 'message' => 'Failed to update sort order.'];
        }
    }

    /**
     * Finds the ProductCategory model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param int $id ID
     * @return ProductCategory the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        $user = Yii::$app->user->identity;
        $companyId = $user instanceof User ? $user->getCompanyId() : null;

        if (!$companyId) {
            throw new NotFoundHttpException('Company not found.');
        }

        $model = ProductCategory::findOne([
            'id' => $id,
            'company_id' => $companyId
        ]);

        if ($model !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested category does not exist.');
    }
}