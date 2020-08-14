<?php


namespace floor12\files\logic;

use floor12\files\components\SimpleImage;
use floor12\files\models\File;
use Yii;
use yii\base\ErrorException;

class ImagePreviewer
{

    protected $model;
    protected $width;
    protected $webp;
    protected $fileName;
    protected $fileNameWebp;

    /**
     * ImagePreviewer constructor.
     * @param File $model
     * @param int $width
     * @param bool $webp
     * @throws ErrorException
     */
    public function __construct(File $model, int $width, $webp = false)
    {
        $this->model = $model;
        $this->width = $width;
        $this->webp = $webp;

        if (!$this->model->isImage() && !$this->model->isVideo())
            throw new ErrorException('File is not an image or video.');
    }

    /**
     * @return string
     * @throws \ErrorException
     * @throws \yii\base\InvalidConfigException
     */
    public function getUrl()
    {
        if ($this->model->isSvg())
            return $this->model->getRootPath();

        $cachePath = Yii::$app->getModule('files')->cacheFullPath;
        $jpegName = $this->model->makeNameWithSize($this->model->filename, $this->width);
        $webpName = $this->model->makeNameWithSize($this->model->filename, $this->width, true);

        $this->fileName = $cachePath . DIRECTORY_SEPARATOR . $jpegName;
        $this->fileNameWebp = $cachePath . DIRECTORY_SEPARATOR . $webpName;

        $this->prepareFolder();

        $sourceImagePath = $this->model->rootPath;
        if ($this->model->isVideo()) {
            $sourceImagePath = $this->fileName . '.jpeg';
            if (!is_file($sourceImagePath))
                Yii::createObject(VideoFrameExtractor::class, [
                    $this->model->rootPath,
                    $sourceImagePath
                ])->extract();
        }

        if (!is_file($this->fileName) || filesize($this->fileName) == 0)
            $this->createPreview($sourceImagePath);

        if ($this->webp && !file_exists($this->fileNameWebp))
            $this->createPreviewWebp();

        if ($this->webp)
            return $this->fileNameWebp;

        return $this->fileName;
    }

    /**
     * Generate all folders for storing image thumbnails cache.
     */
    protected function prepareFolder()
    {
        if (!file_exists(Yii::$app->getModule('files')->cacheFullPath))
            mkdir(Yii::$app->getModule('files')->cacheFullPath);
        $lastFolder = '/';
        $explodes = explode('/', $this->fileName);
        array_pop($explodes);
        if (empty($explodes))
            return;
        foreach ($explodes as $folder) {
            if (empty($folder))
                continue;
            $lastFolder = $lastFolder . $folder . '/';
            if (!file_exists($lastFolder))
                mkdir($lastFolder);
        }
    }

    /**
     * Creat JPG preview
     * @param $sourceImagePath
     * @throws ErrorException
     */
    protected function createPreview($sourceImagePath)
    {
        $img = new SimpleImage();
        $img->load($sourceImagePath);

        $imgWidth = $img->getWidth();
        $imgHeight = $img->getHeight();

        if ($this->width && $this->width < $imgWidth) {
            $ratio = $this->width / $imgWidth;
            $img->resizeToWidth($this->width);
        }

        $saveType = $img->image_type;
        if ($saveType == IMG_WEBP || $saveType == IMG_QUADRATIC)
            $saveType = IMG_JPEG;
        $img->save($this->fileName, $saveType);
    }

    /**
     *  Create webp from default preview
     */
    protected function createPreviewWebp()
    {
        $img = new SimpleImage();
        $img->load($this->fileName);
        $img->save($this->fileNameWebp, IMAGETYPE_WEBP, 70);
    }
}
