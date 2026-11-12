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
            $this->createPreview($sourceImagePath, $this->model->getWatermark());

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
        self::ensureDir(Yii::$app->getModule('files')->cacheFullPath);
        $lastFolder = '/';
        $explodes = explode('/', $this->fileName);
        array_pop($explodes);
        if (empty($explodes))
            return;
        foreach ($explodes as $folder) {
            if (empty($folder))
                continue;
            $lastFolder = $lastFolder . $folder . '/';
            self::ensureDir($lastFolder);
        }
    }

    /**
     * Создаёт папку, если её нет. Параллельные запросы превью могут создать её одновременно,
     * поэтому «уже существует» после неудачного mkdir — не ошибка.
     */
    public static function ensureDir(string $path): void
    {
        if (!is_dir($path) && !@mkdir($path) && !is_dir($path)) {
            throw new \RuntimeException("Unable to create directory {$path}");
        }
    }

    /**
     * Creat JPG preview
     * @param $sourceImagePath
     * @param string|array|null $watermark `watermark` option of the FileBehaviour attribute
     * @throws ErrorException
     */
    protected function createPreview($sourceImagePath, $watermark = null)
    {
        $img = new SimpleImage();
        $img->load($sourceImagePath);

        // the legacy stamp is sized for the original, the positioned one is scaled to the preview
        if ($watermark && !is_array($watermark))
            $img->applyWatermark($watermark);

        $imgWidth = $img->getWidth();
        $imgHeight = $img->getHeight();

        if ($this->width && $this->width < $imgWidth) {
            $ratio = $this->width / $imgWidth;
            $img->resizeToWidth($this->width);
        }

        if (is_array($watermark))
            $img->applyWatermark($watermark);

        $saveType = $img->image_type;
        if ($saveType == IMG_WEBP || $saveType == IMG_QUADRATIC) {
            $saveType = IMG_JPEG;
        }

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
