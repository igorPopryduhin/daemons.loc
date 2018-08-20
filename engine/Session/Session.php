<?php


namespace Engine\Session;


/**
 * Класс для работы с сессиями
 * Class Session
 * @package Engine\Core\Session
 */
class Session
{

    /**
     * Session constructor.
     */
    public function __construct ()
    {
       @session_start();
    }

    /**
     * @param string $key
     * @param $value
     */
    public function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    /**
     * @param $key
     * @param string $default
     * @return null
     */
    public function get($key, $default = '')
    {
        return ($this->has($key) != false) ? $this->has($key): $default;
    }

    /**
     * @param $key
     * @return null
     */
    public function has($key)
    {
        return isset($_SESSION[$key]) ? $_SESSION[$key] : false;
    }
}
