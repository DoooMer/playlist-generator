<?php

namespace app\controllers;

use yii\helpers\Url;
use yii\rest\Controller;

class HelloController extends Controller
{
    public function behaviors()
    {
        return [];
    }

    public function actionIndex()
    {
        return [
            'files' => Url::to(['file/index'], true),
            'playlists' => Url::to(['playlist/index'], true),
        ];
    }
}