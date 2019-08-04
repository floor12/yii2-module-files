<?php


namespace floor12\files\models;


use yii2mod\enum\helpers\BaseEnum;

class VideoStatus extends BaseEnum
{
    const QUEUE = 0;
    const CONVERTING = 1;
    const READY = 2;

    public static $messageCategory = 'files';

    public static $list = [
        self::QUEUE => 'queued',
        self::CONVERTING => 'converting',
        self::READY => 'ready',
    ];

}