<?php
/**
 * Created by PhpStorm.
 * User: igorp
 * Date: 19.08.2018
 * Time: 21:33
 */

namespace Engine\Helper;



class Ini
{


    private $modified = false;
    private $fileIni = '';
    private $iniData = [];


    /**
     * Ini constructor.
     * @param string $fileIni
     */
    public function __construct (string $fileIni)
    {
        $this->fileIni = $fileIni;

        if (is_file($fileIni)){
            $this->iniData = parse_ini_file($fileIni, true);
        }
    }

    /**
     * @param $section
     */
    public function removeSection($section)
    {
        foreach ($this->iniData as $sect => $pair){
            if ($section == $sect){
                unset($this->iniData[$section]);
                $this->modified = true;
                break;
            }
        }
    }

    public function removeKey($section, $key)
    {
        foreach ($this->iniData as $sect => $pair){
            if ($section == $sect) {
                foreach ($pair as $name => $value) {
                    if ($key == $name){
                        unset($this->iniData[$section][$name]);
                        $this->modified = true;
                        break;
                    }
                }
            }
        }
    }


    /**
     * @param $section
     * @param $key
     * @param string $default
     * @return bool|mixed
     */
    public function read($section, $key, $default = '')
    {
        return isset($this->iniData[$section][$key]) ? $this->iniData[$section][$key]: $default;
    }

    /**
     * @param $section
     * @param $key
     * @param $value
     */
    public function write($section, $key, $value)
    {
        $this->iniData[$section][$key] = $value;
        $this->modified = true;
    }

    public function updateFile()
    {
        if ($this->modified){
            $iniStr = '';
            foreach ($this->iniData as $sect => $pair){
                $iniStr .= '[' . $sect . ']' . PHP_EOL;
                foreach ($pair as $key => $value){
                    if (is_string($value)){
                        $iniStr .= $key . ' = "' . $value . '"' . PHP_EOL;
                    } elseif (is_int($value)){
                        $iniStr .= $key . ' = ' . $value . PHP_EOL;
                    } else {
                        $iniStr .= $key . ' = ' . $value . PHP_EOL;
                    }
                }
            }

            file_put_contents($this->fileIni, $iniStr, LOCK_EX);
        }
    }

    public function __destruct ()
    {
        $this->updateFile();
    }
}