<?php

/* @var $this yii\web\View */
/* @var $error string */

use yii\helpers\Html;

$this->title = 'Admin Settings - Error';
$this->params['breadcrumbs'][] = ['label' => 'Admin', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="admin-settings-error">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?= Html::encode($this->title) ?></h1>
        <?= Html::a('<i class="fas fa-arrow-left mr-2"></i>Back to Dashboard', ['index'], ['class' => 'btn btn-secondary']) ?>
    </div>

    <div class="alert alert-danger">
        <h4><i class="fas fa-exclamation-triangle mr-2"></i>Database Error</h4>
        <p><strong>The admin settings table is missing.</strong></p>
        <p>This usually happens when the database migration hasn't been run yet.</p>
        <hr>
        <p class="mb-0">
            <small><strong>Error Details:</strong> <?= Html::encode($error) ?></small>
        </p>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title"><i class="fas fa-tools mr-2"></i>How to Fix This</h5>
        </div>
        <div class="card-body">
            <h6>Option 1: Run the Migration Script</h6>
            <p>Execute the following command on your server:</p>
            <pre class="bg-light p-3 rounded"><code>cd /opt/bitnami/apps/jdosa/invoice-manager
./yii migrate --interactive=0</code></pre>
            
            <h6>Option 2: Use the Auto-Fix Script</h6>
            <p>Run the automatic table creation script:</p>
            <pre class="bg-light p-3 rounded"><code>cd /opt/bitnami/apps/jdosa/invoice-manager
./create_admin_settings_table.sh</code></pre>
            
            <h6>Option 3: Manual Database Creation</h6>
            <p>Execute the following SQL in your database:</p>
            <pre class="bg-light p-3 rounded"><code>CREATE TABLE IF NOT EXISTS `jdosa_admin_settings` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `setting_key` varchar(100) NOT NULL,
    `setting_value` text,
    `description` text,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `jdosa_admin_settings` (`setting_key`, `setting_value`, `description`) VALUES
('allow_signup', '0', 'Allow new user registration (1 = enabled, 0 = disabled)'),
('max_users', '100', 'Maximum number of users allowed'),
('site_maintenance', '0', 'Site maintenance mode (1 = enabled, 0 = disabled)'),
('password_min_length', '6', 'Minimum password length requirement'),
('session_timeout', '3600', 'Session timeout in seconds'),
('email_notifications', '1', 'Enable email notifications'),
('backup_enabled', '1', 'Enable automatic backups'),
('max_companies_per_user', '5', 'Maximum companies per user');</code></pre>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header">
            <h5 class="card-title"><i class="fas fa-info-circle mr-2"></i>What are Admin Settings?</h5>
        </div>
        <div class="card-body">
            <p>Admin Settings allow you to configure various system parameters:</p>
            <ul>
                <li><strong>User Registration:</strong> Enable/disable user signup</li>
                <li><strong>System Limits:</strong> Maximum users, companies per user</li>
                <li><strong>Security Settings:</strong> Password requirements, session timeout</li>
                <li><strong>Maintenance Mode:</strong> Put the site in maintenance mode</li>
                <li><strong>Notifications:</strong> Email notification settings</li>
                <li><strong>Backups:</strong> Automatic backup configuration</li>
            </ul>
        </div>
    </div>
</div>

<style>
.card {
    border: none;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border-radius: 0.5rem;
}

.alert-danger {
    border-left: 4px solid #dc3545;
}

pre {
    font-size: 0.875rem;
    overflow-x: auto;
}

code {
    color: #e83e8c;
    font-weight: 500;
}
</style>