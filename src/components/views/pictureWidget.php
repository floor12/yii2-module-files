<?php
/**
 * @var $this \yii\web\View
 * @var $model \floor12\files\models\File
 * @var $width integer
 * @var $alt string
 */
?>

<picture>
    <source
            type="image/webp"
            srcset="
                <?= $model->getPreviewWebPath($width, 0, true) ?> 1x,
                <?= $model->getPreviewWebPath(2 * $width, 0, true) ?> 2x
            ">
    <source
            type="image/jpeg"
            srcset="
                <?= $model->getPreviewWebPath($width) ?> 1x,
                <?= $model->getPreviewWebPath(2 * $width) ?> 2x
            ">
    <img src="<?= $model->getPreviewWebPath($width) ?>" alt="<?= $alt ?>">
</picture>
