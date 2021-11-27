<?php
/**
 * Created by PhpStorm.
 * User: floor12
 * Date: 31.12.2017
 * Time: 16:00
 */

namespace floor12\files\components;

use floor12\files\assets\FileInputWidgetAsset;
use floor12\files\logic\ClassnameEncoder;
use Yii;
use yii\helpers\Url;
use yii\jui\InputWidget;
use yii\web\View;


class FileInputWidget extends InputWidget
{
    const MODE_SINGLE = 0;
    const MODE_MULTI = 1;

    const VIEW_SINGLE = 'singleFileInputWidget';
    const VIEW_MULTI = 'multiFileInputWidget';

    public $uploadButtonText;
    public $cropperHideCancel = 'false';
    public $uploadButtonClass = "btn btn-default btn-sm btn-upload";

    private $block_id;
    private $layout = self::VIEW_SINGLE;
    private $ratio;

    public function init()
    {
        $this->registerTranslations();
        $this->block_id = rand(9999999, 999999999);

        if (!$this->uploadButtonText)
            $this->uploadButtonText = Yii::t('files', 'Upload');

        $this->ratio = $this->model->getBehavior('files')->attributes[$this->attribute]['ratio'] ?? null;

        if (
            isset($this->model->behaviors['files']->attributes[$this->attribute]['validator']) &&
            isset($this->model->behaviors['files']->attributes[$this->attribute]['validator']['yii\validators\FileValidator']) &&
            $this->model->behaviors['files']->attributes[$this->attribute]['validator']['yii\validators\FileValidator']->maxFiles > 1
        ) {
            $mode = self::MODE_MULTI;
            $this->layout = self::VIEW_MULTI;
        }

        parent::init();
    }

    public function registerTranslations()
    {
        $i18n = Yii::$app->i18n;
        $i18n->translations['files'] = [
            'class' => 'yii\i18n\PhpMessageSource',
            'sourceLanguage' => 'en-US',
            'basePath' => '@vendor/floor12/yii2-module-files/src/messages',
        ];
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
        $this->getView()->registerJs("yii2CsrfParam = '" . Yii::$app->request->csrfParam . "'", View::POS_BEGIN, 'yii2CsrfFieldName');
        $this->getView()->registerJs("yii2DeleteRoute = '{$deleteRoute}'", View::POS_BEGIN, 'yii2DeleteRoute');
        $this->getView()->registerJs("yii2CropperRoute = '{$cropperRoute}'", View::POS_BEGIN, 'yii2DeleteRoute');
        $this->getView()->registerJs("yii2CropRoute = '{$cropRoute}'", View::POS_BEGIN, 'yii2CropRoute');
        $this->getView()->registerJs("yii2RenameRoute = '{$renameRoute}'", View::POS_BEGIN, 'yii2RenameRoute');
        $this->getView()->registerJs("yii2FileFormToken = '" . self::generateToken() . "'", View::POS_BEGIN, 'yii2FileFormToken');
        $this->getView()->registerJs("FileUploadedText = '" . Yii::t('files', 'The file is uploaded') . "'", View::POS_BEGIN, 'FileUploadedText');
        $this->getView()->registerJs("FileSavedText = '" . Yii::t('files', 'The file is saved') . "'", View::POS_BEGIN, 'FileSavedText');
        $this->getView()->registerJs("FileRemovedText = '" . Yii::t('files', 'The file is removed') . "'", View::POS_BEGIN, 'FileRemovedText');
        $this->getView()->registerJs("FilesRemovedText = '" . Yii::t('files', 'The files are removed') . "'", View::POS_BEGIN, 'FilesRemovedText');
        $this->getView()->registerJs("FileRenamedText = '" . Yii::t('files', 'The file is renamed') . "'", View::POS_BEGIN, 'FileRenamedText');
        $this->getView()->registerJs("cropperHideCancel = '{$this->cropperHideCancel}'", View::POS_BEGIN, 'cropperHideCancel');

        FileInputWidgetAsset::register($this->getView());

        return $this->render($this->layout, [
            'className' => $this->model->classname(),
            'uploadButtonText' => $this->uploadButtonText,
            'uploadButtonClass' => $this->uploadButtonClass,
            'block_id' => $this->block_id,
            'scenario' => $this->model->scenario,
            'attribute' => $this->attribute,
            'model' => $this->model,
            'ratio' => $this->ratio,
        ]);
    }

    /** Генератор токена защиты форм
     * @return string
     */
    public static function generateToken()
    {
        return md5(Yii::$app->getModule('files')->token_salt . Yii::$app->request->userAgent . Yii::$app->name);
    }
}
