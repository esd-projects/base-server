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

class MasterProcess extends Process
{
    public function __construct(Server $server, string $groupName = self::DEFAULT_GROUP)
    {
        parent::__construct($server, $groupName);
        $this->groupName = "server";
        $this->processName = "master";
        $this->processId = 0;
        $this->processPid = posix_getpid();
    }

    public function onProcessStart()
    {
        // TODO: Implement onProcessStart() method.
    }

    public function onProcessStop()
    {
        // TODO: Implement onProcessStop() method.
    }

    public function onPipeMessage(Message $message, Process $fromProcess)
    {
        // TODO: Implement onPipeMessage() method.
    }
}