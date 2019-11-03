<?php
/**
 * Created by PhpStorm.
 * User: floor12
 * Date: 01.01.2018
 * Time: 13:34
 */

namespace floor12\files\logic;


use yii\base\ErrorException;

class PathGenerator
{

    private $path = '';


    public function __construct($storagePath)
    {

        if (!$storagePath)
            throw new ErrorException('Storage path not set for path generator.');

        if (!file_exists($storagePath))
            mkdir($storagePath);

        if (!file_exists($storagePath))
            throw new ErrorException('Unable to create storage.');

        $folderName0 = rand(10, 99);
        $folderName1 = rand(10, 99);

        $path0 = DIRECTORY_SEPARATOR . $folderName0;
        $path1 = DIRECTORY_SEPARATOR . $folderName0 . DIRECTORY_SEPARATOR . $folderName1;

        $fullPath0 = $storagePath . $path0;
        $fullPath1 = $storagePath . $path1;

        if (!file_exists($fullPath0))
            mkdir($fullPath0);
        if (!file_exists($fullPath1))
            mkdir($fullPath1);

        $this->path = $path1 . DIRECTORY_SEPARATOR . md5(rand(0, 1000) . time());
    }

    public function __toString()
    {
        return $this->path;
    }
}