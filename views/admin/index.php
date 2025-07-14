<?php

/* @var $this yii\web\View */
/* @var $stats array */

use yii\helpers\Html;

$this->title = Yii::t('app', 'Admin Dashboard');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="admin-index">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?= Html::encode($this->title) ?></h1>
        <div class="action-buttons">
            <?= Html::a('<i class="fas fa-user-plus mr-2"></i>' . Yii::t('app', 'Create User'), ['create-user'], ['class' => 'btn btn-success']) ?>
            <?= Html::a('<i class="fas fa-cog mr-2"></i>' . Yii::t('app', 'Settings'), ['settings'], ['class' => 'btn btn-outline-primary']) ?>
            <?= Html::a('<i class="fas fa-users mr-2"></i>' . Yii::t('app', 'Manage Users'), ['users'], ['class' => 'btn btn-outline-primary']) ?>
            <?= Html::a('<i class="fas fa-calculator mr-2"></i>' . Yii::t('app', 'Tax Management'), ['/tax-jurisdiction/index'], ['class' => 'btn btn-outline-warning']) ?>
            <?= Html::a('<i class="fas fa-percent mr-2"></i>' . Yii::t('app', 'State Tax Rates'), ['/state-tax-rate/index'], ['class' => 'btn btn-outline-info']) ?>
        </div>
    </div>

    <div class="alert alert-info mb-4">
        <i class="fas fa-info-circle mr-2"></i>
        <strong><?= Yii::t('app', 'Account Creation') ?>:</strong> <?= Yii::t('app', 'Users can only be created by administrators') ?>. 
        <?= Yii::t('app', 'The public signup feature has been disabled for security') ?>. 
        <?= Yii::t('app', 'Use the "Create User" button to add new accounts to the system') ?>.
    </div>

    <div class="row">
        <div class="col-md-3">
            <div class="card text-white bg-primary mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title"><?= Yii::t('app', 'Total Users') ?></h5>
                            <h2 class="card-text"><?= $stats['totalUsers'] ?></h2>
                        </div>
                        <div class="card-icon">
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card text-white bg-success mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title"><?= Yii::t('app', 'Active Users') ?></h5>
                            <h2 class="card-text"><?= $stats['activeUsers'] ?></h2>
                        </div>
                        <div class="card-icon">
                            <i class="fas fa-user-check fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card text-white bg-warning mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title"><?= Yii::t('app', 'Inactive Users') ?></h5>
                            <h2 class="card-text"><?= $stats['inactiveUsers'] ?></h2>
                        </div>
                        <div class="card-icon">
                            <i class="fas fa-user-times fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card text-white bg-info mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title"><?= Yii::t('app', 'Admin Users') ?></h5>
                            <h2 class="card-text"><?= $stats['adminUsers'] ?></h2>
                        </div>
                        <div class="card-icon">
                            <i class="fas fa-user-shield fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title"><?= Yii::t('app', 'Quick Actions') ?></h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <?= Html::a('<i class="fas fa-user-plus mr-2"></i>' . Yii::t('app', 'Create New User'), ['create-user'], ['class' => 'list-group-item list-group-item-action']) ?>
                        <?= Html::a('<i class="fas fa-cogs mr-2"></i>' . Yii::t('app', 'System Settings'), ['settings'], ['class' => 'list-group-item list-group-item-action']) ?>
                        <?= Html::a('<i class="fas fa-users-cog mr-2"></i>' . Yii::t('app', 'User Management'), ['users'], ['class' => 'list-group-item list-group-item-action']) ?>
                        <?= Html::a('<i class="fas fa-calculator mr-2"></i>' . Yii::t('app', 'Tax Management'), ['/tax-jurisdiction/index'], ['class' => 'list-group-item list-group-item-action']) ?>
                        <?= Html::a('<i class="fas fa-chart-bar mr-2"></i>' . Yii::t('app', 'Tax Statistics'), ['/tax-jurisdiction/stats'], ['class' => 'list-group-item list-group-item-action']) ?>
                        <?= Html::a('<i class="fas fa-upload mr-2"></i>' . Yii::t('app', 'Import Tax Rates'), ['/tax-jurisdiction/import-csv'], ['class' => 'list-group-item list-group-item-action']) ?>
                        <?= Html::a('<i class="fas fa-percent mr-2"></i>' . Yii::t('app', 'State Tax Rates'), ['/state-tax-rate/index'], ['class' => 'list-group-item list-group-item-action']) ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title"><?= Yii::t('app', 'System Information') ?></h5>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-4"><?= Yii::t('app', 'PHP Version') ?>:</dt>
                        <dd class="col-sm-8"><?= PHP_VERSION ?></dd>
                        
                        <dt class="col-sm-4"><?= Yii::t('app', 'Yii Version') ?>:</dt>
                        <dd class="col-sm-8"><?= Yii::getVersion() ?></dd>
                        
                        <dt class="col-sm-4"><?= Yii::t('app', 'Server Time') ?>:</dt>
                        <dd class="col-sm-8"><?= date('Y-m-d H:i:s') ?></dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Dark mode styles for admin dashboard */
body.dark-mode .admin-index .card {
    background-color: #374151 !important;
    border-color: #4b5563 !important;
    color: #e5e7eb !important;
}

body.dark-mode .admin-index .card-header {
    background-color: #4b5563 !important;
    border-color: #6b7280 !important;
    color: #f9fafb !important;
}

body.dark-mode .admin-index .card-body {
    background-color: #374151 !important;
    color: #e5e7eb !important;
}

body.dark-mode .admin-index .card-title {
    color: #f9fafb !important;
}

body.dark-mode .admin-index .list-group {
    background-color: #374151 !important;
}

body.dark-mode .admin-index .list-group-item {
    background-color: #374151 !important;
    border-color: #6b7280 !important;
    color: #e5e7eb !important;
}

body.dark-mode .admin-index .list-group-item-action {
    background-color: #374151 !important;
    color: #e5e7eb !important;
}

body.dark-mode .admin-index .list-group-item-action:hover {
    background-color: #6b7280 !important;
    color: #f9fafb !important;
}

body.dark-mode .admin-index .list-group-item-action:focus {
    background-color: #6b7280 !important;
    color: #f9fafb !important;
}

/* Additional specific targeting for list-group-flush */
body.dark-mode .admin-index .list-group-flush .list-group-item {
    background-color: #374151 !important;
    border-color: #6b7280 !important;
    color: #e5e7eb !important;
}

body.dark-mode .admin-index .list-group-flush .list-group-item-action {
    background-color: #374151 !important;
    color: #e5e7eb !important;
}

body.dark-mode .admin-index .list-group-flush .list-group-item-action:hover {
    background-color: #6b7280 !important;
    color: #f9fafb !important;
}

/* Force override any Bootstrap defaults */
body.dark-mode .admin-index .list-group-flush {
    background-color: transparent !important;
}

body.dark-mode .alert-info {
    background-color: #1e3a8a !important;
    border-color: #3b82f6 !important;
    color: #dbeafe !important;
}

body.dark-mode .alert-info .fas {
    color: #60a5fa !important;
}

/* Dark mode definition list */
body.dark-mode dl.row dt {
    color: #d1d5db !important;
}

body.dark-mode dl.row dd {
    color: #e5e7eb !important;
}

/* Light mode styles */
.card-icon {
    opacity: 0.7;
    align-self: center;
}

.card {
    border: none;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border-radius: 0.5rem;
}

.list-group-item-action {
    transition: all 0.2s ease;
}

.list-group-item-action:hover {
    background-color: #f8f9fa;
    transform: translateX(5px);
}
</style>