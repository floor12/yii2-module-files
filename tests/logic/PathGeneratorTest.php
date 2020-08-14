<?php
/**
 * Created by PhpStorm.
 * User: floor12
 * Date: 07.01.2018
 * Time: 12:45
 */

namespace floor12\files\tests\logic;


use ArgumentCountError;
use ErrorException;
use floor12\files\logic\PathGenerator;
use floor12\files\tests\TestCase;

class PathGeneratorTest extends TestCase
{

    private $storagePath = "tests/storage";

    /**
     * Storage cleaning
     */
    private function clearStorage()
    {
        exec('rm -rf tests/storage/*');
    }

    public function testNoStoragePath()
    {
        $this->expectException(ArgumentCountError::class);
        new PathGenerator();
    }

    /**
     * Empty storage path check
     *
     *
     *
     */
    public function testEmptyStoragePath()
    {
        $this->expectException(ErrorException::class);
        $this->expectExceptionMessage("Storage path not set for path generator.");
        new PathGenerator("");
    }

    /**
     * Create storage path if it not exists
     */
    public function testCreateStoragePath()
    {
        $path = 'tests/st1';
        new PathGenerator($path);
        $this->assertTrue(file_exists($path));
        exec("rm -f $path");
    }

    /**
     * Create file path and check it exists
     */
    public function testGeneratePath()
    {
        $path = (string)new PathGenerator($this->storagePath);
        $pre_path = substr($path, 0, 6);
        $fullPath = "{$this->storagePath}{$pre_path}";
        $this->assertTrue(file_exists($fullPath), $fullPath);
    }

}
