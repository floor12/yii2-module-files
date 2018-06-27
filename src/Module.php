<?php
/**
 * Created by PhpStorm.
 * User: floor12
 * Date: 31.12.2017
 * Time: 14:45
 */

namespace floor12\files;

use \Yii;
use yii\i18n\PhpMessageSource;

/**
 * Class Module
 * @package floor12\files
 * @property string $token_salt
 * @property string $fontAwesome
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

    public $ffmpeg = 'ffmpeg';

    public $token_salt = 'randomString412DDs@#KJH';

    public $fontAwesome = 'rmrevin\yii\fontawesome\FontAwesome';

    public $storageFullPath;

    public $allowOfficePreview = true;

    public $params = ['db' => 'db'];

    public $db;

    public $editRoles = ['@'];

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->registerTranslations();

        $this->db = Yii::$app->{$this->params['db']};

        $this->fontAwesome = Yii::createObject($this->fontAwesome);

        $this->storageFullPath = Yii::getAlias($this->storage);
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

    public function adminMode()
    {
        if (!$this->editRoles)
            return true;
        if ($this->editRoles == ['@'])
            return !\Yii::$app->user->isGuest;
        else
            return \Yii::$app->user->can($this->editRole);
    }
}