<?php

namespace floor12\files\tests\components;

use floor12\files\components\SimpleImage;
use floor12\files\tests\TestCase;

class SimpleImageTest extends TestCase
{
    private $stamp = 'tests/storage/stamp.png';

    public function setUp(): void
    {
        parent::setUp();
        // 200x100 red stamp
        $stamp = imagecreatetruecolor(200, 100);
        imagefill($stamp, 0, 0, imagecolorallocate($stamp, 255, 0, 0));
        imagepng($stamp, $this->stamp);
    }

    public function tearDown(): void
    {
        @unlink($this->stamp);
        parent::tearDown();
    }

    private function image(int $width, int $height): SimpleImage
    {
        $image = new SimpleImage();
        $image->image = imagecreatetruecolor($width, $height);
        imagefill($image->image, 0, 0, imagecolorallocate($image->image, 0, 0, 255));
        return $image;
    }

    private function isStamp(SimpleImage $image, int $x, int $y): bool
    {
        return (imagecolorat($image->image, $x, $y) >> 16 & 255) > 200;
    }

    public function testPlacedBottomRightIsScaledToImage()
    {
        $image = $this->image(1000, 600);
        $image->applyWatermark(['path' => $this->stamp]);

        // 25 % of 1000 = 250x125, 30px from the right and bottom edges
        $this->assertTrue($this->isStamp($image, 1000 - 30 - 5, 600 - 30 - 5));
        $this->assertTrue($this->isStamp($image, 1000 - 30 - 245, 600 - 30 - 120));
        $this->assertFalse($this->isStamp($image, 1000 - 30 - 255, 600 - 30 - 5));
        $this->assertFalse($this->isStamp($image, 1000 - 10, 600 - 10));
        $this->assertFalse($this->isStamp($image, 10, 10));
    }

    public function testPlacedCenter()
    {
        $image = $this->image(400, 400);
        $image->applyWatermark(['path' => $this->stamp, 'position' => SimpleImage::WATERMARK_CENTER, 'scale' => 0.5]);

        // 200x100 in the middle: 100..300 x 150..250
        $this->assertTrue($this->isStamp($image, 200, 200));
        $this->assertTrue($this->isStamp($image, 105, 155));
        $this->assertFalse($this->isStamp($image, 95, 200));
        $this->assertFalse($this->isStamp($image, 200, 145));
    }

    public function testPlacedNeverTallerThanImage()
    {
        $image = $this->image(1000, 100);
        $image->applyWatermark(['path' => $this->stamp, 'position' => SimpleImage::WATERMARK_TOP_LEFT, 'scale' => 1, 'margin' => 0]);

        // 1000x500 would not fit, shrunk to 200x100
        $this->assertTrue($this->isStamp($image, 195, 95));
        $this->assertFalse($this->isStamp($image, 205, 50));
    }

    public function testLegacyLeavesImageOutsideStampIntact()
    {
        $image = $this->image(800, 600);
        $image->applyWatermark($this->stamp);

        $this->assertTrue($this->isStamp($image, 10, 10));
        $this->assertSame(255, imagecolorat($image->image, 400, 300) & 255);
    }
}
