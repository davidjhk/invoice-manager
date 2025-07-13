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

	<!-- Google Fonts -->
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link
		href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@400;500;600;700&family=Playfair+Display:wght@400;500;600;700;800&display=swap"
		rel="stylesheet">

	<?php $this->head() ?>

	<!-- Fallback CDN resources in case local assets fail -->
	<script>
	// Check if jQuery is loaded, if not load from CDN
	if (typeof jQuery === 'undefined') {
		document.write('<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"><\/script>');
	}
	</script>
	<!-- jQuery UI -->
	<script src="https://cdn.jsdelivr.net/npm/jquery-ui-dist@1.13.2/jquery-ui.min.js"></script>
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jquery-ui-dist@1.13.2/jquery-ui.min.css">
	<script>
	// Check if Bootstrap is loaded, if not load from CDN
	if (typeof bootstrap === 'undefined') {
		document.write(
			'<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"><\/script>');
	}
	</script>

	<!-- Fallback CSS in case site.css fails to load -->
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

	<!-- Custom CSS loaded last to override other styles -->
	<style>
	/* Force navigation layout - inline styles have highest priority */
	body .main-navbar {
		position: fixed !important;
		top: 80px !important;
		left: 0 !important;
		right: 0 !important;
		z-index: 1030 !important;
		padding: 1rem 0 !important;
		background: transparent !important;
		width: 100% !important;
	}

	body .main-navbar .container {
		max-width: 1600px !important;
		margin: 0 auto !important;
		padding: 0 1rem !important;
		display: flex !important;
		justify-content: center !important;
	}

	body .main-navbar .navbar-nav {
		display: flex !important;
		flex-direction: row !important;
		flex-wrap: nowrap !important;
		justify-content: center !important;
		align-items: center !important;
		list-style: none !important;
		margin: 0 !important;
		padding: 0.5rem !important;
		background: rgba(55, 65, 81, 0.95) !important;
		border-radius: 1rem !important;
		box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04) !important;
		backdrop-filter: blur(10px) !important;
		border: 1px solid rgba(55, 65, 81, 0.8) !important;
		gap: 0.25rem !important;
	}

	body .main-navbar .navbar-nav>* {
		display: inline-block !important;
		vertical-align: middle !important;
		list-style: none !important;
		margin: 0 !important;
		padding: 0 !important;
	}

	body .main-navbar .navbar-nav .nav-link {
		padding: 0.75rem 1.5rem !important;
		margin: 0 0.25rem !important;
		border-radius: 0.5rem !important;
		transition: all 0.2s ease !important;
		font-weight: 500 !important;
		color: #d1d5db !important;
		text-decoration: none !important;
		font-size: 0.875rem !important;
		display: inline-block !important;
		position: relative !important;
		overflow: hidden !important;
		/* 일정한 높이 설정 */
		line-height: 1.25 !important;
		vertical-align: middle !important;
		/* Liquid Glass Style - 기본 상태 */
		background: rgba(75, 85, 99, 0.1) !important;
		border: 1px solid rgba(156, 163, 175, 0.2) !important;
		backdrop-filter: blur(8px) !important;
		box-shadow:
			0 1px 3px rgba(0, 0, 0, 0.1),
			0 1px 2px rgba(0, 0, 0, 0.06),
			inset 0 1px 0 rgba(255, 255, 255, 0.1) !important;
	}

	body .main-navbar .navbar-nav .nav-link::before {
		content: '' !important;
		position: absolute !important;
		top: 0 !important;
		left: 0 !important;
		width: 0 !important;
		height: 100% !important;
		background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%) !important;
		transition: width 0.3s ease !important;
		z-index: -1 !important;
	}

	body .main-navbar .navbar-nav .nav-link:hover::before {
		width: 100% !important;
	}

	body .main-navbar .navbar-nav .nav-link:hover {
		color: #ffffff !important;
		text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3) !important;
		text-decoration: none !important;
		transform: translateY(-2px) !important;
		/* Liquid Glass Style - 호버 상태 */
		background: rgba(75, 85, 99, 0.25) !important;
		border: 1px solid rgba(156, 163, 175, 0.4) !important;
		backdrop-filter: blur(12px) !important;
		box-shadow:
			0 8px 25px -5px rgba(0, 0, 0, 0.15),
			0 10px 10px -5px rgba(0, 0, 0, 0.1),
			inset 0 1px 0 rgba(255, 255, 255, 0.2),
			inset 0 -1px 0 rgba(0, 0, 0, 0.1) !important;
	}

	body .main-navbar .navbar-nav .nav-link.active {
		background: linear-gradient(135deg, #5b52f0 0%, #8b4aff 100%) !important;
		font-weight: 700 !important;
		color: #ffffff !important;
		text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3) !important;
		/* Liquid Glass Style - 활성 상태 강화 */
		border: 2px solid rgba(79, 70, 229, 0.8) !important;
		backdrop-filter: blur(20px) !important;
		transform: translateY(-1px) !important;
		box-shadow:
			0 12px 30px -8px rgba(79, 70, 229, 0.5),
			0 8px 16px -4px rgba(79, 70, 229, 0.3),
			inset 0 1px 0 rgba(255, 255, 255, 0.4),
			inset 0 -1px 0 rgba(0, 0, 0, 0.1),
			0 0 0 1px rgba(79, 70, 229, 0.2) !important;
	}

	body .main-navbar .navbar-nav .nav-link.active::before {
		width: 100% !important;
		background: linear-gradient(135deg, rgba(255, 255, 255, 0.2) 0%, rgba(255, 255, 255, 0.1) 100%) !important;
	}

	/* 활성 상태 펄스 효과 */
	body .main-navbar .navbar-nav .nav-link.active::after {
		content: '' !important;
		position: absolute !important;
		top: -2px !important;
		left: -2px !important;
		right: -2px !important;
		bottom: -2px !important;
		background: linear-gradient(135deg, rgba(79, 70, 229, 0.3), rgba(124, 58, 237, 0.3)) !important;
		border-radius: 0.5rem !important;
		z-index: -2 !important;
		opacity: 0.8 !important;
		animation: activeGlow 2s ease-in-out infinite alternate !important;
	}

	@keyframes activeGlow {
		0% {
			opacity: 0.5;
			transform: scale(1);
		}

		100% {
			opacity: 0.8;
			transform: scale(1.02);
		}
	}

	/* Dropdown styles */
	body .main-navbar .dropdown {
		position: relative !important;
		display: inline-block !important;
	}

	body .main-navbar .dropdown .nav-link {
		padding: 0.75rem 1.5rem !important;
		margin: 0 0.25rem !important;
		border-radius: 0.5rem !important;
		transition: all 0.2s ease !important;
		font-weight: 500 !important;
		color: #d1d5db !important;
		text-decoration: none !important;
		font-size: 0.875rem !important;
		display: inline-block !important;
		position: relative !important;
		overflow: hidden !important;
		/* 일정한 높이 설정 */
		line-height: 1.25 !important;
		vertical-align: middle !important;
		/* Liquid Glass Style - 드롭다운 기본 상태 */
		background: rgba(75, 85, 99, 0.1) !important;
		border: 1px solid rgba(156, 163, 175, 0.2) !important;
		backdrop-filter: blur(8px) !important;
		box-shadow:
			0 1px 3px rgba(0, 0, 0, 0.1),
			0 1px 2px rgba(0, 0, 0, 0.06),
			inset 0 1px 0 rgba(255, 255, 255, 0.1) !important;
	}

	/* 아이콘 높이 조정 */
	body .main-navbar .dropdown .nav-link i {
		vertical-align: middle !important;
		line-height: 1 !important;
		font-size: 0.875rem !important;
	}

	body .main-navbar .dropdown .nav-link::before {
		content: '' !important;
		position: absolute !important;
		top: 0 !important;
		left: 0 !important;
		width: 0 !important;
		height: 100% !important;
		background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%) !important;
		transition: width 0.3s ease !important;
		z-index: -1 !important;
	}

	body .main-navbar .dropdown .nav-link:hover::before {
		width: 100% !important;
	}

	body .main-navbar .dropdown .nav-link:hover {
		color: #ffffff !important;
		text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3) !important;
		text-decoration: none !important;
		transform: translateY(-2px) !important;
		/* Liquid Glass Style - 드롭다운 호버 상태 */
		background: rgba(75, 85, 99, 0.25) !important;
		border: 1px solid rgba(156, 163, 175, 0.4) !important;
		backdrop-filter: blur(12px) !important;
		box-shadow:
			0 8px 25px -5px rgba(0, 0, 0, 0.15),
			0 10px 10px -5px rgba(0, 0, 0, 0.1),
			inset 0 1px 0 rgba(255, 255, 255, 0.2),
			inset 0 -1px 0 rgba(0, 0, 0, 0.1) !important;
	}

	/* Dark mode navbar adjustments */
	body.dark-mode .main-navbar .navbar-nav {
		background: rgba(31, 41, 55, 0.95) !important;
		border: 1px solid rgba(55, 65, 81, 0.8) !important;
		box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3), 0 10px 10px -5px rgba(0, 0, 0, 0.1) !important;
	}

	body.dark-mode .main-navbar .navbar-nav .nav-link {
		color: #d1d5db !important;
		/* Liquid Glass Style - 다크모드 기본 상태 */
		background: rgba(31, 41, 55, 0.15) !important;
		border: 1px solid rgba(75, 85, 99, 0.3) !important;
		backdrop-filter: blur(10px) !important;
		box-shadow:
			0 1px 3px rgba(0, 0, 0, 0.2),
			0 1px 2px rgba(0, 0, 0, 0.1),
			inset 0 1px 0 rgba(255, 255, 255, 0.05) !important;
	}

	body.dark-mode .main-navbar .navbar-nav .nav-link:hover {
		/* Liquid Glass Style - 다크모드 호버 상태 */
		background: rgba(31, 41, 55, 0.3) !important;
		border: 1px solid rgba(75, 85, 99, 0.5) !important;
		backdrop-filter: blur(15px) !important;
		box-shadow:
			0 8px 25px -5px rgba(0, 0, 0, 0.25),
			0 10px 10px -5px rgba(0, 0, 0, 0.15),
			inset 0 1px 0 rgba(255, 255, 255, 0.1),
			inset 0 -1px 0 rgba(0, 0, 0, 0.2) !important;
	}

	body.dark-mode .main-navbar .navbar-nav .nav-link.active {
		color: #ffffff !important;
		font-weight: 700 !important;
		text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5) !important;
		/* Liquid Glass Style - 다크모드 활성 상태 강화 */
		border: 2px solid rgba(79, 70, 229, 0.9) !important;
		backdrop-filter: blur(25px) !important;
		transform: translateY(-1px) !important;
		box-shadow:
			0 12px 30px -8px rgba(79, 70, 229, 0.6),
			0 8px 16px -4px rgba(79, 70, 229, 0.4),
			inset 0 1px 0 rgba(255, 255, 255, 0.3),
			inset 0 -1px 0 rgba(0, 0, 0, 0.2),
			0 0 0 1px rgba(79, 70, 229, 0.3) !important;
	}

	/* Dark mode dropdown styles */
	body.dark-mode .main-navbar .dropdown .nav-link {
		/* Liquid Glass Style - 다크모드 드롭다운 기본 상태 */
		background: rgba(31, 41, 55, 0.15) !important;
		border: 1px solid rgba(75, 85, 99, 0.3) !important;
		backdrop-filter: blur(10px) !important;
		box-shadow:
			0 1px 3px rgba(0, 0, 0, 0.2),
			0 1px 2px rgba(0, 0, 0, 0.1),
			inset 0 1px 0 rgba(255, 255, 255, 0.05) !important;
	}

	body.dark-mode .main-navbar .dropdown .nav-link:hover {
		/* Liquid Glass Style - 다크모드 드롭다운 호버 상태 */
		background: rgba(31, 41, 55, 0.3) !important;
		border: 1px solid rgba(75, 85, 99, 0.5) !important;
		backdrop-filter: blur(15px) !important;
		box-shadow:
			0 8px 25px -5px rgba(0, 0, 0, 0.25),
			0 10px 10px -5px rgba(0, 0, 0, 0.15),
			inset 0 1px 0 rgba(255, 255, 255, 0.1),
			inset 0 -1px 0 rgba(0, 0, 0, 0.2) !important;
	}

	/* Light mode navbar adjustments */
	body:not(.dark-mode) .main-navbar .navbar-nav {
		background: rgba(255, 255, 255, 0.95) !important;
		border: 1px solid rgba(229, 231, 235, 0.8) !important;
		box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04) !important;
	}

	body:not(.dark-mode) .main-navbar .navbar-nav .nav-link {
		color: #374151 !important;
		font-weight: 500 !important;
		/* Liquid Glass Style - 라이트모드 기본 상태 */
		background: rgba(255, 255, 255, 0.2) !important;
		border: 1px solid rgba(229, 231, 235, 0.4) !important;
		backdrop-filter: blur(10px) !important;
		box-shadow:
			0 1px 3px rgba(0, 0, 0, 0.05),
			0 1px 2px rgba(0, 0, 0, 0.03),
			inset 0 1px 0 rgba(255, 255, 255, 0.3) !important;
	}

	body:not(.dark-mode) .main-navbar .navbar-nav .nav-link:hover {
		/* Liquid Glass Style - 라이트모드 호버 상태 */
		background: rgba(255, 255, 255, 0.4) !important;
		border: 1px solid rgba(229, 231, 235, 0.6) !important;
		backdrop-filter: blur(15px) !important;
		box-shadow:
			0 8px 25px -5px rgba(0, 0, 0, 0.1),
			0 10px 10px -5px rgba(0, 0, 0, 0.05),
			inset 0 1px 0 rgba(255, 255, 255, 0.5),
			inset 0 -1px 0 rgba(0, 0, 0, 0.05) !important;
	}

	body:not(.dark-mode) .main-navbar .dropdown .nav-link {
		color: #374151 !important;
		font-weight: 500 !important;
		/* Liquid Glass Style - 라이트모드 드롭다운 기본 상태 */
		background: rgba(255, 255, 255, 0.2) !important;
		border: 1px solid rgba(229, 231, 235, 0.4) !important;
		backdrop-filter: blur(10px) !important;
		box-shadow:
			0 1px 3px rgba(0, 0, 0, 0.05),
			0 1px 2px rgba(0, 0, 0, 0.03),
			inset 0 1px 0 rgba(255, 255, 255, 0.3) !important;
	}

	body:not(.dark-mode) .main-navbar .dropdown .nav-link:hover {
		/* Liquid Glass Style - 라이트모드 드롭다운 호버 상태 */
		background: rgba(255, 255, 255, 0.4) !important;
		border: 1px solid rgba(229, 231, 235, 0.6) !important;
		backdrop-filter: blur(15px) !important;
		box-shadow:
			0 8px 25px -5px rgba(0, 0, 0, 0.1),
			0 10px 10px -5px rgba(0, 0, 0, 0.05),
			inset 0 1px 0 rgba(255, 255, 255, 0.5),
			inset 0 -1px 0 rgba(0, 0, 0, 0.05) !important;
	}

	body:not(.dark-mode) .main-navbar .navbar-nav .nav-link.active {
		color: #1f2937 !important;
		font-weight: 700 !important;
		text-shadow: 0 1px 2px rgba(255, 255, 255, 0.8) !important;
		/* Liquid Glass Style - 라이트모드 활성 상태 강화 */
		background: rgba(255, 255, 255, 0.9) !important;
		border: 2px solid rgba(79, 70, 229, 0.7) !important;
		backdrop-filter: blur(25px) !important;
		transform: translateY(-1px) !important;
		box-shadow:
			0 12px 30px -8px rgba(79, 70, 229, 0.4),
			0 8px 16px -4px rgba(79, 70, 229, 0.25),
			inset 0 1px 0 rgba(255, 255, 255, 0.9),
			inset 0 -1px 0 rgba(0, 0, 0, 0.05),
			0 0 0 1px rgba(79, 70, 229, 0.15) !important;
	}

	body:not(.dark-mode) .main-navbar .navbar-nav .nav-link:hover {
		color: #ffffff !important;
		text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3) !important;
	}

	body:not(.dark-mode) .main-navbar .dropdown .nav-link:hover {
		color: #ffffff !important;
		text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3) !important;
	}

	/* Light mode dropdown menu */
	body:not(.dark-mode) .main-navbar .dropdown-menu {
		background: rgba(255, 255, 255, 0.95) !important;
		border: 1px solid rgba(229, 231, 235, 0.8) !important;
		box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04) !important;
		backdrop-filter: blur(10px) !important;
		position: absolute !important;
		top: 100% !important;
		left: 0 !important;
		z-index: 1000 !important;
		display: none !important;
		min-width: 200px !important;
		padding: 0.5rem 0 !important;
		margin: 0.125rem 0 0 !important;
		border-radius: 0.5rem !important;
		opacity: 0 !important;
		transform: translateY(-10px) !important;
		transition: all 0.2s ease !important;
	}

	body:not(.dark-mode) .main-navbar .dropdown-menu.show {
		display: block !important;
		opacity: 1 !important;
		transform: translateY(0) !important;
	}

	body:not(.dark-mode) .main-navbar .dropdown-item {
		color: #374151 !important;
		display: block !important;
		width: 100% !important;
		padding: 0.5rem 1rem !important;
		clear: both !important;
		font-weight: 400 !important;
		text-align: inherit !important;
		white-space: nowrap !important;
		background: transparent !important;
		border: 0 !important;
		text-decoration: none !important;
		font-size: 0.875rem !important;
		line-height: 1.5 !important;
		transition: all 0.15s ease !important;
	}

	body:not(.dark-mode) .main-navbar .dropdown-item:hover,
	body:not(.dark-mode) .main-navbar .dropdown-item:focus {
		background: rgba(249, 250, 251, 0.8) !important;
		color: #4f46e5 !important;
		text-decoration: none !important;
	}

	/* Top Bar Dropdown Styles */
	.top-bar .dropdown {
		position: relative !important;
	}

	.top-bar .dropdown-menu {
		position: absolute !important;
		top: 100% !important;
		right: 0 !important;
		left: auto !important;
		z-index: 9999 !important;
		display: none !important;
		float: none !important;
		min-width: 120px !important;
		padding: 0.5rem 0 !important;
		margin: 0.125rem 0 0 !important;
		background: rgba(255, 255, 255, 0.98) !important;
		border: 2px solid rgba(99, 102, 241, 0.3) !important;
		border-radius: 0.5rem !important;
		box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.4), 0 10px 20px -5px rgba(0, 0, 0, 0.2) !important;
		backdrop-filter: blur(10px) !important;
		transform: translateY(0) !important;
		opacity: 0 !important;
		transition: all 0.2s ease !important;
	}

	.top-bar .dropdown-menu.show {
		display: block !important;
		opacity: 1 !important;
		transform: translateY(0) !important;
	}

	.top-bar .dropdown-item {
		display: block !important;
		width: 100% !important;
		padding: .3rem .8rem !important;
		clear: both !important;
		font-weight: 400 !important;
		color: #374151 !important;
		text-align: inherit !important;
		white-space: nowrap !important;
		background: transparent !important;
		border: 0 !important;
		text-decoration: none !important;
		font-size: 0.875rem !important;
		line-height: 1.5 !important;
		transition: all 0.15s ease !important;
	}

	.top-bar .dropdown-item:hover,
	.top-bar .dropdown-item:focus {
		background: rgba(99, 102, 241, 0.1) !important;
		color: #4f46e5 !important;
		text-decoration: none !important;
	}

	.top-bar .dropdown-divider {
		height: 0 !important;
		margin: 0.5rem 0 !important;
		overflow: hidden !important;
		border-top: 1px solid rgba(0, 0, 0, 0.1) !important;
	}

	/* Dark mode top bar dropdown */
	body.dark-mode .top-bar .dropdown-menu {
		background: rgba(31, 41, 55, 0.98) !important;
		border: 2px solid rgba(139, 92, 246, 0.3) !important;
		box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.6), 0 10px 20px -5px rgba(0, 0, 0, 0.3) !important;
	}

	body.dark-mode .top-bar .dropdown-item {
		color: #d1d5db !important;
	}

	body.dark-mode .top-bar .dropdown-item:hover,
	body.dark-mode .top-bar .dropdown-item:focus {
		background: rgba(99, 102, 241, 0.2) !important;
		color: #a5b4fc !important;
	}

	body.dark-mode .top-bar .dropdown-divider {
		border-top: 1px solid rgba(255, 255, 255, 0.1) !important;
	}

	/* Dark mode main navbar dropdown */
	body.dark-mode .main-navbar .dropdown-menu {
		background: rgba(31, 41, 55, 0.95) !important;
		border: 1px solid rgba(75, 85, 99, 0.8) !important;
		box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3), 0 10px 10px -5px rgba(0, 0, 0, 0.1) !important;
		backdrop-filter: blur(10px) !important;
		position: absolute !important;
		top: 100% !important;
		left: 0 !important;
		z-index: 1000 !important;
		display: none !important;
		min-width: 200px !important;
		padding: 0.5rem 0 !important;
		margin: 0.125rem 0 0 !important;
		border-radius: 0.5rem !important;
		opacity: 0 !important;
		transform: translateY(-10px) !important;
		transition: all 0.2s ease !important;
	}

	body.dark-mode .main-navbar .dropdown-menu.show {
		display: block !important;
		opacity: 1 !important;
		transform: translateY(0) !important;
	}

	body.dark-mode .main-navbar .dropdown-item {
		color: #d1d5db !important;
		display: block !important;
		width: 100% !important;
		padding: 0.5rem 1rem !important;
		clear: both !important;
		font-weight: 400 !important;
		text-align: inherit !important;
		white-space: nowrap !important;
		background: transparent !important;
		border: 0 !important;
		text-decoration: none !important;
		font-size: 0.875rem !important;
		line-height: 1.5 !important;
		transition: all 0.15s ease !important;
	}

	body.dark-mode .main-navbar .dropdown-item:hover,
	body.dark-mode .main-navbar .dropdown-item:focus {
		background: rgba(99, 102, 241, 0.2) !important;
		color: #a5b4fc !important;
		text-decoration: none !important;
	}

	/* Compact Mode Styles */
	body .main-navbar .navbar-nav .nav-link.compact-mode .nav-text,
	body .main-navbar .dropdown .nav-link.compact-mode .nav-text {
		display: none !important;
	}

	body .main-navbar .navbar-nav .nav-link.compact-mode,
	body .main-navbar .dropdown .nav-link.compact-mode {
		padding: 0.75rem 1rem !important;
		min-width: auto !important;
		width: auto !important;
	}

	body .main-navbar .navbar-nav .nav-link.compact-mode i,
	body .main-navbar .dropdown .nav-link.compact-mode i {
		margin-right: 0 !important;
		font-size: 1rem !important;
	}

	/* Ensure icons are centered in compact mode */
	body .main-navbar .navbar-nav .nav-link.compact-mode,
	body .main-navbar .dropdown .nav-link.compact-mode {
		display: flex !important;
		align-items: center !important;
		justify-content: center !important;
		text-align: center !important;
	}

	/* Top Bar Compact Mode Styles */
	.top-bar .btn.compact-mode .btn-text {
		display: none !important;
	}

	.top-bar .btn.compact-mode {
		padding: 0.375rem 0.75rem !important;
		min-width: auto !important;
		width: auto !important;
	}

	.top-bar .btn.compact-mode i {
		margin-right: 0 !important;
		font-size: 1rem !important;
	}

	/* Center icons in compact top bar buttons */
	.top-bar .btn.compact-mode {
		display: flex !important;
		align-items: center !important;
		justify-content: center !important;
		text-align: center !important;
	}

	/* Tooltip Styles for Compact Mode */
	.tooltip {
		font-family: "Inter", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
		font-size: 0.75rem !important;
		z-index: 1060 !important;
	}

	.tooltip .tooltip-inner {
		background-color: rgba(31, 41, 55, 0.95) !important;
		color: #ffffff !important;
		border-radius: 0.375rem !important;
		padding: 0.375rem 0.75rem !important;
		font-weight: 500 !important;
		backdrop-filter: blur(10px) !important;
		border: 1px solid rgba(156, 163, 175, 0.3) !important;
		box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06) !important;
	}

	.tooltip.bs-tooltip-bottom .arrow::before {
		border-bottom-color: rgba(31, 41, 55, 0.95) !important;
	}

	.tooltip.bs-tooltip-top .arrow::before {
		border-top-color: rgba(31, 41, 55, 0.95) !important;
	}

	/* Dark mode tooltip adjustments */
	body.dark-mode .tooltip .tooltip-inner {
		background-color: rgba(55, 65, 81, 0.95) !important;
		border: 1px solid rgba(107, 114, 128, 0.3) !important;
	}

	body.dark-mode .tooltip.bs-tooltip-bottom .arrow::before {
		border-bottom-color: rgba(55, 65, 81, 0.95) !important;
	}

	body.dark-mode .tooltip.bs-tooltip-top .arrow::before {
		border-top-color: rgba(55, 65, 81, 0.95) !important;
	}
	</style>

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

	/* ===== MOBILE RESPONSIVE IMPROVEMENTS - COMPLETE REWRITE ===== */

	// Mobile menu toggle functionality - rewritten
	function toggleMobileMenu() {
		console.log('toggleMobileMenu called');
		var overlay = document.querySelector('.mobile-nav-overlay');
		var hamburger = document.querySelector('.mobile-hamburger');

		if (overlay && hamburger) {
			if (overlay.classList.contains('active')) {
				// Close menu
				overlay.classList.remove('active');
				hamburger.classList.remove('active');
				document.body.style.overflow = '';
			} else {
				// Open menu
				overlay.classList.add('active');
				hamburger.classList.add('active');
				document.body.style.overflow = 'hidden';
			}

			// Animate hamburger lines
			var spans = hamburger.querySelectorAll('span');
			if (hamburger.classList.contains('active')) {
				spans[0].style.transform = 'rotate(45deg) translate(5px, 5px)';
				spans[1].style.opacity = '0';
				spans[2].style.transform = 'rotate(-45deg) translate(7px, -6px)';
			} else {
				spans[0].style.transform = '';
				spans[1].style.opacity = '';
				spans[2].style.transform = '';
			}
		}
	}

	// Handle clicks outside menu to close it
	document.addEventListener('click', function(e) {
		// Close language overlay if clicking outside
		var langOverlay = document.querySelector('.mobile-lang-overlay');
		var langIcon = document.querySelector('.mobile-lang-icon');
		if (langOverlay && langIcon && langOverlay.classList.contains('active')) {
			var langMenu = langOverlay.querySelector('.mobile-lang-menu');
			// If clicked outside the language menu or on language icon, close menu
			if (!langMenu.contains(e.target) && !langIcon.contains(e.target)) {
				langOverlay.classList.remove('active');
				document.body.style.overflow = '';
			}
		}

		// Close mobile menu if clicking outside
		var overlay = document.querySelector('.mobile-nav-overlay');
		var hamburger = document.querySelector('.mobile-hamburger');
		var mobileNavMenu = document.querySelector('.mobile-nav-menu');

		if (overlay && hamburger && overlay.classList.contains('active')) {
			// If clicked outside the mobile nav menu or on hamburger, close menu
			if (!mobileNavMenu.contains(e.target) && !hamburger.contains(e.target)) {
				toggleMobileMenu();
			}
		}
	});

	// --- START RESPONSIVE LAYOUT REWRITE ---

	// Function to remove mobile-specific elements and restore desktop view
	function destroyMobileLayout() {
		var mobileHeader = document.querySelector('.mobile-header');
		if (mobileHeader) {
			console.log('Destroying mobile layout.');
			mobileHeader.remove();
		}

		// Restore display of original language switchers so Bootstrap classes can work
		var existingLangSwitchers = document.querySelectorAll('.simple-language-switcher, .language-switcher');
		existingLangSwitchers.forEach(function(switcher) {
			switcher.style.display = ''; // Reset inline style
		});
	}

	// Central handler for responsive layout changes
	function handleLayoutChange() {
		if (window.innerWidth <= 768) {
			// We are on a mobile-sized screen
			setupMobileLayout();
		} else {
			// We are on a desktop-sized screen
			destroyMobileLayout();

			// If mobile menu is open, close it
			var overlay = document.querySelector('.mobile-nav-overlay');
			var hamburger = document.querySelector('.mobile-hamburger');
			if (overlay && hamburger && overlay.classList.contains('active')) {
				toggleMobileMenu();
			}
		}
	}

	// Handle window resize events
	window.addEventListener('resize', handleLayoutChange);

	// Mobile dropdown toggle functionality
	function toggleMobileDropdown(dropdownToggle) {
		console.log('toggleMobileDropdown called');
		var dropdown = dropdownToggle.closest('.dropdown');
		var menu = dropdown.querySelector('.dropdown-menu');

		if (menu) {
			// Close other dropdowns first
			document.querySelectorAll('.mobile-nav-menu .dropdown-menu').forEach(function(otherMenu) {
				if (otherMenu !== menu) {
					otherMenu.classList.remove('show');
				}
			});

			// Toggle current dropdown
			menu.classList.toggle('show');
		}
	}

	// Auto-close mobile menu when clicking nav links
	document.addEventListener('DOMContentLoaded', function() {
		// Set initial layout based on window size
		handleLayoutChange();

		// Add click handlers to mobile nav links
		document.querySelectorAll('.mobile-nav-menu .nav-link:not(.dropdown-toggle)').forEach(function(link) {
			link.addEventListener('click', function() {
				// Close mobile menu after clicking a link
				var overlay = document.querySelector('.mobile-nav-overlay');
				var hamburger = document.querySelector('.mobile-hamburger');

				if (overlay && hamburger && overlay.classList.contains('active')) {
					setTimeout(function() {
						toggleMobileMenu();
					}, 200); // Small delay for better UX
				}
			});
		});

		// Add click handlers to mobile dropdown items
		document.querySelectorAll('.mobile-nav-menu .dropdown-item').forEach(function(item) {
			item.addEventListener('click', function() {
				// Close mobile menu after clicking a dropdown item
				var overlay = document.querySelector('.mobile-nav-overlay');
				var hamburger = document.querySelector('.mobile-hamburger');

				if (overlay && hamburger && overlay.classList.contains('active')) {
					setTimeout(function() {
						toggleMobileMenu();
					}, 200);
				}
			});
		});

		console.log('Mobile navigation initialized');
	});

	// Mobile layout setup function
	function setupMobileLayout() {
		console.log('Setting up mobile layout');

		// Get the top bar container
		var container = document.querySelector('.top-bar .container');
		if (!container) return;

		// Check if mobile header already exists
		if (document.querySelector('.mobile-header')) return;

		// Hide existing language switchers and hamburger menu explicitly
		var existingLangSwitchers = document.querySelectorAll('.simple-language-switcher, .language-switcher');
		existingLangSwitchers.forEach(function(switcher) {
			switcher.style.display = 'none';
		});

		// Hide existing hamburger menu elements have been removed from HTML

		// Create mobile header structure
		var mobileHeader = document.createElement('div');
		mobileHeader.className = 'mobile-header';

		// Create language icon container (left)
		var langContainer = document.createElement('div');
		langContainer.style.position = 'relative';

		var langIcon = document.createElement('div');
		langIcon.className = 'mobile-lang-icon';
		langIcon.innerHTML = '🌐';
		langIcon.setAttribute('role', 'button');
		langIcon.setAttribute('aria-label', 'Select language');
		langIcon.onclick = function(e) {
			e.stopPropagation();
			var overlay = document.querySelector('.mobile-lang-overlay');
			if (overlay) {
				if (overlay.classList.contains('active')) {
					// Close language menu
					overlay.classList.remove('active');
					document.body.style.overflow = '';
				} else {
					// Open language menu
					overlay.classList.add('active');
					document.body.style.overflow = 'hidden';
				}
			}
		};

		// Create language dropdown overlay (similar to mobile nav overlay)
		var langOverlay = document.createElement('div');
		langOverlay.className = 'mobile-lang-overlay';

		var langDropdown = document.createElement('div');
		langDropdown.className = 'mobile-lang-menu';

		// Create all language options (including current language for mobile)
		var allLanguages = [{
				code: 'en-US',
				name: '<span class="lang-code">EN</span> English',
				url: '/site/change-language?language=en-US'
			},
			{
				code: 'es-ES',
				name: '<span class="lang-code">ES</span> Español',
				url: '/site/change-language?language=es-ES'
			},
			{
				code: 'ko-KR',
				name: '<span class="lang-code">KO</span> 한국어',
				url: '/site/change-language?language=ko-KR'
			},
			{
				code: 'zh-CN',
				name: '<span class="lang-code">CN</span> 简体中文',
				url: '/site/change-language?language=zh-CN'
			},
			{
				code: 'zh-TW',
				name: '<span class="lang-code">TW</span> 繁體中文',
				url: '/site/change-language?language=zh-TW'
			}
		];

		// Get current language from HTML lang attribute or default to en-US
		var currentLang = document.documentElement.lang || 'en-US';

		// Add all language options to mobile dropdown
		allLanguages.forEach(function(lang) {
			var langLink = document.createElement('a');
			langLink.href = lang.url;
			langLink.innerHTML = lang.name;
			langLink.className = 'mobile-lang-link';

			// Mark current language
			if (lang.code === currentLang ||
				(currentLang === 'en' && lang.code === 'en-US') ||
				(currentLang === 'es' && lang.code === 'es-ES') ||
				(currentLang === 'ko' && lang.code === 'ko-KR') ||
				(currentLang === 'zh-CN' && lang.code === 'zh-CN') ||
				(currentLang === 'zh-TW' && lang.code === 'zh-TW')) {
				langLink.classList.add('current');
			}

			// Add click handler to close overlay after selection
			langLink.onclick = function() {
				setTimeout(function() {
					var overlay = document.querySelector('.mobile-lang-overlay');
					if (overlay) {
						overlay.classList.remove('active');
						document.body.style.overflow = '';
					}
				}, 100);
			};

			langDropdown.appendChild(langLink);
		});

		// Append dropdown to overlay
		langOverlay.appendChild(langDropdown);

		langContainer.appendChild(langIcon);

		// Add overlay to body (not container)
		document.body.appendChild(langOverlay);

		// Create title (center)
		var titleLink = document.createElement('a');
		titleLink.className = 'mobile-title';
		titleLink.href = '<?= Yii::$app->homeUrl ?>';
		titleLink.textContent = '<?= Html::encode(Yii::$app->params['siteName'] ?? 'Invoice Manager') ?>';

		// Create hamburger menu (right)
		var hamburger = document.createElement('div');
		hamburger.className = 'mobile-hamburger';
		hamburger.setAttribute('role', 'button');
		hamburger.setAttribute('aria-label', 'Toggle menu');
		hamburger.innerHTML = '<span></span><span></span><span></span>';
		hamburger.onclick = toggleMobileMenu;

		// Append elements to mobile header
		mobileHeader.appendChild(langContainer);
		mobileHeader.appendChild(titleLink);
		mobileHeader.appendChild(hamburger);

		// Add mobile header to container
		container.appendChild(mobileHeader);

		console.log('Mobile layout setup complete');
	}
	</script>

	<!-- Mobile Responsive CSS -->
	<style>
	/* ===== MOBILE RESPONSIVE STYLES ===== */

	/* Mobile Utility Classes */
	@media (max-width: 768px) {

		/* Hide elements completely on mobile */
		.mobile-hidden {
			display: none !important;
		}

		/* Hide text but keep element structure and icons */
		.mobile-hide-text {
			font-size: 0 !important;
			text-indent: -9999px !important;
			color: transparent !important;
		}

		/* Show icons even when text is hidden */
		.mobile-hide-text .fa,
		.mobile-hide-text .fas,
		.mobile-hide-text .far,
		.mobile-hide-text .fab,
		.mobile-hide-text i {
			font-size: 1rem !important;
			text-indent: 0 !important;
			color: inherit !important;
			display: inline !important;
		}

		/* Icon-only mode - hide text, keep icons */
		.mobile-icon-only {
			overflow: hidden !important;
			text-indent: -9999px !important;
			white-space: nowrap !important;
		}

		.mobile-icon-only .fa,
		.mobile-icon-only .fas,
		.mobile-icon-only .far,
		.mobile-icon-only .fab,
		.mobile-icon-only i {
			text-indent: 0 !important;
			float: left !important;
			margin-right: 0 !important;
		}

		/* Compact mode - reduce spacing */
		.mobile-compact {
			padding: 0.25rem 0.5rem !important;
			margin: 0.125rem !important;
			font-size: 0.875rem !important;
		}

		/* Full width on mobile */
		.mobile-full-width {
			width: 100% !important;
			display: block !important;
		}
	}

	/* Desktop - restore normal appearance */
	@media (min-width: 769px) {
		.mobile-hidden {
			display: inherit !important;
		}

		.mobile-hide-text {
			font-size: inherit !important;
			text-indent: 0 !important;
			color: inherit !important;
		}

		.mobile-icon-only {
			overflow: visible !important;
			text-indent: 0 !important;
			white-space: normal !important;
		}

		.mobile-icon-only .fa,
		.mobile-icon-only .fas,
		.mobile-icon-only .far,
		.mobile-icon-only .fab,
		.mobile-icon-only i {
			float: none !important;
			margin-right: 0.5rem !important;
		}

		.mobile-compact {
			padding: inherit !important;
			margin: inherit !important;
			font-size: inherit !important;
		}

		.mobile-full-width {
			width: auto !important;
			display: inline !important;
		}
	}

	/* Simple Mobile Layout */
	@media (max-width: 768px) {

		/* Container */
		.container {
			padding-left: 15px !important;
			padding-right: 15px !important;
		}

		/* Mobile Top Bar */
		.top-bar {
			padding: 0.75rem 0 !important;
		}

		.top-bar .container {
			padding: 0 15px !important;
		}

		/* Keep flex layout but modify it */
		.top-bar .d-flex {
			display: flex !important;
			align-items: center !important;
			justify-content: space-between !important;
			position: relative !important;
		}

		/* Title centered */
		.brand-title {
			flex: 1 !important;
			text-align: center !important;
			font-size: 1.2rem !important;
			font-weight: 700 !important;
			margin: 0 !important;
		}

		.brand-link {
			color: #ffffff !important;
			text-decoration: none !important;
			text-shadow: 0 1px 3px rgba(0, 0, 0, 0.3) !important;
		}

		/* Hide desktop elements but keep container structure */
		.top-bar .d-flex .brand-title,
		.top-bar .d-flex .user-menu,

		/* Hide original language switchers */
		.simple-language-switcher,
		.language-switcher {
			display: none !important;
		}

		/* Mobile Header Layout */
		.mobile-header {
			display: flex !important;
			align-items: center !important;
			justify-content: space-between !important;
			width: 100% !important;
			position: relative !important;
		}

		/* Language Icon - Left */
		.mobile-lang-icon {
			width: 40px !important;
			height: 40px !important;
			display: flex !important;
			align-items: center !important;
			justify-content: center !important;
			background: rgba(255, 255, 255, 0.1) !important;
			border: 1px solid rgba(255, 255, 255, 0.2) !important;
			border-radius: 0.5rem !important;
			cursor: pointer !important;
			font-size: 1.2rem !important;
			position: relative !important;
			transition: all 0.3s ease !important;
		}

		.mobile-lang-icon:hover {
			background: rgba(255, 255, 255, 0.2) !important;
			border-color: rgba(255, 255, 255, 0.3) !important;
		}

		/* Title - Center */
		.mobile-title {
			position: absolute !important;
			left: 50% !important;
			transform: translateX(-50%) !important;
			text-align: center !important;
			font-size: 1.1rem !important;
			font-weight: 700 !important;
			color: #ffffff !important;
			text-decoration: none !important;
			text-shadow: 0 1px 3px rgba(0, 0, 0, 0.3) !important;
			white-space: nowrap !important;
		}

		/* Hamburger Menu - Right */
		.mobile-hamburger {
			width: 40px !important;
			height: 40px !important;
			display: flex !important;
			flex-direction: column !important;
			align-items: center !important;
			justify-content: center !important;
			background: rgba(255, 255, 255, 0.1) !important;
			border: 1px solid rgba(255, 255, 255, 0.2) !important;
			border-radius: 0.5rem !important;
			cursor: pointer !important;
			transition: all 0.3s ease !important;
			gap: 3px !important;
		}

		.mobile-hamburger:hover {
			background: rgba(255, 255, 255, 0.2) !important;
			border-color: rgba(255, 255, 255, 0.3) !important;
		}

		.mobile-hamburger.active {
			background: rgba(99, 102, 241, 0.9) !important;
			border-color: rgba(99, 102, 241, 0.8) !important;
		}

		.mobile-hamburger span {
			display: block !important;
			width: 20px !important;
			height: 2px !important;
			background: #ffffff !important;
			transition: all 0.3s ease !important;
		}

		/* Language overlay - Full screen like mobile nav */
		.mobile-lang-overlay {
			position: fixed !important;
			top: 70px !important;
			left: 0 !important;
			right: 0 !important;
			bottom: 0 !important;
			background: rgba(0, 0, 0, 0.5) !important;
			backdrop-filter: none !important;
			z-index: 1055 !important;
			display: none !important;
			padding-top: 1rem !important;
			overflow-y: auto !important;
			opacity: 0 !important;
			transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
			transform: translateY(-10px) scale(0.98) !important;
			border-top: none !important;
		}

		.mobile-lang-overlay.active {
			display: block !important;
			opacity: 1 !important;
			transform: translateY(0) scale(1) !important;
		}

		.mobile-lang-menu {
			padding: 1.5rem 1rem !important;
			max-width: 320px !important;
			margin: 0 auto !important;
			position: relative !important;
			z-index: 1060 !important;
		}

		.mobile-lang-link {
			display: block !important;
			padding: 0.6rem 0.8rem !important;
			margin: 0.25rem 0 !important;
			width: 100% !important;
			text-align: center !important;
			border-radius: 0.5rem !important;
			border: 1px solid rgba(99, 102, 241, 0.3) !important;
			font-weight: 500 !important;
			letter-spacing: 0.025em !important;
			color: #1f2937 !important;
			text-decoration: none !important;
			background: rgba(255, 255, 255, 0.95) !important;
			backdrop-filter: blur(10px) !important;
			transition: all 0.3s ease !important;
			font-size: 0.875rem !important;
			min-height: 42px !important;
			display: flex !important;
			align-items: center !important;
			justify-content: center !important;
			box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
		}

		.mobile-lang-link:hover,
		.mobile-lang-link:active {
			color: #ffffff !important;
			background: rgba(99, 102, 241, 0.95) !important;
			border-color: rgba(99, 102, 241, 0.8) !important;
			transform: translateY(-2px) !important;
			box-shadow: 0 8px 25px rgba(99, 102, 241, 0.4) !important;
		}

		.mobile-lang-link.current {
			background: rgba(99, 102, 241, 0.9) !important;
			color: #ffffff !important;
			border-color: rgba(99, 102, 241, 0.8) !important;
			font-weight: 600 !important;
			box-shadow: 0 8px 25px rgba(99, 102, 241, 0.3) !important;
		}

		/* Modern language code styling for mobile */
		.mobile-lang-link .lang-code {
			display: inline-block !important;
			background: rgba(99, 102, 241, 0.9) !important;
			color: #ffffff !important;
			font-size: 0.6rem !important;
			font-weight: 700 !important;
			padding: 0.1rem 0.3rem !important;
			border-radius: 0.25rem !important;
			margin-right: 0.4rem !important;
			text-transform: uppercase !important;
			letter-spacing: 0.05em !important;
			box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2) !important;
			vertical-align: middle !important;
		}



		/* Mobile menu toggle button - improved design */
		.mobile-hamburger {
			display: flex !important;
			flex-direction: column !important;
			justify-content: center !important;
			align-items: center !important;
			width: 40px !important;
			height: 40px !important;
			background: rgba(255, 255, 255, 0.15) !important;
			border: 1px solid rgba(255, 255, 255, 0.25) !important;
			border-radius: 0.6rem !important;
			cursor: pointer !important;
			transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
			margin-left: 0.3rem !important;
			flex-shrink: 0 !important;
			z-index: 1060 !important;
			box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15) !important;
			backdrop-filter: blur(10px) !important;
		}

		.mobile-hamburger:hover {
			background: rgba(255, 255, 255, 0.25) !important;
			border-color: rgba(255, 255, 255, 0.4) !important;
			transform: scale(1.05) !important;
			box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2) !important;
		}

		.mobile-hamburger.active {
			background: rgba(99, 102, 241, 0.9) !important;
			border-color: rgba(99, 102, 241, 0.8) !important;
			box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3) !important;
		}

		.mobile-hamburger .bar {
			width: 22px !important;
			height: 2.5px !important;
			background: #e5e7eb !important;
			margin: 2.5px 0 !important;
			transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
			border-radius: 2px !important;
			box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1) !important;
		}

		.mobile-hamburger.active .bar {
			background: #ffffff !important;
			box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2) !important;
		}

		/* HIDE DESKTOP NAVIGATION COMPLETELY ON MOBILE */
		.main-navbar {
			display: none !important;
		}

		/* Mobile navigation overlay - completely transparent background */
		.mobile-nav-overlay {
			position: fixed !important;
			top: 70px !important;
			left: 0 !important;
			right: 0 !important;
			bottom: 0 !important;
			background: rgba(0, 0, 0, 0.5) !important;
			backdrop-filter: none !important;
			z-index: 1055 !important;
			display: none !important;
			padding-top: 1rem !important;
			overflow-y: auto !important;
			opacity: 0 !important;
			transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
			transform: translateY(-10px) scale(0.98) !important;
			border-top: none !important;
		}

		.mobile-nav-overlay.active {
			display: block !important;
			opacity: 1 !important;
			transform: translateY(0) scale(1) !important;
		}

		.mobile-nav-menu {
			padding: 1.5rem 1rem !important;
			max-width: 320px !important;
			margin: 0 auto !important;
			position: relative !important;
			z-index: 1060 !important;
		}

		.mobile-nav-menu .nav-link {
			display: block !important;
			padding: 0.6rem 0.8rem !important;
			margin: 0.25rem 0 !important;
			width: 100% !important;
			text-align: center !important;
			border-radius: 0.5rem !important;
			border: 1px solid rgba(99, 102, 241, 0.3) !important;
			font-weight: 500 !important;
			letter-spacing: 0.025em !important;
			color: #1f2937 !important;
			text-decoration: none !important;
			background: rgba(255, 255, 255, 0.95) !important;
			backdrop-filter: blur(10px) !important;
			transition: all 0.3s ease !important;
			font-size: 0.875rem !important;
			min-height: 42px !important;
			display: flex !important;
			align-items: center !important;
			justify-content: center !important;
			box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
		}

		.mobile-nav-menu .nav-link:hover,
		.mobile-nav-menu .nav-link:active {
			color: #ffffff !important;
			background: rgba(99, 102, 241, 0.95) !important;
			border-color: rgba(99, 102, 241, 0.8) !important;
			transform: translateY(-2px) !important;
			box-shadow: 0 8px 25px rgba(99, 102, 241, 0.4) !important;
		}

		.mobile-nav-menu .dropdown {
			width: 100% !important;
			margin: 0.5rem 0 !important;
		}

		.mobile-nav-menu .dropdown-menu {
			position: static !important;
			float: none !important;
			width: 100% !important;
			margin: 0.5rem 0 !important;
			box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
			background: rgba(248, 250, 252, 0.95) !important;
			border: 1px solid rgba(99, 102, 241, 0.2) !important;
			border-radius: 0.5rem !important;
			padding: 0.5rem !important;
			display: none !important;
			backdrop-filter: blur(10px) !important;
		}

		.mobile-nav-menu .dropdown-menu.show {
			display: block !important;
		}

		.mobile-nav-menu .dropdown-item {
			padding: 0.75rem 1rem !important;
			color: #374151 !important;
			border-radius: 0.375rem !important;
			margin: 0.25rem 0 !important;
			transition: all 0.3s ease !important;
			background: transparent !important;
		}

		.mobile-nav-menu .dropdown-item:hover {
			background: rgba(99, 102, 241, 0.1) !important;
			color: #4f46e5 !important;
		}

		/* Mobile navigation extras */
		.mobile-nav-divider {
			height: 1px !important;
			background: rgba(156, 163, 175, 0.3) !important;
			margin: 1rem 0 !important;
		}

		.mobile-nav-logout {
			margin-top: 1rem !important;
			padding-top: 1rem !important;
			border-top: 1px solid rgba(156, 163, 175, 0.3) !important;
		}

		.mobile-nav-logout .logout-btn {
			display: block !important;
			padding: 0.6rem 0.8rem !important;
			margin: 0 !important;
			width: 100% !important;
			text-align: center !important;
			border-radius: 0.5rem !important;
			border: 1px solid rgba(220, 38, 38, 0.3) !important;
			font-weight: 500 !important;
			letter-spacing: 0.025em !important;
			color: #dc2626 !important;
			text-decoration: none !important;
			background: rgba(254, 226, 226, 0.95) !important;
			backdrop-filter: blur(10px) !important;
			transition: all 0.3s ease !important;
			font-size: 0.875rem !important;
			min-height: 42px !important;
			display: flex !important;
			align-items: center !important;
			justify-content: center !important;
			box-shadow: 0 4px 12px rgba(220, 38, 38, 0.15) !important;
			cursor: pointer !important;
		}

		.mobile-nav-logout .logout-btn:hover {
			color: #ffffff !important;
			background: rgba(220, 38, 38, 0.9) !important;
			border-color: rgba(220, 38, 38, 0.8) !important;
			transform: translateY(-1px) !important;
			box-shadow: 0 8px 25px rgba(220, 38, 38, 0.3) !important;
		}

		/* Content area mobile adjustments */
		main {
			margin-top: 80px !important;
			padding-top: 0.5rem !important;
		}

		/* Remove excessive top spacing */
		body {
			padding-top: 0 !important;
			margin-top: 0 !important;
		}

		/* Breadcrumbs mobile */
		.breadcrumb {
			font-size: 0.875rem !important;
			padding: 0.5rem 0 !important;
			margin-bottom: 1rem !important;
		}

		/* Form mobile optimization */
		.form-group {
			margin-bottom: 1rem !important;
		}

		.btn {
			font-size: 0.875rem !important;
			padding: 0.5rem 1rem !important;
		}

		/* Table mobile optimization */
		.table-responsive {
			border: none !important;
		}

		.table {
			font-size: 0.875rem !important;
		}

		.table th,
		.table td {
			padding: 0.5rem !important;
			vertical-align: middle !important;
		}

		/* Card mobile optimization */
		.card {
			margin-bottom: 1rem !important;
			border-radius: 0.75rem !important;
			box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1) !important;
		}

		.card-header {
			padding: 0.75rem 1rem !important;
			border-radius: 0.75rem 0.75rem 0 0 !important;
		}

		.card-body {
			padding: 1rem !important;
		}

		/* Dashboard cards mobile styling */
		.dashboard-card {
			margin-bottom: 1.25rem !important;
		}

		.dashboard-card .card-body {
			text-align: center !important;
			padding: 1.5rem 1rem !important;
		}

		/* Statistics display mobile */
		.stats-number {
			font-size: 2rem !important;
			font-weight: 700 !important;
			line-height: 1.2 !important;
		}

		.stats-label {
			font-size: 0.875rem !important;
			opacity: 0.8 !important;
			margin-top: 0.5rem !important;
		}

		/* Button group mobile */
		.btn-group {
			flex-wrap: wrap !important;
		}

		.btn-group .btn {
			margin: 0.25rem !important;
		}

		/* Dropdown menu mobile improvements */
		.dropdown-menu {
			font-size: 0.875rem !important;
		}

		.dropdown-item {
			padding: 0.5rem 1rem !important;
		}

		/* Language switcher mobile */
		.simple-language-switcher .simple-lang-button,
		.language-switcher .dropdown-toggle {
			font-size: 0.75rem !important;
			padding: 0.4rem 0.6rem !important;
			white-space: nowrap !important;
		}

		/* User menu buttons mobile */
		.user-menu .btn {
			font-size: 0.75rem !important;
			padding: 0.4rem 0.6rem !important;
			white-space: nowrap !important;
		}



		.simple-language-switcher .simple-lang-menu,
		.language-switcher .dropdown-menu {
			min-width: 180px !important;
			font-size: 0.875rem !important;
		}

		/* Footer mobile optimization */
		.footer {
			padding: 1rem 0 !important;
		}

		.footer .container {
			text-align: center !important;
		}

		.footer .float-left,
		.footer .float-right {
			float: none !important;
			display: block !important;
			margin: 0.25rem 0 !important;
			font-size: 0.875rem !important;
		}
	}

	/* Large mobile devices (landscape phones, 576px and up) */
	@media (min-width: 576px) and (max-width: 767.98px) {
		.brand-title {
			font-size: 1.2rem !important;
		}

		.main-navbar .nav-link {
			padding: 0.75rem 1.25rem !important;
		}
	}

	/* Small mobile devices (portrait phones, less than 576px) */
	@media (max-width: 575.98px) {
		.container {
			padding-left: 10px !important;
			padding-right: 10px !important;
		}

		.brand-title {
			font-size: 1rem !important;
			margin: 0 8px !important;
		}

		.user-menu .btn {
			font-size: 0.7rem !important;
			padding: 0.3rem 0.4rem !important;
			min-width: 36px !important;
			height: 36px !important;
		}

		/* Compact layout for small screens */
		.user-menu {
			gap: 0.15rem !important;
		}

		.user-menu .btn,
		.user-menu .dropdown-toggle {
			width: 36px !important;
			height: 36px !important;
		}

		.mobile-hamburger {
			width: 36px !important;
			height: 36px !important;
		}

		/* Even smaller hamburger menu */
		.mobile-hamburger {
			width: 36px !important;
			height: 36px !important;
		}

		.mobile-hamburger .bar {
			width: 18px !important;
		}

		.main-navbar .nav-link {
			padding: 0.875rem 1rem !important;
			font-size: 0.9rem !important;
		}

		.table {
			font-size: 0.75rem !important;
		}

		.btn-sm {
			font-size: 0.75rem !important;
			padding: 0.375rem 0.75rem !important;
		}

		/* Stack form elements */
		.form-row .col-md-6,
		.form-row .col-md-4,
		.form-row .col-md-3 {
			margin-bottom: 0.5rem !important;
		}

		/* Reduce card padding */
		.card-body {
			padding: 0.75rem !important;
		}

		/* Smaller buttons on mobile */
		.btn-group .btn {
			font-size: 0.75rem !important;
			padding: 0.375rem 0.5rem !important;
		}
	}

	/* Hide mobile menu toggle on desktop */
	@media (min-width: 769px) {

		.mobile-nav-overlay,
		.mobile-lang-overlay {
			display: none !important;
		}

		/* Show desktop navigation */
		.main-navbar {
			display: block !important;
		}
	}

	/* Additional mobile breakpoint for larger screens */
	@media (min-width: 992px) {

		/* Reset main navbar positioning for desktop */
		.main-navbar {
			position: fixed !important;
			top: 80px !important;
			left: 0 !important;
			right: 0 !important;
			z-index: 1030 !important;
			padding: 1rem 0 !important;
			background: transparent !important;
			width: 100% !important;
		}

		main {
			margin-top: 20px !important;
			padding-top: 0 !important;
		}
	}

	/* Dark mode mobile adjustments */
	body.dark-mode .mobile-hamburger {
		background: rgba(255, 255, 255, 0.15) !important;
		border-color: rgba(255, 255, 255, 0.25) !important;
	}

	body.dark-mode .mobile-hamburger:hover {
		background: rgba(255, 255, 255, 0.25) !important;
		border-color: rgba(255, 255, 255, 0.35) !important;
	}

	body.dark-mode .mobile-hamburger .bar {
		background: #e5e7eb !important;
	}

	body.dark-mode .main-navbar .navbar-nav {
		background: rgba(17, 24, 39, 0.98) !important;
		border-top-color: rgba(75, 85, 99, 0.5) !important;
	}

	/* Accessibility improvements */
	@media (prefers-reduced-motion: reduce) {

		.mobile-hamburger .bar,
		.main-navbar .navbar-nav,
		.nav-link {
			transition: none !important;
		}
	}

	/* High contrast mode support */
	@media (prefers-contrast: high) {
		.main-navbar .nav-link {
			border-width: 2px !important;
		}

		.mobile-hamburger {
			border-width: 2px !important;
		}
	}

	/* Touch-friendly improvements */
	@media (hover: none) and (pointer: coarse) {

		/* Increase touch targets for better mobile interaction */
		.btn,
		.nav-link,
		.dropdown-item,
		.mobile-hamburger {
			min-height: 44px !important;
			display: flex !important;
			align-items: center !important;
		}

		/* Better touch feedback */
		.nav-link:active,
		.btn:active,
		.dropdown-item:active {
			background: rgba(99, 102, 241, 0.3) !important;
			transform: scale(0.98) !important;
		}

		/* Remove hover effects on touch devices */
		.nav-link:hover,
		.btn:hover {
			transform: none !important;
		}
	}

	/* Orientation changes */
	@media screen and (orientation: landscape) and (max-height: 480px) {
		.main-navbar .navbar-nav {
			max-height: 300px !important;
			overflow-y: auto !important;
		}

		.mobile-hamburger {
			width: 35px !important;
			height: 35px !important;
		}
	}
	</style>
