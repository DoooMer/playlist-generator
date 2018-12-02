<?php

namespace app\controllers;

use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\rest\Controller;

/**
 * Контроллер файлов
 */
class FileController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [];
    }

    /**
     * Список файлов в директории
     */
    public function actionIndex(): array
    {
        $files = [];

        foreach (new \FilesystemIterator(\Yii::getAlias('@app/testing')) as $file) {
            /** @var \SplFileInfo $file */
            if ($file->isDir()) {
                continue;
            }

            $newName = str_replace(' ', '_', mb_strtolower($file->getBasename()));
            rename(
                $file->getPath() . DIRECTORY_SEPARATOR . $file->getBasename(),
                $file->getPath() . DIRECTORY_SEPARATOR . $newName
            );

            $files[] = [
                'path' => $file->getPath(),
                'basename' => $newName,
            ];
        }

        return $files;
    }

    /**
     * Добавление файла в плейлист
     */
    public function actionInclude(): void
    {
        $playlistName = \Yii::$app->getRequest()->getBodyParam('playlist', 'playlist');
        $path = \Yii::$app->getRequest()->getBodyParam('path');
        $basename = \Yii::$app->getRequest()->getBodyParam('basename');

        $pathAliases = [
            \Yii::getAlias('@app/testing') => Url::to(['/data/mp3'], true),
        ];

        $playlistPath = \Yii::getAlias("@app/testing/{$playlistName}.json");

        if (file_exists($playlistPath)) {
            $rawContent = file_get_contents($playlistPath);

            if (!empty($rawContent)) {
                $content = Json::decode($rawContent);
            } else {
                $content = [];
            }

        } else {
            $content = [];
        }

        $files = array_unique(ArrayHelper::merge(
            $content, [ArrayHelper::getValue($pathAliases, $path) . DIRECTORY_SEPARATOR . $basename]
        ));
        $preparedPlaylist = Json::encode($files);

        file_put_contents($playlistPath, $preparedPlaylist);

        \Yii::$app->getResponse()->setStatusCode(201);
    }
}
