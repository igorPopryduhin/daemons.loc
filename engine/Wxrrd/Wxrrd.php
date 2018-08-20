<?php
/**
 * Created by PhpStorm.
 * User: igorp
 * Date: 19.08.2018
 * Time: 15:35
 */

namespace Engine\Wxrrd;

use Engine\Helper\Curl;
use Engine\Helper\Ini;
use Engine\Session\Session;

class Wxrrd
{

    const REST_API = 'http://apis.wxrrd.com/router/rest';

    private $rest = [];
    private $dirSettings = '';
    private $dirLog = '';
    private $debugToLog = false;

    /**
     * @var Curl
     */
    protected $curl;

    /**
     * @var Session
     */
    protected $session;

    public $appId       = '3c1157b05eea8d0b';
    public $secret      = '584133c1157b05eea8d0bbd58de56445';
    public $accessToken = '';
    public $sign        = '4E50ABD83E3008113BD686B439211CEE';


    /**
     * @var WxrrdAuth
     */
    public $wxrrdAuth;

    /**
     * @var Ini
     */
    public $ini;

    /**
     * Wxrrd constructor.
     */
    public function __construct ()
    {
        $this->dirSettings = dirname(__DIR__, 2) . '/';
        $this->curl = new Curl();
        $this->curl->setOption(CURLOPT_COOKIEJAR,         __DIR__ .'/cookie.txt');
        $this->curl->setOption(CURLOPT_COOKIEFILE,        __DIR__ .'/cookie.txt');

        $this->wxrrdAuth = new WxrrdAuth();
        $this->wxrrdAuth->login = 'thtz2017:IG03o_r111';
        $this->wxrrdAuth->password = 'PR3ya_hin2';

        $this->session   = new Session();
        $this->sign = $this->session->has('wxrrdSign') ? $this->session->get('wxrrdSign') : '4E50ABD83E3008113BD686B439211CEE';

        $this->ini         = new Ini($this->dirSettings . 'settings.ini');
        $this->accessToken = $this->ini->read('wxrrd', 'access_token', '');
        $this->dirLog      = $this->ini->read('wxrrd', 'dirLog', dirname(__DIR__ . '/'));
        $this->debugToLog  = (bool)$this->ini->read('wxrrd', 'debugToLog', false);
    }

    /**
     * @param string $sign
     */
    public function setSign (string $sign): void
    {
        $this->sign = $sign;
    }


    /**
     * @param $method
     * @param string $rest
     * @return mixed
     */
    public function CallAPI($method, $rest='')
    {
        $this->log(sprintf('CallAPI: %s', $this->mkUrl($method,$rest)));
        $error_counter = 0;
        lRecur:
        $result	 =	$this->curl->get($this->mkUrl($method,$rest));
        $r 		 = 	json_decode($result,true);
        $errCode = (int)$r['errCode'];

        if ($errCode != 0)
            $error_counter++;

        if ($error_counter >= 20){
            $this->log('error_counter = max');
            $this->log('exit.');
            $this->log('');
            exit;
        }

        $this->log(sprintf('errCode: %d', $errCode));

        if ($this->debugToLog){
            $this->debugLog($r);
        }

        switch ($errCode) {
            case 0:
                $this->clearField();
                return $r;
            case 30010: // Подпись не передана
                $this->sign = $r['sign'];
                $this->session->set('wxrrdSign', $this->sign);
                goto lRecur;
            case 30011: // Неверная подпись
                $this->log($r['sign']);
                $this->sign = $r['sign'];
                $this->session->set('wxrrdSign', $this->sign);
                goto lRecur;
            case 30006: // Проверьте параметры времени
                break;
            case 30007: // Отсутствует access_token
                $this->setAccessToken($this->wxrrdAuth->getAccessToken());
                break;
            case 30008: // Неверный access_token
                $this->setAccessToken($this->wxrrdAuth->getAccessToken());
                break;
            case 30018:
                sleep(30);
                break;
        }

        return null;
    }


    /**
     * @param $method
     * @param string $rest
     * @return string
     */
    private function mkUrl($method, $rest='')
    {
        $ts = time()+5*3600;
        $timestamp = date("Y-m-d+H:i:s",$ts);
        $timestamp = str_replace(':','%3A',$timestamp);
        return self::REST_API.'?appid='.$this->appId.
            '&secret='.$this->secret.
            '&method='.$method.
            '&access_token='. $this->accessToken.
            '&timestamp='. $timestamp.
            '&sign='.$this->sign.$rest;
    }

    /**
     * @param string $name
     * @param $value
     */
    public function setField(string $name, $value)
    {
        $this->rest[$name] = $value;
    }

    /**
     * Очистить rest
     */
    public function clearField()
    {
        $this->rest = [];
    }


    /**
     * @return string
     */
    public function restBuild()
    {
        $result = '';
        foreach ($this->rest as $name => $value){
            $result .= sprintf('&%s=%s', $name, $value);
        }
        return $result;
    }

    /**
     * @param bool|mixed|string $accessToken
     */
    public function setAccessToken ($accessToken)
    {
        $this->accessToken = $accessToken;
        $this->ini->write('wxrrd', 'access_token', $accessToken);
        $this->ini->write('wxrrd', 'update_at', gmdate("D, d M Y H:i:s"));
        $this->ini->write('wxrrd', 'length', strlen($this->accessToken));
        $this->ini->updateFile();
    }


    /**
     * @param $msg
     */
    private function log($msg)
    {
        file_put_contents($this->dirLog . 'wxrrd.log', $msg . PHP_EOL, FILE_APPEND);
    }

    private function debugLog($data)
    {
        file_put_contents($this->dirLog . 'wxrrdDebug.log', print_r($data, true) . PHP_EOL, FILE_APPEND);
        $hr = '';
        for ($i = 0; $i > 64; $i++){$hr .= '-';}
        file_put_contents($this->dirLog . 'wxrrdDebug.log', $hr . PHP_EOL, FILE_APPEND);
    }

}