<?php

use GoSwoole\BaseServer\ExampleClass\Server\DefaultServer;

require __DIR__ . '/../vendor/autoload.php';

define("ROOT_DIR", __DIR__ . "/..");
define("RES_DIR", __DIR__ . "/resources");

$server = new DefaultServer();
//配置
$server->configure();
//启动
$server->start();
