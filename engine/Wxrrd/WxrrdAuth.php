<?php
/**
 * Created by PhpStorm.
 * User: igorp
 * Date: 17.08.2018
 * Time: 6:41
 */

namespace Engine\Wxrrd;





use Engine\Helper\Curl;

class WxrrdAuth
{

    protected $appId       = '3c1157b05eea8d0b';
    protected $secret      = '584133c1157b05eea8d0bbd58de56445';
    protected $accessToken = '';
    protected $sign        = '4E50ABD83E3008113BD686B439211CEE';

    public $login = '';
    public $password = '';
    public $redirectUrl = 'http://178.218.213.220';

    /**
     * @var Curl
     */
    protected $curl;

    /**
     * WxrrdAuth constructor.
     */
    public function __construct ()
    {
        $this->curl = new Curl();
        $this->curl->setOption(CURLOPT_RETURNTRANSFER,  true);
        $this->curl->setOption(CURLOPT_FOLLOWLOCATION,  1);
        $this->curl->setOption(CURLOPT_HEADER,          false);
        $this->curl->setOption(CURLOPT_TIMEOUT,         30);
        $this->curl->setOption(CURLOPT_AUTOREFERER,     true);
        $this->curl->setOption(CURLOPT_COOKIEJAR,       __DIR__ .'/cookie.txt');
        $this->curl->setOption(CURLOPT_COOKIEFILE,      __DIR__ .'/cookie.txt');
        $this->curl->setOption(CURLOPT_USERAGENT,       'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/65.0.3325.181 Safari/537.36');
    }

    /**
     * @return mixed
     */
    private function getCode()
    {
        $postData = array(  'username' => $this->login,
                            'password' => $this->password);
        $Request_code = sprintf('http://apis.wxrrd.com/authorize?appid=%s&secret=%s&response_type=code&redirect_uri=%s',
            $this->appId,$this->secret, urlencode($this->redirectUrl));
        $Referer = sprintf('http://apis.wxrrd.com/authorize/code?appid=%s&secret=%s&response_type=code&redirect_uri=%s',
            $this->appId,$this->secret, urlencode($this->redirectUrl));

        $response = $this->curl->post($Request_code, $postData, '');
        $jsonObj = json_decode($response, true);
        if (isset($jsonObj)){
            if ($jsonObj['status'] == true){
                $this->curl->post($Referer);
                $header = $this->curl->curlGetInfo();
                $url    = $header['url'];
                $params = parse_url($url);

                if (isset($params['query'])){
                    parse_str($params['query'], $output);
                    if (isset($output['code'])){
                        return $output['code'];
                    }
                }
            }
        }
    }

    /**
     * @param string $code
     * @return mixed
     */
    private function getToken(string $code)
    {
        $Request_token = sprintf('http://apis.wxrrd.com/token?appid=%s&secret=%s&grant_type=%s&code=%s&redirect_uri=%s',
            $this->appId, $this->secret, 'authorization_code', $code, urlencode($this->redirectUrl));
        return $this->curl->get($Request_token);
    }

    /**
     * Получить токен
     * @return bool|mixed
     */
    public function getAccessToken()
    {
        $code  = $this->getCode();
        $jsonObj = json_decode($this->getToken($code), 1);
        return !empty($jsonObj['access_token']) ? $jsonObj['access_token']: false;
    }




}