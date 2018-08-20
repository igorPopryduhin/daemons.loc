<?php
ini_set('error_reporting', E_ALL);

require_once __DIR__ . '/vendor/autoload.php';

use Engine\Daemon\DaemonPHP;
use Engine\DI\DI;
use Engine\Model\Orders\Orders;


class SyncOrders extends DaemonPHP {
    const WORK_SLEEP = 15;

    protected $di;
    /**
     * @var \Engine\Model\Orders\Orders
     */
    protected $orders;

    public function __construct (DI $di, string $path = null)
    {
        parent::__construct($di, $path);
        $this->di = $di;
    }

    public function run() {
        $this->orders = new Orders($this->di);
        while (true) {
            // Параметры выборки
            $this->orders->wxrrd->setField('type',  'all');
            $this->orders->wxrrd->setField('limit',  '600');
            $this->orders->wxrrd->setField('created_at_start',  '2018-01-01');
            $this->orders->wxrrd->setField('created_at_end',  '2018-08-01');

            $this->orders->tradeLists();
            sleep(self::WORK_SLEEP);
        }
    }
}


$di     = new DI();
$daemon = new SyncOrders($di, '/tmp/SyncOrders.pid');
try {
    $daemon->setChroot('/')//Устанавливаем каталог для chroot
    ->setLog('/SyncOrders.log')->setErr('/SyncOrders.err')//После chroot файлы будут созданы в /home/shaman/work/PHPTest/daemon
    ->handle($argv);
} catch (\Engine\DaemonException\DaemonException $e) {
    echo $e->getMessage();
}