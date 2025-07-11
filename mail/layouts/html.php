<?php
use yii\helpers\Html;

/** @var \yii\web\View $this view component instance */
/** @var \yii\mail\MessageInterface $message the message being composed */
/** @var string $content main view render result */
?>
<?php $this->beginPage() ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=<?= Yii::$app->charset ?>" />
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
    <style type="text/css">
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            border-bottom: 1px solid #eee;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        .footer {
            text-align: center;
            border-top: 1px solid #eee;
            padding-top: 20px;
            margin-top: 20px;
            font-size: 12px;
            color: #666;
        }
        a {
            color: #007bff;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <?php $this->beginBody() ?>
    <div class="container">
        <div class="header">
            <h2><?= Html::encode(Yii::$app->name) ?></h2>
        </div>
        
        <?= $content ?>
        
        <div class="footer">
            <p>© <?= date('Y') ?> <?= Html::encode(Yii::$app->name) ?>. All rights reserved.</p>
        </div>
    </div>
    <?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
