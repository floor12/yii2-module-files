<?php
/**
 * Created by PhpStorm.
 * User: floor12
 * Date: 31.12.2017
 * Time: 16:10
 */

use floor12\files\assets\IconHelper;
use yii\bootstrap\BootstrapPluginAsset;
use yii\db\ActiveRecord;
use yii\helpers\Html;
use yii\web\View;

/**
 * @var View $this
 * @var string $uploadButtonText
 * @var string $uploadButtonClass
 * @var integer $block_id
 * @var string $attribute
 * @var string $className
 * @var string $scenario
 * @var ActiveRecord $model
 * @var float|null $ratio
 */

BootstrapPluginAsset::register($this);

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

<div class="floor12-files-widget-block files-widget-block" id="files-widget-block_<?= $block_id ?>"
     data-ratio="<?= $ratio ?>"
     data-classname="<?= $className ?>"
     data-attribute="<?= $attribute ?>">
    <button class="<?= $uploadButtonClass ?>" type="button">
        <div class="icon"><?= IconHelper::PLUS ?></div>
        <?= $uploadButtonText ?>
    </button>
    <?= Html::hiddenInput((new ReflectionClass($model))->getShortName() . "[{$attribute}_ids][]", null) ?>
    <div class="floor12-files-widget-list floor12-files-widget-list-multi" data-field="<?= $attribute ?>">
        <?php if ($model->$attribute) foreach ($model->$attribute as $file) echo $this->render('@vendor/floor12/yii2-module-files/src/views/default/_file', ['model' => $file, 'ratio' => $ratio]) ?>
    </div>
    <div class="clearfix"></div>
</div>
