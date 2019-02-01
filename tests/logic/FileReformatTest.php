<?php
/**
 * Created by PhpStorm.
 * User: floor12
 * Date: 07.01.2018
 * Time: 12:45
 */

namespace floor12\files\tests\logic;

use floor12\files\logic\FileReformat;
use floor12\files\tests\TestCase;
use yii\base\ErrorException;
use yii\web\UploadedFile;

/**
 * Class FileReformatTest
 * @package floor12\files\tests\logic
 * @group reformat
 */
class FileReformatTest extends TestCase
{

    private $photoJpegPath = __DIR__ . "/../data/photo.jpeg";
    private $photoPngPath = __DIR__ . "/../data/photo.png";
    private $graphicJpegPath = __DIR__ . "/../data/graphic.jpeg";
    private $graphicPngPath = __DIR__ . "/../data/graphic.png";
    private $graphicPngAlphaPath = __DIR__ . "/../data/graphic_alpha.png";

//    private $testFilePath = "tests/data/testImage.jpg";
//    private $testFileName = "testFileName.jpg";
//    private $testOwnerClassName = "floor12\files\tests\Person";
//    private $testOwnerFieldName = "files";
//    private $storagePath = "tests/storage";
//    private $model;


    public function setUp()
    {
        parent::setUp();
        $this->setApp();
    }

    public function tearDown()
    {
        $this->clearDb();
        parent::tearDown();
    }

    /**
     * @@expectedException  \ErrorException
     */
    public function testEmptyPath()
    {
        $result = FileReformat::checkBestFormat("wrong/path");

    }


    public function testBestForPhotoFromJpeg()
    {
        $result = FileReformat::checkBestFormat($this->photoJpegPath);
        $this->assertEquals($result, IMAGETYPE_JPEG);
    }

    public function testBestForPhotoFromPng()
    {
        $result = FileReformat::checkBestFormat($this->photoPngPath);
        $this->assertEquals($result, IMAGETYPE_JPEG);
    }

    public function testBestForFraphicFromJpeg()
    {
        $result = FileReformat::checkBestFormat($this->graphicJpegPath);
        $this->assertEquals($result, IMAGETYPE_PNG);
    }

//    public function testBestForGraphicFromPng()
//    {
//        $result = FileReformat::checkBestFormat($this->graphicPngPath);
//        $this->assertFalse($result);
//    }

    public function testAlphaPng()
    {
        $result = FileReformat::checkBestFormat($this->graphicPngAlphaPath);
        $this->assertFalse($result);
    }


}