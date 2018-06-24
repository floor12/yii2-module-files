<?php
/**
 * Created by PhpStorm.
 * User: floor12
 * Date: 31.12.2017
 * Time: 16:00
 */

namespace floor12\files\components;

use \Yii;
use floor12\files\logic\ClassnameEncoder;
use yii\jui\InputWidget;
use floor12\files\assets\FileInputWidgetAsset;
use floor12\files\assets\CropperAsset;
use yii\helpers\Url;
use yii\web\View;
use floor12\files\Module;


class FileInputWidget extends InputWidget
{
    const MODE_SINGLE = 0;
    const MODE_MULTI = 1;

    const VIEW_SINGLE = 'singleFileInputWidget';
    const VIEW_MULTI = 'multiFileInputWidget';

    public $uploadButtonText;
    public $uploadButtonClass = "btn btn-default btn-sm btn-upload";

    private $block_id;
    private $mode = self::MODE_SINGLE;
    private $layout = self::VIEW_SINGLE;
    private $ratio;

    /** Генератор токена защиты форм
     * @return string
     */
    public static function generateToken()
    {
        return md5(Yii::$app->getModule('files')->token_salt . Yii::$app->request->userAgent . Yii::$app->name);
    }


    public function init()
    {
        $this->block_id = rand(9999999, 999999999);
        $this->uploadButtonText = Yii::t('files', 'Upload');

        $this->ratio = $this->model->getBehavior('files')->attributes[$this->attribute]['ratio'] ?? null;

        if (isset($this->model->behaviors['files']->attributes[$this->attribute]['validator']) && $this->model->behaviors['files']->attributes[$this->attribute]['validator']->maxFiles > 1) {
            $this->mode = self::MODE_MULTI;
            $this->layout = self::VIEW_MULTI;
        }

        parent::init();
    }

    public function run()
    {

        $uploadRoute = Url::toRoute(['/files/default/upload']);
        $deleteRoute = Url::toRoute(['/files/default/delete']);
        $cropperRoute = Url::toRoute(['/files/default/cropper']);
        $cropRoute = Url::toRoute(['/files/default/crop']);
        $renameRoute = Url::toRoute(['/files/default/rename']);

        $className = new ClassnameEncoder($this->model->classname());

        $this->getView()->registerJs("Yii2FilesUploaderSet('files-widget-block_{$this->block_id}','{$className}','{$this->attribute}','{$this->model->scenario}')", View::POS_READY, $this->block_id);
        $this->getView()->registerJs("yii2UploadRoute = '{$uploadRoute}'", View::POS_BEGIN, 'yii2UploadRoute');
        $this->getView()->registerJs("yii2DeleteRoute = '{$deleteRoute}'", View::POS_BEGIN, 'yii2DeleteRoute');
        $this->getView()->registerJs("yii2CropperRoute = '{$cropperRoute}'", View::POS_BEGIN, 'yii2DeleteRoute');
        $this->getView()->registerJs("yii2CropRoute = '{$cropRoute}'", View::POS_BEGIN, 'yii2CropRoute');
        $this->getView()->registerJs("yii2RenameRoute = '{$renameRoute}'", View::POS_BEGIN, 'yii2RenameRoute');
        $this->getView()->registerJs("yii2FileFormToken = '" . self::generateToken() . "'", View::POS_BEGIN, 'yii2FileFormToken');

        FileInputWidgetAsset::register($this->getView());
        CropperAsset::register($this->getView());

        return $this->render($this->layout, [
            'uploadButtonText' => $this->uploadButtonText,
            'uploadButtonClass' => $this->uploadButtonClass,
            'block_id' => $this->block_id,
            'scenario' => $this->model->scenario,
            'attribute' => $this->attribute,
            'model' => $this->model,
            'ratio' => $this->ratio,
        ]);
    }
}