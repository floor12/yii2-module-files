<?php
/**
 * Created by PhpStorm.
 * User: floor12
 * Date: 22.08.2018
 * Time: 17:30
 */

namespace floor12\files\components;


use floor12\files\logic\FileReformat;
use yii\validators\Validator;

class ReformatValidator extends Validator
{

    protected function validateValue($value)
    {
        $format = FileReformat::checkBestFormat($value->tempName);

        if ($format) {
            FileReformat::convert($value->tempName, $format);
            $value->type = mime_content_type($value->tempName);
        }
    }
}