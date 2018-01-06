<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace floor12\files\assets;

use yii\web\AssetBundle;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class LightboxAsset extends AssetBundle
{
    public $sourcePath = '@bower';

    public $css = [
        'lightbox2/dist/css/lightbox.min.css',
    ];
    public $js = [
        'lightbox2/dist/js/lightbox.min.js',
    ];
    public $depends = [
        'yii\web\JqueryAsset',
    ];
}
