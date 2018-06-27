<?php
/**
 * Created by PhpStorm.
 * User: floor12
 * Date: 31.12.2017
 * Time: 16:00
 */

namespace floor12\files\components;

use \Yii;
use yii\helpers\Url;
use yii\jui\InputWidget;
use floor12\notification\NotificationAsset;
use floor12\files\assets\FilesBlockAsset;


class FilesBlock extends InputWidget
{
    public $files;
    public $title;
    public $zipTitle = 'files';
    public $downloadAll = false;
    public $passFirst = false;


    public function init()
    {
        $this->registerTranslations();
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
        NotificationAsset::register($this->getView());
        FilesBlockAsset::register($this->getView());

        $this->getView()->registerJs("yiiDownloadAllLink = '" . Url::toRoute('files/default/zip') . "'", \yii\web\View::POS_BEGIN, 'yiiDownloadAllLink');

        if ($this->passFirst && sizeof($this->files) > 0)
            $this->files = array_slice($this->files, 1);

        if ($this->files)
            return $this->render('filesBlock', [
                'files' => $this->files,
                'zipTitle' => $this->zipTitle,
                'title' => $this->title,
                'downloadAll' => $this->downloadAll,
            ]);
    }
}