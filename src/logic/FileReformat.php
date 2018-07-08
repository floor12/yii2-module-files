<?php
/**
 * Created by PhpStorm.
 * User: floor12
 * Date: 08.07.2018
 * Time: 18:41
 */

namespace floor12\files\logic;

use floor12\files\models\File;
use floor12\files\components\SimpleImage;
use yii\base\ErrorException;
use \Yii;

class FileReformat
{
    private $_file;
    private $_maxWidth;
    private $_maxHeight;
    private $_compression;
    private $_imageType = IMAGETYPE_JPEG;

    /**
     * FileReformat constructor.
     * @param File $file Модель файла
     * @param int $compression качество jpeg
     */
    public function __construct(File $file, $compression = 60)
    {
        $this->_file = $file;

        if ($this->_file->type != File::TYPE_IMAGE)
            throw new ErrorException('This file is not an image.');

        if (!file_exists($this->_file->rootPath))
            throw new ErrorException("File not found on disk: {$this->_file->id} {$this->_file->rootPath}");

        $this->_compression = $compression;
    }


    public function execute()
    {
        $image = new SimpleImage();
        $image->load($this->_file->rootPath);

        $tmpName = md5(time() . $this->_file->rootPath);
        $tmpPngName = Yii::$app->getModule('files')->storageFullPath . "/" . $tmpName . ".png";
        $tmpJpegName = Yii::$app->getModule('files')->storageFullPath . "/" . $tmpName . ".jpeg";

        $image->save($tmpPngName, IMAGETYPE_PNG);
        $pngSize = filesize($tmpPngName);
        $image->save($tmpJpegName, IMAGETYPE_JPEG, $this->_compression);
        $jpgSize = filesize($tmpJpegName);


        @unlink($this->_file->rootPath);
        @unlink($this->_file->rootPreviewPath);
        list($filename) = explode('.', $this->_file->filename);
        $this->_file->filename = $filename;
        $filepath = $this->_file->rootPath;

        if ($pngSize < $jpgSize) {
            $this->_file->size = $pngSize;
            $text = "PNG is better";
            $convertTo = IMAGETYPE_PNG;
            $this->_file->filename .= ".png";
            $this->_file->content_type = "image/png";
            $percent = $pngSize / $jpgSize * 100;
        } else {
            $this->_file->size = $jpgSize;
            $text = "JPEG is better";
            $convertTo = IMAGETYPE_JPEG;
            $this->_file->filename .= ".jpeg";
            $this->_file->content_type = "image/jpeg";
            $percent = $jpgSize / $pngSize * 100;
        }

        echo "{$text} {$percent}\n";
        unlink($tmpJpegName);
        unlink($tmpPngName);

        $image->save($this->_file->rootPath, $convertTo, $this->_compression);
        echo $this->_file->filename . "\n";
        $this->_file->save(false);
        $this->_file->updatePreview();

        return true;
    }

}