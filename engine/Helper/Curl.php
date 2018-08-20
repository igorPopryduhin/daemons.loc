<?php
/**
 * Created by PhpStorm.
 * User: igorp
 * Date: 16.08.2018
 * Time: 12:33
 */

namespace Engine\Helper;


class Curl
{
    protected $curl;
    protected $header = [];

    /**
     * Request constructor.
     * @param array $options
     */
    public function __construct ($options = [])
    {
        $this->curl = curl_init();
        // Default
        $this->setOption(CURLOPT_RETURNTRANSFER,    true);
        $this->setOption(CURLOPT_FOLLOWLOCATION,    1);
        $this->setOption(CURLOPT_HEADER,            false);
        $this->setOption(CURLOPT_TIMEOUT,           5);
        $this->setOption(CURLOPT_USERAGENT,         'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/65.0.3325.181 Safari/537.36');

        $this->setHeader('Accept', 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8');
        $this->setHeader('Connection', 'keep-alive');

        // Инициализация параметров
        foreach ($options as $option => $value) {
            $this->setOption($option, $value);
        }
    }

    /**
     * Установка параметров CURL
     * @param $option
     * @param $value
     */
    public function setOption ($option, $value)
    {
        curl_setopt($this->curl, $option, $value);
    }

    /**
     *
     */
    public function __destruct ()
    {
        // TODO: Implement __destruct() method.
        curl_close($this->curl);
    }

    /**
     * GET
     * @param $url
     * @param null $referer
     * @return mixed
     */
    public function get($url, $referer = '')
    {
        $this->setOption(CURLOPT_URL, $url);
        ($referer != '') ? $this->setOption(CURLOPT_REFERER, $referer) : null;

        if (!empty($this->header)){
            $this->setOption(CURLOPT_HTTPHEADER, $this->header);
        }

        return curl_exec($this->curl);
    }

    /**
     * POST
     * @param $url
     * @param $postData
     * @param null $referer
     * @return mixed
     */
    public function post($url, $postData = [], $referer = null)
    {
        if (!empty($postData)) {
            $this->setOption(CURLOPT_POST, 1);
            $this->setOption(CURLOPT_POSTFIELDS, $postData);
        } else {
            $this->setOption( CURLOPT_POST, 0);
        }
        ($referer != null) ? $this->setOption(CURLOPT_REFERER, $referer) : $this->setOption(CURLOPT_REFERER, 1);

        $this->setOption(CURLOPT_URL, $url);
        return curl_exec($this->curl);
    }

    /**
     * Добавить заголовок в запрос
     * <hr>
     * @param $name
     * @param $value
     */
    public function setHeader ($name, $value)
    {
        $this->header[] = sprintf('%s: %s', $name, $value);
    }

    /**
     * @return mixed
     */
    public function curlGetInfo()
    {
        return curl_getinfo($this->curl);
    }

}