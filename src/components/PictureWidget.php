<?php


namespace floor12\files\components;


use floor12\files\models\File;
use floor12\files\models\FileType;
use yii\base\Widget;

class PictureWidget extends Widget
{
    /** @var File|null */
    public $model;
    /** @var integer|array */
    public $width;
    /** @var string */
    public $alt;
    /** @var string */
    public $classPicture;
    /** @var string */
    public $classImg;
    /** @var string */
    public $view = 'pictureWidget';

    /**
     * @return string|null
     */
    public function run(): ?string
    {
        if (empty($this->model) || !in_array($this->model->type, [FileType::IMAGE, FileType::VIDEO]))
            return null;

        if (is_array($this->width))
            $this->view = 'mediaPictureWidget';

        return $this->render($this->view, [
            'model' => $this->model,
            'width' => $this->width,
            'alt' => $this->alt,
            'classPicture' => $this->classPicture,
            'classImg' => $this->classImg,
        ]);
    }
}
