<?php
/**
 * Created by PhpStorm.
 * User: floor12
 * Date: 08.07.2018
 * Time: 18:41
 */

namespace floor12\files\logic;

use floor12\files\components\SimpleImage;
use floor12\files\models\File;
use Yii;
use yii\base\ErrorException;

class FileReformat
{
    private $_file;
    private $_compression;

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


    static public function convert(string $filepath, $format = null)
    {
        if (!$format)
            return false;

        // читоаем картинку
        $image = new SimpleImage();
        $image->load($filepath);
        $image->save($filepath, $format);
    }


    public static function checkBestFormat($filepath)
    {
        if (!file_exists($filepath))
            throw new \ErrorException("File not found: {$filepath}");

        Yii::debug("--- best format detecting ---");
        Yii::debug("file: {$filepath}");

        $originalSize = filesize($filepath);
        Yii::debug("Original size: {$originalSize}");

        // читоаем картинку
        $image = new SimpleImage();
        $image->load($filepath);

        // Если это PNG то в цикле пробегаемся по всем пикселям и ищем там альфа канал.
        // если он есть, значит возрвщаем false, что пережимать ничего не надо.
        if ($image->image_type == IMAGETYPE_PNG) {
            $alphaExist = false;
            $height = $image->getHeight();
            $width = $image->getWidth();
            for ($x = 0; $x < $width; $x++) {
                for ($y = 0; $y < $height; $y++) {
                    $rgba = imagecolorat($image->image, $x, $y);
                    $alpha = ($rgba & 0x7F000000) >> 24;
                    if ($alpha > 0) {
                        $alphaExist = true;
                    }
                }
            }
            if ($alphaExist)
                return false;
        }

        // сохраняем в двух форматах и сравниваем размеры
        $tmpName = md5(time() . $filepath);

        $tmpPngName = sys_get_temp_dir() . "/" . $tmpName . ".png";
        $tmpJpegName = sys_get_temp_dir() . "/" . $tmpName . ".jpeg";

        $image->save($tmpPngName, IMAGETYPE_PNG, 10);
        $pngSize = filesize($tmpPngName);
        Yii::debug("PNG size: {$pngSize}");

        $image->save($tmpJpegName, IMAGETYPE_JPEG, 70);
        $jpgSize = filesize($tmpJpegName);
        Yii::debug("JPG size: {$jpgSize}");

        // чистим временные файлы
        unlink($tmpJpegName);
        unlink($tmpPngName);
        if ($jpgSize < $originalSize || $pngSize < $originalSize) {
            if ($jpgSize < $pngSize) {
                Yii::debug('File need to be converted to JPG');
                return IMAGETYPE_JPEG;
            } else {
                Yii::debug('File need to be converted to PNG');
                return IMAGETYPE_PNG;
            }
        } else
            Yii::debug('File already has good compression.');
        return false;
    }

}