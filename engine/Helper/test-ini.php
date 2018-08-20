<?php
include_once  'Ini.php';


try {
    $ini = new \Engine\Helper\Ini(__DIR__ . '/ini.ini');
    $ini->write('wxrrdAuth', 'access_token', 'd8dgg8w238jqj334jrhh3239823hd2u3h8934yg3u23');
    $ini->write('wxrrdAuth', 'num', 500);

} catch (Exception $e) {
    echo $e->getMessage();
}