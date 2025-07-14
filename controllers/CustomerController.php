<?php

namespace app\controllers;

use Yii;
use app\models\Customer;
use app\models\Company;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\Response;

/**
 * CustomerController implements the CRUD actions for Customer model.
 */
class CustomerController extends Controller
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
                        'roles' => ['@'], // Only authenticated users
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
     * Lists all Customer models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $company = Company::getCurrent();
        if (!$company) {
            return $this->redirect(['company/select']);
        }

        $searchTerm = Yii::$app->request->get('search', '');
        
        $query = Customer::find()->where(['company_id' => $company->id]);

        if (!empty($searchTerm)) {
            $query->andWhere(['or',
                ['like', 'customer_name', $searchTerm],
                ['like', 'customer_email', $searchTerm],
                ['like', 'customer_phone', $searchTerm],
            ]);
        }

        $customers = $query->orderBy(['customer_name' => SORT_ASC])->all();

        return $this->render('index', [
            'customers' => $customers,
            'searchTerm' => $searchTerm,
            'company' => $company,
        ]);
    }

    /**
     * Displays a single Customer model.
     *
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        
        // Get customer's invoices
        $invoices = $model->getInvoices()->orderBy(['created_at' => SORT_DESC])->all();
        
        return $this->render('view', [
            'model' => $model,
            'invoices' => $invoices,
        ]);
    }

    /**
     * Creates a new Customer model.
     *
     * @return string|Response
     */
    public function actionCreate()
    {
        $company = Company::getCurrent();
        if (!$company) {
            return $this->redirect(['company/select']);
        }

        $model = new Customer();
        $model->company_id = $company->id;
        $model->is_active = true;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Customer created successfully.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
            'company' => $company,
        ]);
    }

    /**
     * Updates an existing Customer model.
     *
     * @param int $id ID
     * @return string|Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Customer updated successfully.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
            'company' => $model->company,
        ]);
    }

    /**
     * Deletes an existing Customer model.
     *
     * @param int $id ID
     * @return Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        
        // Check if customer has invoices
        $invoiceCount = $model->getInvoicesCount();
        if ($invoiceCount > 0) {
            Yii::$app->session->setFlash('error', "Cannot delete customer. There are {$invoiceCount} invoices associated with this customer.");
            return $this->redirect(['view', 'id' => $model->id]);
        }

        $model->delete();
        Yii::$app->session->setFlash('success', 'Customer deleted successfully.');

        return $this->redirect(['index']);
    }

    /**
     * Toggle customer active status
     *
     * @param int $id
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionToggleStatus($id)
    {
        $model = $this->findModel($id);
        
        $model->is_active = !$model->is_active;
        
        if ($model->save()) {
            $status = $model->is_active ? 'activated' : 'deactivated';
            Yii::$app->session->setFlash('success', "Customer {$status} successfully.");
        } else {
            Yii::$app->session->setFlash('error', 'Failed to update customer status.');
        }

        return $this->redirect(['view', 'id' => $model->id]);
    }

    /**
     * Create customer via AJAX
     *
     * @return Response
     */
    public function actionCreateAjax()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        $company = Company::getCurrent();
        if (!$company) {
            return ['success' => false, 'message' => 'Default company not found.'];
        }

        $model = new Customer();
        $model->company_id = $company->id;
        $model->is_active = true;

        // Load data from POST request
        $postData = Yii::$app->request->post();
        
        // Manually set attributes from form data
        $model->customer_name = $postData['customer_name'] ?? '';
        $model->customer_email = $postData['customer_email'] ?? '';
        $model->customer_phone = $postData['customer_phone'] ?? '';
        $model->customer_address = $postData['customer_address'] ?? '';
        $model->city = $postData['city'] ?? '';
        $model->state = $postData['state'] ?? '';
        $model->zip_code = $postData['zip_code'] ?? '';
        $model->country = $postData['country'] ?? 'US';
        $model->payment_terms = $postData['payment_terms'] ?? 'Net 30';

        if ($model->save()) {
            return [
                'success' => true,
                'customer' => [
                    'id' => $model->id,
                    'customer_name' => $model->customer_name,
                    'customer_email' => $model->customer_email,
                    'customer_phone' => $model->customer_phone,
                    'customer_address' => $model->customer_address,
                    'city' => $model->city,
                    'state' => $model->state,
                    'zip_code' => $model->zip_code,
                    'country' => $model->country,
                    'payment_terms' => $model->payment_terms,
                    'displayName' => method_exists($model, 'getDisplayName') ? $model->getDisplayName() : $model->customer_name,
                ],
                'message' => 'Customer created successfully.',
            ];
        }

        return [
            'success' => false,
            'errors' => $model->errors,
            'attributes' => $model->attributes,
            'postData' => $postData,
            'message' => 'Failed to create customer. ' . implode(', ', array_map(function($field, $errors) {
                return $field . ': ' . implode(', ', $errors);
            }, array_keys($model->errors), $model->errors)),
        ];
    }

    /**
     * Search customers via AJAX
     *
     * @return Response
     */
    public function actionSearch()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        $term = Yii::$app->request->get('term', '');
        $company = Company::getCurrent();
        
        if (!$company || empty($term)) {
            return [];
        }
        
        $customers = Customer::search($term, $company->id)->limit(20)->all();
        
        $result = [];
        foreach ($customers as $customer) {
            $result[] = [
                'id' => $customer->id,
                'text' => $customer->getDisplayName(),
                'name' => $customer->customer_name,
                'email' => $customer->customer_email,
                'phone' => $customer->customer_phone,
                'address' => $customer->customer_address,
                'totalInvoices' => $customer->getInvoicesCount(),
                'totalAmount' => number_format($customer->getTotalAmount(), 2),
            ];
        }
        
        return $result;
    }

    /**
     * Get customer data via AJAX
     *
     * @param int $id
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionGetData($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        $model = $this->findModel($id);
        
        return [
            'success' => true,
            'customer' => [
                'id' => $model->id,
                'customer_name' => $model->customer_name,
                'customer_email' => $model->customer_email,
                'customer_phone' => $model->customer_phone,
                'customer_address' => $model->customer_address,
                'city' => $model->city,
                'state' => $model->state,
                'zip_code' => $model->zip_code,
                'country' => $model->country,
                'payment_terms' => $model->payment_terms ?: 'Net 30',
                'billing_address' => $model->customer_address,
                'shipping_address' => $model->customer_address,
                'displayName' => $model->getDisplayName(),
                'formattedAddress' => $model->getFormattedAddress(),
                'totalInvoices' => $model->getInvoicesCount(),
                'totalAmount' => number_format($model->getTotalAmount(), 2),
            ]
        ];
    }

    /**
     * Export customers to CSV
     *
     * @return Response
     */
    public function actionExport()
    {
        $company = Company::getCurrent();
        if (!$company) {
            return $this->redirect(['company/select']);
        }

        $customers = Customer::findActiveByCompany($company->id)->all();
        
        $filename = 'customers_' . date('Y-m-d_H-i-s') . '.csv';
        
        Yii::$app->response->format = Response::FORMAT_RAW;
        Yii::$app->response->headers->add('Content-Type', 'text/csv');
        Yii::$app->response->headers->add('Content-Disposition', 'attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        // CSV header
        fputcsv($output, [
            'ID',
            'Customer Name',
            'Email',
            'Phone',
            'Address',
            'Total Invoices',
            'Total Amount',
            'Status',
            'Created At',
        ]);
        
        // CSV data
        foreach ($customers as $customer) {
            fputcsv($output, [
                $customer->id,
                $customer->customer_name,
                $customer->customer_email,
                $customer->customer_phone,
                $customer->customer_address,
                $customer->getInvoicesCount(),
                number_format($customer->getTotalAmount(), 2),
                $customer->is_active ? 'Active' : 'Inactive',
                $customer->created_at,
            ]);
        }
        
        fclose($output);
        
        return Yii::$app->response;
    }

    /**
     * Finds the Customer model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * Also ensures the customer belongs to the current company.
     *
     * @param int $id ID
     * @return Customer the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        $company = Company::getCurrent();
        if (!$company) {
            throw new NotFoundHttpException('No company selected.');
        }
        
        $model = Customer::findOne(['id' => $id, 'company_id' => $company->id]);
        if ($model !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}