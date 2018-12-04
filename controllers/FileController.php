<?php

namespace app\controllers;

use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\rest\Controller;
use yii\web\BadRequestHttpException;
use yii\web\ServerErrorHttpException;

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
        $paths = [];
        $pathAliases = ArrayHelper::getValue(\Yii::$app->params, 'dataDirectoryAliases', []);

        foreach ($pathAliases as $path => $alias) {
            $paths[] = sha1(Yii::getAlias($path));
        }

        return $paths;
    }

    /**
     * Список файлов в заданной директории.
     *
     * @param string $directory
     * @return array
     * @throws BadRequestHttpException
     * @throws ServerErrorHttpException
     */
    public function actionScan(string $directory): array
    {
        $files = [];
        $pathAliases = ArrayHelper::getValue(\Yii::$app->params, 'dataDirectoryAliases', []);
        $hashPaths = [];

        foreach ($pathAliases as $path => $alias) {
            $realPath = Yii::getAlias($path);
            $hashPaths[sha1($realPath)] = $realPath;
        }

        if (!array_key_exists($directory, $hashPaths)) {
            throw new BadRequestHttpException('Невозможно обработать запрос для заданной директории.');
        }

        $directoryPath = ArrayHelper::getValue($hashPaths, $directory);

        if (!$directoryPath) {
            throw new ServerErrorHttpException('На стороне сервера произошла ошибка.');
        }

        foreach (new \FilesystemIterator($directoryPath) as $file) {
            /** @var \SplFileInfo $file */
            if ($file->isDir()) {
                continue;
            }

            if (\in_array($file->getExtension(), ['json', 'm3u'])) {
                continue;
            }

            $newName = str_replace(' ', '_', mb_strtolower($file->getBasename()));
            rename(
                $file->getPath() . DIRECTORY_SEPARATOR . $file->getBasename(),
                $file->getPath() . DIRECTORY_SEPARATOR . $newName
            );

            $files[] = [
                'basename' => $newName,
            ];
        }

        return $files;
    }

    /**
     * Добавление файла в плейлист
     * @throws ServerErrorHttpException
     * @throws BadRequestHttpException
     */
    public function actionInclude(): void
    {
        $playlistName = \Yii::$app->getRequest()->getBodyParam('playlist', 'playlist');
        $directory = \Yii::$app->getRequest()->getBodyParam('directory');
        $basename = \Yii::$app->getRequest()->getBodyParam('basename');

        $pathAliases = ArrayHelper::getValue(\Yii::$app->params, 'dataDirectoryAliases', []);
        $hashPaths = [];

        foreach ($pathAliases as $path => $alias) {
            $realPath = Yii::getAlias($path);
            $hashPaths[sha1($realPath)] = $realPath;
        }

        if (!array_key_exists($directory, $hashPaths)) {
            throw new BadRequestHttpException('Невозможно обработать запрос для заданной директории.');
        }

        $playlistPath = \Yii::getAlias("@app/runtime/playlist/{$playlistName}.json");

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
            $content, [$directory . DIRECTORY_SEPARATOR . $basename]
        ));
        $preparedPlaylist = Json::encode($files);

        file_put_contents($playlistPath, $preparedPlaylist);

        \Yii::$app->getResponse()->setStatusCode(201);
    }
}
