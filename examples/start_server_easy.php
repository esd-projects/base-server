<?php

use ESD\ExampleClass\Server\DefaultServer;

require __DIR__ . '/../vendor/autoload.php';

define("ROOT_DIR", __DIR__ . "/..");
define("RES_DIR", __DIR__ . "/resources");
\ESD\Coroutine\Co::enableCo();
$server = new DefaultServer();
//配置
$server->configure();
//启动
$server->start();
