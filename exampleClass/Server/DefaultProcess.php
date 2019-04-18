<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2019-04-17
 * Time: 17:39
 */

namespace GoSwoole\BaseServer\ExampleClass\Server;


use GoSwoole\BaseServer\Event\Event;
use GoSwoole\BaseServer\Event\EventDispatcher;
use GoSwoole\BaseServer\Server\Message\Message;
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
        $message = new Message("message", "test");
        foreach ($this->getProcessManager()->getProcesses() as $process) {
            $this->sendMessage($message, $process);
        }
        $this->getEventDispatcher()->add("testEvent", function (Event $event) {
            print_r("[$this->className:{$this->getProcessId()}]\t[Event]\t{$event->getData()}\n");
        });
        if ($this->getProcessId() == 0) {
            sleep(1);
            $this->getEventDispatcher()->dispatchEvent(new Event("testEvent", "Hello"));
            $this->getEventDispatcher()->dispatchProcessEvent(new Event("testEvent", "Hello Every Process"), ...$this->getProcessManager()->getProcesses());
        }
    }

    public function onProcessStop()
    {
        print_r("[$this->className:{$this->getProcessId()}]\t[{$this->getProcessName()}]\t[onProcessStop]\n");
    }

    public function onPipeMessage(Message $message, Process $fromProcess)
    {
        print_r("[$this->className:{$this->getProcessId()}]\t[{$this->getProcessName()}]\t[onPipeMessage]\t[FromProcess:{$fromProcess->getProcessId()}]\t[{$message->toString()}]\n");
    }

    public function getEventDispatcher(): EventDispatcher
    {
        return $this->getContext()->getByClassName(EventDispatcher::class);
    }
}