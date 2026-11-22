<?php

/**
 * Created by PhpStorm.
 * User: floor12
 * Date: 06.01.2018
 * Time: 12:09
 */

use floor12\files\assets\IconHelper;
use floor12\files\models\File;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/**
 * @var View $this
 * @var string $title
 * @var string $zipTitle
 * @var bool $downloadAll
 * @var string $lightboxKey
 * @var bool $allowImageSrcDownload
 * @var File[] $files
 */

?>

<div class="files-block">
    <?php if ($title) : ?>
        <label><?= $title ?></label>
    <?php endif; ?>
    <ul>
        <?php foreach ($files as $file) {
            echo $this->render('_fileListWidget', [
                'model' => $file,
                'lightboxKey' => $lightboxKey,
                'allowImageSrcDownload' => $allowImageSrcDownload
            ]);
        } ?>
        <?php if ($downloadAll && sizeof($files) > 1) { ?>
            <li>
                <a class="f12-files-btn-download-all"
                   onclick="filesDownloadAll('<?= $zipTitle ?>',event,'<?= Url::toRoute(['/files/default/zip']) ?>')">
                    <?= IconHelper::DOWNLOAD ?>
                    <?= Yii::t('files', 'Download all') ?>
                </a>
            </li>
        <?php } ?>
    </ul>
</div>