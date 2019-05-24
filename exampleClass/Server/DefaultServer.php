<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2019-04-17
 * Time: 17:41
 */

namespace ESD\ExampleClass\Server;


use ESD\BaseServer\Plugins\Logger\Logger;
use ESD\BaseServer\Server\Config\ServerConfig;
use ESD\BaseServer\Server\Process;
use ESD\BaseServer\Server\Server;

class DefaultServer extends Server
{

    /**
     * 这里context获取不到任何插件，因为插件还没有加载
     * DefaultServer constructor.
     * @param ServerConfig $serverConfig
     * @param string $portClass
     * @param string $processClass
     * @throws \ESD\BaseServer\Exception
     * @throws \ReflectionException
     * @throws \DI\DependencyException
     */
    public function __construct(?ServerConfig $serverConfig = null, string $portClass = DefaultServerPort::class, string $processClass = DefaultProcess::class)
    {
        if ($serverConfig == null) {
            $serverConfig = new ServerConfig();
        }
        parent::__construct($serverConfig, $portClass, $processClass);
    }

    /**
     * 所有的配置插件已初始化好
     * @return mixed
     */
    public function configureReady()
    {
        $this->log = getDeepContextValueByClassName(Logger::class);
    }

    public function onStart()
    {
        $this->log->log(Logger::INFO, "start");
    }

    public function onShutdown()
    {
        $this->log->info("shutdown");
    }

    public function onWorkerError(Process $process, int $exit_code, int $signal)
    {
        $this->log->info("{$process->getProcessName()}");
    }

    public function onManagerStart()
    {
        $this->log->info("managerStart");
    }

    public function onManagerStop()
    {
        $this->log->info("managerStop");
    }
}