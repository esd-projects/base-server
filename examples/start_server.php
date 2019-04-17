<?php

use Core\Server\Config\PortConfig;
use Core\Server\Config\ServerConfig;
use Core\Server\DefaultServer;

require __DIR__ . '/../vendor/autoload.php';

$httpPortConfig = new PortConfig();
$httpPortConfig->setHost("0.0.0.0");
$httpPortConfig->setPort(8080);
$httpPortConfig->setSockType(PortConfig::SWOOLE_SOCK_TCP);
$httpPortConfig->setOpenHttpProtocol(true);

$wsPortConfig = new PortConfig();
$wsPortConfig->setHost("0.0.0.0");
$wsPortConfig->setPort(8081);
$wsPortConfig->setSockType(PortConfig::SWOOLE_SOCK_TCP);
$wsPortConfig->setOpenHttpProtocol(true);

$serverConfig = new ServerConfig();
$serverConfig->setWorkerNum(4);
$serverConfig->setLogFile(__DIR__ . "/../swoole.log");
$serverConfig->setPidFile(__DIR__ . "/../pid");

$server = new DefaultServer($serverConfig);

$httpPort = $server->addPort($httpPortConfig);
$wsPort = $server->addPort($wsPortConfig);

$test1Process = $server->addProcess("test1");
$test2Process = $server->addProcess("test2");

$server->configure();
$server->start();