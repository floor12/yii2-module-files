<?php
/**
 * Created by PhpStorm.
 * User: floor12
 * Date: 01.01.2018
 * Time: 12:03
 */

namespace floor12\files\controllers;


use floor12\files\components\FileInputWidget;
use floor12\files\components\SimpleImage;
use floor12\files\logic\FileCreateFromInstance;
use floor12\files\logic\FileCropRotate;
use floor12\files\logic\FileRename;
use floor12\files\models\File;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;
use yii\web\Response;

class DefaultController extends Controller
{

    private $actionsToCheck = [
        'crop',
        'rename',
        'upload',
    ];

    public function behaviors()
    {
        return [

            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'zip' => ['GET'],
                    'cropper' => ['GET'],
                    'crop' => ['POST'],
                    'rename' => ['POST'],
                    'upload' => ['POST'],
                    'get' => ['GET'],
                    'preview' => ['GET'],
                ],
            ],
        ];
    }


    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        $this->checkFormToken();

        if ($action->id == 'upload') {
            $this->enableCsrfValidation = false;
        }

        return parent::beforeAction($action);
    }

    public function actionZip(array $hash, $title = 'files')
    {
        $files = File::find()->where(["IN", "hash", $hash])->all();

        $zip = new  \ZipArchive;
        $filename = \Yii::getAlias("@webroot/assets/files}.zip");
        if (sizeof($files) && $zip->open($filename, \ZipArchive::CREATE)) {

            foreach ($files as $file)
                $zip->addFile($file->rootPath, $file->title);


            $zip->close();
            echo 'Archive created!';
            header("Content-disposition: attachment; filename={$title}.zip");
            header('Content-type: application/zip');
            readfile($filename);
        } else {
            echo 'Failed!';
        }


        return sizeof($files);
    }

    /** Првоеряем токен
     * @throws BadRequestHttpException
     */
    private function checkFormToken()
    {
        if (in_array($this->action->id, $this->actionsToCheck) && FileInputWidget::generateToken() != \Yii::$app->request->post('_fileFormToken'))
            throw new BadRequestHttpException('File-form token is wrong or missing.');
    }

    /** Возвращаем HTML шаблон для внедрения в основной макет
     * @return string
     */
    public
    function actionCropper()
    {
        return $this->renderPartial('_cropper');
    }

    /** Кропаем и поворачиваем картинку, возращая ее новый адрес.
     * @return string
     */
    public
    function actionCrop()
    {
        return \Yii::createObject(FileCropRotate::class, [\Yii::$app->request->post()])->execute();
    }

    /** Переименовываем файл
     * @return string
     */
    public
    function actionRename()
    {
        return \Yii::createObject(FileRename::class, [\Yii::$app->request->post()])->execute();
    }


    /** Создаем новый файл
     * @return string
     */
    public
    function actionUpload()
    {
        $model = \Yii::createObject(FileCreateFromInstance::class, [
            UploadedFile::getInstanceByName('file'),
            \Yii::$app->request->post(),
            \Yii::$app->user->identity,
        ])->execute();


        if ($model->errors) {
            throw new BadRequestHttpException('Ошибки валидации модели');
        }

        $ratio = \Yii::$app->request->post('ratio') ?? null;

        $view = \Yii::$app->request->post('mode') == 'single' ? "_single" : "_file";

        if ($ratio)
            $this->getView()->registerJs("initCropper({$model->id}, '{$model->href}', {$ratio}, true);");

        return $this->renderAjax($view, [
            'model' => $model,
            'ratio' => $ratio
        ]);
    }


    /*
     * Выдача файлов через контроллер.
     */

    public function actionGet($hash)
    {
        $model = File::findOne(['hash' => $hash]);

        if (!$model)
            throw new NotFoundHttpException("Запрашиваемый файл не найден");

        if (!file_exists($model->rootPath))
            throw new NotFoundHttpException('Запрашиваемый файл не найден на диске.');

        if ($model->type != File::TYPE_IMAGE) {
            $stream = fopen($model->rootPath, 'rb');
            \Yii::$app->response->sendStreamAsFile($stream, $model->title, ['mimeType' => $model->content_type, 'filesize' => $model->size]);
        } else {

            if ($model->watermark) {
                $image = new SimpleImage();
                $image->load($model->rootPath);
                $image->watermark(\Yii::getAlias("@frontend/web/design/logo-big.png"));
                \Yii::$app->response->sendContentAsFile($image->output(), $model->title);
            } else {
                $stream = fopen($model->rootPath, 'rb');
                \Yii::$app->response->sendStreamAsFile($stream, $model->title, ['mimeType' => $model->content_type, 'filesize' => $model->size]);
            }
        }
    }


    /*
     * Выдача файлов через контроллер.
     */

    public function actionPreview($hash)
    {
        $model = File::findOne(['hash' => $hash]);

        if (!$model)
            throw new NotFoundHttpException("Запрашиваемый файл не найден в базе.");

        $response = \Yii::$app->response;
        $response->format = Response::FORMAT_RAW;
        $response->getHeaders()->set('Content-Type', 'image/jpeg; charset=utf-8');

        if (!file_exists($model->rootPreviewPath))
            throw new NotFoundHttpException('Preview not found.');

        $response->sendFile($model->rootPreviewPath, 'preview.jpg');


    }
}