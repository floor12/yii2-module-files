<?php
/**
 * Created by PhpStorm.
 * User: floor12
 * Date: 31.12.2017
 * Time: 14:45
 */

namespace floor12\files;


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

    /** Путь к файловому хранилищу
     * @var string
     */
    public $storage = '@vendor/../storage';

    public $token_salt = 'randomString412DDs@#KJH';

    public $fontAwesome = 'rmrevin\yii\fontawesome\FontAwesome';

    public $storageFullPath;

    public $allowOfficePreview = true;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->fontAwesome = \Yii::createObject($this->fontAwesome);
        $this->storageFullPath = \Yii::getAlias($this->storage);
        parent::init();

    }
}