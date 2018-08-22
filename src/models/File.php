<?php
/**
 * Created by PhpStorm.
 * User: floor12
 * Date: 23.06.2016
 * Time: 11:23
 */

namespace floor12\files\models;


use floor12\files\components\SimpleImage;
use Yii;
use yii\base\ErrorException;
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
 * @property string $href
 * @property string $hrefPreview
 * @property string $icon
 * @property string $rootPath
 * @property string $rootPreviewPath
 * @property string|null $watermark
 */
class File extends \yii\db\ActiveRecord
{
    const TYPE_FILE = 0;
    const TYPE_IMAGE = 1;
    const TYPE_VIDEO = 2;

    const VIDEO_STATUS_QUEUE = 0;
    const VIDEO_STATUS_CONVERTING = 1;
    const VIDEO_STATUS_READY = 2;

    const DIRECTORY_SEPARATOR = "/";


    /**
     * {@inheritdoc}
     */
    public static function getDb()
    {
        return Yii::$app->getModule('files')->db;
    }


    /**
     * Меняем или усанавливаем хеш. По нему идет доступ к файлу.
     */
    public function changeHash()
    {
        $this->hash = md5(time() . rand(99999, 99999999));

    }

    /**
     * Если хеша нет, устанавливаем.
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
     * @return string
     */
    public function getIcon()
    {
        $icon = 'file';

        if ($this->content_type == 'application/vnd.openxmlformats-officedocument.wordprocessingml.document')
            $icon = 'file-word';

        if ($this->content_type == 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            $icon = 'file-excel';

        if ($this->content_type == 'application/vnd.openxmlformats-officedocument.presentationml.presentation')
            $icon = 'file-powerpoint';

        if ($this->content_type == 'application/x-zip-compressed')
            $icon = 'file-archive';

        if ($this->content_type == 'application/octet-stream')
            $icon = 'file-archive';

        if (preg_match('/audio/', $this->content_type))
            $icon = 'file-audio';

        if (preg_match('/pdf/', $this->content_type))
            $icon = 'file-pdf';

        if ($this->type == self::TYPE_VIDEO)
            $icon = 'file-video';


        return $icon;
    }


    function mime_content_type($filename)
    {
        $idx = explode('.', $filename);
        $count_explode = count($idx);
        $idx = strtolower($idx[$count_explode - 1]);

        $mimet = array(
            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'swf' => 'application/x-shockwave-flash',
            'flv' => 'video/x-flv',

            // images
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',

            // archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed',

            // audio/video
            'mp3' => 'audio/mpeg',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',

            // adobe
            'pdf' => 'application/pdf',
            'psd' => 'image/vnd.adobe.photoshop',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',

            // ms office
            'doc' => 'application/msword',
            'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',
            'docx' => 'application/msword',
            'xlsx' => 'application/vnd.ms-excel',
            'pptx' => 'application/vnd.ms-powerpoint',


            // open office
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        );

        if (isset($mimet[$idx])) {
            return $mimet[$idx];
        } else {
            return 'application/octet-stream';
        }
    }

    /**
     * @inheritdoc
     */

    public static function tableName()
    {
        return 'file';
    }

    /**
     * @inheritdoc
     */

    public function rules()
    {
        return [
            [['class', 'field', 'filename', 'content_type', 'type'], 'required'],
            [['object_id', 'type', 'video_status', 'ordering'], 'integer'],
            [['class', 'field', 'title', 'filename', 'content_type'], 'string', 'max' => 255],
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
        ];
    }

    /**
     * Updating preview
     */

    public function updatePreview()
    {
//        if ($this->type == self::TYPE_VIDEO) {
//            if (file_exists($this->rootPath)) {
//                @exec(Yii::$app->getModule('files')->ffmpeg . " -i {$this->rootPath} -ss 00:00:15.000 -vframes 1  {$this->rootPreviewPath}");
//            }
//            if (!file_exists($this->rootPreviewPath)) {
//                @exec(Yii::$app->getModule('files')->ffmpeg . " -i {$this->rootPath} -ss 00:00:6.000 -vframes 1  {$this->rootPreviewPath}");
//            }
//            if (!file_exists($this->rootPreviewPath)) {
//                @exec(Yii::$app->getModule('files')->ffmpeg . " -i {$this->rootPath} -ss 00:00:2.000 -vframes 1  {$this->rootPreviewPath}");
//            }
//        }

        if ($this->type == self::TYPE_IMAGE)
            if (file_exists($this->rootPath)) {
                $image = new SimpleImage();
                $image->load($this->rootPath);

                if ($image->getWidth() > 1920 || $image->getHeight() > 1080) {
                    $image->resizeToWidth(1920);
                    if ($this->content_type == 'image/png')
                        $image->save($this->rootPath, IMAGETYPE_PNG);
                    else
                        $image->save($this->rootPath);
                }

                $image->resizeToWidth(350);
                $image->save($this->rootPreviewPath);
            }
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
     * Return root path of preview
     * @return string
     */

    public function getRootPreviewPath()
    {
        return Yii::$app->getModule('files')->storageFullPath . DIRECTORY_SEPARATOR . $this->filename . '.jpg';
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
     * Return web path of preview
     * @return string
     */

    public function getHrefPreview()
    {
        return Url::to(['/files/default/preview', 'hash' => $this->hash]);
    }


    /**
     * Delete files from disk
     */

    public function afterDelete()
    {
        @unlink($this->rootPath);
        // @unlink($this->rootPreviewPath);
        parent::afterDelete();
    }

    /**
     * Method to read files from any mime types
     * @return bool
     */

    public function imageCreateFromAny()
    {
        $type = exif_imagetype($this->rootPath);
        $allowedTypes = array(
            1, // [] gif
            2, // [] jpg
            3, // [] png
            6   // [] bmp
        );
        if (!in_array($type, $allowedTypes)) {
            return false;
        }
        switch ($type) {
            case 1 :
                $im = imageCreateFromGif($this->rootPath);
                break;
            case 2 :
                $im = imageCreateFromJpeg($this->rootPath);
                break;
            case 3 :
                $im = imageCreateFromPng($this->rootPath);
                break;
            case 6 :
                $im = imageCreateFromBmp($this->rootPath);
                break;
        }
        return $im;
    }

    public function setZeroObject()
    {
        $this->object_id = 0;
        $this->save(false);
    }

    public function getWatermark()
    {
        $owner = new $this->class;

        try {
            return $owner->behaviors['files']->attributes[$this->field]['watermark'];
        } catch (ErrorException $exception) {
            return null;
        }
    }


    public function __toString()
    {
        return $this->href;
    }
}
