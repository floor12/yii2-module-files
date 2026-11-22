<?php

use floor12\files\models\File;
use yii\web\View;

/**
 * @var View $this
 * @var File $model
 * @var array $width
 * @var string $classPicture
 * @var string $classImg
 * @var string $alt
 */

?>

<picture<?= $classPicture ? " class=\"{$classPicture}\"" : NULL ?>>
    <?php foreach ($width as $widthMediaQuery => $widthValue) { ?>
        <source
                type="image/webp"
                media='(<?= $widthMediaQuery ?>)'
                srcset="
                <?= $model->getPreviewWebPath(1.5 * $widthValue, true) ?> 1x,
                <?= $model->getPreviewWebPath(2 * $widthValue, true) ?> 2x">
    <?php } ?>
    <?php foreach ($width as $widthMediaQuery => $widthValue) { ?>
        <source
                type="image/jpeg"
                media='(<?= $widthMediaQuery ?>)'
                srcset="
                <?= $model->getPreviewWebPath(1.5 * $widthValue) ?> 1x,
                <?= $model->getPreviewWebPath(2 * $widthValue) ?> 2x">
    <?php } ?>
    <img src="<?= $model->getPreviewWebPath(end($width)) ?>" alt="<?= $alt ?>" <?= $classImg ? "class=\"{$classImg}\"" : NULL ?>>
</picture>
