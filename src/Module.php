<?php
/**
 * Created by PhpStorm.
 * User: floor12
 * Date: 31.12.2017
 * Time: 14:45
 */

namespace floor12\files;

use Yii;
use yii\base\ErrorException;

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

    /** Путь к файловому хранилищу* @var string
     */
    public $storage = '@vendor/../storage';

    public $hostStatic = '';

    public $ffmpeg = 'ffmpeg';

    public $token_salt = 'randomString412DDs@#KJH';

    public $storageFullPath;

    public $allowOfficePreview = true;

    public $params = ['db' => 'db'];

    public $db;

    public $cwebp = '/usr/local/bin/cwebp';


    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->registerTranslations();

        $this->db = Yii::$app->{$this->params['db']};

        $this->storageFullPath = Yii::getAlias($this->storage);

        if (!is_executable($this->cwebp))
            throw new ErrorException('CWEBP cli is not found or not executable.');
    }

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