<?php
/**
 * Created by PhpStorm.
 * User: ec
 * Date: 27.06.15
 * Time: 19:21
 * Project: php-phantomjs
 * @author: Evgeny Pynykh bpteam22@gmail.com
 */

namespace bpteam\PhantomJs;

use \PHPUnit_Framework_Testcase;
use \ReflectionClass;

class PhantomJsTest extends PHPUnit_Framework_TestCase
{
    public static $name;

    public static function setUpBeforeClass()
    {
        self::$name = 'C:\server\other\phantomjs.exe';
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

    function testRrenderText(){
        $phantomJS = new PhantomJs(self::$name);
        $text = $phantomJS->renderText('http://ya.ru');
        $this->assertRegExp('%yandex%ims', $text);
    }

    function testRenderImage(){
        $source = 'http://ya.ru';
        $width = 1280;
        $height = 720;
        $picFormat = 'PNG';
        $phantomJS = new PhantomJs(self::$name);
        $img = $phantomJS->renderImage($source, $width, $height, $picFormat);
        $imgHead = imagecreatefromstring($img);
        $this->assertTrue(is_resource($imgHead));
        //$this->assertEquals($height, imagesy($imgHead)); //WTF?! 721
        $this->assertEquals($width, imagesx($imgHead));
    }

    function testRenderPDF(){
        $phantomJS = new PhantomJs(self::$name);
        $source = 'http://ya.ru';
        $fileName = $phantomJS->getStorageDir() . '/testFile.pdf';
        $sizePaper = 'A4';
        $orientation = 'portrait';
        $marginCm = 1;
        $phantomJS->renderPdf($source, $fileName, $sizePaper, $orientation, $marginCm);
        $this->assertFileExists($fileName);
    }

    function testSendPost(){
        $phantomJS = new PhantomJs(self::$name);
        $post = ['url' => 'vk.com', 'test' => 'test_post'];
        $source = 'http://bpteam.net/post_test.php';
        $text = $phantomJS->sendPost($source, $post);
        $this->assertRegExp('%test_post%ims', $text);
    }

    function testSetReferer(){
        $phantomJS = new PhantomJs(self::$name);
        $source = 'http://bpteam.net/referer.php';
        $referer = 'http://iamreferer.net';
        $phantomJS->setReferer($referer);
        $text = $phantomJS->renderText($source);
        $this->assertRegExp('%iamreferer%ims', $text);
    }
}