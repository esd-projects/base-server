# base_server
基础服务

封装swoole，模块更科学更易用
```
use core\server\config\PortConfig;
use core\server\config\ServerConfig;
use core\server\DefaultServer;

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

$server->addPort($httpPortConfig);
$server->addPort($wsPortConfig);

$server->addProcess("test1");
$server->addProcess("test2");

$server->configure();
$server->start();
```
