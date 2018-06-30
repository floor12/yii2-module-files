<?php
/**
 * Created by PhpStorm.
 * User: floor12
 * Date: 07.01.2018
 * Time: 12:40
 */

namespace floor12\files\tests;

use yii\console\Application;
use floor12\files\tests\data\m180627_121715_files;

abstract class TestCase extends \PHPUnit_Framework_TestCase
{

    public $sqlite = 'tests/sqlite.db';


    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();
        $this->mockApplication();
    }


    /**
     * Настраиваем основные параметры приложения: базу данных и модуль
     */

    protected function setApp()
    {
        $files = [
            'class' => 'floor12\files\Module',
            'storage' => '@app/storage',
        ];
        \Yii::$app->setModule('files', $files);


        $db = [
            'class' => 'yii\db\Connection',
            'dsn' => "sqlite:$this->sqlite",
        ];
        \Yii::$app->set('db', $db);

        \Yii::createObject(m180627_121715_files::class, [])->safeUp();

    }

    /**
     * Чистим за собой временную базу данных
     */
    protected function clearDb()
    {
        \Yii::createObject(m180627_121715_files::class, [])->safeDown();
    }

    /**
     * @inheritdoc
     */
    protected function tearDown()
    {
        $this->destroyApplication();
        parent::tearDown();
    }

    /**
     *  Запускаем приложение
     */
    protected function mockApplication()
    {
        new Application([
            'id' => 'testapp',
            'basePath' => __DIR__,
            'vendorPath' => dirname(__DIR__) . '/vendor',
            'runtimePath' => __DIR__ . '/runtime',
        ]);
    }


    /**
     * Убиваем приложение
     */
    protected function destroyApplication()
    {
        \Yii::$app = null;
    }
}