</head>

<?php
// Determine dark mode setting
$currentCompany = null;
if (!Yii::$app->user->isGuest) {
	$companyId = Yii::$app->session->get('current_company_id');
	if ($companyId) {
		$currentCompany = \app\models\Company::findForCurrentUser()->where(['id' => $companyId])->one();
	}
}
$isDarkMode = $currentCompany && $currentCompany->dark_mode;
$isCompactMode = $currentCompany && $currentCompany->compact_mode;
?>

<body class="d-flex flex-column h-100<?= $isDarkMode ? ' dark-mode' : '' ?>">
	<?php $this->beginBody() ?>

	<header>
		<!-- Top Bar -->
		<div class="top-bar">
			<div class="container">
				<div class="d-flex justify-content-between align-items-center">
					<div class="brand-title">
						<?= Html::a(Yii::$app->params['siteName'] ?? 'Invoice Manager', Yii::$app->user->isGuest || !Yii::$app->user->identity->isDemo() ? Yii::$app->homeUrl : ['/demo/index'], ['class' => 'brand-link']) ?>
					</div>
					<div class="user-menu d-flex align-items-center">
						<?php if (Yii::$app->user->isGuest): ?>
						<?= Html::a('<i class="fas fa-sign-in-alt"></i> ' . Yii::t('app/nav', 'Login'), ['/site/login'], ['class' => 'btn btn-outline-light btn-sm login-btn']) ?>
						<?php else: ?>
						<?php
							// Get current company
							$currentCompany = null;
							$companyId = Yii::$app->session->get('current_company_id');
							if ($companyId) {
								$currentCompany = \app\models\Company::findForCurrentUser()->where(['id' => $companyId])->one();
							}
							?>

						<!-- Language Switcher -->
						<div class="mr-3 d-none d-md-block">
							<?= \app\widgets\LanguageSwitcher::widget() ?>
						</div>
						<div class="mr-3 d-md-none">
							<?= \app\widgets\SimpleLanguageSwitcher::widget() ?>
						</div>

						<!-- Change Mode Button -->
						<?php if ($currentCompany): ?>
						<div class="dropdown mr-3">
							<button
								class="btn btn-outline-light btn-sm dropdown-toggle theme-toggle-btn<?= $isCompactMode ? ' compact-mode' : '' ?>"
								type="button" data-toggle="dropdown" aria-expanded="false"
								<?= $isCompactMode ? ' data-compact-tooltip="' . Yii::t('app/nav', 'Change Mode') . '"' : '' ?>>
								<i class="fas fa-palette mr-1"></i><span
									class="btn-text"><?= Yii::t('app/nav', 'Change Mode') ?></span>
							</button>
							<ul class="dropdown-menu">
								<li>
									<a href="#" class="dropdown-item theme-toggle-item"
										data-mode="<?= $currentCompany->dark_mode ? 'light' : 'dark' ?>">
										<i class="fas fa-<?= $currentCompany->dark_mode ? 'sun' : 'moon' ?> mr-2"></i>
										<?= $currentCompany->dark_mode ? Yii::t('app/nav', 'Switch to Light Mode') : Yii::t('app/nav', 'Switch to Dark Mode') ?>
									</a>
								</li>
								<li>
									<a href="#" class="dropdown-item compact-toggle-item"
										data-compact-mode="<?= $currentCompany->compact_mode ? 'normal' : 'compact' ?>">
										<i
											class="fas fa-<?= $currentCompany->compact_mode ? 'expand-arrows-alt' : 'compress-arrows-alt' ?> mr-2"></i>
										<?= $currentCompany->compact_mode ? Yii::t('app/nav', 'Switch to Normal Mode') : Yii::t('app/nav', 'Switch to Compact Mode') ?>
									</a>
								</li>
							</ul>
						</div>
						<?php endif; ?>

						<!-- Company Dropdown -->
						<?php if ($currentCompany): ?>
						<div class="dropdown mr-3">
							<button
								class="btn btn-outline-light btn-sm dropdown-toggle company-btn<?= $isCompactMode ? ' compact-mode' : '' ?>"
								type="button" data-toggle="dropdown" aria-expanded="false"
								<?= $isCompactMode ? ' data-compact-tooltip="' . Html::encode($currentCompany->company_name) . '"' : '' ?>>
								<i class="fas fa-building mr-1"></i><span
									class="btn-text"><?= Html::encode($currentCompany->company_name) ?></span>
							</button>
							<ul class="dropdown-menu">
								<li><?= Html::a('<i class="fas fa-cog mr-2"></i>' . Yii::t('app/nav', 'Settings'), ['/company/settings'], ['class' => 'dropdown-item']) ?>
								</li>
								<li><?= Html::a('<i class="fas fa-exchange-alt mr-2"></i>' . Yii::t('app/nav', 'Switch Company'), ['/company/select'], ['class' => 'dropdown-item']) ?>
								</li>
								<?php if (Yii::$app->user->identity->canCreateMoreCompanies()): ?>
								<li>
									<hr class="dropdown-divider">
								</li>
								<li><?= Html::a('<i class="fas fa-plus mr-2"></i>' . Yii::t('app/nav', 'Add New Company'), ['/company/create'], ['class' => 'dropdown-item']) ?>
								</li>
								<?php endif; ?>
							</ul>
						</div>
						<?php endif; ?>

						<!-- User Dropdown -->
						<div class="dropdown">
							<button
								class="btn btn-outline-light btn-sm dropdown-toggle user-btn<?= $isCompactMode ? ' compact-mode' : '' ?>"
								type="button" data-toggle="dropdown" aria-expanded="false"
								<?= $isCompactMode ? ' data-compact-tooltip="' . Html::encode(Yii::$app->user->identity->getDisplayName()) . '"' : '' ?>>
								<i class="fas fa-user-circle mr-1"></i><span
									class="btn-text"><?= Yii::$app->user->identity->getDisplayName() ?></span>
							</button>
							<ul class="dropdown-menu dropdown-menu-right">
								<?php if (Yii::$app->user->identity->isDemo()): ?>
								<li><?= Html::a('<i class="fas fa-user-check mr-2"></i>' . Yii::t('app/nav', 'Demo Dashboard'), ['/demo/index'], ['class' => 'dropdown-item']) ?>
								</li>
								<li><?= Html::a('<i class="fas fa-refresh mr-2"></i>' . Yii::t('app/nav', 'Reset Demo Data'), ['/demo/reset-demo-data'], ['class' => 'dropdown-item text-danger', 'data' => ['confirm' => Yii::t('app/nav', 'Are you sure you want to reset all demo data?'), 'method' => 'post']]) ?>
								</li>
								<?php else: ?>
								<li><?= Html::a('<i class="fas fa-key mr-2"></i>' . Yii::t('app/nav', 'Change Password'), ['/site/change-password'], ['class' => 'dropdown-item']) ?>
								</li>
								<?php endif; ?>
								<?php if (Yii::$app->user->identity->isAdmin()): ?>
								<li><?= Html::a('<i class="fas fa-cog mr-2"></i>' . Yii::t('app/nav', 'Admin Panel'), ['/admin/index'], ['class' => 'dropdown-item']) ?>
								</li>
								<?php endif; ?>
								<li>
									<hr class="dropdown-divider">
								</li>
								<li>
									<?= Html::beginForm(['/site/logout'], 'post', ['class' => 'd-inline']) ?>
									<?= Html::submitButton('<i class="fas fa-sign-out-alt mr-2"></i>' . Yii::t('app/nav', 'Logout'), ['class' => 'dropdown-item logout-btn']) ?>
									<?= Html::endForm() ?>
								</li>
							</ul>
						</div>

						<?php endif; ?>
					</div>

				</div>
			</div>
		</div>

		<!-- Main Navigation -->
		<?php if (!Yii::$app->user->isGuest): ?>
		<nav class="main-navbar">
			<div class="container">
				<div class="navbar-nav mx-auto">
					<?php
					// Main navigation items with icons
					$dashboardLabel = Yii::t('app/nav', 'Dashboard');
					$invoicesLabel = Yii::t('app/nav', 'Invoices');
					$estimatesLabel = Yii::t('app/nav', 'Estimates');
					$customersLabel = Yii::t('app/nav', 'Customers');
					$productsLabel = Yii::t('app/nav', 'Products');
					
					$mainNavItems = [
						['label' => $dashboardLabel, 'icon' => 'fas fa-home', 'url' => Yii::$app->user->identity->isDemo() ? ['/demo/index'] : ['/site/index']],
						['label' => $invoicesLabel, 'icon' => 'fas fa-file-invoice', 'url' => ['/invoice/index']],
						['label' => $estimatesLabel, 'icon' => 'fas fa-file-alt', 'url' => ['/estimate/index']],
						['label' => $customersLabel, 'icon' => 'fas fa-users', 'url' => ['/customer/index']],
						['label' => $productsLabel, 'icon' => 'fas fa-box', 'url' => ['/product/index']],
					];
					
					// Check if compact mode is enabled
					$isCompactMode = $currentCompany && $currentCompany->compact_mode;
					$compactClass = $isCompactMode ? ' compact-mode' : '';
					
					// Render navigation items directly
					foreach ($mainNavItems as $item) {
						$active = (((Yii::$app->controller->id === 'site' && Yii::$app->controller->action->id === 'index') || 
								   (Yii::$app->controller->id === 'demo' && Yii::$app->controller->action->id === 'index')) && $item['label'] === $dashboardLabel) ||
								  (Yii::$app->controller->id === 'invoice' && $item['label'] === $invoicesLabel) ||
								  (Yii::$app->controller->id === 'estimate' && $item['label'] === $estimatesLabel) ||
								  (Yii::$app->controller->id === 'customer' && $item['label'] === $customersLabel) ||
								  (Yii::$app->controller->id === 'product' && $item['label'] === $productsLabel);
						
						$activeClass = $active ? ' active' : '';
						$iconHtml = '<i class="' . $item['icon'] . ' mr-1"></i>';
						$labelHtml = '<span class="nav-text">' . $item['label'] . '</span>';
						$linkOptions = ['class' => 'nav-link' . $activeClass . $compactClass];
						
						// Add tooltip for compact mode
						if ($isCompactMode) {
							$linkOptions['title'] = $item['label'];
							$linkOptions['data-toggle'] = 'tooltip';
							$linkOptions['data-placement'] = 'bottom';
						}
						
						echo Html::a($iconHtml . $labelHtml, $item['url'], $linkOptions);
					}
					?>

					<!-- Create Dropdown -->
					<div class="dropdown">
						<a class="nav-link dropdown-toggle<?= $compactClass ?>" href="#" role="button"
							aria-expanded="false"
							<?= $isCompactMode ? ' data-compact-tooltip="' . Yii::t('app/nav', 'Create') . '"' : '' ?>>
							<i class="fas fa-plus mr-1"></i><span
								class="nav-text"><?= Yii::t('app/nav', 'Create') ?></span>
						</a>
						<div class="dropdown-menu">
							<?= Html::a('<i class="fas fa-file-invoice mr-2"></i>' . Yii::t('app/nav', 'New Invoice'), ['/invoice/create'], ['class' => 'dropdown-item']) ?>
							<?= Html::a('<i class="fas fa-file-alt mr-2"></i>' . Yii::t('app/nav', 'New Estimate'), ['/estimate/create'], ['class' => 'dropdown-item']) ?>
							<?= Html::a('<i class="fas fa-users mr-2"></i>' . Yii::t('app/nav', 'New Customer'), ['/customer/create'], ['class' => 'dropdown-item']) ?>
							<?= Html::a('<i class="fas fa-box mr-2"></i>' . Yii::t('app/nav', 'New Product'), ['/product/create'], ['class' => 'dropdown-item']) ?>
						</div>
					</div>
				</div>
			</div>
		</nav>

		<!-- Mobile Navigation Overlay -->
		<div class="mobile-nav-overlay">
			<div class="mobile-nav-menu">
				<?php
				// Mobile navigation items
				foreach ($mainNavItems as $item) {
					$active = (((Yii::$app->controller->id === 'site' && Yii::$app->controller->action->id === 'index') || 
							   (Yii::$app->controller->id === 'demo' && Yii::$app->controller->action->id === 'index')) && $item['label'] === $dashboardLabel) ||
							  (Yii::$app->controller->id === 'invoice' && $item['label'] === $invoicesLabel) ||
							  (Yii::$app->controller->id === 'estimate' && $item['label'] === $estimatesLabel) ||
							  (Yii::$app->controller->id === 'customer' && $item['label'] === $customersLabel) ||
							  (Yii::$app->controller->id === 'product' && $item['label'] === $productsLabel);
					
					$activeClass = $active ? ' active' : '';
					echo Html::a($item['label'], $item['url'], ['class' => 'nav-link mobile-nav-link' . $activeClass]);
				}
				?>

				<!-- Mobile Create Dropdown -->
				<div class="dropdown">
					<a class="nav-link mobile-nav-link dropdown-toggle" href="#"
						onclick="toggleMobileDropdown(this); return false;" role="button" aria-expanded="false">
						<i class="fas fa-plus mr-1"></i><?= Yii::t('app/nav', 'Create') ?>
					</a>
					<div class="dropdown-menu">
						<?= Html::a('<i class="fas fa-file-invoice mr-2"></i>' . Yii::t('app/nav', 'New Invoice'), ['/invoice/create'], ['class' => 'dropdown-item']) ?>
						<?= Html::a('<i class="fas fa-file-alt mr-2"></i>' . Yii::t('app/nav', 'New Estimate'), ['/estimate/create'], ['class' => 'dropdown-item']) ?>
						<?= Html::a('<i class="fas fa-users mr-2"></i>' . Yii::t('app/nav', 'New Customer'), ['/customer/create'], ['class' => 'dropdown-item']) ?>
						<?= Html::a('<i class="fas fa-box mr-2"></i>' . Yii::t('app/nav', 'New Product'), ['/product/create'], ['class' => 'dropdown-item']) ?>
					</div>
				</div>

				<!-- Divider -->
				<div class="mobile-nav-divider"></div>

				<!-- Settings Menu -->
				<?= Html::a('<i class="fas fa-cog mr-2"></i>' . Yii::t('app/nav', 'Settings'), ['/company/settings'], ['class' => 'nav-link mobile-nav-link']) ?>

				<!-- Change Mode -->
				<?php if ($currentCompany): ?>
				<a href="#" class="nav-link mobile-nav-link theme-toggle-item"
					data-mode="<?= $currentCompany->dark_mode ? 'light' : 'dark' ?>">
					<i class="fas fa-<?= $currentCompany->dark_mode ? 'sun' : 'moon' ?> mr-2"></i>
					<?= $currentCompany->dark_mode ? Yii::t('app/nav', 'Switch to Light Mode') : Yii::t('app/nav', 'Switch to Dark Mode') ?>
				</a>
				<?php endif; ?>

				<!-- Change Password (for non-demo users) -->
				<?php if (!Yii::$app->user->identity->isDemo()): ?>
				<?= Html::a('<i class="fas fa-key mr-2"></i>' . Yii::t('app/nav', 'Change Password'), ['/site/change-password'], ['class' => 'nav-link mobile-nav-link']) ?>
				<?php endif; ?>

				<!-- Admin Panel (for admin users) -->
				<?php if (Yii::$app->user->identity->isAdmin()): ?>
				<?= Html::a('<i class="fas fa-shield-alt mr-2"></i>' . Yii::t('app/nav', 'Admin Panel'), ['/admin/index'], ['class' => 'nav-link mobile-nav-link']) ?>
				<?php endif; ?>

				<!-- Logout -->
				<div class="mobile-nav-logout">
					<?= Html::beginForm(['/site/logout'], 'post', ['class' => 'd-inline']) ?>
					<?= Html::submitButton('<i class="fas fa-sign-out-alt mr-2"></i>' . Yii::t('app/nav', 'Logout'), ['class' => 'nav-link mobile-nav-link logout-btn']) ?>
					<?= Html::endForm() ?>
				</div>
			</div>
		</div>
		<?php endif; ?>
	</header>

	<?php
	// Always load the site.css file for proper navigation styling
	$this->registerCssFile('@web/css/site.css');
	?>

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
			$company = null;
			if (!Yii::$app->user->isGuest) {
				$companyId = Yii::$app->session->get('current_company_id');
				if ($companyId) {
					$company = \app\models\Company::findForCurrentUser()->where(['id' => $companyId])->one();
				}
			}
			$siteName = Yii::$app->params['siteName'] ?? 'Invoice Manager';
			?>
			<p class="float-left">&copy; <?= Html::encode($siteName) ?> <?= date('Y') ?></p>
			<p class="float-right"><?= Yii::powered() ?></p>
		</div>
	</footer>

	<?php $this->endBody() ?>

	<!-- Initialize Tooltips for Compact Mode -->
	<script>
	// Initialize tooltips when DOM is ready
	$(document).ready(function() {
		// Initialize tooltips for navigation elements with data-toggle="tooltip"
		$('[data-toggle="tooltip"]').tooltip({
			container: 'body',
			delay: {
				show: 500,
				hide: 100
			},
			trigger: 'hover focus'
		});

		// Initialize custom tooltips for compact mode elements
		$('[data-compact-tooltip]').each(function() {
			var $this = $(this);
			var tooltipText = $this.attr('data-compact-tooltip');

			if (tooltipText) {
				$this.tooltip({
					title: tooltipText,
					placement: 'bottom',
					container: 'body',
					delay: {
						show: 500,
						hide: 100
					},
					trigger: 'hover focus'
				});
			}
		});
	});
	</script>

	<script>
	// Debug function to check if elements exist
	function debugDropdowns() {
		console.log('=== Dropdown Debug Info ===');
		console.log('jQuery loaded:', typeof jQuery !== 'undefined');
		console.log('Bootstrap loaded:', typeof bootstrap !== 'undefined');
		console.log('Top bar dropdowns found:', $('.top-bar .dropdown').length);
		console.log('Dropdown toggles found:', $('.top-bar .dropdown-toggle').length);
		console.log('Dropdown menus found:', $('.top-bar .dropdown-menu').length);
		console.log('Language switcher found:', $('.language-switcher').length);
		console.log('Language switcher items found:', $('.language-switcher .dropdown-item').length);

		$('.top-bar .dropdown-toggle').each(function(i) {
			console.log('Dropdown toggle ' + i + ':', $(this).text().trim());
		});
	}

	// Initialize Bootstrap 4 dropdowns and navigation
	$(document).ready(function() {
		// Run debug function
		debugDropdowns();

		// Debug main navbar dropdowns
		console.log('Main navbar dropdown toggles found:', $('.main-navbar .dropdown-toggle').length);
		console.log('Main navbar dropdown menus found:', $('.main-navbar .dropdown-menu').length);

		// Initialize Bootstrap 4 dropdowns
		try {
			$('[data-toggle="dropdown"]').dropdown();
			console.log('Bootstrap dropdown initialization complete');
		} catch (e) {
			console.error('Bootstrap dropdown initialization failed:', e);
		}

		// Test all possible event handlers for top bar dropdowns (excluding language switcher)
		$('.top-bar .dropdown-toggle').not('.language-switcher .dropdown-toggle').off('click').on('click',
			function(e) {
				console.log('=== Top bar dropdown clicked ===');
				console.log('Target element:', this);
				console.log('Element text:', $(this).text().trim());

				e.preventDefault();
				e.stopPropagation();

				var $button = $(this);
				var $dropdown = $button.closest('.dropdown');
				var $menu = $dropdown.find('.dropdown-menu');

				console.log('Dropdown container:', $dropdown.length);
				console.log('Menu element:', $menu.length);

				// Close all other dropdowns first
				$('.top-bar .dropdown').not($dropdown).removeClass('show');
				$('.top-bar .dropdown-menu').not($menu).removeClass('show');

				// Toggle current dropdown
				var isOpen = $dropdown.hasClass('show');
				console.log('Current state - isOpen:', isOpen);

				if (isOpen) {
					$dropdown.removeClass('show');
					$menu.removeClass('show');
					console.log('Dropdown closed');
				} else {
					$dropdown.addClass('show');
					$menu.addClass('show');
					console.log('Dropdown opened');
				}
			});

		// Alternative event binding methods (excluding language switcher)
		$('.top-bar').on('click', '.dropdown-toggle:not(.language-switcher .dropdown-toggle)', function(e) {
			console.log('=== Alternative click handler triggered ===');
			e.preventDefault();
			e.stopPropagation();

			var $button = $(this);
			var $dropdown = $button.closest('.dropdown');
			var $menu = $dropdown.find('.dropdown-menu');

			// Close all other dropdowns
			$('.top-bar .dropdown').not($dropdown).removeClass('show');
			$('.top-bar .dropdown-menu').not($menu).removeClass('show');

			// Toggle current dropdown
			$dropdown.toggleClass('show');
			$menu.toggleClass('show');
		});

		// Test with mousedown event
		$('.top-bar .dropdown-toggle').on('mousedown', function(e) {
			console.log('=== Mousedown event on dropdown toggle ===');
		});

		// Enhanced dropdown behavior for main navigation
		$('.main-navbar .dropdown-toggle').on('click', function(e) {
			console.log('=== Main navbar dropdown clicked ===');
			e.preventDefault();
			e.stopPropagation();

			var $toggle = $(this);
			var $dropdown = $toggle.closest('.dropdown');
			var $menu = $toggle.next('.dropdown-menu');

			console.log('Toggle found:', $toggle.length);
			console.log('Dropdown found:', $dropdown.length);
			console.log('Menu found:', $menu.length);

			// Close all other dropdowns
			$('.main-navbar .dropdown').not($dropdown).removeClass('show');
			$('.main-navbar .dropdown-menu').not($menu).removeClass('show');

			// Toggle current dropdown
			$dropdown.toggleClass('show');
			$menu.toggleClass('show');

			console.log('Menu has show class:', $menu.hasClass('show'));
		});

		// Close dropdown when clicking outside
		$(document).on('click', function(e) {
			if (!$(e.target).closest('.dropdown').length) {
				$('.dropdown').removeClass('show');
				$('.dropdown-menu').removeClass('show');
			}
		});

		// Ensure proper navigation styling is applied
		$('.navbar-nav .nav-link').each(function() {
			var href = $(this).attr('href');
			if (href && window.location.pathname.indexOf(href) === 0) {
				$(this).addClass('active');
			}
		});

		// Theme toggle functionality
		$('.theme-toggle-item').on('click', function(e) {
			e.preventDefault();

			var $button = $(this);
			var mode = $button.data('mode');

			// Show loading state
			$button.html('<i class="fas fa-spinner fa-spin mr-2"></i>Switching...');

			// Make AJAX request
			$.ajax({
				url: '<?= \yii\helpers\Url::to(['/site/toggle-theme']) ?>',
				type: 'POST',
				data: {
					'<?= Yii::$app->request->csrfParam ?>': '<?= Yii::$app->request->csrfToken ?>',
					mode: mode
				},
				success: function(response) {
					if (response.success) {
						// Reload page to apply new theme
						window.location.reload();
					} else {
						alert('Failed to change theme: ' + (response.message ||
							'Unknown error'));
						// Reset button
						var icon = mode === 'dark' ? 'moon' : 'sun';
						var text = mode === 'dark' ?
							'<?= Yii::t('app/nav', 'Switch to Dark Mode') ?>' :
							'<?= Yii::t('app/nav', 'Switch to Light Mode') ?>';
						$button.html('<i class="fas fa-' + icon + ' mr-2"></i>' + text);
					}
				},
				error: function() {
					alert('Failed to change theme. Please try again.');
					// Reset button
					var icon = mode === 'dark' ? 'moon' : 'sun';
					var text = mode === 'dark' ?
						'<?= Yii::t('app/nav', 'Switch to Dark Mode') ?>' :
						'<?= Yii::t('app/nav', 'Switch to Light Mode') ?>';
					$button.html('<i class="fas fa-' + icon + ' mr-2"></i>' + text);
				}
			});
		});

		// Compact Mode Toggle Handler
		$('.compact-toggle-item').on('click', function(e) {
			e.preventDefault();

			var $button = $(this);
			var currentMode = $button.data('compact-mode');

			// Show loading state
			var originalHtml = $button.html();
			$button.html('<i class="fas fa-spinner fa-spin mr-2"></i>Switching...');

			// Make AJAX request
			$.ajax({
				url: '<?= \yii\helpers\Url::to(['/company/toggle-compact-mode']) ?>',
				type: 'POST',
				data: {
					'<?= Yii::$app->request->csrfParam ?>': '<?= Yii::$app->request->csrfToken ?>'
				},
				success: function(response) {
					if (response.success) {
						// Show success message briefly
						$button.html('<i class="fas fa-check mr-2"></i>' + response.message);

						// Reload page to apply compact mode changes
						setTimeout(function() {
							window.location.reload();
						}, 1000);
					} else {
						alert('Failed to toggle compact mode: ' + (response.message ||
							'Unknown error'));
						// Reset button
						$button.html(originalHtml);
					}
				},
				error: function() {
					alert('Failed to toggle compact mode. Please try again.');
					// Reset button
					$button.html(originalHtml);
				}
			});
		});
	});
	</script>

	<!-- Collapse Helper Script -->
	<script src="<?= Yii::$app->request->baseUrl ?>/js/collapse-helper.js?v=<?= time() ?>"></script>
</body>

</html>
<?php $this->endPage() ?>