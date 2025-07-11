<?php

/** @var yii\web\View $this */
/** @var string $content */

use yii\bootstrap4\Html;

?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" class="h-100">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <?php $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?> - <?= Yii::$app->params['siteName'] ?? 'JIMS' ?></title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@400;500;600;700&family=Playfair+Display:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <?php $this->head() ?>
    
    <style>
        /* Modern Auth Layout with Bootstrap 5 */
        :root {
            --auth-primary: #6366f1;
            --auth-secondary: #8b5cf6;
            --auth-accent: #06b6d4;
            --auth-success: #10b981;
            --auth-warning: #f59e0b;
            --auth-danger: #ef4444;
            --auth-bg-start: #667eea;
            --auth-bg-end: #764ba2;
            --auth-card-bg: rgba(255, 255, 255, 0.95);
            --auth-text: #1f2937;
            --auth-text-muted: #6b7280;
            --auth-border: #e5e7eb;
            --auth-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            --auth-shadow-lg: 0 35px 60px -12px rgba(0, 0, 0, 0.3);
        }
        
        /* Global Styles */
        * {
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, var(--auth-bg-start) 0%, var(--auth-bg-end) 100%);
            min-height: 100vh;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            position: relative;
            overflow-x: hidden;
        }
        
        /* Animated Background */
        body::before {
            content: '';
            position: fixed;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            animation: float 6s ease-in-out infinite;
            pointer-events: none;
        }
        
        body::after {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000"><defs><radialGradient id="a"><stop offset="0" stop-color="%23ffffff" stop-opacity="0.15"/><stop offset="1" stop-color="%23ffffff" stop-opacity="0"/></radialGradient></defs><g opacity="0.6"><circle cx="200" cy="200" r="180" fill="url(%23a)" class="floating-circle"/><circle cx="800" cy="300" r="120" fill="url(%23a)" class="floating-circle"/><circle cx="600" cy="700" r="140" fill="url(%23a)" class="floating-circle"/><circle cx="100" cy="800" r="100" fill="url(%23a)" class="floating-circle"/></g></svg>') no-repeat center center;
            background-size: cover;
            pointer-events: none;
            animation: backgroundFloat 8s ease-in-out infinite;
        }
        
        /* Floating Particles */
        .floating-particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 1;
        }
        
        .particle {
            position: absolute;
            width: 4px;
            height: 4px;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            animation: particleFloat 15s linear infinite;
        }
        
        .particle:nth-child(1) { left: 10%; animation-delay: 0s; }
        .particle:nth-child(2) { left: 20%; animation-delay: 2s; }
        .particle:nth-child(3) { left: 30%; animation-delay: 4s; }
        .particle:nth-child(4) { left: 40%; animation-delay: 6s; }
        .particle:nth-child(5) { left: 50%; animation-delay: 8s; }
        .particle:nth-child(6) { left: 60%; animation-delay: 10s; }
        .particle:nth-child(7) { left: 70%; animation-delay: 12s; }
        .particle:nth-child(8) { left: 80%; animation-delay: 14s; }
        .particle:nth-child(9) { left: 90%; animation-delay: 16s; }
        .particle:nth-child(10) { left: 15%; animation-delay: 18s; }
        
        @keyframes particleFloat {
            0% {
                transform: translateY(100vh) rotate(0deg);
                opacity: 0;
            }
            10% {
                opacity: 1;
            }
            90% {
                opacity: 1;
            }
            100% {
                transform: translateY(-100vh) rotate(360deg);
                opacity: 0;
            }
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }
        
        @keyframes backgroundFloat {
            0%, 100% { opacity: 0.6; transform: scale(1); }
            50% { opacity: 0.8; transform: scale(1.05); }
        }
        
        /* Top Bar */
        .top-bar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            padding: 1rem 0;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1040;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .top-bar .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }
        
        .brand-title {
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: -0.025em;
        }
        
        .brand-link {
            color: var(--auth-text) !important;
            text-decoration: none !important;
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: -0.025em;
            transition: all 0.2s ease;
        }
        
        .brand-link:hover {
            color: var(--auth-primary) !important;
            text-decoration: none !important;
        }
        
        .login-btn {
            color: var(--auth-text) !important;
            border: 1px solid var(--auth-border) !important;
            background: rgba(255, 255, 255, 0.8) !important;
            font-size: 0.875rem !important;
            padding: 0.5rem 1rem !important;
            text-decoration: none !important;
            border-radius: 8px !important;
            font-weight: 500 !important;
            transition: all 0.2s ease !important;
            backdrop-filter: blur(10px) !important;
        }
        
        .login-btn:hover {
            background: white !important;
            border-color: var(--auth-primary) !important;
            color: var(--auth-primary) !important;
            text-decoration: none !important;
            transform: translateY(-1px) !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
        }
        
        /* Main Content Area */
        .auth-main-content {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 100px 0 2rem 0;
            min-height: calc(100vh - 140px);
        }
        
        /* Auth Container */
        .auth-container {
            width: 100%;
            max-width: 650px;
            padding: 1.5rem;
            position: relative;
            z-index: 10;
        }
        
        /* Auth Card */
        .auth-card {
            background: var(--auth-card-bg);
            border-radius: 20px;
            box-shadow: var(--auth-shadow);
            padding: 2.5rem;
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            position: relative;
            overflow: hidden;
            transform: translateY(0);
            transition: all 0.3s ease;
        }
        
        .auth-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--auth-shadow-lg);
        }
        
        .auth-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--auth-primary), var(--auth-secondary), var(--auth-accent));
            border-radius: 24px 24px 0 0;
            animation: gradientShift 3s ease-in-out infinite;
        }
        
        .auth-card::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: conic-gradient(from 0deg, transparent, rgba(99, 102, 241, 0.1), transparent);
            animation: rotate 20s linear infinite;
            pointer-events: none;
        }
        
        @keyframes gradientShift {
            0%, 100% {
                background: linear-gradient(90deg, var(--auth-primary), var(--auth-secondary), var(--auth-accent));
            }
            50% {
                background: linear-gradient(90deg, var(--auth-accent), var(--auth-primary), var(--auth-secondary));
            }
        }
        
        @keyframes rotate {
            0% {
                transform: rotate(0deg);
            }
            100% {
                transform: rotate(360deg);
            }
        }
        
        /* Auth Header */
        .auth-header {
            text-align: center;
            margin-bottom: 1.5rem;
        }
        
        .auth-logo {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, var(--auth-primary), var(--auth-secondary));
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            box-shadow: 0 8px 24px rgba(99, 102, 241, 0.4);
            position: relative;
        }
        
        .auth-logo::before {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: linear-gradient(135deg, var(--auth-primary), var(--auth-secondary), var(--auth-accent));
            border-radius: 26px;
            z-index: -1;
            opacity: 0.3;
            animation: pulse 2s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 0.3; }
            50% { transform: scale(1.05); opacity: 0.6; }
        }
        
        .auth-logo i {
            font-size: 1.8rem;
            color: white;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
        }
        
        .auth-title {
            font-family: 'Poppins', 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
            font-size: 2rem !important;
            font-weight: 700 !important;
            color: var(--auth-text);
            margin-bottom: 0.25rem;
            line-height: 1.2;
            letter-spacing: -0.02em;
        }
        
        .auth-subtitle {
            color: var(--auth-text-muted);
            font-size: 0.875rem;
            margin-bottom: 0;
            line-height: 1.4;
            font-weight: 400;
        }
        
        /* Form Styles */
        .auth-form {
            margin-bottom: 1rem;
        }
        
        .form-floating {
            margin-bottom: 1.25rem;
        }
        
        .form-floating > .form-control {
            height: 50px;
            border: 2px solid var(--auth-border);
            border-radius: 12px;
            padding: 0.75rem 1rem;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
        }
        
        .form-floating > .form-control:focus {
            border-color: var(--auth-primary);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.15);
            background: white;
            transform: translateY(-2px) scale(1.02);
        }
        
        .form-floating > .form-control:not(:placeholder-shown) {
            border-color: var(--auth-success);
        }
        
        .form-floating > label {
            color: var(--auth-text-muted);
            font-weight: 500;
            padding: 0.75rem 1rem;
            font-size: 0.8rem;
        }
        
        .form-floating > .form-control:focus ~ label,
        .form-floating > .form-control:not(:placeholder-shown) ~ label {
            color: var(--auth-primary);
            transform: scale(0.85) translateY(-0.5rem) translateX(0.15rem);
        }
        
        /* Form Options */
        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 0.75rem;
        }
        
        .form-check {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .form-check-input {
            width: 20px;
            height: 20px;
            border-radius: 6px;
            border: 2px solid var(--auth-border);
            background: white;
            transition: all 0.2s ease;
        }
        
        .form-check-input:checked {
            background: var(--auth-primary);
            border-color: var(--auth-primary);
        }
        
        .form-check-label {
            font-size: 0.875rem;
            color: var(--auth-text-muted);
            font-weight: 500;
            margin: 0;
        }
        
        .forgot-link {
            font-size: 0.875rem;
            color: var(--auth-primary);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s ease;
            position: relative;
        }
        
        .forgot-link::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--auth-primary);
            transition: width 0.2s ease;
        }
        
        .forgot-link:hover::after {
            width: 100%;
        }
        
        .forgot-link:hover {
            color: var(--auth-secondary);
            text-decoration: none;
        }
        
        /* Buttons */
        .btn-auth {
            height: 48px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            border: none;
            letter-spacing: 0.02em;
            width: 100% !important;
            text-align: center !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }
        
        .btn-auth::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.6s ease;
        }
        
        .btn-auth:hover::before {
            left: 100%;
        }
        
        .btn-primary.btn-auth {
            background: linear-gradient(135deg, var(--auth-primary), var(--auth-secondary));
            color: white;
            box-shadow: 0 8px 32px rgba(99, 102, 241, 0.4);
        }
        
        .btn-primary.btn-auth:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 12px 40px rgba(99, 102, 241, 0.5);
        }
        
        .btn-primary.btn-auth:active {
            transform: translateY(-1px) scale(0.98);
            transition: all 0.1s ease;
        }
        
        .btn-outline-primary.btn-auth {
            border: 2px solid var(--auth-primary);
            color: var(--auth-primary);
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
        }
        
        .btn-outline-primary.btn-auth:hover {
            background: var(--auth-primary);
            color: white;
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 8px 32px rgba(99, 102, 241, 0.4);
        }
        
        .btn-outline-primary.btn-auth:active {
            transform: translateY(-1px) scale(0.98);
            transition: all 0.1s ease;
        }
        
        .btn-google.btn-auth {
            background: rgba(255, 255, 255, 0.95);
            border: 2px solid var(--auth-border);
            color: var(--auth-text);
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 0.75rem;
            text-decoration: none;
            backdrop-filter: blur(10px);
            text-align: center !important;
        }
        
        .btn-google.btn-auth:hover {
            background: white;
            border-color: #dadce0;
            color: var(--auth-text);
            text-decoration: none;
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            text-align: center !important;
        }
        
        .btn-google.btn-auth:active {
            transform: translateY(-1px) scale(0.98);
            transition: all 0.1s ease;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            text-align: center !important;
        }
        
        .btn-google.btn-auth i {
            font-size: 1.25rem;
            color: #ea4335;
        }
        
        /* Ensure Bootstrap btn-block works properly */
        .btn-block,
        .btn-block + .btn-block {
            width: 100% !important;
            display: block !important;
        }
        
        /* Form actions styling */
        .form-actions .btn {
            width: 100% !important;
        }
        
        /* Auth social buttons */
        .auth-social .btn {
            width: 100% !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            text-align: center !important;
        }
        
        /* Auth footer buttons */
        .auth-footer .btn {
            width: 100% !important;
            text-align: center !important;
        }
        
        /* Ensure all buttons have centered text */
        .btn-block {
            text-align: center !important;
        }
        
        /* Specific styling for outline buttons */
        .btn-outline-primary.btn-auth {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            text-align: center !important;
        }
        
        /* Dividers */
        .auth-divider {
            text-align: center;
            margin: 1.5rem 0;
            position: relative;
            color: var(--auth-text-muted);
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .auth-divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--auth-border), transparent);
        }
        
        .auth-divider span {
            background: var(--auth-card-bg);
            padding: 0 1.5rem;
            position: relative;
            z-index: 1;
        }
        
        /* Auth Footer */
        .auth-footer {
            margin-top: 1rem;
        }
        
        /* Page Footer */
        .footer {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            padding: 1.5rem 0;
            margin-top: auto;
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .footer .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }
        
        .footer p {
            color: var(--auth-text-muted);
            font-size: 0.875rem;
            margin: 0;
        }
        
        .auth-demo-info {
            text-align: center;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--auth-border);
        }
        
        .auth-demo-info small {
            color: var(--auth-text-muted);
            font-size: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        
        .auth-demo-info i {
            color: var(--auth-primary);
        }
        
        /* Validation Feedback */
        .invalid-feedback {
            color: var(--auth-danger);
            font-size: 0.875rem;
            margin-top: 0.5rem;
            display: block;
        }
        
        .is-invalid {
            border-color: var(--auth-danger) !important;
            box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.15) !important;
        }
        
        /* Loading States */
        .btn-loading {
            pointer-events: none;
            opacity: 0.7;
        }
        
        .btn-loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 20px;
            height: 20px;
            margin-top: -10px;
            margin-left: -10px;
            border: 2px solid transparent;
            border-top: 2px solid currentColor;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Responsive Design */
        @media (max-width: 992px) {
            .auth-container {
                max-width: 600px;
                padding: 1.25rem;
            }
            
            .auth-card {
                padding: 2rem 1.5rem;
            }
            
            .top-bar .container,
            .footer .container {
                padding: 0 1.5rem;
            }
        }
        
        @media (max-width: 768px) {
            .auth-main-content {
                padding: 80px 0.75rem 1rem 0.75rem;
            }
            
            .auth-container {
                padding: 0.75rem;
                max-width: 100%;
            }
            
            .auth-card {
                padding: 1.5rem 1.25rem;
                border-radius: 16px;
                margin: 0;
            }
            
            .auth-logo {
                width: 56px;
                height: 56px;
            }
            
            .auth-logo i {
                font-size: 1.5rem;
            }
            
            .auth-title {
                font-size: 1.75rem;
                font-family: 'Poppins', 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            }
            
            .form-floating > .form-control,
            .btn-auth {
                height: 44px;
            }
            
            .form-options {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.75rem;
            }
            
            .particle {
                display: none;
            }
            
            .auth-form-grid {
                grid-template-columns: 1fr;
                gap: 0.75rem;
            }
            
            .brand-title {
                font-size: 1.25rem;
            }
            
            .top-bar .container,
            .footer .container {
                padding: 0 1rem;
            }
            
            .login-btn {
                font-size: 0.8rem !important;
                padding: 0.4rem 0.8rem !important;
            }
        }
        
        @media (max-width: 576px) {
            .auth-card {
                padding: 1.25rem 1rem;
                border-radius: 12px;
            }
            
            .auth-title {
                font-size: 1.5rem;
                font-family: 'Poppins', 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            }
            
            .auth-subtitle {
                font-size: 0.8rem;
            }
            
            .form-floating > .form-control,
            .btn-auth {
                height: 42px;
            }
            
            .auth-logo {
                width: 48px;
                height: 48px;
            }
            
            .auth-logo i {
                font-size: 1.25rem;
            }
        }
        
        @media (max-width: 480px) {
            .auth-main-content {
                padding: 70px 0.5rem 1rem 0.5rem;
            }
            
            .auth-container {
                padding: 0.5rem;
            }
            
            .auth-card {
                padding: 1rem 0.75rem;
                border-radius: 10px;
            }
            
            .auth-header {
                margin-bottom: 1rem;
            }
            
            .auth-title {
                font-size: 1.25rem;
                font-family: 'Poppins', 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            }
            
            .auth-subtitle {
                font-size: 0.75rem;
            }
            
            .form-floating {
                margin-bottom: 0.75rem;
            }
            
            .form-floating > .form-control,
            .btn-auth {
                height: 40px;
                font-size: 0.85rem;
            }
            
            .auth-logo {
                width: 40px;
                height: 40px;
                margin-bottom: 0.75rem;
            }
            
            .auth-logo i {
                font-size: 1rem;
            }
            
            .brand-title {
                font-size: 1.125rem;
            }
        }
        
        @media (max-width: 360px) {
            .auth-card {
                padding: 0.75rem 0.5rem;
            }
            
            .auth-title {
                font-size: 1.125rem;
                font-family: 'Poppins', 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            }
            
            .auth-subtitle {
                font-size: 0.7rem;
            }
            
            .form-floating > .form-control,
            .btn-auth {
                height: 38px;
                font-size: 0.8rem;
            }
        }
        
        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(40px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-40px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .auth-card {
            animation: fadeInUp 0.8s ease-out;
        }
        
        .auth-header {
            animation: fadeInDown 0.8s ease-out 0.2s both;
        }
        
        .auth-form {
            animation: fadeInUp 0.8s ease-out 0.4s both;
        }
        
        .form-floating {
            animation: fadeInUp 0.6s ease-out calc(0.6s + var(--delay, 0s)) both;
        }
        
        .form-floating:nth-child(1) { --delay: 0.1s; }
        .form-floating:nth-child(2) { --delay: 0.2s; }
        .form-floating:nth-child(3) { --delay: 0.3s; }
        .form-floating:nth-child(4) { --delay: 0.4s; }
        .form-floating:nth-child(5) { --delay: 0.5s; }
        
        .form-actions {
            animation: fadeInUp 0.8s ease-out 1s both;
        }
        
        .auth-social {
            animation: fadeInUp 0.8s ease-out 1.2s both;
        }
        
        .auth-footer {
            animation: fadeInUp 0.8s ease-out 1.4s both;
        }
        
        /* Form Layout - Two Column for Signup */
        .auth-form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .auth-form-grid .form-floating {
            margin-bottom: 0;
        }
        
        .auth-form-full {
            grid-column: 1 / -1;
        }
        
        /* Dark Mode Support */
        @media (prefers-color-scheme: dark) {
            :root {
                --auth-card-bg: rgba(31, 41, 55, 0.95);
                --auth-text: #f9fafb;
                --auth-text-muted: #d1d5db;
                --auth-border: #374151;
            }
            
            .form-floating > .form-control {
                background: rgba(55, 65, 81, 0.9);
                color: var(--auth-text);
            }
            
            .form-floating > .form-control:focus {
                background: rgba(55, 65, 81, 1);
            }
            
            .btn-google.btn-auth {
                background: rgba(55, 65, 81, 0.95);
                border-color: var(--auth-border);
                color: var(--auth-text);
            }
            
            .btn-google.btn-auth:hover {
                background: rgba(55, 65, 81, 1);
            }
            
            .top-bar {
                background: rgba(31, 41, 55, 0.95);
                border-bottom-color: rgba(55, 65, 81, 0.5);
            }
            
            .footer {
                background: rgba(31, 41, 55, 0.95);
                border-top-color: rgba(55, 65, 81, 0.5);
            }
            
            .brand-link {
                color: var(--auth-text) !important;
            }
            
            .brand-link:hover {
                color: #60a5fa !important;
            }
            
            .login-btn {
                background: rgba(55, 65, 81, 0.8) !important;
                border-color: var(--auth-border) !important;
                color: var(--auth-text) !important;
            }
            
            .login-btn:hover {
                background: rgba(55, 65, 81, 1) !important;
                border-color: #60a5fa !important;
                color: #60a5fa !important;
            }
        }
        
        /* High Contrast Mode */
        @media (prefers-contrast: high) {
            :root {
                --auth-shadow: 0 4px 8px rgba(0, 0, 0, 0.5);
                --auth-shadow-lg: 0 8px 16px rgba(0, 0, 0, 0.6);
            }
            
            .auth-card {
                border: 2px solid var(--auth-primary);
            }
            
            .form-floating > .form-control {
                border-width: 2px;
            }
            
            .btn-auth {
                border-width: 2px;
            }
        }
        
        /* Reduced Motion */
        @media (prefers-reduced-motion: reduce) {
            * {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
            
            .particle {
                display: none;
            }
            
            .auth-card::after {
                display: none;
            }
        }
    </style>
</head>
<body>
    <?php $this->beginBody() ?>
    
    <!-- Top Bar -->
    <div class="top-bar">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div class="brand-title">
                    <?= \yii\bootstrap4\Html::a(\Yii::$app->params['siteName'] ?? 'Invoice Manager', \Yii::$app->homeUrl, ['class' => 'brand-link']) ?>
                </div>
                <div class="user-menu d-flex align-items-center">
                    <?php if (\Yii::$app->user->isGuest): ?>
                        <?= \yii\bootstrap4\Html::a('<i class="fas fa-sign-in-alt"></i> Login', ['/site/login'], ['class' => 'btn btn-outline-light btn-sm login-btn']) ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="auth-main-content">
        <?= $content ?>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <?php 
            $siteName = \Yii::$app->params['siteName'] ?? 'Invoice Manager';
            ?>
            <p class="text-center">&copy; <?= \yii\bootstrap4\Html::encode($siteName) ?> <?= date('Y') ?></p>
        </div>
    </footer>
    
    <?php $this->endBody() ?>
    
    <!-- Bootstrap 5 JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Add floating particles
        function createParticles() {
            const particlesContainer = document.createElement('div');
            particlesContainer.className = 'floating-particles';
            
            // Reduce particles on mobile
            const particleCount = window.innerWidth > 768 ? 10 : 5;
            
            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                particlesContainer.appendChild(particle);
            }
            
            document.body.appendChild(particlesContainer);
        }
        
        // Add loading state to form submissions
        document.addEventListener('DOMContentLoaded', function() {
            // Create floating particles only on larger screens
            if (window.innerWidth > 768) {
                createParticles();
            }
            
            const forms = document.querySelectorAll('.auth-form-inner');
            forms.forEach(form => {
                form.addEventListener('submit', function() {
                    const submitBtn = form.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Please wait...';
                        submitBtn.disabled = true;
                        submitBtn.classList.add('btn-loading');
                    }
                });
            });
            
            // Add focus effects to form inputs
            const inputs = document.querySelectorAll('.form-control');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.classList.add('focused');
                });
                
                input.addEventListener('blur', function() {
                    if (!this.value) {
                        this.parentElement.classList.remove('focused');
                    }
                });
                
                // Add typing animation
                input.addEventListener('input', function() {
                    this.style.transform = 'scale(1.01)';
                    setTimeout(() => {
                        this.style.transform = 'scale(1)';
                    }, 100);
                });
            });
            
            // Add hover effects to buttons
            const buttons = document.querySelectorAll('.btn-auth');
            buttons.forEach(button => {
                button.addEventListener('mouseenter', function() {
                    this.style.transition = 'all 0.3s ease';
                });
                
                button.addEventListener('mouseleave', function() {
                    this.style.transition = 'all 0.3s ease';
                });
            });
            
            // Handle window resize
            window.addEventListener('resize', function() {
                const particles = document.querySelector('.floating-particles');
                if (particles) {
                    if (window.innerWidth <= 768) {
                        particles.style.display = 'none';
                    } else {
                        particles.style.display = 'block';
                    }
                }
            });
        });
    </script>
</body>
</html>
<?php $this->endPage() ?>