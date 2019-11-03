<?php
/**
 * Created by PhpStorm.
 * User: floor12
 * Date: 31.12.2017
 * Time: 14:45
 */

namespace floor12\files;

use Yii;
use yii\db\Connection;

/**
 * Class Module
 * @package floor12\files
 * @property string $token_salt
 * @property string $storage
 * @property string $controllerNamespace
 *
 */
class Module extends \yii\base\Module
{
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'floor12\files\controllers';

    /** Путь к файловому хранилищу
     * @var string
     */
    public $storage = '@vendor/../storage';

    /** Путь к  хранилищу кешей
     * @var string
     */
    public $cache = '@vendor/../storage_cache';
    /**
     * @var string
     */
    public $hostStatic = '';
    /**
     * @var string
     */
    public $ffmpeg = '/usr/bin/ffmpeg';
    /**
     * @var string
     */
    public $token_salt = 'randomString412DDs@#KJH';
    /**
     * @var string
     */
    public $storageFullPath;
    /**
     * @var string
     */
    public $cacheFullPath;
    /**
     * @var bool
     */
    public $allowOfficePreview = true;
    /**
     * @var array
     */
    public $params = ['db' => 'db'];
    /**
     * @var Connection
     */
    public $db;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->registerTranslations();
        $this->db = Yii::$app->{$this->params['db']};
        $this->storageFullPath = Yii::getAlias($this->storage);
        $this->cacheFullPath = Yii::getAlias($this->cache);
    }

    /**
     * @return void
     */
    public function registerTranslations()
    {
        $i18n = Yii::$app->i18n;
        $i18n->translations['files'] = [
            'class' => 'yii\i18n\PhpMessageSource',
            'sourceLanguage' => 'en-US',
            'basePath' => '@vendor/floor12/yii2-module-files/src/messages',
        ];
    }

}