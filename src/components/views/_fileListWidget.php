<?php

/**
 * Created by PhpStorm.
 * User: floor12
 * Date: 01.01.2018
 * Time: 12:14
 *
 * @var $this View
 * @var $model File
 * @var $lightboxKey string
 * @var $allowImageSrcDownload bool
 */

use floor12\files\assets\IconHelper;
use floor12\files\models\File;
use floor12\files\models\FileType;
use yii\web\View;

$doc_contents = [
    'application/msword',
    'application/vnd.ms-excel',
    'application/vnd.ms-powerpoint',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'application/vnd.openxmlformats-officedocument.presentationml.presentation'
];


?>
<li>
    <?php if ($model->type == FileType::IMAGE) { ?>

        <?php if ($allowImageSrcDownload === true) { ?>
            <a href="<?= $model->href ?>" target="_blank" data-pjax="0" class="files-download-btn">
                <?= IconHelper::DOWNLOAD ?>
            </a>
        <?php } ?>

        <a data-title="<?= $model->title ?>" href="<?= $model->href ?>" data-hash="<?= $model->hash ?>"
           class="f12-file-object" style="background-image: url(<?= $model->href ?>)" title=" <?= $model->title ?>"
           data-lightbox="<?= $lightboxKey ?>"></a>


    <?php } else { ?>

        <?php if (Yii::$app->getModule('files')->allowOfficePreview && in_array($model->content_type, $doc_contents)) { ?>
            <a href="https://view.officeapps.live.com/op/view.aspx?src=<?= Yii::$app->request->hostInfo . $model->href ?>"
               target="_blank" data-pjax="0" class="files-download-btn">
                <?= IconHelper::VIEW ?>
            </a>
        <?php } ?>

        <a href="<?= $model->href ?>" target="_blank" data-pjax="0" data-title="<?= $model->title ?>"
           class="f12-file-object" data-hash="<?= $model->hash ?>" title="<?= $model->title ?>">
            <?= $model->icon ?>
            <span><?= $model->title ?></span>
        </a>

    <?php } ?>
</li>