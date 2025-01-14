<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'components' => [
        'authClientCollection' => [
            'class' => 'yii\authclient\Collection',
            'clients' => [
                'keycloak' => [
                    'class' => 'yii\authclient\OpenIdConnect', // Указываем путь к вашему классу
                    'authUrl' => 'http://192.168.0.215:8180/realms/musicapi/protocol/openid-connect/auth',
                    "issuerUrl" => 'http://192.168.0.215:8180/realms/musicapi/',
                    'apiBaseUrl' => 'http://192.168.0.215:8180/realms/musicapi/protocol/openid-connect',
                    'clientId' => 'musicapi', // Замените на ваш Client ID
                    'clientSecret' => 'YiMc6ZeKT0AQG7TdBiKipYKJF3JoVbLp', // Замените на ваш Client Secret
                    'scope' => 'openid profile email',
                    'returnUrl' => 'http://localhost:80/index.php?r=site/auth-callback',
                    "name" => "keycloak",
                    "validateAuthState" => true,
                    "autoRefreshAccessToken" => true,
                    "validateJws" => false,
                    "stateStorage" => [
                        "class" => "yii\authclient\SessionStateStorage",
                        "session" => "session",
                    ],
                    "scope" => "openid profile email",

                ],
            ],
        ],

        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'G8sGVSeYi4RqnvsY0wqa5oNVd8TXnNLl',
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
            'class' => \yii\symfonymailer\Mailer::class,
            'viewPath' => '@app/mail',
            // send all mails to a file by default.
            'useFileTransport' => true,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
        /*
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
            ],
        ],
        */
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}

return $config;