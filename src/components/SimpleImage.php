<?php
/**
 * Created by PhpStorm.
 * User: floor12
 * Date: 03.04.2016
 * Time: 21:21
 */

namespace floor12\files\components;


use Yii;
use yii\base\ErrorException;

class SimpleImage
{
    const WATERMARK_CENTER = 'center';
    const WATERMARK_TOP_LEFT = 'top-left';
    const WATERMARK_TOP_RIGHT = 'top-right';
    const WATERMARK_BOTTOM_LEFT = 'bottom-left';
    const WATERMARK_BOTTOM_RIGHT = 'bottom-right';

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
            imageSaveAlpha($this->image, true);
        } elseif ($this->image_type == IMAGETYPE_PNG) {
            $this->image = @imagecreatefrompng($filename); // https://stackoverflow.com/questions/22745076/libpng-warning-iccp-known-incorrect-srgb-profile
            imageSaveAlpha($this->image, true);
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
        ob_start();
        if ($image_type == IMAGETYPE_JPEG) {
            imagejpeg($this->image);
        } elseif ($image_type == IMAGETYPE_GIF) {
            imagegif($this->image);
        } elseif ($image_type == IMAGETYPE_PNG) {
            imagepng($this->image);
        } elseif ($image_type == IMAGETYPE_WEBP) {
            $dst = imagecreatetruecolor(imagesx($this->image), imagesy($this->image));
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
            $transparent = imagecolorallocatealpha($dst, 255, 255, 255, 127);
            imagefilledrectangle($dst, 0, 0, imagesx($this->image), imagesy($this->image), $transparent);
            imagecopy($dst, $this->image, 0, 0, 0, 0, imagesx($this->image), imagesy($this->image));
            imagewebp($dst);
        }
        return ob_get_clean();
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
        $this->resize((int)$width, (int)$height);
    }

    function scale($scale)
    {
        $width = $this->getWidth() * $scale / 100;
        $height = $this->getheight() * $scale / 100;
        $this->resize((int)$width, (int)$height);
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


    /**
     * Legacy watermark: PNG is laid over the top left corner pixel to pixel, without scaling.
     * @param string $path
     */
    public function watermark($path)
    {
        $stamp = imagecreatefrompng($path);
        // only the part of the stamp that exists: reading outside of it gives opaque black
        $width = min($this->getWidth(), imagesx($stamp));
        $height = min($this->getHeight(), imagesy($stamp));

        $transparentStamp = imagecreatetruecolor($this->getWidth(), $this->getHeight());
        imagealphablending($transparentStamp, false);
        imagesavealpha($transparentStamp, true);
        $transparent = imagecolorallocatealpha($transparentStamp, 255, 255, 255, 127);
        imagecolortransparent($transparentStamp, $transparent);
        imagefilledrectangle($transparentStamp, 0, 0, $this->getWidth(), $this->getHeight(), $transparent);
        imagecopyresampled($transparentStamp, $stamp, 0, 0, 0, 0, $width, $height, $width, $height);

        $newImage = imagecreatetruecolor($this->getWidth(), $this->getHeight());
        imagecopyresampled($newImage, $this->image, 0, 0, 0, 0, $this->getWidth(), $this->getHeight(), $this->getWidth(), $this->getHeight());
        imagecopyresampled($newImage, $transparentStamp, 0, 0, 0, 0, $this->getWidth(), $this->getHeight(), $this->getWidth(), $this->getHeight());
        $this->image = $newImage;
    }

    /**
     * Watermark scaled to the image and placed by position.
     * @param string $path PNG with alpha channel
     * @param string $position one of WATERMARK_* positions
     * @param float $scale stamp width as a share of the image width
     * @param float $margin distance from the edges as a share of the image width
     */
    public function placeWatermark($path, $position = self::WATERMARK_BOTTOM_RIGHT, $scale = 0.25, $margin = 0.03)
    {
        $stamp = imagecreatefrompng($path);
        $imageWidth = $this->getWidth();
        $imageHeight = $this->getHeight();

        $width = max(1, (int)round($imageWidth * $scale));
        $height = max(1, (int)round(imagesy($stamp) * $width / imagesx($stamp)));
        if ($height > $imageHeight) {
            $width = max(1, (int)round($width * $imageHeight / $height));
            $height = $imageHeight;
        }
        $gap = (int)round($imageWidth * $margin);

        $x = (int)(($imageWidth - $width) / 2);
        if (strpos($position, 'left') !== false)
            $x = $gap;
        elseif (strpos($position, 'right') !== false)
            $x = $imageWidth - $width - $gap;

        $y = (int)(($imageHeight - $height) / 2);
        if (strpos($position, 'top') !== false)
            $y = $gap;
        elseif (strpos($position, 'bottom') !== false)
            $y = $imageHeight - $height - $gap;

        if (!imageistruecolor($this->image))
            imagepalettetotruecolor($this->image);
        imagealphablending($this->image, true);
        imagecopyresampled($this->image, $stamp, $x, $y, 0, 0, $width, $height, imagesx($stamp), imagesy($stamp));
    }

    /**
     * Applies the `watermark` option of a FileBehaviour attribute: a path to PNG (legacy)
     * or ['path' => ..., 'position' => ..., 'scale' => ..., 'margin' => ...].
     * @param string|array $config
     */
    public function applyWatermark($config)
    {
        if (is_array($config))
            $this->placeWatermark(
                Yii::getAlias($config['path']),
                $config['position'] ?? self::WATERMARK_BOTTOM_RIGHT,
                $config['scale'] ?? 0.25,
                $config['margin'] ?? 0.03
            );
        else
            $this->watermark(Yii::getAlias($config));
    }
}
