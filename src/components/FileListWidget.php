<?php
/**
 * Created by PhpStorm.
 * User: floor12
 * Date: 31.12.2017
 * Time: 16:00
 */

namespace floor12\files\components;

use floor12\files\assets\FileListAsset;
use floor12\notification\NotificationAsset;
use Yii;
use yii\base\Widget;
use yii\helpers\Url;
use yii\web\View;


class FileListWidget extends Widget
{
    public $files;
    public $title;
    public $zipTitle = 'files';
    public $downloadAll = false;
    public $passFirst = false;

    /**
     * @inheritDoc
     */
    public function init()
    {
        Yii::$app->getModule('files')->registerTranslations();
        parent::init();
    }


    /**
     * @return string|null
     */
    public function run()
    {
        NotificationAsset::register($this->getView());
        FileListAsset::register($this->getView());

        $this->getView()->registerJs("yiiDownloadAllLink = '" . Url::toRoute('files/default/zip') . "'", View::POS_BEGIN, 'yiiDownloadAllLink');

        if ($this->passFirst && sizeof($this->files) > 0)
            $this->files = array_slice($this->files, 1);

        if (empty($this->files))
            return null;

        return $this->render('fileListWidget', [
            'files' => $this->files,
            'zipTitle' => $this->zipTitle,
            'title' => $this->title,
            'downloadAll' => $this->downloadAll,
        ]);
    }
}