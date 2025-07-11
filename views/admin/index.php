<?php

/* @var $this yii\web\View */
/* @var $stats array */

use yii\helpers\Html;

$this->title = 'Admin Dashboard';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="admin-index">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?= Html::encode($this->title) ?></h1>
        <div class="btn-group">
            <?= Html::a('<i class="fas fa-cog mr-2"></i>Settings', ['settings'], ['class' => 'btn btn-outline-primary']) ?>
            <?= Html::a('<i class="fas fa-users mr-2"></i>Manage Users', ['users'], ['class' => 'btn btn-outline-primary']) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3">
            <div class="card text-white bg-primary mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title">Total Users</h5>
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
                            <h5 class="card-title">Active Users</h5>
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
                            <h5 class="card-title">Inactive Users</h5>
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
                            <h5 class="card-title">Admin Users</h5>
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
                    <h5 class="card-title">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <?= Html::a('<i class="fas fa-user-plus mr-2"></i>Create New User', ['create-user'], ['class' => 'list-group-item list-group-item-action']) ?>
                        <?= Html::a('<i class="fas fa-cogs mr-2"></i>System Settings', ['settings'], ['class' => 'list-group-item list-group-item-action']) ?>
                        <?= Html::a('<i class="fas fa-users-cog mr-2"></i>User Management', ['users'], ['class' => 'list-group-item list-group-item-action']) ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">System Information</h5>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-4">PHP Version:</dt>
                        <dd class="col-sm-8"><?= PHP_VERSION ?></dd>
                        
                        <dt class="col-sm-4">Yii Version:</dt>
                        <dd class="col-sm-8"><?= Yii::getVersion() ?></dd>
                        
                        <dt class="col-sm-4">Server Time:</dt>
                        <dd class="col-sm-8"><?= date('Y-m-d H:i:s') ?></dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
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