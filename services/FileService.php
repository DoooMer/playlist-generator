<?php

namespace app\services;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * Сервис файлов.
 */
class FileService
{
    private $dataDirectories;

    public function __construct(array $dataDirectories = [])
    {
        $this->dataDirectories = ArrayHelper::getValue(\Yii::$app->params, 'dataDirectoryAliases', $dataDirectories);
    }

    public function getDirectoriesHash(): array
    {
        $paths = [];
        $pathAliases = $this->dataDirectories;

        foreach ($pathAliases as $path => $alias) {
            $paths[] = $this->buildHashPath($path);
        }

        return $paths;
    }

    public function getDirectoriesRealPathHashIndexed(): array
    {
        $pathAliases = $this->dataDirectories;
        $hashPaths = [];

        foreach ($pathAliases as $path => $alias) {
            $realPath = Yii::getAlias($path);
            $hashPaths[sha1($realPath)] = $realPath;
        }

        return $hashPaths;
    }

    public function getDirectoriesAliasHashIndexed(): array
    {
        $hashPaths = [];

        foreach ($this->dataDirectories as $path => $alias) {
            $hashPaths[$this->buildHashPath($path)] = $alias;
        }

        return $hashPaths;
    }

    public function scanDirectory(string $directoryPath): array
    {
        $files = [];

        foreach (new \FilesystemIterator($directoryPath) as $file) {
            /** @var \SplFileInfo $file */
            if ($file->isDir()) {
                continue;
            }

            $newName = str_replace(' ', '_', mb_strtolower($file->getBasename()));

            $files[] = $newName; // @todo: $file->getBasename();
        }

        return $files;
    }

    public function rename(string $path, string $oldName, string $newName): void
    {
        rename(
            $path . DIRECTORY_SEPARATOR . $oldName,
            $path . DIRECTORY_SEPARATOR . $newName
        );
    }

    private function buildHashPath(string $path): string
    {
        return sha1(Yii::getAlias($path));
    }
}