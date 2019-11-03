<?php
/**
 * Created by PhpStorm.
 * User: floor12
 * Date: 27.06.2016
 * Time: 8:32
 */

namespace floor12\files\controllers;


use floor12\files\models\File;
use floor12\files\models\FileType;
use floor12\files\models\VideoStatus;
use Yii;
use yii\console\Controller;
use yii\helpers\Console;

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


    function actionConvert()
    {
        $ffmpeg = Yii::$app->getModule('files')->ffmpeg;

        if (!file_exists($ffmpeg))
            return $this->stdout("ffmpeg is not found: {$ffmpeg}" . PHP_EOL, Console::FG_RED);

        if (!is_executable($ffmpeg))
            return $this->stdout("ffmpeg is not executable: {$ffmpeg}" . PHP_EOL, Console::FG_RED);

        $file = File::find()
            ->where(['type' => FileType::VIDEO, 'video_status' => VideoStatus::QUEUE])
            ->andWhere(['!=', 'object_id', 0])
            ->one();

        if (!$file)
            return $this->stdout("Convert queue is empty" . PHP_EOL, Console::FG_GREEN);

        if (!file_exists($file->rootPath))
            return $this->stdout("Source file is not found: {$file->rootPath}" . PHP_EOL, Console::FG_RED);


        $file->video_status = VideoStatus::CONVERTING;
        $file->save();
        exec(Yii::$app->getModule('files')->ffmpeg . " -i {$file->rootPath} -vf scale=1280:-1 -threads 4 {$file->rootPath}.mp4");
        @unlink($file->rootPath);
        $file->filename = $file->filename . ".mp4";
        $file->video_status = VideoStatus::READY;
        $file->save();

        return $this->stdout("File converted: {$file->rootPath}" . PHP_EOL, Console::FG_GREEN);
    }


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