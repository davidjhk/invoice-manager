<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\LinkPager;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var string $searchTerm */
/** @var string $statusFilter */
/** @var app\models\Company $company */

$this->title = 'Estimates';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="estimate-index">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?= Html::encode($this->title) ?></h1>
        <div class="action-buttons">
            <?= Html::a('<i class="fas fa-download mr-1"></i>Export', ['export'], [
                'class' => 'btn btn-outline-info',
                'target' => '_blank',
                'encode' => false
            ]) ?>
            <?= Html::a('<i class="fas fa-plus mr-1"></i>New Estimate', ['create'], [
                'class' => 'btn btn-success',
                'encode' => false
            ]) ?>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-6">
            <?= Html::beginForm(['index'], 'get', ['class' => 'form-inline']) ?>
                <div class="input-group">
                    <?= Html::input('text', 'search', $searchTerm, [
                        'class' => 'form-control',
                        'placeholder' => 'Search estimates...',
                        'id' => 'searchInput'
                    ]) ?>
                    <div class="input-group-append">
                        <?= Html::submitButton('<i class="fas fa-search"></i> Search', ['class' => 'btn btn-outline-secondary', 'encode' => false]) ?>
                    </div>
                </div>
            <?= Html::endForm() ?>
        </div>
        <div class="col-md-6 text-right">
            <div class="btn-group" role="group">
                <?= Html::a('All', ['index'], [
                    'class' => 'btn btn-sm ' . (empty($statusFilter) ? 'btn-primary' : 'btn-outline-primary')
                ]) ?>
                <?= Html::a('Draft', ['index', 'status' => 'draft'], [
                    'class' => 'btn btn-sm ' . ($statusFilter === 'draft' ? 'btn-secondary' : 'btn-outline-secondary')
                ]) ?>
                <?= Html::a('Printed', ['index', 'status' => 'printed'], [
                    'class' => 'btn btn-sm ' . ($statusFilter === 'printed' ? 'btn-primary' : 'btn-outline-primary')
                ]) ?>
                <?= Html::a('Sent', ['index', 'status' => 'sent'], [
                    'class' => 'btn btn-sm ' . ($statusFilter === 'sent' ? 'btn-info' : 'btn-outline-info')
                ]) ?>
                <?= Html::a('Accepted', ['index', 'status' => 'accepted'], [
                    'class' => 'btn btn-sm ' . ($statusFilter === 'accepted' ? 'btn-success' : 'btn-outline-success')
                ]) ?>
                <?= Html::a('Expired', ['index', 'status' => 'expired'], [
                    'class' => 'btn btn-sm ' . ($statusFilter === 'expired' ? 'btn-danger' : 'btn-outline-danger')
                ]) ?>
            </div>
        </div>
    </div>

    <?php if ($dataProvider->totalCount == 0): ?>
        <div class="alert alert-info text-center">
            <h4>No Estimates Found</h4>
            <p>You haven't created any estimates yet.</p>
            <?= Html::a('Create Your First Estimate', ['create'], ['class' => 'btn btn-primary']) ?>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="thead-dark">
                    <tr>
                        <th>Estimate #</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Expiry Date</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dataProvider->models as $estimate): ?>
                        <tr>
                            <td>
                                <?= Html::a(Html::encode($estimate->estimate_number), ['view', 'id' => $estimate->id], [
                                    'class' => 'font-weight-bold text-decoration-none'
                                ]) ?>
                            </td>
                            <td>
                                <?= Html::encode($estimate->customer->customer_name) ?>
                                <?php if ($estimate->customer->customer_email): ?>
                                    <br><small class="text-muted"><?= Html::encode($estimate->customer->customer_email) ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?= Yii::$app->formatter->asDate($estimate->estimate_date) ?></td>
                            <td>
                                <?php if ($estimate->expiry_date): ?>
                                    <?php
                                    $isExpired = $estimate->expiry_date < date('Y-m-d') && $estimate->status !== 'accepted';
                                    $class = $isExpired ? 'text-danger font-weight-bold' : '';
                                    ?>
                                    <span class="<?= $class ?>">
                                        <?= Yii::$app->formatter->asDate($estimate->expiry_date) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="font-weight-bold">
                                    <?= $estimate->formatAmount($estimate->total_amount) ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-<?= $estimate->getStatusClass() ?>">
                                    <?= Html::encode($estimate->getStatusLabel()) ?>
                                </span>
                                <?php if ($estimate->converted_to_invoice): ?>
                                    <br><small class="text-success">Converted to Invoice</small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <?= Html::a('<i class="fas fa-eye"></i>', ['view', 'id' => $estimate->id], [
                                        'class' => 'btn btn-outline-primary',
                                        'title' => 'View',
                                        'data-toggle' => 'tooltip',
                                        'encode' => false
                                    ]) ?>
                                    
                                    <?php if ($estimate->canEdit()): ?>
                                        <?= Html::a('<i class="fas fa-edit"></i>', ['update', 'id' => $estimate->id], [
                                            'class' => 'btn btn-outline-secondary',
                                            'title' => 'Edit',
                                            'data-toggle' => 'tooltip',
                                            'encode' => false
                                        ]) ?>
                                    <?php endif; ?>
                                    
                                    <?= Html::a('<i class="fas fa-file-pdf"></i>', ['preview', 'id' => $estimate->id], [
                                        'class' => 'btn btn-outline-info',
                                        'title' => 'Preview',
                                        'data-toggle' => 'tooltip',
                                        'encode' => false
                                    ]) ?>
                                    
                                    <?php if ($estimate->customer->customer_email && in_array($estimate->status, ['draft', 'printed'])): ?>
                                        <?= Html::a('<i class="fas fa-envelope"></i>', ['send-email', 'id' => $estimate->id], [
                                            'class' => 'btn btn-outline-success',
                                            'title' => 'Send Email',
                                            'data-toggle' => 'tooltip',
                                            'encode' => false
                                        ]) ?>
                                    <?php endif; ?>
                                    
                                    <?php if ($estimate->canConvertToInvoice()): ?>
                                        <?= Html::a('<i class="fas fa-exchange-alt"></i>', ['convert-to-invoice', 'id' => $estimate->id], [
                                            'class' => 'btn btn-outline-warning',
                                            'title' => 'Convert to Invoice',
                                            'data-toggle' => 'tooltip',
                                            'data-confirm' => 'Are you sure you want to convert this estimate to an invoice?',
                                            'data-method' => 'post',
                                            'encode' => false
                                        ]) ?>
                                    <?php endif; ?>
                                    
                                    <?= Html::a('<i class="fas fa-copy"></i>', ['duplicate', 'id' => $estimate->id], [
                                        'class' => 'btn btn-outline-info',
                                        'title' => 'Duplicate',
                                        'data-toggle' => 'tooltip',
                                        'data-confirm' => 'Are you sure you want to duplicate this estimate?',
                                        'data-method' => 'post',
                                        'encode' => false
                                    ]) ?>
                                    
                                    <?php if ($estimate->canEdit() && !$estimate->converted_to_invoice): ?>
                                        <?= Html::a('<i class="fas fa-trash"></i>', ['delete', 'id' => $estimate->id], [
                                            'class' => 'btn btn-outline-danger',
                                            'title' => 'Delete',
                                            'data-toggle' => 'tooltip',
                                            'data-confirm' => 'Are you sure you want to delete this estimate?',
                                            'data-method' => 'post',
                                            'encode' => false
                                        ]) ?>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="row mt-3">
            <div class="col-md-6">
                <div class="dataTables_info">
                    Showing <?= $dataProvider->getCount() ?> of <?= $dataProvider->totalCount ?> estimates
                </div>
            </div>
            <div class="col-md-6">
                <?= LinkPager::widget([
                    'pagination' => $dataProvider->pagination,
                    'options' => ['class' => 'pagination justify-content-end'],
                    'linkOptions' => ['class' => 'page-link'],
                    'disabledListItemSubTagOptions' => ['class' => 'page-link'],
                    'activePageCssClass' => 'active',
                    'disabledPageCssClass' => 'disabled',
                    'prevPageLabel' => '&laquo; Previous',
                    'nextPageLabel' => 'Next &raquo;',
                    'firstPageLabel' => '&laquo;&laquo; First',
                    'lastPageLabel' => 'Last &raquo;&raquo;',
                ]) ?>
            </div>
        </div>
    <?php endif; ?>

</div>

<?php
$this->registerCss("
    .table-responsive {
        box-shadow: 0 0 20px rgba(0,0,0,0.1);
        border-radius: 8px;
        overflow: hidden;
    }
    
    .table th {
        border-top: none;
        font-weight: 600;
    }
    
    .btn-group-sm > .btn {
        margin-right: 2px;
    }
    
    .btn-group-sm > .btn:last-child {
        margin-right: 0;
    }
");

$this->registerJs("
    $('[data-toggle=\"tooltip\"]').tooltip();
");
?>