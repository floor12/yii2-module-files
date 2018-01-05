<?php

namespace floor12\files\assets;

use yii\web\AssetBundle;


class SimpleAjaxUploaderAsset extends AssetBundle
{

    public $sourcePath = '@bower/';
    public $js = [
        'simple-ajax-uploader/SimpleAjaxUploader.js'
    ];
    public $depends = [
        'yii\web\JqueryAsset',
    ];

}
