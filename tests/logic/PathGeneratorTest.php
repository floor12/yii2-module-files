<?php
/**
 * Created by PhpStorm.
 * User: floor12
 * Date: 07.01.2018
 * Time: 12:45
 */

namespace floor12\files\tests\logic;


use floor12\files\logic\ClassnameEncoder;
use floor12\files\logic\PathGenerator;
use floor12\files\tests\TestCase;

class PathGeneratorTest extends TestCase
{

    private $storagePath = "tests/storage";

    private function clearStorage()
    {
        exec('rm -rf tests/storage/*');
    }

    /** Пробуем вызвать генератор имени без указания адреса хранилища
     * @expectedException \ArgumentCountError
     */
    public function testNoStoragePath()
    {
        new PathGenerator();
    }

    /** Пробуем вызвать генератор имени без указания адреса хранилища
     * @expectedException \ErrorException
     * @expectedExceptionMessage Storage path not set for path generator.
     */
    public function testEmptyStoragePath()
    {
        new PathGenerator("");
    }

    /** Пробуем вызвать генератор имени без указания адреса хранилища
     * @expectedException \ErrorException
     * @expectedExceptionMessage Storage not found.
     */
    public function testWrongStoragePath()
    {
        new PathGenerator("/wrong/path");
    }

    /**
     * Генерируем адрес и пробуем, создался ли он в хранилище
     */
    public function testGeneratePath()
    {
        $path = (string)new PathGenerator($this->storagePath);
        $pre_path = substr($path, 0, 6);
        $fullPath = "{$this->storagePath}{$pre_path}";
        $this->assertTrue(file_exists($fullPath), $fullPath);
    }

}