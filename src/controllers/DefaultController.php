<?php
/**
 * Created by PhpStorm.
 * User: floor12
 * Date: 01.01.2018
 * Time: 12:03
 */

namespace floor12\files\controllers;

use floor12\files\actions\GetFileAction;
use floor12\files\actions\GetPreviewAction;
use floor12\files\components\FileInputWidget;
use floor12\files\logic\FileCreateFromInstance;
use floor12\files\logic\FileCropRotate;
use floor12\files\logic\FileRename;
use floor12\files\models\File;
use Yii;
use yii\base\InvalidConfigException;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UploadedFile;
use ZipArchive;

class DefaultController extends Controller
{
    /**
     * @var array
     */
    private $actionsToCheck = [
        'crop',
        'rename',
        'upload',
    ];

    /**
     * @inheritDoc
     * @return array
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'zip' => ['GET', 'HEAD'],
                    'cropper' => ['GET', 'HEAD'],
                    'crop' => ['POST', 'HEAD'],
                    'rename' => ['POST', 'HEAD'],
                    'upload' => ['POST', 'HEAD'],
                    'get' => ['GET', 'HEAD'],
                    'preview' => ['GET', 'HEAD'],
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
        return parent::beforeAction($action);
    }

    /** Првоеряем токен
     * @throws BadRequestHttpException
     */
    private function checkFormToken()
    {
        if (in_array($this->action->id, $this->actionsToCheck) && FileInputWidget::generateToken() != Yii::$app->request->post('_fileFormToken'))
            throw new BadRequestHttpException('File-form token is wrong or missing.');
    }

    /**
     * @param array $hash
     * @param string $title
     */
    public function actionZip(array $hash, $title = 'files')
    {
        $files = File::find()->where(["IN", "hash", $hash])->all();

        $zip = new  ZipArchive;
        $filename = Yii::getAlias("@webroot/assets/files.zip");
        if (file_exists($filename))
            @unlink($filename);
        if (sizeof($files) && $zip->open($filename, ZipArchive::CREATE)) {

            foreach ($files as $file)
                $zip->addFile($file->rootPath, $file->title);

            $zip->close();
            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Cache-Control: public");
            header("Content-Description: File Transfer");
            header("Content-type: application/octet-stream");
            header("Content-Disposition: attachment; filename={$title}.zip");
            header("Content-Transfer-Encoding: binary");
            header("Content-Length: " . filesize($filename));

            while (ob_get_level()) {
                ob_end_clean();
            }
            readfile($filename);
        } else {
            echo 'Failed!';
        }
    }

    /** Возвращаем HTML шаблон для внедрения в основной макет
     * @return string
     */
    public function actionCropper()
    {
        return $this->renderPartial('_cropper');
    }

    /** Кропаем и поворачиваем картинку, возращая ее новый адрес.
     * @return string
     * @throws InvalidConfigException
     * @throws \yii\base\ErrorException
     */
    public function actionCrop()
    {
        return Yii::createObject(FileCropRotate::class, [Yii::$app->request->post()])->execute();
    }

    /** Переименовываем файл
     * @return string
     * @throws BadRequestHttpException
     * @throws InvalidConfigException
     */
    public function actionRename()
    {
        return Yii::createObject(FileRename::class, [Yii::$app->request->post()])->execute();
    }


    /** Создаем новый файл
     * @return string
     * @throws BadRequestHttpException
     * @throws InvalidConfigException
     */
    public function actionUpload()
    {
        $model = Yii::createObject(FileCreateFromInstance::class, [
            UploadedFile::getInstanceByName('file'),
            Yii::$app->request->post(),
            Yii::$app->user->identity,
        ])->execute();


        if ($model->errors) {
            throw new BadRequestHttpException('Ошибки валидации модели');
        }

        $ratio = Yii::$app->request->post('ratio') ?? null;

        $view = Yii::$app->request->post('mode') == 'single' ? "_single" : "_file";

        if ($ratio)
            $this->getView()->registerJs("initCropper({$model->id}, '{$model->href}', {$ratio}, true);");

        return $this->renderAjax($view, [
            'model' => $model,
            'ratio' => $ratio
        ]);
    }

    /**
     * @return array|string[]
     */
    public function actions()
    {
        return [
            'get' => GetFileAction::class,
            'image' => GetPreviewAction::class
        ];
    }

    /*
     * Выдача файлов через контроллер.
     */
    public function actionPreview($hash)
    {
        $model = File::findOne(['hash' => $hash]);

        if (!$model)
            throw new NotFoundHttpException("Запрашиваемый файл не найден в базе.");

        $response = Yii::$app->response;
        $response->format = Response::FORMAT_RAW;
        $response->getHeaders()->set('Content-Type', 'image/jpeg; charset=utf-8');

        Yii::$app->response->headers->set('Last-Modified', date("c", $model->created));
        Yii::$app->response->headers->set('Cache-Control', 'public, max-age=' . (60 * 60 * 24 * 15));

        if (!file_exists($model->getRootPreviewPath()))
            throw new NotFoundHttpException('Preview not found.');

        $response->sendFile($model->getRootPreviewPath(), 'preview.jpg');

    }
}
