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
use yii\web\BadRequestHttpException;
use yii\web\UploadedFile;

class FileCreateFromInstanceTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->setApp();
    }

    public function tearDown(): void
    {
        $this->clearDb();
        parent::tearDown();
    }

    /** Вызываем без параметров
     *
     */

    public function testNoParams()
    {
        $this->expectException(ArgumentCountError::class);
        new FileCreateFromInstance();
    }

    /** Вызываем без параметров
     *
     *
     */

    public function testBadParams()
    {
        $this->expectExceptionMessage("Attribute or class name not set.");
        $this->expectException(\yii\web\BadRequestHttpException::class);
        $instance = new UploadedFile();
        $data = [];
        new FileCreateFromInstance($instance, $data);
    }

    /** Вызываем без параметров
     *
     *
     */

    public function testWrongOwnerClassname()
    {
        $this->expectExceptionMessage("Attribute or class name not set.");
        $this->expectException(BadRequestHttpException::class);

        $instance = new UploadedFile();
        $data = [
            'modelClass' => "notExistClassName",
            'attribute' => 'images',
        ];
        $logic = new FileCreateFromInstance($instance, [], null, false);
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
            'modelClass' => TestModel::class,
            'attribute' => 'images',
        ];
        $logic = new FileCreateFromInstance($instance, $data, null, false);

        $model = $logic->execute();

        $this->assertTrue(is_object($model));
        $this->assertFalse($model->isNewRecord);
        $this->assertTrue(file_exists($model->rootPath), $model->rootPath);
        $this->assertTrue(file_exists($model->rootPath), $model->getPreviewWebPath());


    }


}
