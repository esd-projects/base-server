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

class MasterProcess extends Process
{
    public function __construct(Server $server)
    {
        parent::__construct($server, 0, "master", Process::SERVER_GROUP);
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