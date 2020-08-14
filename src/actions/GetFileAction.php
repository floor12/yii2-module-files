<?php

namespace floor12\files\actions;

use floor12\files\components\SimpleImage;
use floor12\files\models\File;
use floor12\files\models\FileType;
use Yii;
use yii\base\Action;
use yii\web\NotFoundHttpException;

class GetFileAction extends Action
{
    public function run($hash)
    {
        $model = File::findOne(['hash' => $hash]);

        if (!$model)
            throw new NotFoundHttpException("Запрашиваемый файл не найден");

        if (!file_exists($model->rootPath))
            throw new NotFoundHttpException('Запрашиваемый файл не найден на диске.');

        Yii::$app->response->headers->set('Last-Modified', date("c", $model->created));
        Yii::$app->response->headers->set('Cache-Control', 'public, max-age=' . (60 * 60 * 24 * 15));

        if ($model->type == FileType::IMAGE && $model->watermark) {
            $image = new SimpleImage();
            $image->load($model->rootPath);
            $image->watermark(Yii::getAlias("@frontend/web/design/logo-big.png"));
            $tmpName = Yii::getAlias("@runtime/" . md5(time() . $model->id));
            $image->save($tmpName, IMAGETYPE_JPEG);
            $stream = fopen($tmpName, 'rb');
            Yii::$app->response->sendStreamAsFile($stream, $model->title, ['inline' => true, 'mimeType' => "image/jpeg", 'filesize' => $model->size]);
            @unlink($tmpName);

        } else {
            $stream = fopen($model->rootPath, 'rb');
            Yii::$app->response->sendStreamAsFile($stream, $model->title, ['inline' => true, 'mimeType' => $model->content_type, 'filesize' => $model->size]);
        }
    }
}
