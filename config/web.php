<?php

// Define the path to the custom font directory
define('K_PATH_FONTS', dirname(__DIR__) . '/fonts/tcpdf-fonts/');

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log', 'app\components\LanguageBootstrap'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'language' => 'en-US',
    'sourceLanguage' => 'en-US',
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'uU_LmYuKvoQLzZgEPDKs3@PveVG_eX0',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => 'yii\symfonymailer\Mailer',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure transport
            // for the mailer to send real emails.
            'useFileTransport' => true,
            'messageConfig' => [
                'charset' => 'UTF-8',
                'from' => [$params['senderEmail'] => $params['senderName']],
            ],
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning', 'info'],
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['info'],
                    'categories' => ['app\controllers\*'],
                    'logFile' => '@runtime/logs/controller.log',
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['info', 'warning'],
                    'categories' => ['language'],
                    'logFile' => '@runtime/logs/language.log',
                ],
            ],
        ],
        'db' => $db,
        'i18n' => [
            'translations' => [
                'app*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@app/messages',
                    'sourceLanguage' => 'en-US',
                    'fileMap' => [
                        'app' => 'app.php',
                        'app/error' => 'error.php',
                        'app/invoice' => 'invoice.php',
                        'app/estimate' => 'estimate.php',
                        'app/customer' => 'customer.php',
                        'app/product' => 'product.php',
                        'app/company' => 'company.php',
                        'app/nav' => 'nav.php',
                        'app/form' => 'form.php',
                    ],
                ],
                'invoice' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@app/messages',
                    'sourceLanguage' => 'en-US',
                    'forceTranslation' => true,
                    'fileMap' => [
                        'invoice' => 'invoice.php',
                    ],
                ],
                'estimate' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@app/messages',
                    'sourceLanguage' => 'en-US',
                    'forceTranslation' => true,
                    'fileMap' => [
                        'estimate' => 'estimate.php',
                    ],
                ],
            ],
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
			'rules' => [
				'request-password-reset' => 'site/request-password-reset',
				'reset-password/<token:.+>' => 'site/reset-password',
				'admin' => 'admin/index',
				'admin/<action:\w+>' => 'admin/<action>',
				'admin/<action:\w+>/<id:\d+>' => 'admin/<action>',
				'state-tax-rate' => 'state-tax-rate/index',
				'state-tax-rate/<action:\w+>' => 'state-tax-rate/<action>',
				'state-tax-rate/<action:\w+>/<id:\d+>' => 'state-tax-rate/<action>',
				'demo' => 'demo/index',
				'demo/<action:\w+>' => 'demo/<action>',
				'customer-import' => 'customer-import/index',
				'customer-import/<action:\w+>' => 'customer-import/<action>',
				'product-import' => 'product-import/index',
				'product-import/<action:\w+>' => 'product-import/<action>',
				'ai-helper/<action:\w+>' => 'ai-helper/<action>',
				'<controller:\w+>/<action:\w+>' => '<controller>/<action>',
				'<controller:\w+>/<id:\d+>' => '<controller>/view',
				'<controller:\w+>/<action:\w+>/<id:\d+>' => '<controller>/<action>',
			],
		],
    ],
	'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    
    // Only load debug module if the class exists
    if (class_exists('yii\debug\Module')) {
        $config['bootstrap'][] = 'debug';
        $config['modules']['debug'] = [
            'class' => 'yii\debug\Module',
            // uncomment the following to add your IP if you are not connecting from localhost.
            'allowedIPs' => ['127.0.0.1', '::1'],
        ];
    }

    // Only load gii module if the class exists
    if (class_exists('yii\gii\Module')) {
        $config['bootstrap'][] = 'gii';
        $config['modules']['gii'] = [
            'class' => 'yii\gii\Module',
            // uncomment the following to add your IP if you are not connecting from localhost.
            //'allowedIPs' => ['127.0.0.1', '::1'],
        ];
    }
}

// Load local configuration if it exists
$localConfigPath = __DIR__ . '/web-local.php';
if (file_exists($localConfigPath)) {
    $localConfig = require $localConfigPath;
    $config = \yii\helpers\ArrayHelper::merge($config, $localConfig);
}

return $config;