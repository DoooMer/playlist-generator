<?php

namespace app\services;

use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\UrlManager;
use yii\web\View;

/**
 * Сервис плейлистов.
 */
class PlaylistService
{
    /**
     * @var FileService
     */
    private $fileService;
    /**
     * @var string
     */
    private $jsonPath;
    /**
     * @var string
     */
    private $webPath;
    /**
     * @var UrlManager
     */
    private $urlManager;
    /**
     * @var View
     */
    private $view;
    /**
     * @var string
     */
    private $m3uTemplate = '@app/views/m3u.php';

    /**
     * @param string $jsonPath
     * @param string $webPath
     * @param UrlManager $urlManager
     * @param View $view
     * @param FileService $fileService
     */
    public function __construct(string $jsonPath, string $webPath, UrlManager $urlManager, View $view, FileService $fileService)
    {
        $this->jsonPath = $jsonPath;
        $this->webPath = $webPath;
        $this->urlManager = $urlManager;
        $this->view = $view;
        $this->fileService = $fileService;
    }

    /**
     * Включает файл в заготовку плейлиста.
     *
     * @param string $name
     * @param string $directory
     * @param string $filename
     */
    public function includeFileToJson(string $name, string $directory, string $filename): void
    {
        $playlistPath = $this->getJsonPath($name);
        $this->appendFile($playlistPath, $directory . DIRECTORY_SEPARATOR . $filename);
    }

    /**
     * Получение пути к заготовке плейлиста.
     *
     * @param string $name
     * @return bool|string
     */
    public function getJsonPath(string $name)
    {
        return Yii::getAlias("{$this->jsonPath}/{$name}.json");
    }

    /**
     * Получение списка файлов из заготовки плейлиста.
     *
     * @param string $path
     * @return array
     */
    private function getJsonContent(string $path): array
    {
        if (!file_exists($path)) {
            return [];
        }

        $rawContent = file_get_contents($path);

        if (!empty($rawContent)) {
            return Json::decode($rawContent);
        }

        return [];
    }

    /**
     * Добавление файла в заготовку плейлиста.
     *
     * @param string $path
     * @param string $file
     */
    private function appendFile(string $path, string $file): void
    {
        $content = $this->getJsonContent($path);
        $files = array_unique(ArrayHelper::merge($content, [$file]));
        $preparedPlaylist = Json::encode($files);

        file_put_contents($path, $preparedPlaylist);
    }

    /**
     * Получение всех плейлистов.
     *
     * @return array
     */
    public function getAll(): array
    {
        $playlists = [];

        foreach (new \DirectoryIterator(Yii::getAlias($this->webPath)) as $file) {

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
     * Создание плейлиста из заготовки.
     *
     * @param string $name
     * @return string
     */
    public function create(string $name): string
    {
        $playlistPath = $this->getJsonPath($name);

        if (file_exists($playlistPath)) {
            $rawContent = file_get_contents($playlistPath);

            if (!empty($rawContent)) {
                $content = $this->collectFiles(Json::decode($rawContent));

            } else {
                $content = [];
            }

        } else {
            $content = [];
        }

        if (empty($content)) {
            throw new \RuntimeException('Плейлист пуст.');
        }

        file_put_contents(
            Yii::getAlias("{$this->webPath}/{$name}.m3u"),
            $this->view->renderFile($this->m3uTemplate, compact('content'))
        );

        return Url::to("/playlist/{$name}.m3u", true);
    }

    /**
     * Сбор ссылок на файлы для плейлиста.
     *
     * @param array $content
     * @return array
     */
    private function collectFiles(array $content): array
    {
        $hashPaths = $this->fileService->getDirectoriesAliasHashIndexed();

        foreach ($content as $i => $record) {
            [$directory, $filename] = explode('/', $record);
            $alias = ArrayHelper::getValue($hashPaths, $directory);
            $content[$i] = $this->urlManager->createAbsoluteUrl([$alias, 'filename' => $filename], true);
        }

        return $content;
    }
}