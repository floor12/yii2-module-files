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
use Throwable;
use Yii;
use yii\console\Controller;
use yii\db\ActiveRecord;
use yii\db\StaleObjectException;
use yii\helpers\Console;

class ConsoleController extends Controller
{

    /**
     * Run `./yii files/console/clean` to remove all unlinked files more then 6 hours
     *
     * @throws Throwable
     * @throws StaleObjectException
     */
    function actionClean()
    {
        $time = strtotime('- 6 hours');
        $files = File::find()->where(['object_id' => '0'])->andWhere(['<', 'created', $time])->all();
        if ($files) foreach ($files as $file) {
            $file->delete();
        }
    }

    function actionClear()
    {
        $countDeleted = $countOk = 0;
        $module = Yii::$app->getModule('files');
        $path1 = $module->storageFullPath;
        foreach (scandir($path1) as $folder1) {
            $path2 = $path1 . '/' . $folder1;
            if ($this->checkFolderItem($folder1)) {
                continue;
            }
            foreach (scandir($path2) as $folder2) {
                $path3 = $path2 . '/' . $folder2;
                if ($this->checkFolderItem($folder2)) {
                    continue;
                };
                foreach (scandir($path3) as $filename) {
                    $path4 = $path3 . '/' . $filename;
                    if ($this->checkFolderItem($filename)) {
                        continue;
                    };
                    $dbFileName = "/{$folder1}/{$folder2}/{$filename}";
                    if (is_file($path4)) {
                        $this->stdout($path4 . "...");
                        if (File::find()->where(['filename' => $dbFileName])->count() === 0) {
                            $this->stdout('no' . PHP_EOL, Console::FG_RED);
                            unlink($path4);
                            $countDeleted++;
                        } else {
                            $this->stdout('ok' . PHP_EOL, Console::FG_GREEN);
                            $countOk++;
                        }
                    }
                }
            }
        }
        $this->stdout('Deleted: ' . $countDeleted . PHP_EOL, Console::FG_YELLOW);
        $this->stdout('Ok: ' . $countOk . PHP_EOL, Console::FG_GREEN);
    }

    private function checkFolderItem($string)
    {
        $ignoreItems = ['.', '..', '.gitignore', 'summerfiles'];
        if (in_array($string, $ignoreItems)) {
            return true;
        }
        return false;
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
        $width = $this->getVideoWidth($file->class, $file->field);
        $height = $this->getVideoHeight($file->class, $file->field);
        $newFileName = $file->filename . ".mp4";
        $newFilePath = $file->rootPath . ".mp4";
        $command = Yii::$app->getModule('files')->ffmpeg . " -i {$file->rootPath} -vf scale={$width}:{$height} -threads 4 {$newFilePath}";
        echo $command . PHP_EOL;
        exec($command,
            $outout, $result);
        if ($result == 0) {
            @unlink($file->rootPath);
            $file->filename = $newFileName;
            $file->content_type = 'video/mp4';
            $file->video_status = VideoStatus::READY;
        } else {
            $file->video_status = VideoStatus::QUEUE;
        }
        $file->save();

        return $this->stdout("File converted: {$file->rootPath}" . PHP_EOL, Console::FG_GREEN);
    }

    protected
    function getVideoWidth($classname, $field)
    {
        /** @var ActiveRecord $ownerClassObject */
        $ownerClassObject = new $classname;
        return $ownerClassObject->getBehavior('files')->attributes[$field]['videoWidth'] ?? 1280;
    }

    protected
    function getVideoHeight($classname, $field)
    {
        /** @var ActiveRecord $ownerClassObject */
        $ownerClassObject = new $classname;
        return $ownerClassObject->getBehavior('files')->attributes[$field]['videoHeight'] ?? -1;
    }


}
