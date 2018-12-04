<?php

namespace app\controllers;

use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\rest\Controller;
use yii\web\UrlManager;

/**
 * Контроллер плейлистов.
 */
class PlaylistController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [];
    }

    /**
     * Список созданных плейлистов.
     *
     * @return array
     */
    public function actionIndex()
    {
        $directory = Yii::getAlias('@app/web/playlist');
        $playlists = [];

        foreach (new \DirectoryIterator($directory) as $file) {

            if ($file->isDir() || $file->getExtension() !== 'm3u') {
                continue;
            }

            $playlists[] = [
                'name' => $file->getBasename('.' . $file->getExtension()),
                'link' => Url::to("/playlist/{$file->getFilename()}", true),
            ];

        }

        return $playlists;
    }

    /**
     * Создание m3u плейлиста с выбранными треками.
     *
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function actionCreate()
    {
        $playlistName = \Yii::$app->getRequest()->getBodyParam('playlist', 'playlist');
        $playlistPath = \Yii::getAlias("@app/runtime/playlist/{$playlistName}.json");

        if (file_exists($playlistPath)) {
            $rawContent = file_get_contents($playlistPath);

            if (!empty($rawContent)) {
                $content = Json::decode($rawContent);
                $pathAliases = ArrayHelper::getValue(\Yii::$app->params, 'dataDirectoryAliases', []);
                $hashPaths = [];

                foreach ($pathAliases as $path => $alias) {
                    $hashPaths[sha1(Yii::getAlias($path))] = $alias;
                }

                /** @var UrlManager $urlManager */
                $urlManager = Yii::$app->get('playlistUrlManager');

                foreach ($content as $i => $record) {
                    [$directory, $filename] = explode('/', $record);
                    $alias = ArrayHelper::getValue($hashPaths, $directory);
                    $content[$i] = $urlManager->createAbsoluteUrl([$alias, 'filename' => $filename], true);
                }

            } else {
                $content = [];
            }

        } else {
            $content = [];
        }

        file_put_contents(
            Yii::getAlias("@app/web/playlist/{$playlistName}.m3u"),
            $this->renderFile('@app/views/m3u.php', compact('content'))
        );

        Yii::$app->getResponse()->setStatusCode(201);

        return Url::to("/playlist/{$playlistName}.m3u", true);
    }
}