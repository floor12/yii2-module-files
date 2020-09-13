<?php
/**
 * @var $this View
 * @var $model File
 * @var $width array
 * @var $classPicture string
 * @var $classImg string
 * @var $alt string
 */

use floor12\files\models\File;
use yii\web\View;

?>

<picture<?= $classPicture ? " class=\"{$classPicture}\"" : NULL ?>>
    <?php foreach ($width as $widthMediaQuery => $widthValue) { ?>
        <source
                type="image/webp"
                media='(<?= $widthMediaQuery ?>)'
                srcset="
                <?= $model->getPreviewWebPath($widthValue, true) ?> 1x,
                <?= $model->getPreviewWebPath(2 * $widthValue, true) ?> 2x">
    <?php } ?>
    <?php foreach ($width as $widthMediaQuery => $widthValue) { ?>
        <source
                type="image/jpeg"
                media='(<?= $widthMediaQuery ?>)'
                srcset="
                <?= $model->getPreviewWebPath($widthValue) ?> 1x,
                <?= $model->getPreviewWebPath(2 * $widthValue) ?> 2x">
    <?php } ?>
    <img src="<?= $model->getPreviewWebPath(end($width)) ?>" alt="<?= $alt ?>" <?= $classImg ? "class=\"{$classImg}\"" : NULL ?>>
</picture>
