<?php


namespace floor12\files\logic;

use floor12\files\components\SimpleImage;
use floor12\files\models\File;
use floor12\files\models\FileType;
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

        if ($this->model->type != FileType::IMAGE)
            throw new ErrorException('File is not an image.');
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        if ($this->model->isSvg())
            return $this->model->getRootPath();

        $this->fileName = Yii::$app->getModule('files')->cacheFullPath . $this->model->makeNameWithSize($this->model->filename, $this->width, 0);
        $this->fileNameWebp = Yii::$app->getModule('files')->cacheFullPath . $this->model->makeNameWithSize($this->model->filename, $this->width, 0, true);

        $this->prepareFolders();

        if (!file_exists($this->fileName) || filesize($this->fileName) == 0)
            $this->createPreview();

        if ($this->webp && !file_exists($this->fileNameWebp))
            $this->createPreviewWebp();

        if ($this->webp)
            return $this->fileNameWebp;

        return $this->fileName;
    }

    /**
     * @return void
     */
    protected function prepareFolders()
    {
        if (!file_exists(Yii::$app->getModule('files')->cacheFullPath))
            mkdir(Yii::$app->getModule('files')->cacheFullPath);

        preg_match('/(.+\/\d{2})\/\d{2}\//', $this->fileName, $matches);

        if (!file_exists($matches[1]))
            @mkdir($matches[1]);
        if (!file_exists($matches[0]))
            @mkdir($matches[0]);
        if (!file_exists($matches[0]))
            throw new ErrorException("Unable to create cache folder: {$matches[0]}");
    }

    /**
     * Creat JPG preview
     */
    protected function createPreview()
    {
        $img = new SimpleImage();
        $img->load($this->model->rootPath);

        $imgWidth = $img->getWidth();
        $imgHeight = $img->getHeight();

        if ($this->width && $this->width < $imgWidth) {
            $ratio = $this->width / $imgWidth;
            $img->resizeToWidth($this->width);
        }

        $img->save($this->fileName, $img->image_type);
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