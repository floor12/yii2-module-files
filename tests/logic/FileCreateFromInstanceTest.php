<?php
/**
 * Created by PhpStorm.
 * User: floor12
 * Date: 07.01.2018
 * Time: 12:45
 */

namespace floor12\files\tests\logic;


use ArgumentCountError;
use floor12\files\logic\FileCreateFromInstance;
use floor12\files\tests\data\TestModel;
use floor12\files\tests\TestCase;
use yii\base\ErrorException;
use yii\web\UploadedFile;

class FileCreateFromInstanceTest extends TestCase
{

    private $testFilePath = "tests/data/testImage.jpg";
    private $testFileName = "testFileName.jpg";
    private $testOwnerClassName = "floor12\files\tests\Person";
    private $testOwnerFieldName = "files";
    private $storagePath = "tests/storage";
    private $model;


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

    /** Вызываем без параметров
     * @expectedException ArgumentCountError
     */

    public function testNoParams()
    {
        new FileCreateFromInstance();
    }

    /** Вызываем без параметров
     * @expectedException yii\web\BadRequestHttpException
     * @expectedExceptionMessage Attribute or class name not set.
     */

    public function testBadParams()
    {
        $instance = new UploadedFile();
        $data = [];
        new FileCreateFromInstance($instance, $data);
    }

    /** Вызываем без параметров
     * @expectedException ErrorException;
     * @expectedExceptionMessage Attribute or class name not set.
     */

    public function testWrongOwnerClassname()
    {
        $instance = new UploadedFile();
        $data = [
            'modelClass' => "notExistClassName",
            'attribute' => 'images',
        ];
    }

    /** Вызываем с нормальными параметрами
     */

    public function testGoodParams()
    {

        $instance = new UploadedFile();
        $instance->error = 0;
        $instance->name = 'testName.jpg';
        $instance->tempName = "tests/data/testImage.jpg";
        $instance->size = filesize($instance->tempName);
        $instance->type = "image/jpeg";


        $this->assertTrue(file_exists($instance->tempName));

        $data = [
            'modelClass' => TestModel::className(),
            'attribute' => 'images',
        ];
        $logic = new FileCreateFromInstance($instance, $data, null, false);

        $model = $logic->execute();

        $this->assertTrue(is_object($model));
        $this->assertFalse($model->isNewRecord);
        $this->assertTrue(file_exists($model->rootPath), $model->rootPath);
        $this->assertTrue(file_exists($model->rootPath), $model->rootPreviewPath);


    }


}