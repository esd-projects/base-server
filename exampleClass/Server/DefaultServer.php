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

class DefaultServer extends Server
{
    public function __construct(ServerConfig $serverConfig, string $portClass = DefaultServerPort::class, string $processClass = DefaultProcess::class)
    {
        parent::__construct($serverConfig, $portClass, $processClass);
    }

    public function onStart()
    {
        print_r("[DefaultServer]\t[onStart]\n");
    }

    public function onShutdown()
    {
        print_r("[DefaultServer]\t[onShutdown]\n");
    }

    public function onWorkerError(Process $process, int $exit_code, int $signal)
    {
        print_r("[DefaultServer]\t[onWorkerError:{$process->getProcessId()}]\t[{$process->getProcessName()}]\n");
    }

    public function onManagerStart()
    {
        print_r("[DefaultServer]\t[onManagerStart]\n");
    }

    public function onManagerStop()
    {
        print_r("[DefaultServer]\t[onManagerStop]\n");
    }
}