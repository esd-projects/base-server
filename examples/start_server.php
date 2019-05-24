<?php

use ESD\BaseServer\Server\Config\PortConfig;
use ESD\BaseServer\Server\Config\ServerConfig;
use ESD\ExampleClass\Server\DefaultServer;

require __DIR__ . '/../vendor/autoload.php';

class MyPort extends \ESD\ExampleClass\Server\DefaultServerPort
{

}

class MyProcess extends \ESD\ExampleClass\Server\DefaultProcess
{

}

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

$tcpPortConfig = new PortConfig();
$tcpPortConfig->setHost("0.0.0.0");
$tcpPortConfig->setPort(8082);
$tcpPortConfig->setOpenLengthCheck(true);
$tcpPortConfig->setPackageLengthType("N");
$tcpPortConfig->setPackageLengthOffset(0);
$tcpPortConfig->setPackageBodyOffset(0);
$tcpPortConfig->setSockType(PortConfig::SWOOLE_SOCK_TCP);

//---服务器配置---
$serverConfig = new ServerConfig();
$serverConfig->setWorkerNum(4);
$serverConfig->setRootDir(__DIR__ . "/../");

$server = new DefaultServer($serverConfig);
//添加端口
$server->addPort("http", $httpPortConfig, MyPort::class);//使用自定义实例
$server->addPort("ws", $wsPortConfig);//使用默认实例
$server->addPort("tcp", $tcpPortConfig);//使用默认实例
//添加进程
$server->addProcess("test1");
$server->addProcess("test2", MyProcess::class);//使用自定义实例
//配置
$server->configure();
//configure后可以获取实例
$test1Process = $server->getProcessManager()->getProcessFromName("test1");
$test2Process = $server->getProcessManager()->getProcessFromName("test2");
$httpPort = $server->getPortManager()->getPortFromName("http");
$wsPort = $server->getPortManager()->getPortFromName("ws");
//启动
$server->start();
