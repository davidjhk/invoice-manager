<?php

/** @var yii\web\View $this */
/** @var string $content */

use app\assets\AppAsset;
use app\widgets\Alert;
use yii\bootstrap4\Breadcrumbs;
use yii\bootstrap4\Html;
use yii\bootstrap4\Nav;
use yii\bootstrap4\NavBar;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" class="h-100">

<head>
	<meta charset="<?= Yii::$app->charset ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<?php $this->registerCsrfMetaTags() ?>
	<title><?= Html::encode($this->title) ?></title>
	<?php $this->head() ?>

	<!-- Suppress PageSpeed errors -->
	<script>
	window.addEventListener('error', function(e) {
		if (e.message && e.message.indexOf('pagespeed') !== -1) {
			e.preventDefault();
			e.stopPropagation();
			return false;
		}
	});

	// Define pagespeed as empty object to prevent reference errors
	if (typeof pagespeed === 'undefined') {
		window.pagespeed = {};
	}
	</script>
</head>

<body class="d-flex flex-column h-100">
	<?php $this->beginBody() ?>

	<header>
		<?php
    NavBar::begin([
        'brandLabel' => 'Invoice Manager',
        'brandUrl' => Yii::$app->homeUrl,
        'options' => [
            'class' => 'navbar navbar-expand-md navbar-dark fixed-top',
            'style' => 'background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);',
        ],
    ]);
    echo Nav::widget([
        'options' => ['class' => 'navbar-nav mr-auto'],
        'items' => [
            ['label' => '<i class="fas fa-tachometer-alt mr-1"></i>Dashboard', 'url' => ['/site/index'], 'encode' => false],
            ['label' => '<i class="fas fa-file-invoice mr-1"></i>Invoices', 'url' => ['/invoice/index'], 'encode' => false],
            ['label' => '<i class="fas fa-file-alt mr-1"></i>Estimates', 'url' => ['/estimate/index'], 'encode' => false],
            ['label' => '<i class="fas fa-users mr-1"></i>Customers', 'url' => ['/customer/index'], 'encode' => false],
            ['label' => '<i class="fas fa-box mr-1"></i>Products', 'url' => ['/product/index'], 'encode' => false],
            ['label' => '<i class="fas fa-cog mr-1"></i>Settings', 'url' => ['/company/settings'], 'encode' => false],
        ],
    ]);
    echo Nav::widget([
        'options' => ['class' => 'navbar-nav ml-auto'],
        'items' => [
            [
                'label' => '<i class="fas fa-plus mr-1"></i>New',
                'encode' => false,
                'items' => [
                    ['label' => '<i class="fas fa-file-invoice mr-2"></i>Invoice', 'url' => ['/invoice/create'], 'encode' => false],
                    ['label' => '<i class="fas fa-file-alt mr-2"></i>Estimate', 'url' => ['/estimate/create'], 'encode' => false],
                    ['label' => '<i class="fas fa-box mr-2"></i>Product/Service', 'url' => ['/product/create'], 'encode' => false],
                ],
            ],
            Yii::$app->user->isGuest ? (
                ['label' => '<i class="fas fa-sign-in-alt mr-1"></i>Login', 'url' => ['/site/login'], 'encode' => false]
            ) : (
                '<li>'
                . Html::beginForm(['/site/logout'], 'post', ['class' => 'form-inline'])
                . Html::submitButton(
                    '<i class="fas fa-sign-out-alt mr-1"></i>Logout (' . Yii::$app->user->identity->username . ')',
                    ['class' => 'btn btn-link logout nav-link', 'style' => 'color: rgba(255,255,255,.5);']
                )
                . Html::endForm()
                . '</li>'
            )
        ],
    ]);
    NavBar::end();
    ?>
	</header>

	<main role="main" class="flex-shrink-0">
		<div class="container">
			<?= Breadcrumbs::widget([
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
			<?= Alert::widget() ?>
			<?= $content ?>
		</div>
	</main>

	<footer class="footer mt-auto py-3 text-muted">
		<div class="container">
			<?php 
			$company = \app\models\Company::getDefault();
			$companyName = $company ? $company->company_name : 'JDOSA';
			?>
			<p class="float-left">&copy; <?= Html::encode($companyName) ?> <?= date('Y') ?></p>
			<p class="float-right"><?= Yii::powered() ?></p>
		</div>
	</footer>

	<?php $this->endBody() ?>
</body>

</html>
<?php $this->endPage() ?>