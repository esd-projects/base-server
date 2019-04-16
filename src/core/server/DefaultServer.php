<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/16
 * Time: 10:22
 */

namespace core\server;


use core\server\config\ServerConfig;

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