<?php
/**
 * Created by PhpStorm.
 * User: floor12
 * Date: 31.12.2017
 * Time: 16:00
 */

namespace floor12\files\components;


use yii\jui\InputWidget;
use floor12\files\assets\FilesBlockAsset;

class FilesBlock extends InputWidget
{
    public $files;

    public function run()
    {
        FilesBlockAsset::register($this->getView());

        return $this->render('filesBlock', [
            'files' => $this->files
        ]);
    }
}