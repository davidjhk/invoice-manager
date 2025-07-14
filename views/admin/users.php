<?php

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

$this->title = Yii::t('app', 'User Management');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Admin'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="admin-users">
	<div class="d-flex justify-content-between align-items-center mb-4">
		<h1><?= Html::encode($this->title) ?></h1>
		<div class="btn-group">
			<?= Html::a('<i class="fas fa-plus mr-2"></i>' . Yii::t('app', 'Create User'), ['create-user'], ['class' => 'btn btn-primary']) ?>
			<?= Html::a('<i class="fas fa-arrow-left mr-2"></i>' . Yii::t('app', 'Back to Dashboard'), ['index'], ['class' => 'btn btn-secondary']) ?>
		</div>
	</div>

	<div class="card">
		<div class="card-body">
			<?php Pjax::begin(); ?>

			<?= GridView::widget([
                'dataProvider' => $dataProvider,
                'tableOptions' => ['class' => 'table table-striped table-bordered table-hover'],
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],
                    
                    [
                        'attribute' => 'full_name',
                        'label' => Yii::t('app', 'Name'),
                        'value' => function($model) {
                            return $model->full_name ?: $model->username;
                        },
                    ],
                    'email:email',
                    [
                        'attribute' => 'role',
                        'label' => Yii::t('app', 'Role'),
                        'value' => function($model) {
                            return $model->getRoleLabel();
                        },
                        'format' => 'raw',
                        'contentOptions' => function($model) {
                            return ['class' => $model->role === 'admin' ? 'text-primary font-weight-bold' : ''];
                        },
                    ],
                    [
                        'attribute' => 'is_active',
                        'label' => Yii::t('app', 'Status'),
                        'value' => function($model) {
                            return $model->is_active ? 
                                '<span class="badge badge-success">' . Yii::t('app', 'Active') . '</span>' : 
                                '<span class="badge badge-secondary">' . Yii::t('app', 'Inactive') . '</span>';
                        },
                        'format' => 'raw',
                    ],
                    [
                        'attribute' => 'login_type',
                        'label' => Yii::t('app', 'Login Type'),
                        'value' => function($model) {
                            return $model->login_type === 'google' ? 
                                '<span class="badge badge-info">' . Yii::t('app', 'Google') . '</span>' : 
                                '<span class="badge badge-secondary">' . Yii::t('app', 'Local') . '</span>';
                        },
                        'format' => 'raw',
                    ],
                    [
                        'attribute' => 'created_at',
                        'label' => Yii::t('app', 'Created'),
                        'value' => function($model) {
                            return Yii::$app->formatter->asRelativeTime($model->created_at);
                        },
                    ],
                    [
                        'class' => 'yii\grid\ActionColumn',
                        'template' => '{view} {update} {reset-password} {toggle-status} {delete}',
                        'buttons' => [
                            'view' => function ($url, $model, $key) {
                                return Html::a('<i class="fas fa-eye"></i>', '#', [
                                    'class' => 'btn btn-sm btn-outline-info',
                                    'title' => Yii::t('app', 'View User'),
                                    'data-toggle' => 'modal',
                                    'data-target' => '#userModal',
                                    'data-user-id' => $model->id,
                                ]);
                            },
                            'update' => function ($url, $model, $key) {
                                return Html::a('<i class="fas fa-edit"></i>', ['update-user', 'id' => $model->id], [
                                    'class' => 'btn btn-sm btn-outline-primary',
                                    'title' => Yii::t('app', 'Edit User'),
                                ]);
                            },
                            'reset-password' => function ($url, $model, $key) {
                                return Html::a('<i class="fas fa-key"></i>', ['reset-user-password', 'id' => $model->id], [
                                    'class' => 'btn btn-sm btn-outline-warning',
                                    'title' => Yii::t('app', 'Reset Password'),
                                ]);
                            },
                            'toggle-status' => function ($url, $model, $key) {
                                if ($model->id === Yii::$app->user->id) {
                                    return '';
                                }
                                $icon = $model->is_active ? 'fas fa-user-slash' : 'fas fa-user-check';
                                $class = $model->is_active ? 'btn-outline-warning' : 'btn-outline-success';
                                $title = $model->is_active ? Yii::t('app', 'Deactivate User') : Yii::t('app', 'Activate User');
                                
                                return Html::a("<i class=\"{$icon}\"></i>", ['toggle-user-status', 'id' => $model->id], [
                                    'class' => "btn btn-sm {$class}",
                                    'title' => $title,
                                    'data-method' => 'post',
                                    'data-confirm' => Yii::t('app', 'Are you sure you want to {action}?', ['action' => strtolower($title)]),
                                ]);
                            },
                            'delete' => function ($url, $model, $key) {
                                if ($model->id === Yii::$app->user->id) {
                                    return '';
                                }
                                return Html::a('<i class="fas fa-trash"></i>', ['delete-user', 'id' => $model->id], [
                                    'class' => 'btn btn-sm btn-outline-danger',
                                    'title' => Yii::t('app', 'Delete User'),
                                    'data-method' => 'post',
                                    'data-confirm' => Yii::t('app', 'Are you sure you want to delete this user?'),
                                ]);
                            },
                        ],
                        'contentOptions' => ['class' => 'text-center btn-group'],
                    ],
                ],
            ]); ?>

			<?php Pjax::end(); ?>
		</div>
	</div>
</div>

<style>
.card {
	border: none;
	box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
	border-radius: 0.5rem;
}

.table {
	margin-bottom: 0;
}

.table th {
	background-color: #343a40;
	border-top: none;
	font-weight: 600;
	color: #ffffff;
}

.btn-outline-info:hover {
	background-color: #17a2b8;
	border-color: #17a2b8;
}

.btn-outline-primary:hover {
	background-color: #4f46e5;
	border-color: #4f46e5;
}

.btn-outline-warning:hover {
	background-color: #ffc107;
	border-color: #ffc107;
}

.btn-outline-success:hover {
	background-color: #28a745;
	border-color: #28a745;
}

.btn-outline-danger:hover {
	background-color: #dc3545;
	border-color: #dc3545;
}

.badge {
	font-size: 0.75rem;
	padding: 0.25em 0.5em;
}
</style>