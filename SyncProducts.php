<?php
ini_set('error_reporting', E_ALL);

require_once __DIR__ . '/vendor/autoload.php';

use Engine\Daemon\DaemonPHP;
use Engine\DI\DI;
use Engine\Model\Product\Product;


class SyncProducts extends DaemonPHP {
    const WORK_SLEEP = 30;

    protected $di;
    /**
     * @var \Engine\Model\Product\Product
     */
    protected $product;


    public function __construct (DI $di, string $path = null)
    {
        parent::__construct($di, $path);
        $this->di = $di;
    }

    public function run() {
        $this->product = new Product($this->di);
        $pages = 0;
        $offset = 0;
        while (true) {
            // Параметры выборки
            $pages = $this->product->goodsListsSyncDataBase($offset);

            if ($offset <= $pages) {
                $offset++;
                continue;
            }
            if ($offset >= $pages){
                $offset = 0;
            }

            sleep(self::WORK_SLEEP);
        }
    }
}


$di     = new DI();
$daemon = new SyncProducts($di, '/tmp/SyncOrders.pid');
try {
    $daemon->setChroot('/')//Устанавливаем каталог для chroot
    ->setLog('/SyncProducts.log')
    ->setErr('/SyncProducts.err')//После chroot файлы будут созданы в /home/shaman/work/PHPTest/daemon
    ->handle($argv);
} catch (\Engine\DaemonException\DaemonException $e) {
    echo $e->getMessage();
}