<?php
/**
 * Created by PhpStorm.
 * User: floor12
 * Date: 23.06.2016
 * Time: 11:23
 */

namespace floor12\files\models;


use ErrorException;
use floor12\files\assets\IconHelper;
use Yii;
use yii\db\ActiveRecord;
use yii\helpers\Url;


/**
 * @property integer $id
 * @property string $class
 * @property string $field
 * @property integer $object_id
 * @property string $title
 * @property string $filename
 * @property string $content_type
 * @property integer $type
 * @property integer $video_status
 * @property integer $ordering
 * @property integer $created
 * @property integer $user_id
 * @property integer $size
 * @property string $hash
 * @property string $alt
 * @property string $href
 * @property string $icon
 * @property string $rootPath
 * @property string|null $watermark
 */
class File extends ActiveRecord
{
    const DIRECTORY_SEPARATOR = "/";


    /**
     * {@inheritdoc}
     */
    public static function getDb()
    {
        return Yii::$app->getModule('files')->db;
    }

    /**
     * @inheritdoc
     */

    public static function tableName()
    {
        return '{{%file}}';
    }

    /**
     * Create hash if its empty
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if (!$this->hash) {
            $this->changeHash();
        }
        return parent::beforeSave($insert);
    }

    /**
     * Change object hash
     */
    public function changeHash()
    {
        $this->hash = md5(time() . rand(99999, 99999999));

    }

