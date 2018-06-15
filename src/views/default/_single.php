<?php
/**
 * Created by PhpStorm.
 * User: floor12
 * Date: 01.01.2018
 * Time: 12:14
 *
 * @var $this \yii\web\View
 * @var $model \floor12\files\models\File
 * @var $ratio float
 *
 */

use \yii\helpers\Html;
use \floor12\files\models\File;

if (is_array($model))
    $model = $model[0];

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

    <div data-title="<?= $model->title ?>"
         id="yii2-file-object-<?= $model->id ?>"
         class="dropdown-toggle btn-group floor12-single-file-object <?= ($model->type == \floor12\files\models\File::TYPE_IMAGE) ? "floor12-file-object-image" : NULL ?>"
         style="background-image: url(<?= $model->href ?>); <?= $ratio ? "padding-bottom: " . 1 / $ratio * 100 . "%" : NULL ?>"
         data-toggle="dropdown" aria-haspopup="true"
         aria-expanded="false" title="<?= $model->title ?>">


        <?= Html::hiddenInput((new \ReflectionClass($model->class))->getShortName() . "[{$model->field}_ids][]", $model->id) ?>

        <?php if ($model->type != \floor12\files\models\File::TYPE_IMAGE): ?>
            <?= \Yii::$app->getModule('files')->fontAwesome->icon($model->icon) ?>
            <?= $model->title ?>
        <?php endif; ?>


    </div>

    <ul class="dropdown-menu dropdown-menu-file-object dropdown-menu-file-object-single">
        <li>
            <a href="<?= $model->href ?>" target="_blank" data-pjax="0">
                <?= \Yii::$app->getModule('files')->fontAwesome->icon('cloud-download') ?>
                Скачать
            </a>
        </li>
        <?php if (in_array($model->content_type, $doc_contents)): ?>
            <li>
                <a href="https://view.officeapps.live.com/op/view.aspx?src=<?= \Yii::$app->request->hostInfo . $model->href ?>}"
                   target="_blank" data-pjax="0">
                    <?= \Yii::$app->getModule('files')->fontAwesome->icon('eye') ?>
                    Просмотреть
                </a>
            </li>
        <?php endif; ?>
        <li>
            <a onclick="showRenameFileForm(<?= $model->id ?>, event); return false;">
                <?= \Yii::$app->getModule('files')->fontAwesome->icon('edit') ?>
                Переименовать
            </a>
        </li>
        <?php if ($model->type == File::TYPE_IMAGE): ?>
            <li>
                <a onclick="initCropper(<?= $model->id ?>,'<?= $model->href ?>',<?= $ratio ?>)">
                    <?= \Yii::$app->getModule('files')->fontAwesome->icon('picture-o') ?>
                    Редактировать
                </a>
            </li>
        <?php endif; ?>
        <li>
            <a onclick="removeFile(<?= $model->id ?>); showUploadButton(event); return false;">
                <?= \Yii::$app->getModule('files')->fontAwesome->icon('trash') ?>
                Удалить
            </a>
        </li>
    </ul>

</div>


