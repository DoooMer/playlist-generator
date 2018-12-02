<?php
return [
    'id' => 'playlist-generator',
    // basePath (базовый путь) приложения будет каталог `micro-app`
    'basePath' => __DIR__ . '/../',
    // это пространство имен где приложение будет искать все контроллеры
    'controllerNamespace' => 'app\controllers',
    // установим псевдоним '@micro', чтобы включить автозагрузку классов из пространства имен 'micro'
    'aliases' => [
        '@micro' => __DIR__ . '/../',
    ],
    'components' => [
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
        ],
    ],
];
