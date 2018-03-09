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

use yii\helpers\Html;

if (YII_ENV == 'test')
    echo Html::fileInput('files', null, [
        'id' => "files-upload-field-{$attribute}",
        'class' => 'yii2-files-upload-field',
        'data-modelclass' => $model::className(),
        'data-attribute' => $attribute,
        'data-mode' => 'multi',
        'data-ratio' => $ratio ?? 0,
        'data-block' => $block_id,

    ]) ?>

<div class="floor12-files-widget-block" id="files-widget-block_<?= $block_id ?>" data-ratio="<?= $ratio ?>">
    <button class="<?= $uploadButtonClass ?>">
        <div class="icon"><?= \Yii::$app->getModule('files')->fontAwesome->icon('plus') ?></div>
        <?= $uploadButtonText ?>
    </button>
    <?= Html::hiddenInput((new \ReflectionClass($model))->getShortName() . "[{$attribute}_ids][]", null) ?>
    <div class="floor12-files-widget-list floor12-files-widget-list-multi">
        <?php if ($model->$attribute) foreach ($model->$attribute as $file) echo $this->render('@vendor/floor12/yii2-module-files/src/views/default/_file', ['model' => $file, 'ratio' => $ratio]) ?>
    </div>
    <div class="clearfix"></div>
</div>
