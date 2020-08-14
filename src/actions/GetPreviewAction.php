<?php

namespace floor12\files\actions;

use floor12\files\logic\ImagePreviewer;
use floor12\files\models\File;
use Yii;
use yii\web\NotFoundHttpException;
use yii\web\Response;


class GetPreviewAction extends \yii\base\Action
{
    const HEADER_CACHE_TIME = 60 * 60 * 24 * 15;

    /** @var int */
    protected $width;
    /** @var File */
    protected $model;

    /**
     * @param $hash
     * @param null $width
     * @param null $webp
     * @throws NotFoundHttpException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\RangeNotSatisfiableHttpException
     */
    public function run($hash, $width = null, $webp = null)
    {

        $this->loadAndCheckModel($hash);
        $this->width = $width;

        if ($width) {
            $this->sendPreview($width);
        } else {
            $this->sendAsIs();
        }
    }

    /**
     * @param string $hash
     * @throws NotFoundHttpException
     */
    private function loadAndCheckModel(string $hash): void
    {
        $this->model = File::findOne(['hash' => $hash]);
        if (!$this->model)
            throw new NotFoundHttpException("Запрашиваемый файл не найден");

        if (!$this->model->isImage() && !$this->model->isVideo())
            throw new NotFoundHttpException("Запрашиваемый файл не является изображением");

        if (!file_exists($this->model->rootPath))
            throw new NotFoundHttpException('Запрашиваемый файл не найден на диске.');
    }

    /**
     * @param $width
     * @throws NotFoundHttpException
     * @throws \yii\base\InvalidConfigException
     */
    protected function sendPreview($width)
    {
        $filename = Yii::createObject(ImagePreviewer::class, [$this->model, $width, $webp])->getUrl();

        if (!file_exists($filename))
            throw new NotFoundHttpException('Запрашиваемый файл не найден на диске.');

        $response = Yii::$app->response;
        $response->format = Response::FORMAT_RAW;
        $coontentType = mime_content_type($filename);
        $this->setHeaders($response, $coontentType, md5($this->model->created));
        $stream = fopen($filename, 'rb');
        Yii::$app->response->sendStreamAsFile($stream, $this->model->title, [
            'inline' => true,
            'mimeType' => $this->model->content_type,
            'filesize' => $this->model->size
        ]);
    }

    /**
     * @param string $coontentType
     * @param string $etag
     */
    private function setHeaders($response, string $coontentType, string $etag): void
    {
        $response->headers->set('Last-Modified', date("c", $this->model->created));
        $response->headers->set('Cache-Control', 'public, max-age=' . self::HEADER_CACHE_TIME);
        $response->headers->set('Content-Type', $coontentType . '; charset=utf-8');
        $response->headers->set('ETag', $etag);
    }

    /**
     * @throws \yii\web\RangeNotSatisfiableHttpException
     */
    protected function sendAsIs()
    {
        $stream = fopen($this->model->rootPath, 'rb');
        Yii::$app->response->sendStreamAsFile($stream, $this->model->title, [
            'inline' => true,
            'mimeType' => $this->model->content_type,
            'filesize' => $this->model->size
        ]);
    }
}
