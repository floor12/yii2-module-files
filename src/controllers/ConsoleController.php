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

    /**
     * Run `./yii files/console/clean` to remove all unlinked files more then 6 hours
     *
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    function actionClean()
    {
        $time = strtotime('- 6 hours');
        $files = File::find()->where(['object_id' => '0'])->andWhere(['<', 'created', $time])->all();
        if ($files) foreach ($files as $file) {
            $file->delete();
        }
    }

    /**
     * Run `./yii files/console/clean-cache` to remove all generated images and previews
     */
    function actionCleanCache()
    {
        $module = Yii::$app->getModule('files');
        $commands = [];
        $commands[] = "find {$module->storageFullPath}  -regextype egrep -regex \".+/.{32}_.*\"  -exec rm -rf {} \;";
        $commands[] = "find {$module->cacheFullPath}  -regextype egrep -regex \".+/.{32}_.*\" -exec rm -rf {} \;";
        $commands[] = "find {$module->storageFullPath}  -regextype egrep -regex \".+/.{32}\..{3,4}\.jpg\" -exec rm -rf {} \;";
        $commands[] = "find {$module->cacheFullPath}  -regextype egrep -regex \".+/.{32}\..{3,4}\.jpg\" -exec rm -rf {} \;";

        array_map(function ($command) {
            exec($command);
        }, $commands);

    }

    /**
     * Run `./yii files/console/convert` to proccess one video file from queue with ffmpeg
     * @return bool|int
     */
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


}
