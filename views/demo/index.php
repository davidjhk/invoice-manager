<?php

/* @var $this yii\web\View */

use yii\helpers\Html;

$this->title = 'Demo Dashboard';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="demo-index">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?= Html::encode($this->title) ?></h1>
        <div>
            <span class="badge badge-warning badge-lg">
                <i class="fas fa-user-check mr-1"></i>
                Demo User
            </span>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="alert alert-info">
                <h4><i class="fas fa-info-circle mr-2"></i>Welcome to Demo Mode!</h4>
                <p class="mb-2">You are currently logged in as a <strong>demo user</strong>. This mode allows you to explore the system with the following restrictions:</p>
                <ul class="mb-3">
                    <li>You can create and manage invoices, estimates, customers, and products</li>
                    <li>All demo data is isolated from other users</li>
                    <li>You can reset all demo data at any time</li>
                    <li>Some advanced features may be limited</li>
                </ul>
                <p class="mb-0">
                    <strong>Demo Credentials:</strong> Username: <code>demo</code>, Password: <code>admin123</code>
                </p>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-file-invoice mr-2"></i>
                        Invoice Management
                    </h5>
                </div>
                <div class="card-body">
                    <p class="card-text">Create, edit, and manage invoices for your demo company.</p>
                    <div class="mt-auto">
                        <?= Html::a('<i class="fas fa-plus mr-2"></i>Create Invoice', ['/invoice/create'], [
                            'class' => 'btn btn-primary btn-sm mr-2'
                        ]) ?>
                        <?= Html::a('<i class="fas fa-list mr-2"></i>View All', ['/invoice/index'], [
                            'class' => 'btn btn-outline-primary btn-sm'
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-file-contract mr-2"></i>
                        Estimate Management
                    </h5>
                </div>
                <div class="card-body">
                    <p class="card-text">Create and manage estimates for potential clients.</p>
                    <div class="mt-auto">
                        <?= Html::a('<i class="fas fa-plus mr-2"></i>Create Estimate', ['/estimate/create'], [
                            'class' => 'btn btn-success btn-sm mr-2'
                        ]) ?>
                        <?= Html::a('<i class="fas fa-list mr-2"></i>View All', ['/estimate/index'], [
                            'class' => 'btn btn-outline-success btn-sm'
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-users mr-2"></i>
                        Customer Management
                    </h5>
                </div>
                <div class="card-body">
                    <p class="card-text">Manage your demo customer database.</p>
                    <div class="mt-auto">
                        <?= Html::a('<i class="fas fa-plus mr-2"></i>Add Customer', ['/customer/create'], [
                            'class' => 'btn btn-info btn-sm mr-2'
                        ]) ?>
                        <?= Html::a('<i class="fas fa-list mr-2"></i>View All', ['/customer/index'], [
                            'class' => 'btn btn-outline-info btn-sm'
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header bg-warning text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-box mr-2"></i>
                        Product Management
                    </h5>
                </div>
                <div class="card-body">
                    <p class="card-text">Manage your demo product catalog.</p>
                    <div class="mt-auto">
                        <?= Html::a('<i class="fas fa-plus mr-2"></i>Add Product', ['/product/create'], [
                            'class' => 'btn btn-warning btn-sm mr-2'
                        ]) ?>
                        <?= Html::a('<i class="fas fa-list mr-2"></i>View All', ['/product/index'], [
                            'class' => 'btn btn-outline-warning btn-sm'
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header bg-secondary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-building mr-2"></i>
                        Company Settings
                    </h5>
                </div>
                <div class="card-body">
                    <p class="card-text">Configure your demo company information.</p>
                    <div class="mt-auto">
                        <?= Html::a('<i class="fas fa-cog mr-2"></i>Settings', ['/company/settings'], [
                            'class' => 'btn btn-secondary btn-sm'
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100 border-danger">
                <div class="card-header bg-danger text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-refresh mr-2"></i>
                        Reset Demo Data
                    </h5>
                </div>
                <div class="card-body">
                    <p class="card-text">Reset all demo data to start fresh.</p>
                    <div class="mt-auto">
                        <?= Html::a('<i class="fas fa-refresh mr-2"></i>Reset Data', ['reset-demo-data'], [
                            'class' => 'btn btn-danger btn-sm',
                            'data' => [
                                'confirm' => 'Are you sure you want to reset all demo data? This action cannot be undone.',
                                'method' => 'post',
                            ],
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-question-circle mr-2"></i>
                        Demo Mode Help
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6><i class="fas fa-check-circle text-success mr-2"></i>What You Can Do:</h6>
                            <ul>
                                <li>Create and manage invoices and estimates</li>
                                <li>Add customers and products</li>
                                <li>Generate PDF documents</li>
                                <li>Send emails (demo mode only)</li>
                                <li>Configure company settings</li>
                                <li>Reset demo data anytime</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6><i class="fas fa-times-circle text-danger mr-2"></i>Demo Limitations:</h6>
                            <ul>
                                <li>Cannot create additional companies</li>
                                <li>Cannot access admin functions</li>
                                <li>Cannot change user password</li>
                                <li>Data is isolated from other users</li>
                                <li>Some advanced features may be disabled</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.badge-lg {
    font-size: 1rem;
    padding: 0.5rem 1rem;
}

.card {
    border: none;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border-radius: 0.5rem;
    margin-bottom: 1rem;
}

.card-header {
    border-bottom: 1px solid rgba(0, 0, 0, 0.125);
    border-radius: 0.5rem 0.5rem 0 0 !important;
}

.alert-info {
    background-color: #dbeafe;
    border-color: #3b82f6;
    color: #1e40af;
}

.btn {
    border-radius: 0.375rem;
    font-weight: 500;
    transition: all 0.2s ease;
}

.btn:hover {
    transform: translateY(-1px);
}

.btn-sm {
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
}
</style>