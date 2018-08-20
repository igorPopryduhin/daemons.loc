<?php

namespace Engine\DI;


/**
 * Dependency injection
 * Class DI
 * @package Engine\DI
 */
class DI
{
    /**
     * @var array
     */
    private $container = [];


    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function set($key, $value)
    {
        $this->container[$key] = $value;

        return $this;
    }


    /**
     * @param $key
     * @return bool
     */
    public function get($key)
    {
        return $this->has($key);
    }


    /**
     * @param $key
     * @return bool
     */
    public function has($key)
    {
        return isset($this->container[$key]) ? $this->container[$key] : null;
    }

    /**
     * @param $key
     * @param array $element
     */
    public function push($key, $element = [])
    {
        if ($this->has($key) !== null){
            $this->set($key, []);
        }

        if (!empty($element)){
            $this->container[$key][$element[$key]] = $element['value'];
        }

    }

}
