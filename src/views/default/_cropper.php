<?php
/**
 * Created by PhpStorm.
 * User: floor12
 * Date: 31.05.2016
 * Time: 12:42
 */

use floor12\files\assets\IconHelper;

?>

<div class="input-group mb-3">

</div>


<div id="yii2-file-title-editor" style="display: none;">
    <div class="input-group">
        <span class="input-group-addon" id="basic-addon1"><?= Yii::t('files', 'Filename') ?>:</span>
        <input type="text" class="form-control" id="yii2-file-title-input"
               placeholder="<?= Yii::t('files', 'Filename') ?>"
               aria-describedby="basic-addon1">
        <span class="input-group-btn">
        <button class="btn btn-default" type="button" onclick="saveFileTitle()">
            <?= IconHelper::SAVE ?>
            <?= Yii::t('files', 'Save') ?>
        </button>
        <button class="btn btn-default" type="button" onclick="hideYii2FileTitleEditor()">
            <?= IconHelper::CLOSE ?>
            <?= Yii::t('files', 'Cancel') ?>
        </button>
      </span>
    </div>
</div>

<div class="modal fade" id="cropperModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h2><?= Yii::t('files', 'Image editor') ?></h2>
            </div>
            <div class="modal-body">
                <div id="cropperArea"></div>
            </div>
            <div class="modal-footer">
                <div class="row">
                    <div class="col-xs-6 text-left">
                        <div class="btn-group" role="group">
                            <button type="button" onclick="cropper.cropper('rotate', -90);"
                                    class="btn btn-default"><?= IconHelper::ROTATE_LEFT ?></button>
                            <button type="button" onclick="cropper.cropper('rotate', 90);"
                                    class="btn btn-default"><?= IconHelper::ROTATE_RIGHT ?></button>
                        </div>
                        <div class="btn-group cropper-ratio-btn-group" role="group">
                            <button type="button" class="btn btn-default"
                                    onclick="cropper.cropper('setAspectRatio', 1);">
                                1/1
                            </button>
                            <button type="button" class="btn btn-default"
                                    onclick="cropper.cropper('setAspectRatio', 3/4);">
                                3/4
                            </button>
                            <button type="button" class="btn btn-default"
                                    onclick="cropper.cropper('setAspectRatio', 4/3);">
                                4/3
                            </button>
                            <button type="button" class="btn btn-default"
                                    onclick="cropper.cropper('setAspectRatio', 16/9);">
                                16/9
                            </button>
                        </div>
                    </div>
                    <div class="col-xs-6 text-right">
                        <button type="button" id="cropper-btn-cancel" class="btn btn-default"
                                onclick="stopCrop()"><?= Yii::t('files', 'Cancel') ?></button>
                        <button type="button" class="btn btn-primary"
                                onclick="cropImage()"><?= Yii::t('files', 'Save') ?></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
