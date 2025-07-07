<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Estimate $model */

$this->title = 'Print Estimate: ' . $model->estimate_number;
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?= Html::encode($this->title) ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            font-size: 14px;
            line-height: 1.4;
            color: #333;
        }
        .header {
            border-bottom: 3px solid #007bff;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .company-info {
            float: left;
            width: 50%;
        }
        .company-info h2 {
            color: #007bff;
            margin: 0 0 10px 0;
        }
        .estimate-info {
            float: right;
            width: 45%;
            text-align: right;
        }
        .estimate-info h1 {
            color: #007bff;
            margin: 0 0 20px 0;
            font-size: 36px;
        }
        .estimate-info table {
            float: right;
            border-spacing: 0;
        }
        .estimate-info table td {
            padding: 5px 10px 5px 0;
            text-align: right;
        }
        .estimate-info table td:first-child {
            text-align: left;
            font-weight: bold;
        }
        .clear {
            clear: both;
        }
        .customer-section {
            margin-bottom: 30px;
        }
        .bill-to, .ship-to {
            float: left;
            width: 48%;
            margin-right: 2%;
        }
        .bill-to h3, .ship-to h3 {
            color: #007bff;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 5px;
            margin-bottom: 15px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .items-table th {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            padding: 12px 8px;
            text-align: left;
            font-weight: bold;
        }
        .items-table td {
            border: 1px solid #e9ecef;
            padding: 10px 8px;
            text-align: left;
        }
        .items-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .totals {
            float: right;
            width: 350px;
            margin-top: 20px;
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .totals table {
            width: 100%;
            border-spacing: 0;
        }
        .totals table td {
            padding: 8px 15px;
            border: none;
        }
        .totals table tr:not(.total-row) td {
            border-bottom: 1px solid #dee2e6;
        }
        .total-row {
            border-top: 3px solid #007bff;
            font-weight: bold;
            font-size: 16px;
            background: white;
        }
        .notes {
            margin-top: 60px;
            clear: both;
        }
        .notes h3 {
            color: #007bff;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 5px;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 12px;
            color: #6c757d;
            border-top: 1px solid #e9ecef;
            padding-top: 20px;
        }
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-draft { background-color: #6c757d; color: white; }
        .status-sent { background-color: #17a2b8; color: white; }
        .status-accepted { background-color: #28a745; color: white; }
        .status-rejected { background-color: #dc3545; color: white; }
        .status-expired { background-color: #ffc107; color: black; }
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <div class="no-print" style="margin-bottom: 20px; text-align: center;">
        <?= Html::button('<i class="fas fa-print mr-2"></i>Print Estimate', [
            'onclick' => 'window.print()',
            'class' => 'btn btn-primary btn-lg'
        ]) ?>
        <?= Html::a('<i class="fas fa-arrow-left mr-2"></i>Back to Estimate', ['view', 'id' => $model->id], [
            'class' => 'btn btn-secondary btn-lg'
        ]) ?>
    </div>

    <div class="header">
        <div class="company-info">
            <h2><?= Html::encode($model->company->company_name) ?></h2>
            <?php if ($model->company->company_address): ?>
                <div><?= nl2br(Html::encode($model->company->company_address)) ?></div>
            <?php endif; ?>
            <?php if ($model->company->company_phone): ?>
                <div><strong>Phone:</strong> <?= Html::encode($model->company->company_phone) ?></div>
            <?php endif; ?>
            <?php if ($model->company->company_email): ?>
                <div><strong>Email:</strong> <?= Html::encode($model->company->company_email) ?></div>
            <?php endif; ?>
        </div>
        
        <div class="estimate-info">
            <h1>ESTIMATE</h1>
            <table>
                <tr>
                    <td><strong>Estimate #:</strong></td>
                    <td><?= Html::encode($model->estimate_number) ?></td>
                </tr>
                <tr>
                    <td><strong>Date:</strong></td>
                    <td><?= Yii::$app->formatter->asDate($model->estimate_date, 'php:M j, Y') ?></td>
                </tr>
                <?php if ($model->expiry_date): ?>
                <tr>
                    <td><strong>Expires:</strong></td>
                    <td><?= Yii::$app->formatter->asDate($model->expiry_date, 'php:M j, Y') ?></td>
                </tr>
                <?php endif; ?>
                <?php if ($model->terms): ?>
                <tr>
                    <td><strong>Terms:</strong></td>
                    <td><?= Html::encode($model->terms) ?></td>
                </tr>
                <?php endif; ?>
                <tr>
                    <td><strong>Status:</strong></td>
                    <td>
                        <span class="status-badge status-<?= $model->status ?>">
                            <?= $model->getStatusLabel() ?>
                        </span>
                    </td>
                </tr>
            </table>
        </div>
        <div class="clear"></div>
    </div>

    <div class="customer-section">
        <div class="bill-to">
            <h3>Bill To:</h3>
            <strong><?= Html::encode($model->customer->customer_name) ?></strong><br>
            <?php if ($model->customer->contact_name): ?>
                <strong>Attn:</strong> <?= Html::encode($model->customer->contact_name) ?><br>
            <?php endif; ?>
            <?php if ($model->customer->billing_address): ?>
                <?= nl2br(Html::encode($model->customer->billing_address)) ?><br>
            <?php endif; ?>
            <?php if ($model->customer->customer_phone): ?>
                <strong>Phone:</strong> <?= Html::encode($model->customer->customer_phone) ?><br>
            <?php endif; ?>
            <?php if ($model->customer->customer_email): ?>
                <strong>Email:</strong> <?= Html::encode($model->customer->customer_email) ?>
            <?php endif; ?>
        </div>

        <?php if ($model->ship_to_address): ?>
        <div class="ship-to">
            <h3>Ship To:</h3>
            <?= nl2br(Html::encode($model->ship_to_address)) ?>
            <?php if ($model->shipping_method): ?>
                <br><strong>Via:</strong> <?= Html::encode($model->shipping_method) ?>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <div class="clear"></div>
    </div>

    <?php if (!empty($model->estimateItems)): ?>
    <table class="items-table">
        <thead>
            <tr>
                <th width="5%">#</th>
                <th width="25%">Product/Service</th>
                <th width="35%">Description</th>
                <th width="10%" class="text-center">Qty</th>
                <th width="12%" class="text-right">Rate</th>
                <th width="13%" class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($model->estimateItems as $index => $item): ?>
            <tr>
                <td class="text-center"><?= $index + 1 ?></td>
                <td><?= Html::encode($item->product_service_name ?: '-') ?></td>
                <td><?= Html::encode($item->description) ?></td>
                <td class="text-center"><?= $item->getFormattedQuantity() ?></td>
                <td class="text-right"><?= $model->formatAmount($item->rate) ?></td>
                <td class="text-right"><strong><?= $item->getFormattedAmount() ?></strong></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

    <div class="totals">
        <table>
            <tr>
                <td>Subtotal:</td>
                <td class="text-right"><?= $model->formatAmount($model->subtotal) ?></td>
            </tr>
            <?php if ($model->discount_amount > 0): ?>
            <tr>
                <td>
                    Discount
                    <?php if ($model->discount_type == 'percentage'): ?>
                        (<?= $model->discount_value ?>%):
                    <?php else: ?>
                        (Fixed):
                    <?php endif; ?>
                </td>
                <td class="text-right" style="color: #dc3545;">-<?= $model->formatAmount($model->discount_amount) ?></td>
            </tr>
            <?php endif; ?>
            <?php if ($model->tax_amount > 0): ?>
            <tr>
                <td>Tax (<?= $model->tax_rate ?>%):</td>
                <td class="text-right"><?= $model->formatAmount($model->tax_amount) ?></td>
            </tr>
            <?php endif; ?>
            <tr class="total-row">
                <td><strong>Total:</strong></td>
                <td class="text-right"><strong><?= $model->formatAmount($model->total_amount) ?></strong></td>
            </tr>
        </table>
    </div>

    <?php if ($model->customer_notes): ?>
    <div class="notes">
        <h3>Notes:</h3>
        <p><?= nl2br(Html::encode($model->customer_notes)) ?></p>
    </div>
    <?php endif; ?>

    <?php if ($model->payment_instructions): ?>
    <div class="notes">
        <h3>Payment Instructions:</h3>
        <p><?= nl2br(Html::encode($model->payment_instructions)) ?></p>
    </div>
    <?php endif; ?>

    <div class="footer">
        <?php if ($model->expiry_date): ?>
            <p><strong>This estimate is valid until <?= Yii::$app->formatter->asDate($model->expiry_date, 'php:F j, Y') ?></strong></p>
        <?php endif; ?>
        <p>Thank you for considering our services!</p>
        <p style="margin-top: 20px; font-size: 10px;">
            Printed on <?= date('F j, Y \a\t g:i A') ?>
        </p>
    </div>

</body>
</html>