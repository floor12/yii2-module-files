<?php
/**
 * Created by PhpStorm.
 * User: floor12
 * Date: 31.12.2017
 * Time: 15:14
 */

namespace floor12\files\components;


use floor12\files\models\File;
use Yii;
use yii\base\Behavior;
use yii\base\ErrorException;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\validators\Validator;

class FileBehaviour extends Behavior
{
    /** Массив для хранения файловых атрибутов и их параметров.
     *  Задается через Behaviors в моделе
     * @var array
     */
    public $attributes = [];

    /** В этот массив помещаются id связанных файлов с текущей моделью для последующейго сохранения.
     * @var array
     */
    private $_values = [];

    /**
     * Вещаем сохранение данных на события.
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'filesSave',
            ActiveRecord::EVENT_AFTER_UPDATE => 'filesSave',
            ActiveRecord::EVENT_AFTER_DELETE => 'filesDelete',
            ActiveRecord::EVENT_BEFORE_VALIDATE => 'validateRequiredFields'
        ];
    }

    protected $cachedFiles = [];


    /**
     * Метод сохранения в базу связей с файлами. Вызывается после сохранения основной модели AR.
     * @throws ErrorException
     * @throws \yii\db\Exception
     */

    public function filesSave()
    {
        $order = 0;
        if ($this->_values) {

            foreach ($this->_values as $field => $ids) {

                Yii::$app->db->createCommand()->update(
                    "{{%file}}",
                    ['object_id' => 0],
                    [
                        'class' => $this->owner->className(),
                        'object_id' => $this->owner->id,
                        'field' => $field,
                    ]
                )->execute();

                if ($ids) foreach ($ids as $id) {
                    if (empty($id))
                        continue;
                    $file = File::findOne($id);
                    if ($file) {
                        $file->object_id = $this->owner->id;
                        $file->ordering = $order++;
                        $file->save();
                        if (!$file->save()) {
                            throw new ErrorException('Невозможно обновить объект File.');
                        }
                    }

                }
            }
        }
    }

    public function filesDelete()
    {
        File::deleteAll([
            'class' => $this->owner->className(),
            'object_id' => $this->owner->id,
        ]);
    }

    public function validateRequiredFields()
    {
        foreach ($this->attributes as $attributeName => $params) {
            $attributeIds = $this->getRealAttributeName($attributeName);

            if (
                isset($params['required']) &&
                $params['required'] &&
                in_array($this->owner->scenario, $params['requiredOn']) &&
                !in_array($this->owner->scenario, $params['requiredExcept']) &&
                !isset($this->_values[$attributeIds][1])
            )
                $this->owner->addError($attributeName, $params['requiredMessage']);
        }
    }

    /**
     * Устанавливаем валидаторы.
     * @param ActiveRecord $owner
     */
    public
    function attach($owner)
    {
        parent::attach($owner);

        // Получаем валидаторы AR
        $validators = $owner->validators;

        // Пробегаемся по валидаторам и вычисляем, какие из них касаются наших файл-полей
        if ($validators)
            foreach ($validators as $key => $validator) {

                // Сначала пробегаемся по файловым валидаторам
                if ($validator::className() == 'yii\validators\FileValidator' || $validator::className() == 'floor12\files\components\ReformatValidator') {
                    foreach ($this->attributes as $field => $params) {

                        if (is_string($params)) {
                            $field = $params;
                            $params = [];
                        }

                        $index = array_search($field, $validator->attributes);
                        if ($index !== false) {
                            $this->attributes[$field]['validator'][$validator::className()] = $validator;
                            unset($validator->attributes[$index]);
                        }
                    }
                }


                if ($validator::className() == 'yii\validators\RequiredValidator') {
                    foreach ($this->attributes as $field => $params) {

                        if (is_string($params)) {
                            $field = $params;
                            $params = [];
                        }

                        $index = array_search($field, $validator->attributes);
                        if ($index !== false) {
                            unset($validator->attributes[$index]);
                            $this->attributes[$field]['required'] = true;
                            $this->attributes[$field]['requiredExcept'] = $validator->except;
                            $this->attributes[$field]['requiredOn'] = sizeof($validator->on) ? $validator->on : [ActiveRecord::SCENARIO_DEFAULT];
                            $this->attributes[$field]['requiredMessage'] = str_replace("{attribute}", $this->owner->getAttributeLabel($field), $validator->message);
                        }
                    }
                }


            }

        // Добавляем дефолтный валидатор для прилетающих айдишников
        if ($this->attributes) foreach ($this->attributes as $fieldName => $fieldParams) {
            $validator = Validator::createValidator('safe', $owner, ["{$fieldName}_ids"]);
            $validators->append($validator);
        }
    }


    /**
     * @inheritdoc
     */
    public function canGetProperty($name, $checkVars = true)
    {
        return array_key_exists($name, $this->attributes) ?
            true : parent::canGetProperty($name, $checkVars);
    }


    /**
     * @inheritdoc
     */
    public function canSetProperty($name, $checkVars = true)
    {
        if (array_key_exists($this->getRealAttributeName($name), $this->attributes))
            return true;

        return parent::canSetProperty($name, $checkVars = true);
    }


    /**
     * @inheritdoc
     */
    public function __get($att_name)
    {
        if (isset($this->_values[$att_name])) {
            unset($this->_values[$att_name][0]);
            if (sizeof($this->_values[$att_name]))
                return array_map(function ($fileId) {
                    return File::findOne($fileId);
                }, $this->_values[$att_name]);
        } else {
            if (!isset($this->cachedFiles[$att_name])) {
                if (
                    isset($this->attributes[$att_name]['validator']) &&
                    isset($this->attributes[$att_name]['validator']['yii\validators\FileValidator']) &&
                    $this->attributes[$att_name]['validator']['yii\validators\FileValidator']->maxFiles > 1
                )
                    $this->cachedFiles[$att_name] = File::find()
                        ->where(
                            [
                                'object_id' => $this->owner->id,
                                'field' => $att_name,
                                'class' => $this->owner->className()
                            ])
                        ->orderBy('ordering ASC')
                        ->all();
                else {
                    $this->cachedFiles[$att_name] = File::find()
                        ->where(
                            [
                                'object_id' => $this->owner->id,
                                'field' => $att_name,
                                'class' => $this->owner->className()
                            ])
                        ->orderBy('ordering ASC')
                        ->one();
                }
            }
            return $this->cachedFiles[$att_name];
        }
    }


    /**
     * @inheritdoc
     */
    public
    function __set($name, $value)
    {
        $attribute = $this->getRealAttributeName($name);

        if (array_key_exists($attribute, $this->attributes))
            $this->_values[$attribute] = $value;
    }


    /** Отбрасываем постфикс _ids
     * @param $attribute string
     * @return string
     */
    private
    function getRealAttributeName($attribute)
    {
        return str_replace("_ids", "", $attribute);
    }
}
