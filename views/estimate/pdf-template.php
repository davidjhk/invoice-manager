<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Estimate $model */
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Estimate <?= Html::encode($model->estimate_number) ?></title>
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            margin: 0;
            padding: 20px;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        .header {
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
        .company-info {
            float: left;
            width: 50%;
        }
        .company-info h2 {
            margin: 0 0 8px 0;
            font-size: 18px;
            color: #333;
        }
        .estimate-info {
            float: right;
            width: 45%;
            text-align: right;
        }
        .estimate-info h1 {
            margin: 0 0 15px 0;
            font-size: 24px;
            color: #333;
        }
        .estimate-info table {
            float: right;
            border-spacing: 0;
            font-size: 11px;
        }
        .estimate-info table td {
            padding: 3px 8px 3px 0;
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
            margin-bottom: 25px;
        }
        .bill-to, .ship-to {
            float: left;
            width: 48%;
            margin-right: 2%;
        }
        .bill-to h3, .ship-to h3 {
            margin: 0 0 8px 0;
            font-size: 14px;
            color: #333;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .items-table th {
            background-color: #f0f0f0;
            padding: 8px 6px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #ccc;
            font-size: 11px;
        }
        .items-table td {
            border: 1px solid #ccc;
            padding: 6px;
            text-align: left;
            font-size: 11px;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .totals {
            float: right;
            width: 250px;
            margin-top: 15px;
        }
        .totals table {
            width: 100%;
            border-spacing: 0;
            font-size: 11px;
        }
        .totals table td {
            padding: 4px 8px;
            border: none;
        }
        .total-row {
            border-top: 2px solid #333;
            font-weight: bold;
            font-size: 12px;
        }
        .notes {
            margin-top: 40px;
            clear: both;
        }
        .notes h3 {
            margin: 0 0 8px 0;
            font-size: 14px;
            color: #333;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
        .status-info {
            margin-top: 10px;
            font-size: 10px;
        }
    </style>
</head>
<body>

    <div class="header">
        <div class="company-info">
            <h2><?= Html::encode($model->company->company_name) ?></h2>
            <?php if ($model->company->company_address): ?>
                <div><?= nl2br(Html::encode($model->company->company_address)) ?></div>
            <?php endif; ?>
            <?php if ($model->company->company_phone): ?>
                <div>Phone: <?= Html::encode($model->company->company_phone) ?></div>
            <?php endif; ?>
            <?php if ($model->company->company_email): ?>
                <div>Email: <?= Html::encode($model->company->company_email) ?></div>
            <?php endif; ?>
        </div>
        
        <div class="estimate-info">
            <h1>ESTIMATE</h1>
            <table>
                <tr>
                    <td>Estimate #:</td>
                    <td><?= Html::encode($model->estimate_number) ?></td>
                </tr>
                <tr>
                    <td>Date:</td>
                    <td><?= Yii::$app->formatter->asDate($model->estimate_date, 'php:m/d/Y') ?></td>
                </tr>
                <?php if ($model->expiry_date): ?>
                <tr>
                    <td>Expires:</td>
                    <td><?= Yii::$app->formatter->asDate($model->expiry_date, 'php:m/d/Y') ?></td>
                </tr>
                <?php endif; ?>
                <?php if ($model->terms): ?>
                <tr>
                    <td>Terms:</td>
                    <td><?= Html::encode($model->terms) ?></td>
                </tr>
                <?php endif; ?>
            </table>
            <div class="status-info">
                Status: <?= Html::encode($model->getStatusLabel()) ?>
            </div>
        </div>
        <div class="clear"></div>
    </div>

    <div class="customer-section">
        <div class="bill-to">
            <h3>Bill To:</h3>
            <strong><?= Html::encode($model->customer->customer_name) ?></strong><br>
            <?php if ($model->customer->contact_name): ?>
                Attn: <?= Html::encode($model->customer->contact_name) ?><br>
            <?php endif; ?>
            <?php if ($model->customer->billing_address): ?>
                <?= nl2br(Html::encode($model->customer->billing_address)) ?><br>
            <?php endif; ?>
            <?php if ($model->customer->customer_phone): ?>
                Phone: <?= Html::encode($model->customer->customer_phone) ?><br>
            <?php endif; ?>
            <?php if ($model->customer->customer_email): ?>
                Email: <?= Html::encode($model->customer->customer_email) ?>
            <?php endif; ?>
        </div>

        <?php if ($model->ship_to_address): ?>
        <div class="ship-to">
            <h3>Ship To:</h3>
            <?= nl2br(Html::encode($model->ship_to_address)) ?>
            <?php if ($model->shipping_method): ?>
                <br>Via: <?= Html::encode($model->shipping_method) ?>
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
                <th width="8%" class="text-center">Qty</th>
                <th width="12%" class="text-right">Rate</th>
                <th width="15%" class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($model->estimateItems as $index => $item): ?>
            <tr>
                <td class="text-center"><?= $index + 1 ?></td>
                <td><?= Html::encode($item->product_service_name ?: '-') ?></td>
                <td><?= nl2br(Html::encode($item->description)) ?></td>
                <td class="text-center"><?= $item->getFormattedQuantity() ?></td>
                <td class="text-right"><?= $model->formatAmount($item->rate) ?></td>
                <td class="text-right"><?= $item->getFormattedAmount() ?></td>
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
                <td class="text-right">-<?= $model->formatAmount($model->discount_amount) ?></td>
            </tr>
            <?php endif; ?>
            <?php if ($model->tax_amount > 0): ?>
            <tr>
                <td>Tax (<?= $model->tax_rate ?>%):</td>
                <td class="text-right"><?= $model->formatAmount($model->tax_amount) ?></td>
            </tr>
            <?php endif; ?>
            <tr class="total-row">
                <td>Total:</td>
                <td class="text-right"><?= $model->formatAmount($model->total_amount) ?></td>
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
            <p>This estimate is valid until <?= Yii::$app->formatter->asDate($model->expiry_date, 'php:F j, Y') ?></p>
        <?php endif; ?>
        <p>Thank you for your business!</p>
    </div>

</body>
</html>