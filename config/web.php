<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm' => '@vendor/npm-asset',
    ],
    'components' => [
        'authClientCollection' => [
            'class' => 'yii\authclient\Collection',
            'clients' => [
                "spotify" => [
                    'class' => 'app\components\SpotifyAuthClient',
                    'clientId' => 'ac4d4e733e0046ad91b602bbd2b17580',
                    'clientSecret' => '1bd82a57fc834d0c8e10886de0827044',
                    'authUrl' => 'https://accounts.spotify.com/authorize',
                    'tokenUrl' => 'https://accounts.spotify.com/api/token',
                    'apiBaseUrl' => 'https://api.spotify.com/v1',
                    'returnUrl' => 'http://localhost:80/index.php?r=spotify/auth-callback',
                    'scope' => 'user-read-email user-read-private playlist-read-private',
                ],


                // "keycloak" => [
                //     "class" => "yii\authclient\OpenIdConnect",
                //     "clientId" => 'musicapi',
                //     "clientSecret" => 'YiMc6ZeKT0AQG7TdBiKipYKJF3JoVbLp',
                //     "returnUrl" => 'http://localhost:80/index.php?r=site/auth-callback',
                //     "issuerUrl" => 'http://host.docker.internal:8180/realms/musicapi/',
                //     "name" => "keycloak",
                //     "validateAuthState" => true,

                //     "autoRefreshAccessToken" => true,
                //     "validateJws" => false,

                //     "stateStorage" => [
                //         "class" => "yii\authclient\SessionStateStorage",
                //         "session" => "session",
                //     ],
                //     "scope" => "openid profile",
                // ],
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