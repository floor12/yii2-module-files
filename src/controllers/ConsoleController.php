<?php
/**
 * Created by PhpStorm.
 * User: floor12
 * Date: 27.06.2016
 * Time: 8:32
 */

namespace floor12\files\controllers;


use yii\console\Controller;
use floor12\files\models\File;

class ConsoleController extends Controller
{
    function actionClean()
    {
        $time = strtotime('- 6 hours');
        $files = File::find()->where("`object_id`=0 AND `created`<'{$time}'")->all();
        if ($files) foreach ($files as $file) {
            $file->delete();
        }
    }

//
//    function actionConvert()
//    {
//        $file = File::find()->where(['type' => File::TYPE_VIDEO, 'video_status' => File::VIDEO_STATUS_QUEUE])->one();
//        if ($file && file_exists($file->rootPath)) {
//            $file->video_status = File::VIDEO_STATUS_CONVERTING;
//            $file->save();
//            exec(Yii::getAlias('@ffmpeg') . " -i {$file->rootPath} -threads 4 {$file->rootPath}.mp4");
//            @unlink($file->rootPath);
//            @unlink($file->rootPreviewPath);
//            $file->filename = $file->filename . ".mp4";
//            $file->video_status = File::VIDEO_STATUS_READY;
//            $file->save();
//            $file->updatePreview();
//        }
//
//    }
    function actionHash()
    {
        $files = File::find()->all();
        if ($files)
            foreach ($files as $file) {
                if (!$file->hash) {
                    $file->hash = md5(rand(100000, 100000000) . time());
                    $file->save();
                    echo "{$file->hash}\n";
                }
            }
    }


}