    /**
     * @return string
     */
    public function getIcon()
    {
        $icon = IconHelper::FILE;

        if ($this->content_type == 'application/vnd.openxmlformats-officedocument.wordprocessingml.document')
            $icon = IconHelper::FILE_WORD;

        if ($this->content_type == 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            $icon = IconHelper::FILE_EXCEL;

        if ($this->content_type == 'application/vnd.openxmlformats-officedocument.presentationml.presentation')
            $icon = IconHelper::FILE_POWERPOINT;

        if ($this->content_type == 'application/x-zip-compressed')
            $icon = IconHelper::FILE_ARCHIVE;

        if ($this->content_type == 'application/octet-stream')
            $icon = IconHelper::FILE_ARCHIVE;

        if (preg_match('/audio/', $this->content_type))
            $icon = IconHelper::FILE_AUDIO;

        if (preg_match('/pdf/', $this->content_type))
            $icon = IconHelper::FILE_PDF;

        if ($this->type == FileType::VIDEO)
            $icon = IconHelper::FILE_VIDEO;

        return $icon;
    }

    /**
     * @inheritdoc
     */

    public function rules()
    {
        return [
            [['class', 'field', 'filename', 'content_type', 'type'], 'required'],
            [['object_id', 'type', 'video_status', 'ordering'], 'integer'],
            [['class', 'field', 'title', 'filename', 'content_type', 'alt'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */

    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'class' => Yii::t('app', 'Class'),
            'field' => Yii::t('app', 'Field'),
            'object_id' => Yii::t('app', 'Object ID'),
            'title' => Yii::t('app', 'Title'),
            'filename' => Yii::t('app', 'Filename'),
            'content_type' => Yii::t('app', 'Con tent Type'),
            'type' => Yii::t('app', 'Type'),
            'video_status' => Yii::t('app', 'Video Status'),
            'alt' => Yii::t('app', 'Alternative title'),
        ];
    }

    /**
     * Return root path of preview
     * @return string
     */

    public function getRootPreviewPath()
    {
        if ($this->isSvg())
            return $this->getRootPath();

        return Yii::$app->getModule('files')->storageFullPath . $this->filename . '.jpg';
    }

    /**
     * @return bool
     */
    public function isSvg()
    {
        return $this->content_type == 'image/svg+xml';
    }

    /**
     * Return root path of image
     * @return string
     */

    public function getRootPath()
    {
        return Yii::$app->getModule('files')->storageFullPath . DIRECTORY_SEPARATOR . $this->filename;
    }


    /**
     * Return web path
     * @return string
     */

    public function getHref()
    {
        return Url::to(['/files/default/get', 'hash' => $this->hash]);
    }

    /**
     * @return bool
     */
    public function isImage(): bool
    {
        return $this->type == FileType::IMAGE;
    }

    /**
     * Delete files from disk
     */

    public function afterDelete()
    {
        $this->deleteFiles();
        parent::afterDelete();
    }

    /**
     * Method to read files from any mime types
     * @return bool
     */

//    public function imageCreateFromAny()
//    {
//        $type = exif_imagetype($this->rootPath);
//        $allowedTypes = array(
//            1, // [] gif
//            2, // [] jpg
//            3, // [] png
//            6   // [] bmp
//        );
//        if (!in_array($type, $allowedTypes)) {
//            return false;
//        }
//        switch ($type) {
//            case 1 :
//                $im = imageCreateFromGif($this->rootPath);
//                break;
//            case 2 :
//                $im = imageCreateFromJpeg($this->rootPath);
//                break;
//            case 3 :
//                $im = imageCreateFromPng($this->rootPath);
//                break;
//            case 6 :
//                $im = imageCreateFromBmp($this->rootPath);
//                break;
//        }
//        return $im;
//    }

    /**
     * Delete all files
     */
    public function deleteFiles()
    {
        $extension = pathinfo($this->rootPath, PATHINFO_EXTENSION);
        array_map('unlink', glob(str_replace(".{$extension}", '*', $this->rootPath)));
    }

    /**
     * Set object_id to 0 to break link with object
     * @return void
     */
    public function setZeroObject()
    {
        $this->object_id = 0;
        $this->save(false);
    }

    /**
     * @return mixed|null
     */
    public function getWatermark()
    {
        $behaviors = (new $this->class)->behaviors();
        return $behaviors['files']['attributes'][$this->field]['watermark'] ?? null;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->href;
    }

    /**
     * Return webp path to preview
     * @param int $width
     * @param bool $webp
     * @return string
     * @throws ErrorException
     */
    public function getPreviewWebPath(int $width = 0, bool $webp = false)
    {
        if (!file_exists($this->getRootPath()))
            return null;

        if (!$this->isVideo() && !$this->isImage())
            throw new ErrorException('Requiested file is not an image and its implsible to resize it.');

        if (Yii::$app->getModule('files')->hostStatic)
            return
                Yii::$app->getModule('files')->hostStatic .
                $this->makeNameWithSize($this->filename, $width, $webp) .
                "?hash={$this->hash}&width={$width}&webp=" . intval($webp);

        return Url::toRoute(['/files/default/image', 'hash' => $this->hash, 'width' => $width, 'webp' => $webp]);
    }

    /**
     * @return bool
     */
    public function isVideo(): bool
    {
        return $this->type == FileType::VIDEO;
    }

    /**
     * Creates file paths to file versions
     * @param $name
     * @param int $width
     * @param bool $webp
     * @return string
     */
    public function makeNameWithSize($name, $width = 0, $webp = false)
    {
        $extension = pathinfo($this->rootPath, PATHINFO_EXTENSION);
        $rootPath = str_replace(".{$extension}", '', $name) . "_w" . $width . ".{$extension}";
        return str_replace($extension, $webp ? 'webp' : 'jpeg', $rootPath);
    }

    /**
     * Returns full path to custom preview version
     * @param int $width
     * @param bool $webp
     * @return string
     * @throws ErrorException
     */
    public function getPreviewRootPath($width = 0, $webp = false)
    {
        if (!$this->isVideo() && !$this->isImage())
            throw new ErrorException('Requiested file is not an image and its implsible to resize it.');
        return $this->makeNameWithSize($this->rootPath, $width, $webp);
    }

    /**
     * @return bool
     */
    public function isFile(): bool
    {
        return $this->type == FileType::FILE;
    }

}
