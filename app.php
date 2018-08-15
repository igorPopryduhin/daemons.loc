<?php
/**
 *
 */

//проверяем запущен ли демон
if (isDaemonActive(__DIR__ . '/daemon.pid')) {
    echo (PHP_EOL);
    echo ('--------------------------'.PHP_EOL);
    echo ('	Демон уже активен!'.PHP_EOL);
    echo ('	PID: '.file_get_contents(__DIR__ . '/daemon.pid').PHP_EOL);
    echo ('	Последняя правка: 03.06.2018'.PHP_EOL);
    echo ('--------------------------'.PHP_EOL);
    exit;
}

error_reporting(E_ALL & ~E_NOTICE);
ini_set('display_errors', true);
ini_set('display_startup_errors', true);


//Создание директории для логов если её нет
if (!file_exists(__DIR__ .'/log')) {
    mkdir(__DIR__ .'/log');
}


// ddd
//
///
/// //

// создаем дочерний процесс
$child_pid 		= pcntl_fork();
$stop_daemon 	= false;


if ($child_pid) {
    // выходим из родительского, привязанного к консоли, процесса
    die;
}

// делаем основным процессом дочерний
// После этого он тоже cможет создавать процессы
posix_setsid();

//чтобы повторно не запустить демона, нужна функция для проверки его pid
function isDaemonActive($pid_file) {
    if (is_file($pid_file)) {
        $pid = file_get_contents($pid_file);
        //проверяем на наличие процесса
        if (posix_kill($pid, 0)) {
            //демон уже запущен
            return true;
        } else {
            //pid-файл есть, но процесса нет
            if (!unlink($pid_file)) {
                //не могу уничтожить pid-файл. ошибка
                exit(-1);
            }
        }
    }
    return false;
}

$date_time = gmdate("D, d M Y H:i:s");

// setup signal handlers
declare(ticks = 1);
pcntl_signal(SIGTERM, "sigHandler");
pcntl_signal(SIGHUP,  "sigHandler");
pcntl_signal(SIGUSR1, "sigHandler");








//сама функция обработчика
function sigHandler($signo) {
    global $date_time;
    $date_time = gmdate("D, d M Y H:i:s");
    global $stop_daemon;
    switch ($signo) {
        case SIGTERM: {
            $stop_daemon = true;
            file_put_contents(__DIR__ .'/log/daemon.log', sprintf('%s	Received signal: %d'."\r\n", $date_time, $signo) , FILE_APPEND);
            break;
        }
        default: {
            //все остальные сигналы
            file_put_contents(__DIR__ .'/log/daemon.log', sprintf('%s	Received signal: %d'."\r\n", $date_time, $signo) , FILE_APPEND);
        }
    }
}

//говорим php принимать сигналы
pcntl_signal_dispatch();




//записываем pid процесса демона
file_put_contents(__DIR__ . '/daemon.pid', getmypid());


echo (PHP_EOL);
echo ('--------------------------'.PHP_EOL);
echo ('	Запуск демона выполнен!'.PHP_EOL);
echo ('	PID: '.getmypid().PHP_EOL);
echo ('--------------------------'.PHP_EOL);



$dt_started = gmdate("D, d M Y H:i:s");

//запускаем бесконечный цикл
while (!$stop_daemon) {
    sleep(2000);
    echo "Демон работает";

}
