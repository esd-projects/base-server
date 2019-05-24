<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/18
 * Time: 18:12
 */

namespace ESD\Core\Server\Process;


use ESD\Core\Message\Message;
use ESD\Core\Server\Server;

class ManagerProcess extends Process
{
    const name = "manager";
    const id = "-2";

    public function __construct(Server $server)
    {
        parent::__construct($server, self::id, self::name, Process::SERVER_GROUP);
    }

    public function onProcessStart()
    {
        Process::setProcessTitle(Server::$instance->getServerConfig()->getName() . "-" . $this->getProcessName());
        $this->processPid = getmypid();
        $this->server->getProcessManager()->setCurrentProcessId($this->processId);
        //堵塞住SIGINT
        pcntl_signal(SIGINT, function () {

        });
    }

    public function onProcessStop()
    {
        return;
    }

    public function onPipeMessage(Message $message, Process $fromProcess)
    {
        return;
    }

    /**
     * 在onProcessStart之前，用于初始化成员变量
     * @return mixed
     */
    public function init()
    {
        return;
    }
}