<?php

namespace Engine;


use Engine\DI\DI;
use Engine\Session\Session;
use Engine\Wxrrd\Wxrrd;
use SafeMySQL;

abstract class Model
{
    /**
     * @var DI
     */
    protected $di;
    /**
     * @var \SafeMySQL
     */
    protected $db;
    /**
     * @var Wxrrd
     */
    protected $wxrrd;
    protected $session;

    /**
     * Model constructor.
     * @param $di
     */
    public function __construct(DI $di)
    {
        $opts = array(
            'user'    => 'rit',
            'pass'    => 'T7z4W9t2',
            'db'      => 'cms_wxrrd',
            'charset' => 'utf8'
        );

        $this->db = new SafeMySQL($opts);
        $this->wxrrd = new Wxrrd();
        $this->session = new Session();

    }
}