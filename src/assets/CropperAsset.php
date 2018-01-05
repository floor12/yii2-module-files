<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace floor12\files\assets;

use yii\web\AssetBundle;

class CropperAsset extends AssetBundle {

    public $sourcePath = '@bower';
    public $css = [
        'cropper/dist/cropper.min.css',
    ];
    public $js = [
        'cropper/dist/cropper.min.js',
    ];
    public $depends = [
        'yii\web\JqueryAsset',
    ];

}
