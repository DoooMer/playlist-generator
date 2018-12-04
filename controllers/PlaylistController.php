<?php

namespace app\controllers;

use app\models\forms\CreatePlaylistForm;
use app\services\PlaylistService;
use Yii;
use yii\base\Module;
use yii\rest\Controller;
use yii\web\BadRequestHttpException;
use yii\web\ServerErrorHttpException;

/**
 * Контроллер плейлистов.
 */
class PlaylistController extends Controller
{
    /**
     * @var PlaylistService
     */
    private $playlistService;

    /**
     * @inheritdoc
     * @param PlaylistService $playlistService
     */
    public function __construct(string $id, Module $module, PlaylistService $playlistService, array $config = [])
    {
        parent::__construct($id, $module, $config);

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
     * Список созданных плейлистов.
     *
     * @return array
     */
    public function actionIndex()
    {
        return $this->playlistService->getAll();
    }

    /**
     * Создание m3u плейлиста с выбранными треками.
     *
     * @return string
     * @throws \yii\base\InvalidConfigException
     * @throws BadRequestHttpException
     * @throws ServerErrorHttpException
     */
    public function actionCreate()
    {
        $form = new CreatePlaylistForm();

        if (!$form->load(Yii::$app->getRequest()->getBodyParams())) {
            throw new BadRequestHttpException('Отсутствуют необходимые данные.');
        }

        if (!$form->validate()) {
            return $form;
        }

        try {
            $link = $this->playlistService->create($form->name);
            Yii::$app->getResponse()->setStatusCode(201);

            return $link;
        } catch (\RuntimeException $e) {
            throw new ServerErrorHttpException('Не удалось создать плейлист: ' . $e->getMessage());
        }
    }
}