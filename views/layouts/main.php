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
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@400;500;600;700&family=Playfair+Display:wght@400;500;600;700;800&display=swap" rel="stylesheet">

	<?php $this->head() ?>

	<!-- Fallback CDN resources in case local assets fail -->
	<script>
	// Check if jQuery is loaded, if not load from CDN
	if (typeof jQuery === 'undefined') {
		document.write('<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"><\/script>');
	}
	</script>
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
		min-width: 200px !important;
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
	</script>
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
?>

<body class="d-flex flex-column h-100<?= $isDarkMode ? ' dark-mode' : '' ?>">
	<?php $this->beginBody() ?>

	<header>
		<!-- Top Bar -->
		<div class="top-bar">
			<div class="container">
				<div class="d-flex justify-content-between align-items-center">
					<div class="brand-title">
						<?= Html::a(Yii::$app->params['siteName'] ?? 'Invoice Manager', Yii::$app->homeUrl, ['class' => 'brand-link']) ?>
					</div>
					<div class="user-menu d-flex align-items-center">
						<?php if (Yii::$app->user->isGuest): ?>
						<?= Html::a('<i class="fas fa-sign-in-alt"></i> Login', ['/site/login'], ['class' => 'btn btn-outline-light btn-sm login-btn']) ?>
						<?php else: ?>
						<?php
							// Get current company
							$currentCompany = null;
							$companyId = Yii::$app->session->get('current_company_id');
							if ($companyId) {
								$currentCompany = \app\models\Company::findForCurrentUser()->where(['id' => $companyId])->one();
							}
							?>


						<!-- Company Dropdown -->
						<?php if ($currentCompany): ?>
						<div class="dropdown mr-3">
							<button class="btn btn-outline-light btn-sm dropdown-toggle company-btn" type="button"
								data-toggle="dropdown" aria-expanded="false">
								<i class="fas fa-building mr-1"></i><?= Html::encode($currentCompany->company_name) ?>
							</button>
							<ul class="dropdown-menu">
								<li><?= Html::a('<i class="fas fa-exchange-alt mr-2"></i>Switch Company', ['/company/select'], ['class' => 'dropdown-item']) ?>
								</li>
								<li><?= Html::a('<i class="fas fa-cog mr-2"></i>Settings', ['/company/settings'], ['class' => 'dropdown-item']) ?>
								</li>
								<?php if (Yii::$app->user->identity->canCreateMoreCompanies()): ?>
								<li>
									<hr class="dropdown-divider">
								</li>
								<li><?= Html::a('<i class="fas fa-plus mr-2"></i>Add New Company', ['/company/create'], ['class' => 'dropdown-item']) ?>
								</li>
								<?php endif; ?>
							</ul>
						</div>
						<?php endif; ?>

						<!-- User Dropdown -->
						<div class="dropdown">
							<button class="btn btn-outline-light btn-sm dropdown-toggle user-btn" type="button"
								data-toggle="dropdown" aria-expanded="false">
								<i
									class="fas fa-user-circle mr-1"></i><?= Yii::$app->user->identity->getDisplayName() ?>
							</button>
							<ul class="dropdown-menu dropdown-menu-right">
								<?php if (Yii::$app->user->identity->isDemo()): ?>
								<li><?= Html::a('<i class="fas fa-user-check mr-2"></i>Demo Dashboard', ['/demo/index'], ['class' => 'dropdown-item']) ?>
								</li>
								<li><?= Html::a('<i class="fas fa-refresh mr-2"></i>Reset Demo Data', ['/demo/reset-demo-data'], ['class' => 'dropdown-item text-danger', 'data' => ['confirm' => 'Are you sure you want to reset all demo data?', 'method' => 'post']]) ?>
								</li>
								<?php else: ?>
								<li><?= Html::a('<i class="fas fa-key mr-2"></i>Change Password', ['/site/change-password'], ['class' => 'dropdown-item']) ?>
								</li>
								<?php endif; ?>
								<?php if (Yii::$app->user->identity->isAdmin()): ?>
								<li><?= Html::a('<i class="fas fa-cog mr-2"></i>Admin Panel', ['/admin/index'], ['class' => 'dropdown-item']) ?>
								</li>
								<?php endif; ?>
								<li>
									<hr class="dropdown-divider">
								</li>
								<li>
									<?= Html::beginForm(['/site/logout'], 'post', ['class' => 'd-inline']) ?>
									<?= Html::submitButton('<i class="fas fa-sign-out-alt mr-2"></i>Logout', ['class' => 'dropdown-item logout-btn']) ?>
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
					// Main navigation items
					$mainNavItems = [
						['label' => 'Dashboard', 'url' => Yii::$app->user->identity->isDemo() ? ['/demo/index'] : ['/site/index']],
						['label' => 'Invoices', 'url' => ['/invoice/index']],
						['label' => 'Estimates', 'url' => ['/estimate/index']],
						['label' => 'Customers', 'url' => ['/customer/index']],
						['label' => 'Products', 'url' => ['/product/index']],
						['label' => 'Categories', 'url' => ['/category/index']],
					];
					
					// Render navigation items directly
					foreach ($mainNavItems as $item) {
						$active = (((Yii::$app->controller->id === 'site' && Yii::$app->controller->action->id === 'index') || 
								   (Yii::$app->controller->id === 'demo' && Yii::$app->controller->action->id === 'index')) && $item['label'] === 'Dashboard') ||
								  (Yii::$app->controller->id === 'invoice' && $item['label'] === 'Invoices') ||
								  (Yii::$app->controller->id === 'estimate' && $item['label'] === 'Estimates') ||
								  (Yii::$app->controller->id === 'customer' && $item['label'] === 'Customers') ||
								  (Yii::$app->controller->id === 'product' && $item['label'] === 'Products') ||
								  (Yii::$app->controller->id === 'category' && $item['label'] === 'Categories');
						
						$activeClass = $active ? ' active' : '';
						echo Html::a($item['label'], $item['url'], ['class' => 'nav-link' . $activeClass]);
					}
					?>

					<!-- Create Dropdown -->
					<div class="dropdown">
						<a class="nav-link dropdown-toggle" href="#" role="button" aria-expanded="false">
							<i class="fas fa-plus mr-1"></i>Create
						</a>
						<div class="dropdown-menu">
							<?= Html::a('<i class="fas fa-file-invoice mr-2"></i>New Invoice', ['/invoice/create'], ['class' => 'dropdown-item']) ?>
							<?= Html::a('<i class="fas fa-file-alt mr-2"></i>New Estimate', ['/estimate/create'], ['class' => 'dropdown-item']) ?>
							<?= Html::a('<i class="fas fa-users mr-2"></i>New Customer', ['/customer/create'], ['class' => 'dropdown-item']) ?>
							<?= Html::a('<i class="fas fa-box mr-2"></i>New Product', ['/product/create'], ['class' => 'dropdown-item']) ?>
							<?= Html::a('<i class="fas fa-tag mr-2"></i>New Category', ['/category/create'], ['class' => 'dropdown-item']) ?>
						</div>
					</div>
				</div>
			</div>
		</nav>
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

	<script>
	// Debug function to check if elements exist
	function debugDropdowns() {
		console.log('=== Dropdown Debug Info ===');
		console.log('jQuery loaded:', typeof jQuery !== 'undefined');
		console.log('Bootstrap loaded:', typeof bootstrap !== 'undefined');
		console.log('Top bar dropdowns found:', $('.top-bar .dropdown').length);
		console.log('Dropdown toggles found:', $('.top-bar .dropdown-toggle').length);
		console.log('Dropdown menus found:', $('.top-bar .dropdown-menu').length);

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

		// Test all possible event handlers for top bar dropdowns
		$('.top-bar .dropdown-toggle').off('click').on('click', function(e) {
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

		// Alternative event binding methods
		$('.top-bar').on('click', '.dropdown-toggle', function(e) {
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
	});
	</script>
	
	<!-- Collapse Helper Script -->
	<script src="<?= Yii::$app->request->baseUrl ?>/js/collapse-helper.js?v=<?= time() ?>"></script>
</body>

</html>
<?php $this->endPage() ?>