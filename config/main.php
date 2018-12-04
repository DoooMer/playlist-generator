<?php
return [
    'id' => 'playlist-generator',
    'basePath' => __DIR__ . '/../',
    'controllerNamespace' => 'app\controllers',
    'aliases' => [
        '@app' => dirname(__DIR__),
    ],
    'components' => [
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                [
                    'class' => \yii\rest\UrlRule::class,
                    'controller' => 'file',
                    'extraPatterns' => [
                        'GET <directory>' => 'scan',
                        'POST ' => 'include',
                        'POST create' => 'create',
                    ],
                ],
                [
                    'class' => \yii\rest\UrlRule::class,
                    'controller' => 'playlist',
                    'extraPatterns' => [
                        'POST ' => 'create',
                    ],
                ],
            ],
        ],
        'playlistUrlManager' => [
            'class' => \yii\web\UrlManager::class,
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'baseUrl' => 'http://192.168.1.2:84',
            'rules' => [
                '/external/<filename>' => 'music/external',
            ],
        ],
        'request' => [
            'parsers' => [
                'application/json' => \yii\web\JsonParser::class,
            ],
        ],
        'response' => [
            'format' => \yii\web\Response::FORMAT_JSON,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => \yii\log\FileTarget::class,
                    'levels' => ['error'],
                    'logVars' => ['_GET', '_POST'],
                    'exportInterval' => 1,
                ],
            ],
            'flushInterval' => 1,
        ],
    ],
    'params' => [
        'dataDirectoryAliases' => [
            // system_path => url
            '@app/testing' => '/music/external',
        ],
    ],
    'container' => [
        'definitions' => [
            \app\services\PlaylistService::class => [
                \app\services\PlaylistService::class,
                [
                    '@app/runtime/playlist',
                    '@app/web/playlist',
                    \yii\di\Instance::of('playlistUrlManager'),
                    \yii\di\Instance::of(\yii\web\View::class),
                    \yii\di\Instance::of(\app\services\FileService::class),
                ],
            ]
        ],
    ],
];
