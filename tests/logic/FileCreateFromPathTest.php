<?php
/**
 * Created by PhpStorm.
 * User: floor12
 * Date: 07.01.2018
 * Time: 12:45
 */

namespace floor12\files\tests\logic;


use floor12\files\logic\FileCreateFromPath;
use floor12\files\models\File;
use floor12\files\tests\TestCase;
use yii\base\ErrorException;

class FileCreateFromPathTest extends TestCase
{
    private $testFilePath = "tests/data/testImage.jpg";
    private $testFileName = "testFileName.jpg";
    private $testOwnerClassName = "floor12\files\tests\data\Person";
    private $testOwnerFieldName = "files";
    private $storagePath = "tests/storage";
    private $model;


    public function setUp()
    {

        $this->model = $this->getMockBuilder('floor12\files\models\File')
            ->setMethods(['save'])
            ->getMock();
        $this->model->method('save')->willReturn(true);


        http://www.yiiframework.com/forum/index.php/topic/71516-how-to-mock-activerecord/
        parent::setUp();
    }

    /** Вызываем несуществуюий файл
     * @expectedException ErrorException
     * @expectedExceptionMessage File not found on disk.
     */

    public function testFileNotExists()
    {
        new FileCreateFromPath(
            $this->model,
            "wrongTestFileName.png",
            $this->testOwnerClassName,
            $this->testOwnerFieldName,
            $this->storagePath,
            $this->testFileName
        );
    }

    /** Вызываем несуществуюий файл
     * @expectedException ErrorException
     * @expectedExceptionMessage Empty params not allowed.
     */

    public function testEmptyParams()
    {
        new FileCreateFromPath(
            $this->model,
            "wrongTestFileName.png",
            "",
            $this->testOwnerFieldName,
            $this->storagePath,
            $this->testFileName
        );
    }

    /** Пробуем дать несуществующий адрес адрес хранилища
     * @expectedException ErrorException
     * @expectedExceptionMessage File storage not found on disk.
     */

    public function testWrongStorage()
    {
        new FileCreateFromPath(
            $this->model,
            $this->testFilePath,
            $this->testOwnerClassName,
            $this->testOwnerFieldName,
            "wrongPath",
            $this->testFileName
        );
    }


    /** Не записываемое хранилище
     * @expectedException ErrorException
     * @expectedExceptionMessage File storage not found on disk.
     */

    public function testNotWritableStorage()
    {
        new FileCreateFromPath(
            $this->model,
            $this->testFilePath,
            $this->testOwnerClassName,
            $this->testOwnerFieldName,
            $this->testFileName
        );
    }


    /**Нормальный сценарий который пока протестировать нормально не удается.
     */

//    public function testCreate()
//    {
//        $logicObject = new FileCreateFromPath(
//            $this->model,
//            $this->testFilePath,
//            $this->testOwnerClassName,
//            $this->testOwnerFieldName,
//            $this->storagePath,
//            $this->testFileName
//        );
//
//        $this->assertTrue(is_object($logicObject));
//
//        $file = $logicObject->execute();
//
//        is_object($file);
//    }


}