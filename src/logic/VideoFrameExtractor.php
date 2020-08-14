<?php


namespace floor12\files\logic;

use ErrorException;
use Yii;

class VideoFrameExtractor
{
    /** @var string */
    private $ffmpegBin;
    /** @var string */
    private $videoSourceFilename;
    /** @var string */
    private $outputImageFilename;

    public function __construct(string $videoSourceFilename, string $outputImageFilename)
    {
        $this->ffmpegBin = Yii::$app->getModule('files')->ffmpeg;

        if (!file_exists($this->ffmpegBin))
            throw new ErrorException("ffmpeg is not found: {$this->ffmpegBin}");

        if (!is_executable($this->ffmpegBin))
            throw new ErrorException("ffmpeg is not executable: {$this->ffmpegBin}");

        if (!is_file($videoSourceFilename))
            throw new ErrorException('File not found on disk.');

        $this->videoSourceFilename = $videoSourceFilename;
        $this->outputImageFilename = $outputImageFilename;
    }

    /**
     * @throws ErrorException
     */
    public function extract()
    {
        $command = "{$this->ffmpegBin} -i {$this->videoSourceFilename}  -ss 00:00:03.000 -vframes 1  {$this->outputImageFilename}";
        exec($command, $out, $result);
        if ($result !== 0)
            throw new ErrorException('FFmpeg frame extracting fail:' . print_r($out, true));
    }

}
