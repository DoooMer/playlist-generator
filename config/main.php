<?php
return [
    'id' => 'playlist-generator',
    'basePath' => __DIR__ . '/../',
    'controllerNamespace' => 'app\controllers',
    'aliases' => [
        '@app' => __DIR__ . '/../',
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
                        'POST ' => 'include',
                    ],
                ],
                '/music' => 'data/mp3',
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
    ],
];
