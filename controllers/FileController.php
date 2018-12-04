<?php

namespace app\controllers;

use app\models\forms\IncludeFileForm;
use app\services\FileService;
use app\services\PlaylistService;
use Yii;
use yii\base\Module;
use yii\helpers\ArrayHelper;
use yii\rest\Controller;
use yii\web\BadRequestHttpException;
use yii\web\ServerErrorHttpException;

/**
 * Контроллер файлов
 */
class FileController extends Controller
{
    /**
     * @var FileService
     */
    private $fileService;
    /**
     * @var PlaylistService
     */
    private $playlistService;

    /**
     * @inheritdoc
     * @param FileService $fileService
     * @param PlaylistService $playlistService
     */
    public function __construct(string $id, Module $module, FileService $fileService, PlaylistService $playlistService, array $config = [])
    {
        parent::__construct($id, $module, $config);

        $this->fileService = $fileService;
        $this->playlistService = $playlistService;
    }

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
        return $this->fileService->getDirectoriesHash();
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
        $hashPaths = $this->fileService->getDirectoriesRealPathHashIndexed();
        $directoryPath = ArrayHelper::getValue($hashPaths, $directory);

        if (!$directoryPath) {
            throw new BadRequestHttpException('Невозможно обработать запрос для заданной директории.');
        }

        return $this->fileService->scanDirectory($directoryPath);

    }

    /**
     * Добавление файла в плейлист
     * @throws ServerErrorHttpException
     * @throws BadRequestHttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionInclude(): ?IncludeFileForm
    {
        $form = new IncludeFileForm();

        if (!$form->load(Yii::$app->getRequest()->getBodyParams())) {
            throw new BadRequestHttpException('Невозможно обработать запрос.');
        }

        if (!$form->validate()) {
            return $form;
        }

        $hashPaths = $this->fileService->getDirectoriesRealPathHashIndexed();

        if (!array_key_exists($form->directory, $hashPaths)) {
            throw new BadRequestHttpException('Невозможно обработать запрос для заданной директории.');
        }

        $this->playlistService->includeFileToJson($form->playlist, $form->directory, $form->basename);

        \Yii::$app->getResponse()->setStatusCode(201);
        return null;
    }
}
