<?php
/**
 * Created by PhpStorm.
 * User: ec
 * Date: 27.06.15
 * Time: 19:21
 * Project: php-phantomjs
 * @author: Evgeny Pynykh bpteam22@gmail.com
 */

namespace bpteam\PhantomJS;

use \PHPUnit_Framework_Testcase;
use \ReflectionClass;

class PhantomJSTest extends PHPUnit_Framework_TestCase
{
    public static $name;

    public static function setUpBeforeClass()
    {
        self::$name = 'unit_test';
    }

    /**
     * @param        $name
     * @param string $className
     * @return \ReflectionMethod
     */
    protected static function getMethod($name, $className = 'bpteam\BigList\JsonList')
    {
        $class = new ReflectionClass($className);
        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method;
    }

    /**
     * @param        $name
     * @param string $className
     * @return \ReflectionProperty
     */
    protected static function getProperty($name, $className = 'bpteam\BigList\JsonList')
    {
        $class = new ReflectionClass($className);
        $property = $class->getProperty($name);
        $property->setAccessible(true);

        return $property;
    }
}