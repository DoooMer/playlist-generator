<?php

namespace app\models\forms;

use yii\base\Model;

/**
 * Форма создания плейлиста.
 */
class CreatePlaylistForm extends Model
{
    /**
     * @var string
     */
    public $name;

    /**
     * @inheritdoc
     */
    public function formName()
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['name', 'required'],
            ['name', 'string'],
        ];
    }
}