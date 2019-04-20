<?php
/**
 * Created by PhpStorm.
 * User: administrato
 * Date: 2019/4/18
 * Time: 18:12
 */

namespace GoSwoole\BaseServer\Server\ServerProcess;


use GoSwoole\BaseServer\Server\Message\Message;
use GoSwoole\BaseServer\Server\Process;
use GoSwoole\BaseServer\Server\Server;

class ManagerProcess extends Process
{
    const name = "-manager";
    const id = "-2";

    public function __construct(Server $server)
    {
        $name = $server->getServerConfig()->getName();
        parent::__construct($server, self::id, $name . self::name, Process::SERVER_GROUP);
    }

    public function onProcessStart()
    {
        Process::setProcessTitle($this->getProcessName());
        $this->processPid = getmypid();
        $this->server->getProcessManager()->setCurrentProcessId($this->processId);
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