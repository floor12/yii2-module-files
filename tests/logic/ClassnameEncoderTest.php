<?php
/**
 * Created by PhpStorm.
 * User: floor12
 * Date: 07.01.2018
 * Time: 12:45
 */

namespace floor12\files\tests\logic;


use floor12\files\logic\ClassnameEncoder;
use floor12\files\tests\TestCase;

class ClassnameEncoderTest extends TestCase
{
    /**
     * Проверяем работу основной функции
     */
    public function testEncodeFullClassName()
    {
        $testname = 'test\class\Name';
        $encoded = (string)new ClassnameEncoder($testname);
        $this->assertEquals('test\\\\class\\\\Name', $encoded);
    }

    /**
     * Проверяем обработку без слешей
     */
    public function testEncodeClassName()
    {
        $testname = 'testname';
        $encoded = (string)new ClassnameEncoder($testname);
        $this->assertEquals($encoded, $testname);
    }

    /**
     * Смотрим чтобы не было исключений и ошибок
     */
    public function testEncodeEmptyClassName()
    {
        $testname = '';
        $encoded = (string)new ClassnameEncoder($testname);
        $this->assertEquals($encoded, $testname);
    }


}
