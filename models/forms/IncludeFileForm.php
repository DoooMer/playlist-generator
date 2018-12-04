<?php

namespace app\models\forms;

use yii\base\Model;

/**
 * Форма добавления файла в плейлист.
 */
class IncludeFileForm extends Model
{
    /**
     * @var string|null
     */
    public $playlist;
    /**
     * @var string
     */
    public $directory;
    /**
     * @var string
     */
    public $basename;

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
            ['directory', 'required'],
            ['directory', 'string'],

            ['basename', 'required'],
            ['basename', 'string'],

            ['playlist', 'string'],
            ['playlist', 'default', 'value' => 'playlist'],
        ];
    }
}