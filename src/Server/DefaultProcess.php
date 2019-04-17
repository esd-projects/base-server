<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/16
 * Time: 12:04
 */

namespace GoSwoole\BaseServer\Server;

/**
 * 默认的进程实例
 * Class DefaultProcess
 * @package GoSwoole\BaseServer\Server
 */
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