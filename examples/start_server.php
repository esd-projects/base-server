<?php

use Core\Server\Config\PortConfig;
use Core\Server\Config\ServerConfig;
use Core\Server\DefaultProcess;
use Core\Server\DefaultServer;
use Core\Server\DefaultServerPort;
use Core\Utils\Utils;

require __DIR__ . '/../vendor/autoload.php';

class MyPort extends DefaultServerPort
{

}

class MyProcess extends DefaultProcess
{

}

Utils::enableRuntimeCoroutine();

//----多端口配置----
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

//---服务器配置---
$serverConfig = new ServerConfig();
$serverConfig->setWorkerNum(4);
$serverConfig->setLogFile(__DIR__ . "/../swoole.log");
$serverConfig->setPidFile(__DIR__ . "/../pid");

$server = new DefaultServer($serverConfig);

try {
    //添加端口
    $httpPort = $server->addPort($httpPortConfig, MyPort::class);//使用自定义实例
    $wsPort = $server->addPort($wsPortConfig);//使用默认实例
    //添加进程
    $test1Process = $server->addProcess("test1");
    $test2Process = $server->addProcess("test2", MyProcess::class);//使用自定义实例
    //配置
    $server->configure();
    //启动
    $server->start();
} catch (Exception $e) {
    var_dump($e->getTrace());
}

