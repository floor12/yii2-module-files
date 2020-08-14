<?php


namespace floor12\files\components;


use floor12\files\models\File;
use floor12\files\models\FileType;
use yii\base\Widget;

class PictureWidget extends Widget
{
    /** @var File */
    public $model;
    /** @var integer */
    public $width;
    /** @var string */
    public $alt;
    /** @var string */
    public $classPicture;
    /** @var string */
    public $classImg;

    /**
     * @return string|null
     */
    public function run(): ?string
    {
        if (!in_array($this->model->type, [FileType::IMAGE, FileType::VIDEO]))
            return null;
        return $this->render('pictureWidget', [
            'model' => $this->model,
            'width' => $this->width,
            'alt' => $this->alt,
            'classPicture' => $this->classPicture,
            'classImg' => $this->classImg,
        ]);
    }
}
