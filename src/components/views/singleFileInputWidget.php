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
 * @var string $scenario
 * @var ActiveRecord $model
 * @var float|null $ratio
 * @var string $name
 * @var \floor12\files\models\File|null $value
 */

BootstrapPluginAsset::register($this);

if (YII_ENV == 'test') // This code is only for testing
    echo Html::fileInput('files', null, [
        'id' => "files-upload-field-{$attribute}",
        'class' => 'yii2-files-upload-field',
        'data-modelclass' => $model::className(),
        'data-attribute' => $attribute,
        'data-mode' => 'single',
        'data-name' => $name ?: (new ReflectionClass($model))->getShortName() . "[{$attribute}_ids][]",
        'data-ratio' => $ratio ?? 0,
        'data-block' => $block_id,

    ]) ?>

<div class="floor12-files-widget-single-block files-widget-block" id="files-widget-block_<?= $block_id ?>"
     data-ratio="<?= $ratio ?>">
    <button class="<?= $uploadButtonClass ?>" type="button">
        <div class="icon"><?= IconHelper::PLUS ?></div>
        <?= $uploadButtonText ?>
    </button>
    <?= Html::hiddenInput($name ?: (new ReflectionClass($model))->getShortName() . "[{$attribute}_ids][]", null) ?>
    <div class="floor12-files-widget-list">
        <?php if ($value ?? $model->$attribute) echo $this->render('@vendor/floor12/yii2-module-files/src/views/default/_single', [
            'model' => $value ?? $model->$attribute,
            'ratio' => $ratio,
            'name' => $name
        ]) ?>
    </div>
    <div class="clearfix"></div>
</div>
