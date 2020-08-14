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
use floor12\files\models\FileType;
use floor12\files\tests\TestCase;
use yii\base\ErrorException;

class FileCreateFromPathTest extends TestCase
{

    private $testFilePath = 'tests/data/testImage.jpg';
    private $testFileName = 'testFileName.jpg';
    private $testOwnerClassName = 'floor12\files\tests\Person';
    private $testOwnerFieldName = 'files';
    private $storagePath = 'tests/storage';
    private $model;


    /** Вызываем несуществующий файл
     */

    public function testFileNotExists()
    {
        $this->expectExceptionMessage("File not found on disk.");
        $this->expectException(ErrorException::class);
        new FileCreateFromPath(
            new File(),
            "wrongTestFileName.png",
            $this->testOwnerClassName,
            $this->testOwnerFieldName,
            $this->storagePath,
            $this->testFileName
        );
    }

    /** Вызываем несуществуюий файл
     *
     *
     */

    public function testEmptyParams()
    {
        $this->expectExceptionMessage("Empty params not allowed.");
        $this->expectException(ErrorException::class);
        new FileCreateFromPath(
            new File(),
            "wrongTestFileName.png",
            "",
            $this->testOwnerFieldName,
            $this->storagePath,
            $this->testFileName
        );
    }

    /** Пробуем дать несуществующий адрес адрес хранилища
     *
     *
     */

    public function testWrongStorage()
    {
        $this->expectExceptionMessage("File storage not found on disk.");
        $this->expectException(ErrorException::class);
        new FileCreateFromPath(
            new File(),
            $this->testFilePath,
            $this->testOwnerClassName,
            $this->testOwnerFieldName,
            "wrongPath",
            $this->testFileName
        );
    }


    /** Не записываемое хранилище
     *
     *
     */

    public function testNotWritableStorage()
    {
        $this->expectExceptionMessage("File storage not found on disk.");
        $this->expectException(ErrorException::class);
        new FileCreateFromPath(
            new File(),
            $this->testFilePath,
            $this->testOwnerClassName,
            $this->testOwnerFieldName,
            $this->testFileName
        );
    }


    /**
     * Нормальный сценарий который пока протестировать нормально не удается.
     */

    public function testCreate()
    {
        $this->setApp();

        $file = new File();

        $logicObject = new FileCreateFromPath(
            $file,
            $this->testFilePath,
            $this->testOwnerClassName,
            $this->testOwnerFieldName,
            $this->storagePath,
            $this->testFileName
        );
        $this->assertTrue(is_object($logicObject));
        $this->assertTrue($logicObject->execute());


        // Проверяем, что файл сохранился нормально.
        $this->assertFalse($file->isNewRecord);
        $this->assertTrue(is_integer($file->id));
        $this->assertEquals($this->testOwnerClassName, $file->class);
        $this->assertEquals(FileType::IMAGE, $file->type);
        $this->assertEquals("image/jpeg", $file->content_type);
        $this->assertTrue(file_exists($file->rootPath), $file->rootPath);

        $this->clearDb();
    }


}
