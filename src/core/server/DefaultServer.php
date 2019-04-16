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
        // TODO: Implement onStart() method.
    }

    public function onShutdown()
    {
        // TODO: Implement onShutdown() method.
    }

    public function onWorkerError(Process $process, int $exit_code, int $signal)
    {
        // TODO: Implement onWorkerError() method.
    }

    public function onManagerStart()
    {
        // TODO: Implement onManagerStart() method.
    }

    public function onManagerStop()
    {
        // TODO: Implement onManagerStop() method.
    }
}