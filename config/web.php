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
                "keycloak" => [
                    "class" => "yii\authclient\OpenIdConnect",
                    "clientId" => 'musiccli',
                    "clientSecret" => '4zeVGXohTLnGDcRm7UYETosSEZK2N5gn',
                    "returnUrl" => 'http://localhost/index.php?r=site/auth-callback',
                    "issuerUrl" => 'http://localhost:8180/realms/music-api/',
                    "name" => "keycloak",
                    "validateAuthState" => true,

                    "autoRefreshAccessToken" => true,
                    "validateJws" => false,

                    "stateStorage" => [
                        "class" => "yii\authclient\SessionStateStorage",
                        "session" => "session",
                    ],
                    "scope" => "openid profile",
                ],
            ],
        ],
  'httpClient' => [
        'class' => \yii\httpclient\Client::class,
    ],
        'request' => [
            'cookieValidationKey' => 'G8sGVSeYi4RqnvsY0wqa5oNVd8TXnNLl',
            'enableCsrfValidation' => false, 
            
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'session' => [
        'class' => 'yii\web\Session',
        'cookieParams' => [
            'httponly' => true,
            'secure' => false, 
        ],
    ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
            'enableSession' => false
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => \yii\symfonymailer\Mailer::class,
            'viewPath' => '@app/mail',
            'useFileTransport' => true,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning', 'info'],
                    'logFile' => '@runtime/logs/app.log', 
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
   
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
       'allowedIPs' => ['127.0.0.1', '::1', '172.18.*.*'],

        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],


    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        'allowedIPs' => ['127.0.0.1', '::1', '172.18.*.*'],

        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],

    ];
}

return $config;
