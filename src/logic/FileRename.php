<?php
/**
 * Created by PhpStorm.
 * User: floor12
 * Date: 04.01.2018
 * Time: 10:39
 */

namespace floor12\files\logic;


use floor12\files\models\File;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;

/**
 * Class FileRename
 * @package floor12\files\logic
 * @property File $_file
 * @property string $_title
 */
class FileRename
{
    private $_file;
    private $_title;

    public function __construct(array $data)
    {

        if (!isset($data['id']))
            throw new BadRequestHttpException('ID of file is not set.');

        if (!isset($data['title']))
            throw new BadRequestHttpException('Title of file is not set.');

        $this->_title = $data['title'];

        $this->_file = File::findOne($data['id']);

        if (!$this->_file)
            throw new NotFoundHttpException('File not found.');

    }

    /**
     * @return string
     * @throws BadRequestHttpException
     */
    public function execute()
    {
        $this->_file->title = $this->_title;

        if (!$this->_file->save())
            throw new BadRequestHttpException('Unable to save file.');

        return $this->_title;
    }
}