<?php
/**
 * Created by PhpStorm.
 * User: floor12
 * Date: 31.12.2017
 * Time: 16:00
 */

namespace floor12\files\components;

use floor12\files\assets\FilesBlockAsset;
use floor12\notification\NotificationAsset;
use Yii;
use yii\helpers\Url;
use yii\jui\InputWidget;
use yii\web\View;


class FilesBlock extends InputWidget
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
        FilesBlockAsset::register($this->getView());

        $this->getView()->registerJs("yiiDownloadAllLink = '" . Url::toRoute('files/default/zip') . "'", View::POS_BEGIN, 'yiiDownloadAllLink');

        if ($this->passFirst && sizeof($this->files) > 0)
            $this->files = array_slice($this->files, 1);

        if (empty($this->files))
            return null;

        return $this->render('filesBlock', [
            'files' => $this->files,
            'zipTitle' => $this->zipTitle,
            'title' => $this->title,
            'downloadAll' => $this->downloadAll,
        ]);
    }
}