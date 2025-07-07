<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Estimate $model */
?>

<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; color: #333;">
    
    <!-- Header -->
    <div style="text-align: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 2px solid #007bff;">
        <h1 style="color: #007bff; margin: 0;"><?= Html::encode($model->company->company_name) ?></h1>
        <?php if ($model->company->company_address): ?>
            <p style="margin: 10px 0; color: #666;"><?= nl2br(Html::encode($model->company->company_address)) ?></p>
        <?php endif; ?>
    </div>

    <!-- Greeting -->
    <div style="margin-bottom: 25px;">
        <p style="font-size: 16px; margin: 0;">Dear <?= Html::encode($model->customer->customer_name) ?>,</p>
    </div>

    <!-- Main Message -->
    <div style="margin-bottom: 30px; line-height: 1.6;">
        <p>We are pleased to provide you with the following estimate for your consideration:</p>
        
        <div style="background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;">
            <table style="width: 100%; border-spacing: 0;">
                <tr>
                    <td style="padding: 5px 0; color: #666;"><strong>Estimate Number:</strong></td>
                    <td style="padding: 5px 0; text-align: right;"><strong><?= Html::encode($model->estimate_number) ?></strong></td>
                </tr>
                <tr>
                    <td style="padding: 5px 0; color: #666;"><strong>Date:</strong></td>
                    <td style="padding: 5px 0; text-align: right;"><?= Yii::$app->formatter->asDate($model->estimate_date, 'php:F j, Y') ?></td>
                </tr>
                <?php if ($model->expiry_date): ?>
                <tr>
                    <td style="padding: 5px 0; color: #666;"><strong>Valid Until:</strong></td>
                    <td style="padding: 5px 0; text-align: right; color: #dc3545;"><strong><?= Yii::$app->formatter->asDate($model->expiry_date, 'php:F j, Y') ?></strong></td>
                </tr>
                <?php endif; ?>
                <tr style="border-top: 2px solid #007bff;">
                    <td style="padding: 10px 0 5px 0; color: #666; font-size: 18px;"><strong>Total Amount:</strong></td>
                    <td style="padding: 10px 0 5px 0; text-align: right; font-size: 18px; color: #007bff;"><strong><?= $model->formatAmount($model->total_amount) ?></strong></td>
                </tr>
            </table>
        </div>

        <p>Please find the detailed estimate attached as a PDF document.</p>
        
        <?php if ($model->customer_notes): ?>
        <div style="margin: 20px 0; padding: 15px; background-color: #e7f3ff; border-left: 4px solid #007bff; border-radius: 0 4px 4px 0;">
            <p style="margin: 0; font-style: italic;"><?= nl2br(Html::encode($model->customer_notes)) ?></p>
        </div>
        <?php endif; ?>
        
        <?php if ($model->expiry_date): ?>
        <p style="color: #dc3545;"><strong>Important:</strong> This estimate is valid until <?= Yii::$app->formatter->asDate($model->expiry_date, 'php:F j, Y') ?>. Please let us know if you would like to proceed before this date.</p>
        <?php endif; ?>
    </div>

    <!-- Call to Action -->
    <div style="text-align: center; margin: 30px 0;">
        <div style="background-color: #007bff; color: white; padding: 15px 30px; border-radius: 6px; display: inline-block;">
            <p style="margin: 0; font-size: 16px;"><strong>Ready to proceed?</strong></p>
            <p style="margin: 5px 0 0 0; font-size: 14px;">Contact us to confirm your order or if you have any questions.</p>
        </div>
    </div>

    <!-- Contact Information -->
    <div style="margin: 30px 0; padding: 20px; background-color: #f8f9fa; border-radius: 8px;">
        <h3 style="color: #007bff; margin: 0 0 15px 0;">Contact Information</h3>
        <table style="width: 100%; border-spacing: 0;">
            <?php if ($model->company->company_phone): ?>
            <tr>
                <td style="padding: 3px 0; color: #666; width: 80px;"><strong>Phone:</strong></td>
                <td style="padding: 3px 0;"><?= Html::encode($model->company->company_phone) ?></td>
            </tr>
            <?php endif; ?>
            <?php if ($model->company->company_email): ?>
            <tr>
                <td style="padding: 3px 0; color: #666;"><strong>Email:</strong></td>
                <td style="padding: 3px 0;"><a href="mailto:<?= Html::encode($model->company->company_email) ?>" style="color: #007bff; text-decoration: none;"><?= Html::encode($model->company->company_email) ?></a></td>
            </tr>
            <?php endif; ?>
        </table>
    </div>

    <!-- Payment Instructions -->
    <?php if ($model->payment_instructions): ?>
    <div style="margin: 25px 0; padding: 15px; background-color: #d4edda; border: 1px solid #c3e6cb; border-radius: 6px;">
        <h4 style="color: #155724; margin: 0 0 10px 0;">Payment Information</h4>
        <p style="margin: 0; color: #155724;"><?= nl2br(Html::encode($model->payment_instructions)) ?></p>
    </div>
    <?php endif; ?>

    <!-- Closing -->
    <div style="margin: 30px 0;">
        <p>Thank you for considering our services. We look forward to working with you!</p>
        <p style="margin-top: 20px;">
            Best regards,<br>
            <strong><?= Html::encode($model->company->company_name) ?></strong>
        </p>
    </div>

    <!-- Footer -->
    <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #e9ecef; text-align: center; color: #666; font-size: 12px;">
        <p style="margin: 0;">This estimate was generated automatically on <?= date('F j, Y \a\t g:i A') ?></p>
        <?php if ($model->expiry_date): ?>
        <p style="margin: 5px 0 0 0; font-weight: bold; color: #dc3545;">Valid until: <?= Yii::$app->formatter->asDate($model->expiry_date, 'php:F j, Y') ?></p>
        <?php endif; ?>
    </div>

</div>