<?php
/**
 * Created by PhpStorm.
 * User: floor12
 * Date: 31.12.2017
 * Time: 16:00
 */

namespace floor12\files\components;


use yii\helpers\Url;
use yii\jui\InputWidget;
use floor12\files\assets\FilesBlockAsset;

class FilesBlock extends InputWidget
{
    public $files;
    public $title;
    public $zipTitle = 'files';
    public $downloadAll = false;
    public $passFirst = false;

    public function run()
    {
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