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
use floor12\files\models\FileType;
use yii\base\ErrorException;

class FileResize
{
    private $_file;
    private $_maxWidth;
    private $_maxHeight;
    private $_compression;
    private $_imageType = IMAGETYPE_JPEG;

    /**
     * FileResize constructor.
     * @param File $file Модель файла
     * @param int $maxWidth максимальная ширина
     * @param int $maxHeight максимальная высота
     * @param int $compression качество jpeg
     * @throws ErrorException
     */
    public function __construct(File $file, int $maxWidth, int $maxHeight, $compression = 60)
    {
        $this->_file = $file;

        if ($this->_file->type != FileType::IMAGE)
            throw new ErrorException('This file is not an image.');

        if (!file_exists($this->_file->rootPath))
            throw new ErrorException('File not found on disk');

        $this->_maxHeight = $maxHeight;
        $this->_maxWidth = $maxWidth;
        $this->_compression = $compression;

    }


    /** Непосредственная обработка
     * @return bool
     * @throws ErrorException
     */
    public function execute(): bool
    {
        if ($this->_file->content_type == 'image/svg+xml')
            return true;

        $image = new SimpleImage();
        $image->load($this->_file->rootPath);

        if ($image->getWidth() > $this->_maxWidth || $image->getHeight() > $this->_maxHeight) {
            $image->resizeToWidth($this->_maxWidth);
            if ($this->_file->content_type == 'image/png')
                $this->_imageType = IMAGETYPE_PNG;
            $image->save($this->_file->rootPath, $this->_imageType, $this->_compression);
            $this->_file->size = filesize($this->_file->rootPath);
            return $this->_file->save(false, ['size']);
        }
        return true;
    }

}
