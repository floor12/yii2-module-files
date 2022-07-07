<?php

namespace floor12\files\components;

use floor12\files\assets\LightboxAsset;
use floor12\files\models\File;
use yii\base\Widget;
use yii\helpers\Html;

class PictureListWidget extends Widget
{
    /** @var File[] */
    public array $models = [];
    public $width;
    /** @var string */
    public $alt;
    /** @var string */
    public $classPicture;
    /** @var string */
    public $classImg;
    /** @var string */
    public $classUl;
    /** @var string */
    public $classLi;
    /** @var bool */
    public $passFirst = false;
    /** @var bool */
    public $lightbox = false;
    /** @var int */
    public $lightboxImageWidth = 1400;

    public function run(): string
    {
        if (empty($this->models)) {
            return '';
        }

        $renderedPictures = [];

        if ($this->passFirst && sizeof($this->models) > 0)
            $this->models = array_slice($this->models, 1);

        $lightboxKey = $this->models[0]->field . '-' . $this->models[0]->object_id;

        if ($this->lightbox) {
            LightboxAsset::register($this->getView());
        }

        foreach ($this->models as $model) {

            $widget = PictureWidget::widget([
                'model' => $model,
                'width' => $this->width,
                'classImg' => $this->classImg,
                'classPicture' => $this->classPicture,
                'alt' => $this->alt,
            ]);

            if ($this->lightbox) {
                $widget = Html::a($widget, $model->getPreviewWebPath($this->lightboxImageWidth), [
                    'data-lightbox' => $lightboxKey
                ]);
            }

            $renderedPictures[] = Html::tag(
                'li',
                $widget, [
                'class' => $this->classLi
            ]);
        }
        return Html::tag('ul', implode($renderedPictures), [
            'class' => $this->classUl
        ]);
    }
}