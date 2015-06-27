<?php
/**
 * Created by PhpStorm.
 * Project: php-phantomjs
 * @author: Evgeny Pynykh bpteam22@gmail.com
 */

namespace bpteam\PhantomJs;

use bpteam\DryText\DryPath;
use bpteam\PhpShell\PhpShell;
use bpteam\Cookie\PhantomJsCookie;
use bpteam\UserAgentSwitcher\UserAgentSwitcher;

class PhantomJs
{

    /**
     * @var string
     */
    protected $url;
    /**
     * @var array
     */
    protected $info;
    /**
     * @var string
     */
    protected $answer;
    /**
     * @var PhpShell
     */
    protected $executor;
    /**
     * @var string
     */
    protected $path;
    /**
     * @var string
     */
    protected $scriptDir = 'script';
    /**
     * @var string
     */
    protected $localStorageDir = 'storage';
    /**
     * @var string
     */
    protected $keyStream;

    const COOKIES_FILE = '--cookies-file';
    const IGNORE_SSL_ERRORS = '--ignore-ssl-errors';
    const LOAD_IMAGES = '--load-images';
    const LOCAL_STORAGE_PATH = '--local-storage-path';
    const OUTPUT_ENCODING = '--output-encoding';
    const PROXY = '--proxy';
    const PROXY_TYPE = '--proxy-type';
    const PROXY_AUTH = '--proxy-auth';
    const LOCAL_TO_REMOTE_URL_ACCESS = '--local-to-remote-url-access';
    protected $options;
    protected $defaultOptions;
    protected $scriptName;
    protected $referer = '';
    /**
     * @var PhantomJsCookie
     */
    private $cookie;
    /**
     * @var UserAgentSwitcher
     */
    public $userAgent;
    /**
     * @var bool
     */
    private $useProxy;
    /**
     * @var string|cProxy
     */
    public $proxy;

