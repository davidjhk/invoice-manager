<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Invoice $invoice */

$this->title = 'Payment History - Invoice ' . $invoice->invoice_number;
$this->params['breadcrumbs'][] = ['label' => 'Invoices', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $invoice->invoice_number, 'url' => ['view', 'id' => $invoice->id]];
$this->params['breadcrumbs'][] = 'Payment History';
?>

<div class="invoice-payments">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?= Html::encode($this->title) ?></h1>
        <div class="btn-group">
            <?= Html::a('<i class="fas fa-arrow-left mr-1"></i>Back to Invoice', ['view', 'id' => $invoice->id], [
                'class' => 'btn btn-secondary', 
                'encode' => false
            ]) ?>
            <?php if ($invoice->canReceivePayment()): ?>
                <?= Html::a('<i class="fas fa-plus mr-1"></i>Record Payment', ['receive-payment', 'id' => $invoice->id], [
                    'class' => 'btn btn-success',
                    'encode' => false
                ]) ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-money-check-alt mr-2"></i>Payment History
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (empty($invoice->payments)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                            <h5>No Payments Recorded</h5>
                            <p class="text-muted">No payments have been recorded for this invoice yet.</p>
                            <?php if ($invoice->canReceivePayment()): ?>
                                <?= Html::a('<i class="fas fa-plus mr-1"></i>Record First Payment', ['receive-payment', 'id' => $invoice->id], [
                                    'class' => 'btn btn-primary',
                                    'encode' => false
                                ]) ?>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Amount</th>
                                        <th>Method</th>
                                        <th>Reference</th>
                                        <th>Notes</th>
                                        <th>Recorded</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($invoice->payments as $payment): ?>
                                    <tr>
                                        <td>
                                            <strong><?= Yii::$app->formatter->asDate($payment->payment_date) ?></strong>
                                        </td>
                                        <td>
                                            <span class="badge badge-success">
                                                <?= $payment->getFormattedAmount() ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-secondary">
                                                <?= $payment->getPaymentMethodLabel() ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($payment->reference_number): ?>
                                                <code><?= Html::encode($payment->reference_number) ?></code>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($payment->notes): ?>
                                                <small><?= Html::encode($payment->notes) ?></small>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <?= Yii::$app->formatter->asRelativeTime($payment->created_at) ?>
                                            </small>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr class="table-info">
                                        <td><strong>Total Payments:</strong></td>
                                        <td colspan="5">
                                            <strong><?= $invoice->formatAmount($invoice->getTotalPaidAmount()) ?></strong>
                                            <small class="text-muted ml-2">(<?= count($invoice->payments) ?> payment<?= count($invoice->payments) !== 1 ? 's' : '' ?>)</small>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Payment Summary -->
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-calculator mr-2"></i>Payment Summary
                    </h6>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-6">Invoice Total:</dt>
                        <dd class="col-sm-6">
                            <strong><?= $invoice->formatAmount($invoice->total_amount) ?></strong>
                        </dd>
                        
                        <dt class="col-sm-6">Total Paid:</dt>
                        <dd class="col-sm-6 text-success">
                            <strong><?= $invoice->formatAmount($invoice->getTotalPaidAmount()) ?></strong>
                        </dd>
                        
                        <dt class="col-sm-6">Remaining:</dt>
                        <dd class="col-sm-6 <?= $invoice->getRemainingBalance() > 0 ? 'text-danger' : 'text-success' ?>">
                            <strong><?= $invoice->formatAmount($invoice->getRemainingBalance()) ?></strong>
                        </dd>
                        
                        <dt class="col-sm-6">Status:</dt>
                        <dd class="col-sm-6">
                            <span class="badge badge-<?= $invoice->getStatusClass() ?>">
                                <?= $invoice->getStatusLabel() ?>
                            </span>
                        </dd>
                    </dl>
                    
                    <?php if ($invoice->getRemainingBalance() > 0): ?>
                        <div class="progress mt-3">
                            <?php $percentage = ($invoice->getTotalPaidAmount() / $invoice->total_amount) * 100; ?>
                            <div class="progress-bar" role="progressbar" 
                                 style="width: <?= $percentage ?>%" 
                                 aria-valuenow="<?= $percentage ?>" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                                <?= number_format($percentage, 1) ?>%
                            </div>
                        </div>
                        <small class="text-muted">Payment Progress</small>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Invoice Info -->
            <div class="card mt-4">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-info-circle mr-2"></i>Invoice Information
                    </h6>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-6">Customer:</dt>
                        <dd class="col-sm-6">
                            <?= Html::a(
                                Html::encode($invoice->customer->customer_name),
                                ['/customer/view', 'id' => $invoice->customer->id],
                                ['class' => 'text-decoration-none']
                            ) ?>
                        </dd>
                        
                        <dt class="col-sm-6">Invoice Date:</dt>
                        <dd class="col-sm-6"><?= Yii::$app->formatter->asDate($invoice->invoice_date) ?></dd>
                        
                        <dt class="col-sm-6">Due Date:</dt>
                        <dd class="col-sm-6">
                            <?php if ($invoice->due_date): ?>
                                <?php
                                $isOverdue = $invoice->due_date < date('Y-m-d') && $invoice->status !== 'paid';
                                $class = $isOverdue ? 'text-danger font-weight-bold' : '';
                                ?>
                                <span class="<?= $class ?>">
                                    <?= Yii::$app->formatter->asDate($invoice->due_date) ?>
                                </span>
                                <?php if ($isOverdue): ?>
                                    <br><small class="text-danger">Overdue</small>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

</div>