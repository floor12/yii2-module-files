<?php


namespace floor12\files\components;


use floor12\files\models\File;
use yii\base\Widget;

class PictureWidget extends Widget
{
    /**
     * @var File
     */
    public $model;
    /**
     * @var integer
     */
    public $width;
    /**
     * @var string
     */
    public $alt;

    /**
     * @return string
     */
    public function run()
    {
        return $this->render('pictureWidget', [
            'model' => $this->model,
            'width' => $this->width,
            'alt' => $this->alt,
        ]);
    }
}
