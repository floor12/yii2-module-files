<?php


namespace floor12\files\models;


use yii2mod\enum\helpers\BaseEnum;

class FileType extends BaseEnum
{
    const FILE = 0;
    const IMAGE = 1;
    const VIDEO = 2;

    public static $list = [
        self::FILE => 'file',
        self::IMAGE => 'image',
        self::VIDEO => 'video',
    ];

    public static $messageCategory = 'files';
}