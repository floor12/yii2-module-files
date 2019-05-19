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

        if ($this->model->type != File::TYPE_IMAGE)
            throw new ErrorException('File is not an image.');
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        if ($this->model->isSvg())
            return $this->model->getRootPath();

        $this->fileName = Yii::$app->getModule('files')->storageFullPath . $this->model->makeNameWithSize($this->model->filename, $this->width, 0);
        $this->fileNameWebp = Yii::$app->getModule('files')->storageFullPath . $this->model->makeNameWithSize($this->model->filename, $this->width, 0, true);

        if (!file_exists($this->fileName))
            $this->createPreview();

        if ($this->webp && !file_exists($this->fileNameWebp))
            $this->createPreviewWebp();

        if ($this->webp)
            return $this->fileNameWebp;

        return $this->fileName;
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
        $img->save($this->fileNameWebp,IMAGETYPE_WEBP,70);
    }
}