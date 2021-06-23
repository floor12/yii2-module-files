<?php
/**
 * Created by PhpStorm.
 * User: floor12
 * Date: 01.01.2018
 * Time: 12:14
 *
 * @var $this View
 * @var $model File
 * @var $ratio float
 *
 */

use floor12\files\assets\IconHelper;
use floor12\files\models\File;
use floor12\files\models\FileType;
use yii\helpers\Html;
use yii\web\View;

if (is_array($model))
    $model = $model[array_key_first($model)];

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
        <div id="yii2-file-object-<?= $model->id ?>" data-toggle="dropdown" aria-haspopup="true"
             aria-expanded="false" class="floor12-single-file-object">
            <img src="<?= $model->href ?>" class="img-responsive">
            <?= Html::hiddenInput((new ReflectionClass($model->class))->getShortName() . "[{$model->field}_ids][]", $model->id) ?>
        </div>

    <?php else: ?>

        <div data-title="<?= $model->title ?>"
             id="yii2-file-object-<?= $model->id ?>"
             class="dropdown-toggle btn-group floor12-single-file-object floor12-single-file-object-no-image"
             data-toggle="dropdown" aria-haspopup="true"
             aria-expanded="false" title="<?= $model->title ?>">


            <?= Html::hiddenInput((new ReflectionClass($model->class))->getShortName() . "[{$model->field}_ids][]", $model->id) ?>

            <?php if ($model->type != FileType::IMAGE): ?>
                <?= $model->icon ?>
                <?= $model->title ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <ul class="dropdown-menu dropdown-menu-file-object dropdown-menu-file-object-single">
        <li>
            <a href="<?= $model->href ?>" target="_blank" data-pjax="0">
                <?= IconHelper::DOWNLOAD ?>
                <?= Yii::t('files', 'Download') ?>
            </a>
        </li>
        <li>
            <a onclick="clipboard('<?= $model->href ?>'); return false;">
                <?= IconHelper::LINK ?>
                <?= Yii::t('files', 'Copy link to clipboard') ?>
            </a>
        </li>
        <?php if (in_array($model->content_type, $doc_contents)): ?>
            <li>
                <a href="https://view.officeapps.live.com/op/view.aspx?src=<?= Yii::$app->request->hostInfo . $model->href ?>}"
                   target="_blank" data-pjax="0">
                    <?= IconHelper::VIEW ?>
                    <?= Yii::t('files', 'View') ?>
                </a>
            </li>
        <?php endif; ?>
        <li>
            <a onclick="showRenameFileForm(<?= $model->id ?>, event); return false;">
                <?= IconHelper::RENAME ?>
                <?= Yii::t('files', 'Rename') ?>
            </a>
        </li>
        <?php if ($model->type == FileType::IMAGE && !$model->isSvg()): ?>
            <li>
                <a onclick="initCropper(<?= $model->id ?>,'<?= $model->href ?>',<?= $ratio ?>)">
                    <?= IconHelper::CROP ?>
                    <?= Yii::t('files', 'Edit') ?>
                </a>
            </li>
        <?php endif; ?>
        <li>
            <a onclick="removeFile(<?= $model->id ?>); showUploadButton(event); return false;">
                <?= IconHelper::TRASH ?>
                <?= Yii::t('files', 'Delete') ?>
            </a>
        </li>
    </ul>

</div>

