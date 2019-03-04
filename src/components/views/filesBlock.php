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

use floor12\files\assets\IconHelper;
use yii\helpers\Html;

?>

<div class="files-block">
    <?php if ($title): ?>
        <label><?= $title ?></label><br>
    <?php endif; ?>
    <?php foreach ($files as $file) {
        echo $this->render('_filesBlock', ['model' => $file]);
    } ?>
    <?php if ($downloadAll && sizeof($files) > 1) echo Html::a(IconHelper::DOWNLOAD . " " .
        Yii::t('files', 'Download all'),
        null,
        ['class' => 'btn btn-default btn-xs', 'onclick' => "filesDownloadAll('{$zipTitle}', event,'" . \yii\helpers\Url::toRoute(['files/default/zip']) . "')"]) ?>
</div>
