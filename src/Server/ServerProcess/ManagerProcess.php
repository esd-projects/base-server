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
    public function __construct(Server $server)
    {
        parent::__construct($server, 0, "manager", Process::SERVER_GROUP);
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