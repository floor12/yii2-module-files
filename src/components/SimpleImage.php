<?php
/**
 * Created by PhpStorm.
 * User: floor12
 * Date: 03.04.2016
 * Time: 21:21
 */

namespace floor12\files\components;


use yii\base\ErrorException;

class SimpleImage
{

    var $image;
    var $image_type;

    function load($filename)
    {
        $image_info = getimagesize($filename);
        $this->image_type = $image_info[2];
        if ($this->image_type == IMAGETYPE_JPEG) {
            $this->image = imagecreatefromjpeg($filename);
        } elseif ($this->image_type == IMAGETYPE_GIF) {
            $this->image = imagecreatefromgif($filename);
            imageSaveAlpha($this->image,true);
        } elseif ($this->image_type == IMAGETYPE_PNG) {
            $this->image = imagecreatefrompng($filename);
            imageSaveAlpha($this->image,true);
        } elseif ($this->image_type == IMAGETYPE_WEBP) {
            $this->image = imagecreatefromwebp($filename);
        }
    }

    function save($filename, $image_type = IMAGETYPE_JPEG, $compression = 75, $permissions = null)
    {
        if ($image_type == IMAGETYPE_JPEG) {
            imagejpeg($this->image, $filename, $compression);
        } elseif ($image_type == IMAGETYPE_GIF) {
            imagegif($this->image, $filename);
        } elseif ($image_type == IMAGETYPE_PNG) {
            imagepng($this->image, $filename);
        } elseif ($image_type == IMAGETYPE_WEBP) {
            $dst = imagecreatetruecolor(imagesx($this->image), imagesy($this->image));
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
            $transparent = imagecolorallocatealpha($dst, 255, 255, 255, 127);
            imagefilledrectangle($dst, 0, 0, imagesx($this->image), imagesy($this->image), $transparent);
            imagecopy($dst, $this->image, 0, 0, 0, 0, imagesx($this->image), imagesy($this->image));
            imagewebp($dst, $filename);
        }

        if ($permissions != null) {
            chmod($filename, $permissions);
        }
    }

    function output($image_type = IMAGETYPE_JPEG)
    {
        if ($image_type == IMAGETYPE_JPEG) {
            imagejpeg($this->image);
        } elseif ($image_type == IMAGETYPE_GIF) {
            imagegif($this->image);
        } elseif ($image_type == IMAGETYPE_PNG) {
            imagepng($this->image);
        }
    }

    function resizeToHeight($height)
    {
        $ratio = $height / $this->getHeight();
        $width = $this->getWidth() * $ratio;
        $this->resize($width, $height);
    }

    function getHeight()
    {
        try {
            return imagesy($this->image);
        } catch (\Throwable $exception) {
            throw new ErrorException('Unable to get height of image. Probably the image is corrupted.');
        }

    }

    function getWidth()
    {
        try {
            return imagesx($this->image);
        } catch (\Throwable $exception) {
            throw new ErrorException('Unable to get width of image. Probably the image is corrupted.');
        }
    }

    function resize($width, $height)
    {
        $new_image = imagecreatetruecolor($width, $height);
        imagealphablending($new_image, false);
        imagesavealpha($new_image, true);
        $transparent = imagecolorallocatealpha($new_image, 255, 255, 255, 127);
        imagefilledrectangle($new_image, 0, 0, $width, $height, $transparent);
        imagecopyresampled($new_image, $this->image, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());
        $this->image = $new_image;
    }

    function resizeToWidth($width)
    {
        $ratio = $width / $this->getWidth();
        $height = $this->getheight() * $ratio;
        $this->resize($width, $height);
    }

    function scale($scale)
    {
        $width = $this->getWidth() * $scale / 100;
        $height = $this->getheight() * $scale / 100;
        $this->resize($width, $height);
    }

    function rotate($direction)
    {
        $degrees = 90;
        if ($direction == 2)
            $degrees = 270;
        $this->image = imagerotate($this->image, $degrees, 0);
    }

    function rotateDegrees($degrees)
    {
        $this->image = imagerotate($this->image, $degrees, 0);
    }


    public function watermark($path)
    {
        $stamp = imagecreatefrompng($path);

        $stamp_new_width = $this->getWidth() / 5;
        $stamp_new_height = imagesy($stamp) * $stamp_new_width / imagesx($stamp);

        $new_stamp = imagecreatetruecolor($stamp_new_width, $stamp_new_height);

        imagealphablending($new_stamp, false);
        imagesavealpha($new_stamp, true);

        $transparent = imagecolorallocatealpha($new_stamp, 255, 255, 255, 127);
        imagecolortransparent($new_stamp, $transparent);

        imagefilledrectangle($new_stamp, 0, 0, $stamp_new_width, $stamp_new_height, $transparent);

        imagecopyresampled($new_stamp, $stamp, 0, 0, 0, 0, $stamp_new_width, $stamp_new_height, imagesx($stamp), imagesy($stamp));

        $marge_right = $this->getWidth() / 10;
        $marge_bottom = $this->getHeight() / 10;

        imagecopyresampled($this->image, $new_stamp, imagesx($this->image) - $stamp_new_width - $marge_right, imagesy($this->image) - $stamp_new_height - $marge_bottom, 0, 0, $stamp_new_width, $stamp_new_height, $stamp_new_width, $stamp_new_height);

    }
}
