<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2019-04-17
 * Time: 17:41
 */

namespace GoSwoole\BaseServer\ExampleClass\Server;


use GoSwoole\BaseServer\Server\Config\ServerConfig;
use GoSwoole\BaseServer\Server\Process;
use GoSwoole\BaseServer\Server\Server;
use Monolog\Logger;

class DefaultServer extends Server
{
    /**
     * @var Logger
     */
    private $log;

    public function __construct(ServerConfig $serverConfig, string $portClass = DefaultServerPort::class, string $processClass = DefaultProcess::class)
    {
        parent::__construct($serverConfig, $portClass, $processClass);
        //这里获取不到log，因为插件还没有加载
    }

    /**
     * 所有的配置插件已初始化好
     * @return mixed
     */
    public function configureReady()
    {
        $this->log = $this->getContext()->getByClassName(Logger::class);
    }

    public function onStart()
    {
        $this->log->log(Logger::INFO, "start");
    }

    public function onShutdown()
    {
        $this->log->log(Logger::INFO, "shutdown");
    }

    public function onWorkerError(Process $process, int $exit_code, int $signal)
    {
        $this->log->log(Logger::INFO, "{$process->getProcessName()}");
    }

    public function onManagerStart()
    {
        $this->log->log(Logger::INFO, "managerStart");
    }

    public function onManagerStop()
    {
        $this->log->log(Logger::INFO, "managerStop");
    }

}