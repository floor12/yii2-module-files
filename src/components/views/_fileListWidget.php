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
<div class="btn-group files-btn-group">

    <?php if ($model->type == FileType::IMAGE): ?>

        <a data-title="<?= $model->title ?>"
           href="<?= $model->href ?>"
           data-hash="<?= $model->hash ?>"
           class="floor12-file-object"
           style="background-image: url(<?= $model->href ?>)" data-toggle="dropdown" aria-haspopup="true"
           aria-expanded="false" title="<?= $model->title ?>"
           data-lightbox="<?= $lightboxKey ?>"></a>


    <?php elseif ($model->content_type == 'application/pdf'): ?>
        <a href="<?= $model->href ?>" target="_blank" data-pjax="0">
            <div data-title="<?= $model->title ?>"
                 class="floor12-file-object"
                 data-hash="<?= $model->hash ?>"
                 title="<?= $model->title ?>">

                <div class="icon"><?= $model->icon ?></div>
                <?= $model->title ?>
            </div>
        </a>
    <?php else: ?>


        <div data-title="<?= $model->title ?>"
             class="floor12-file-object"
             data-hash="<?= $model->hash ?>"
             data-toggle="dropdown" aria-haspopup="true"
             aria-expanded="false" title="<?= $model->title ?>">

            <div class="icon"><?= $model->icon ?></div>
            <?= $model->title ?>
        </div>

        <ul class="dropdown-menu dropdown-menu-file-object dropdown-menu-file-object-multi">
            <li>
                <a href="<?= $model->href ?>" target="_blank" data-pjax="0">
                    <?= IconHelper::DOWNLOAD ?>
                    <?= Yii::t('files', 'Download') ?>
                </a>
            </li>
            <?php if (Yii::$app->getModule('files')->allowOfficePreview && in_array($model->content_type, $doc_contents)): ?>
                <li>
                    <a href="https://view.officeapps.live.com/op/view.aspx?src=<?= Yii::$app->request->hostInfo . $model->href ?>"
                       target="_blank" data-pjax="0">
                        <?= IconHelper::VIEW ?>
                        <?= Yii::t('files', 'View') ?>
                    </a>
                </li>
            <?php endif; ?>
        </ul>

    <?php endif; ?>


</div>


