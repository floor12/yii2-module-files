<?php
/**
 * Created by PhpStorm.
 * User: floor12
 * Date: 31.12.2017
 * Time: 16:10
 *
 * @var $this \yii\web\View
 * @var $uploadButtonText string
 * @var $uploadButtonClass string
 * @var $block_id integer
 * @var $attribute string
 * @var $scenario string
 * @var $model \yii\db\ActiveRecord
 * @var $ratio float
 *
 */

use floor12\files\assets\IconHelper;
use yii\helpers\Html;

if (YII_ENV == 'test') // This code is only for testing
    echo Html::fileInput('files', null, [
        'id' => "files-upload-field-{$attribute}",
        'class' => 'yii2-files-upload-field',
        'data-modelclass' => $model::className(),
        'data-attribute' => $attribute,
        'data-mode' => 'single',
        'data-ratio' => $ratio ?? 0,
        'data-block' => $block_id,

    ]) ?>

<div class="floor12-files-widget-single-block" id="files-widget-block_<?= $block_id ?>" data-ratio="<?= $ratio ?>">
    <button class="<?= $uploadButtonClass ?>">
        <div class="icon"><?= IconHelper::PLUS ?></div>
        <?= $uploadButtonText ?>
    </button>
    <?= Html::hiddenInput((new \ReflectionClass($model))->getShortName() . "[{$attribute}_ids][]", null) ?>
    <div class="floor12-files-widget-list">
        <?php if ($model->$attribute) echo $this->render('@vendor/floor12/yii2-module-files/src/views/default/_single', ['model' => $model->$attribute, 'ratio' => $ratio]) ?>
    </div>
    <div class="clearfix"></div>
</div>