    public function setExecutor($executor)
    {
        $this->executor->setExecutor($executor);
        $this->executor = $executor;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return mixed
     */
    public function getAnswer()
    {
        return $this->answer;
    }

    /**
     * @return string
     */
    public function getScriptDir()
    {
        return $this->path . '/' . $this->scriptDir;
    }

    public function setScriptDir($dir)
    {
        $this->scriptDir = $dir;
    }

    /**
     * @return string
     */
    public function getStorageDir()
    {
        return $this->path . '/' . $this->localStorageDir;
    }

    /**
     * @param string $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * @return string
     */
    public function getPhantomFilesPath()
    {
        return $this->path;
    }

    /**
     * @param string $keyStream
     */
    public function setKeyStream($keyStream = null)
    {
        $this->keyStream = $keyStream?:uniqid('phantomjs');
        $this->cookie->deleteFile();
        $this->cookie->open($keyStream);
        $this->setDefaultOption(self::COOKIES_FILE, $this->cookie->getFileName());
    }

    /**
     * @return string
     */
    public function getKeyStream()
    {
        return $this->keyStream;
    }

    /**
     * @param array $options
     */
    public function setOptions($options)
    {
        $this->options = $options;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param string      $option
     * @param bool|string $value
     */
    public function setOption($option, $value)
    {
        $this->options[$option] = $value;
    }

    /**
     * @param string $option
     * @return bool|string
     */
    public function getOption($option)
    {
        return isset($this->options[$option]) ? $this->options[$option] : null;
    }

    /**
     * @param array $defaultOptions
     */
    public function setDefaultOptions($defaultOptions)
    {
        $this->defaultOptions = $defaultOptions;
    }

    /**
     * @return array
     */
    public function getDefaultOptions()
    {
        return $this->defaultOptions;
    }

    /**
     * @param $option
     * @param $value
     */
    public function setDefaultOption($option, $value)
    {
        $this->defaultOptions[$option] = $value;
    }

    /**
     * @param $option
     * @return array
     */
    public function getDefaultOption($option)
    {
        return $this->defaultOptions[$option];
    }

    /**
     * @param string $name
     */
    public function setScriptName($name)
    {
        $this->scriptName = $name;
    }

    /**
     * @return string
     */
    public function getScriptName()
    {
        return $this->getScriptDir() . '/' . $this->scriptName . '.js';
    }

    /**
     * @param string|bool|int $useProxy 1/0 true/false '123.123.123.123:8080'
     */
    public function setUseProxy($useProxy)
    {
        $this->useProxy = $this->setProxy($useProxy);

    }

    /**
     * @return mixed
     */
    public function getUseProxy()
    {
        return $this->useProxy;
    }

    /**
     * @param bool|int|string $proxy true/false 1/0 '123.123.123.123:8080'
     * @param null|string     $type  http|socks5
     * @param null|string     $user
     * @param null|string     $password
     * @return bool
     */
    protected function setProxy($proxy, $type = null, $user = null, $password = null)
    {
        switch ((bool)$proxy) {
            case true:
                if (is_string($proxy) && $type) {
                    if (DryPath::isIp($proxy)) {
                        $this->proxy['proxy'] = $proxy;
                        $this->proxy['type'] = $type;
                        $this->proxy['auth'] = $user === null || $password === null ? null : $user . ':' . $password;
                    } else {
                        $proxy = false;
                    }
                } elseif (!is_object($this->proxy)) {
                    $this->proxy = new cProxy();
                }
                break;
            default:
                $proxy = false;
        }
        return (bool)$proxy;
    }

    /**
     * @param string $referer
     */
    public function setReferer($referer)
    {
        $this->referer = $referer;
    }

    /**
     * @return string
     */
    public function getReferer()
    {
        return $this->referer;
    }

    function __construct($executor = false)
    {
        $this->setDefaultOptions(
            [
                self::COOKIES_FILE => null, // /path/to/cookies.txt
                self::IGNORE_SSL_ERRORS => 'true',
                self::LOAD_IMAGES => 'false',
                self::LOCAL_STORAGE_PATH => $this->getStorageDir(), // /some/path
                self::OUTPUT_ENCODING => 'utf-8',
                self::PROXY => null, // 192.168.1.42:8080
                self::PROXY_TYPE => null, // http|socks5|none
                self::PROXY_AUTH => null, // username:password
                self::LOCAL_TO_REMOTE_URL_ACCESS => 'true',
            ]
        );
        $this->userAgent = new UserAgentSwitcher('desktop');
        $this->executor = new PhpShell();
        $this->cookie = new PhantomJsCookie();
        if ($executor) {
            $this->setExecutor($executor);
        }
        $this->setPath(__DIR__);
        $this->setKeyStream();
    }

    public function renderText($path, $screenWidthPx = 1280, $screenHeightPx = 720)
    {
        $this->url = $path;
        $answer = $this->customScript(
            'renderText',
            [
                $this->userAgent->getRandomRecord(),
                $this->getReferer(),
                $path,
                $screenWidthPx,
                $screenHeightPx
            ]
        );
        $this->info['header'] = $this->cutHeader($answer);

        return $answer;
    }

    public function sendPost($path, $postStr, $screenWidthPx = 1280, $screenHeightPx = 720)
    {
        $this->url = $path;
        return $this->customScript(
            'sendPost',
            [
                $this->userAgent->getRandomRecord(),
                $this->getReferer(),
                $path,
                $postStr,
                $screenWidthPx,
                $screenHeightPx
            ]
        );
    }

    public function renderImage($path, $screenWidthPx = 1280, $screenHeightPx = 720, $formatImg = 'PNG')
    {
        $data = $this->customScript(
            'renderImage',
            [
                $this->userAgent->getRandomRecord(),
                $this->getReferer(),
                $path,
                $screenWidthPx,
                $screenHeightPx,
                $formatImg
            ]
        );
        $pic = base64_decode($data);

        return $pic;
    }

    public function renderPdf($path, $fileName = 'MyPdf.pdf', $format = 'A4', $orientation = 'portrait', $marginCm = 1)
    {
        return $this->customScript(
            'renderPdf',
            [
                $this->userAgent->getRandomRecord(),
                $this->getReferer(),
                $path,
                $fileName,
                $format,
                $orientation,
                $marginCm . 'cm'
            ]
        );
    }

    public function customScript($scriptName, $arguments = [])
    {
        $this->setScriptName($scriptName);
        $shellArguments = $this->generateOptions();
        $shellArguments[] = $this->getScriptName();
        $shellArguments = array_merge($shellArguments, $arguments);
        $this->executor->setArguments($shellArguments);
        return $this->executor->exec(true); //if infinity run, Issue https://github.com/ariya/phantomjs/issues/10845
    }

    protected function generateOptions()
    {
        $options = [];
        $this->setOptionProxy();
        foreach ($this->getDefaultOptions() as $name => $defaultOption) {
            $option = $this->getOption($name) ? $this->getOption($name) : $defaultOption;
            if ($option) {
                $options[$name] = $option;
            }
        }

        return $options;
    }

    protected function setOptionProxy()
    {
        if ($this->getUseProxy()) {
            if (is_object($this->proxy)) {
                $proxy = $this->proxy->getProxy($this->getKeyStream(), $this->getUrl());
                if (is_string($proxy['proxy']) && DryPath::isIp($proxy['proxy'])) {
                    $this->setOption(self::PROXY, $proxy['proxy']);
                    $this->setOption(self::PROXY_TYPE, $proxy['protocol']);
                }
            } elseif (is_string($this->proxy['proxy'])) {
                $this->setOption(self::PROXY, $this->proxy['proxy']);
                $this->setOption(self::PROXY_TYPE, $this->proxy['type']);
                $this->setOption(self::PROXY_AUTH, $this->proxy['auth']);
            }
        } elseif ($this->getOption('proxy') !== null) {
            $this->setOption(self::PROXY, null);
            $this->setOption(self::PROXY_TYPE, null);
            $this->setOption(self::PROXY_AUTH, null);
        }
    }

    public function load($url)
    {
        $this->setOption(self::LOAD_IMAGES, 'false');

        return $this->renderText($url);
    }

    public function getInfo()
    {
        return $this->info;
    }

    protected function cutHeader(&$answer)
    {
        $header = array();
        if ($answer) {
            while (preg_match('%^<HEADER>\[\[(?<head>.*?)\]\]</HEADER>%Ums', $answer, $data)) {
                $header = explode("\n\n", $data['head']);
                $answer = ltrim(preg_replace('%<HEADER>\[\[' . preg_quote($data['head'], '%') . '\]\]</HEADER>%ims',
                    '', $answer));
            }
        }

        return $header;
    }

    /**
     * @param       $path
     * @param array $arguments
     * @return string
     */
    public function getErrors($path, $arguments = [])
    {
        $answer = $this->customScript('getErrors', ['path' => $path,] + $arguments);

        return $answer;
    }

}