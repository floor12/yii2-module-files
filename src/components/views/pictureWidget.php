<?php

use floor12\files\models\File;
use yii\web\View;

/**
 * @var View $this
 * @var File $model
 * @var integer $width
 * @var string $classPicture
 * @var string $classImg
 * @var string $alt
 */
?>

<picture<?= $classPicture ? " class=\"{$classPicture}\"" : NULL ?>>
    <source
            type="image/webp"
            srcset="
                <?= $model->getPreviewWebPath(1.5 * $width, true) ?> 1x,
                <?= $model->getPreviewWebPath(2 * $width, true) ?> 2x">
    <source
            type="image/jpeg"
            srcset="
                <?= $model->getPreviewWebPath(1.5 * $width) ?> 1x,
                <?= $model->getPreviewWebPath(2 * $width) ?> 2x">
    <img src="<?= $model->getPreviewWebPath($width) ?>" alt="<?= $alt ?>" <?= $classImg ? "class=\"{$classImg}\"" : NULL ?>>
</picture>
