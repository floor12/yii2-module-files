<?php
/**
 * Created by PhpStorm.
 * User: floor12
 * Date: 01.01.2018
 * Time: 12:56
 */

namespace floor12\files\logic;

use floor12\files\models\File;
use yii\base\ErrorException;
use yii\web\BadRequestHttpException;
use \yii\web\UploadedFile;
use yii\web\IdentityInterface;

class FileCreateFromInstance
{

    private $_model;
    private $_owner;
    private $_attribute;
    private $_instance;
    private $_fullPath;

    public function __construct(UploadedFile $file, array $data, IdentityInterface $identity = null)
    {
        // Загружаем полученные данные
        $this->_instance = $file;
        $this->_attribute = $data['attribute'];

        // Инициализируем класс владельца файла для валидаций и ставим сценарий
        $this->_owner = new $data['modelClass'];
        $this->_owner->setScenario($data['scenario']);


        if (isset($this->_owner->behaviors['files']->attributes[$this->_attribute]['validator'])) {
            if (!$this->_owner->behaviors['files']->attributes[$this->_attribute]['validator']->validate($this->_instance, $error))
                throw new BadRequestHttpException($error);
        }


        // Создаем модель нового файла и заполняем первоначальными данными
        $this->_model = new File();
        $this->_model->created = time();
        $this->_model->field = $this->_attribute;
        $this->_model->class = $data['modelClass'];

        $this->_model->filename = new PathGenerator(\Yii::$app->getModule('files')->storageFullPath) . '.' . $this->_instance->extension;
        $this->_model->title = $this->_instance->name;
        $this->_model->content_type = $this->_instance->type;
        $this->_model->size = $this->_instance->size;
        $this->_model->type = $this->detectType();
        if ($identity)
            $this->_model->user_id = $identity->id;
        if ($this->_model->type == File::TYPE_VIDEO)
            $this->_model->video_status = 0;

        //Генерируем полный новый адрес сохранения файла
        $this->_fullPath = \Yii::$app->getModule('files')->storageFullPath . DIRECTORY_SEPARATOR . $this->_model->filename;
    }

    /**
     * @return File
     */

    public function execute()
    {

        $path = \Yii::$app->getModule('files')->storageFullPath . $this->_model->filename;

        if ($this->_model->save()) {
            $this->_instance->saveAs($this->_fullPath);
            $this->_model->updatePreview();
        }

        return $this->_model;
    }

    /**
     * @return string
     */
    public function detectType()
    {
        $contentTypeArray = explode('/', $this->_model->content_type);
        if ($contentTypeArray[0] == 'image')
            return File::TYPE_IMAGE;
        if ($contentTypeArray[0] == 'video')
            return File::TYPE_VIDEO;
        return File::TYPE_FILE;
    }
}