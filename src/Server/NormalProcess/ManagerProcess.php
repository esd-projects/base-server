<?php
/**
 * Created by PhpStorm.
 * User: administrato
 * Date: 2019/4/18
 * Time: 18:12
 */

namespace GoSwoole\BaseServer\Server\NormalProcess;


use GoSwoole\BaseServer\Server\Message\Message;
use GoSwoole\BaseServer\Server\Process;
use GoSwoole\BaseServer\Server\Server;

class ManagerProcess extends Process
{
    public function __construct(Server $server, string $groupName = self::DEFAULT_GROUP)
    {
        parent::__construct($server, $groupName);
        $this->groupName = "Server";
        $this->processName = "manager";
        $this->processId = 0;
    }

    public function onProcessStart()
    {
        $this->processPid = posix_getpid();
    }

    public function onProcessStop()
    {
        return;
    }

    public function onPipeMessage(Message $message, Process $fromProcess)
    {
        return;
    }
}