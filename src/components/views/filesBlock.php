<?php
/**
 * Created by PhpStorm.
 * User: floor12
 * Date: 06.01.2018
 * Time: 12:09
 *
 * @var $this \yii\web\View
 * @var $title string
 * @var $zipTitle string
 * @var $downloadAll bool
 * @var $files \floor12\files\models\File[]
 */

use  \yii\helpers\Html;

?>

<div class="files-block">
    <?php if ($title): ?>
        <label><?= $title ?></label><br>
    <?php endif; ?>
    <?php foreach ($files as $file) {
        echo $this->render('_filesBlock', ['model' => $file]);
    } ?>
    <?php if ($downloadAll) echo Html::a(\Yii::$app->getModule('files')->fontAwesome->icon('cloud-download') .
        ' скачать все', null, ['class' => 'btn btn-default btn-xs', 'onclick' => "filesDownloadAll('{$zipTitle}', event)"]) ?>
</div>
