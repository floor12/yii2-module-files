<?php
/**
 * Created by PhpStorm.
 * User: floor12
 * Date: 01.01.2018
 * Time: 13:28
 */

namespace floor12\files\logic;

/**
 * Class ClassnameEncoder
 * @package floor12\files\logic
 */
class ClassnameEncoder
{
    /**
     * @var string
     */
    private $encoded = "";

    /**
     * ClassnameEncoder constructor.
     * @param $className
     */
    public function __construct($className)
    {
        $this->encoded = str_replace("\\", "\\\\", $className);
    }

    /**
     * @return mixed|string
     */
    public function __toString()
    {
        return $this->encoded;
    }
}