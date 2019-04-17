<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2019-04-17
 * Time: 17:39
 */

namespace GoSwoole\BaseServer\ExampleClass\Server;


use GoSwoole\BaseServer\Server\Process;
use GoSwoole\BaseServer\Server\Server;

class DefaultProcess extends Process
{
    private $className;

    public function __construct(Server $server, string $groupName = self::DEFAULT_GROUP)
    {
        parent::__construct($server, $groupName);
        $this->className = get_class($this);
    }

    public function onProcessStart()
    {
        get_class($this);
        print_r("[$this->className:{$this->getProcessId()}]\t[{$this->getGroupName()}]\t[{$this->getProcessName()}]\t[onProcessStart]\n");
    }

    public function onProcessStop()
    {
        print_r("[$this->className:{$this->getProcessId()}]\t[{$this->getProcessName()}]\t[onProcessStop]\n");
    }

    public function onPipeMessage(string $message, Process $fromProcess)
    {
        print_r("[$this->className:{$this->getProcessId()}]\t[{$this->getProcessName()}]\t[onPipeMessage]\t[FromProcess:{$fromProcess->getProcessId()}]\t[$message]\n");
    }
}