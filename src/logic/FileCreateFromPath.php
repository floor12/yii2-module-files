<?php
/**
 * Created by PhpStorm.
 * User: floor12
 * Date: 07.01.2018
 * Time: 8:43
 */

namespace floor12\files\logic;


use floor12\files\components\SimpleImage;
use floor12\files\models\File;
use yii\base\ErrorException;
use yii\db\ActiveRecordInterface;

class FileCreateFromPath
{

    private $className;
    private $fieldName;
    private $filePath;
    private $fileName;
    private $storagePath;
    private $model;

    public function __construct(ActiveRecordInterface $model, string $filePath, string $className, string $fieldName, string $storagePath, string $fileName = null)
    {

        $this->model = $model;

        if (!$filePath || !$className || !$fieldName || !$storagePath)
            throw new ErrorException("Empty params not allowed.");

        if (!file_exists($storagePath))
            throw new ErrorException("File storage not found on disk.");

        if (!file_exists($filePath))
            throw new ErrorException("File not found on disk.");

        if (!is_writable($storagePath))
            throw new ErrorException("File storage is not writable.");

        $this->filePath = $filePath;
        $this->fileName = $fileName;
        $this->fieldName = $fieldName;
        $this->className = $className;
        $this->storagePath = $storagePath;

    }

    /** Основная  работка
     * @return int
     */
    public function execute()
    {

        // копируем файл в хранилище
        $tmp_extansion = explode('?', pathinfo($this->filePath, PATHINFO_EXTENSION));
        $extansion = $tmp_extansion[0];
        $filename = new PathGenerator($this->storagePath) . "." . $extansion;
        $new_path = $this->storagePath . $filename;
        copy($this->filePath, $new_path);

        // создаем запись в базе
        $this->model->field = $this->fieldName;
        $this->model->class = $this->className;
        $this->model->filename = $filename;
        if ($this->model->filename)
            $this->model->title = $this->fileName;
        else
            $this->model->title = rand(0, 99999); #такой прикол )
        $this->model->content_type = $this->model->mime_content_type($new_path);
        $this->model->type = $this->detectType();
        $this->model->size = filesize($new_path);
        $this->model->created = time();
        $this->model->user_id = (isset(\Yii::$app->user) && \Yii::$app->user->id) ? \Yii::$app->user->id : 0;
        if ($this->model->type == File::TYPE_VIDEO)
            $this->model->video_status = 0;
        if ($this->model->save()) {

            if ($this->model->type == File::TYPE_IMAGE) {
                $exif = '';
                @$exif = exif_read_data($new_path);
                if (isset($exif['Orientation'])) {
                    $ort = $exif['Orientation'];
                    $rotatingImage = new SimpleImage();
                    $rotatingImage->load($new_path);
                    switch ($ort) {

                        case 3: // 180 rotate left
                            $rotatingImage->rotateDegrees(180);
                            break;


                        case 6: // 90 rotate right
                            $rotatingImage->rotateDegrees(270);
                            break;

                        case 8:    // 90 rotate left
                            $rotatingImage->rotateDegrees(90);
                    }
                    $rotatingImage->save($new_path);
                }

            }
            $this->model->updatePreview();
            return $this->model;
        }
    }

    /**
     * @return integer
     */
    private function detectType()
    {
        $contentTypeArray = explode('/', $this->model->content_type);
        if ($contentTypeArray[0] == 'image')
            return File::TYPE_IMAGE;
        if ($contentTypeArray[0] == 'video')
            return File::TYPE_VIDEO;
        return File::TYPE_FILE;
    }
}