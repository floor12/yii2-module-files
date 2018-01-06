<?php
/**
 * Created by PhpStorm.
 * User: floor12
 * Date: 06.01.2018
 * Time: 12:09
 *
 * @var $this \yii\web\View
 * @var $files \floor12\files\models\File[]
 * @var $title string
 */

?>
<label><?= $files ? $title : NULL ?></label>
<div class="files-block">
    <?php foreach ($files as $file) {
        echo $this->render('_filesBlock', ['model' => $file]);
    } ?>
</div>
