<?php

namespace app\services;

use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * Сервис плейлистов.
 */
class PlaylistService
{
    private $jsonPath = '@app/runtime/playlist';

    public function includeFileToJson(string $name, string $directory, string $filename): void
    {
        $playlistPath = $this->getJsonPath($name);
        $this->appendFile($playlistPath, $directory . DIRECTORY_SEPARATOR . $filename);
    }

    private function getJsonPath(string $name)
    {
        return Yii::getAlias("{$this->jsonPath}/{$name}.json");
    }

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

    private function appendFile(string $path, string $file): void
    {
        $content = $this->getJsonContent($path);
        $files = array_unique(ArrayHelper::merge($content, [$file]));
        $preparedPlaylist = Json::encode($files);

        file_put_contents($path, $preparedPlaylist);
    }
}