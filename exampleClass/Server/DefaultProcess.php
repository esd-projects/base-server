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
use Monolog\Logger;

class DefaultProcess extends Process
{
    /**
     * @var Logger
     */
    private $log;
    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;

    /**
     * 在onProcessStart之前，用于初始化成员变量
     * @return mixed
     */
    public function init()
    {
        $this->log = $this->context->getDeepByClassName(Logger::class);
        $this->log->log(Logger::INFO, $this->processName);
        $this->eventDispatcher = Server::$instance->getProcessManager()->getCurrentProcess()->getContext()->getDeepByClassName(EventDispatcher::class);
    }

    public function onProcessStart()
    {
        $this->log->log(Logger::INFO, "start");
        $message = new Message("message", "test");
        foreach ($this->getProcessManager()->getProcesses() as $process) {
            $this->sendMessage($message, $process);
        }
        $this->eventDispatcher->add("testEvent", function (Event $event) {
            $this->log->log(Logger::INFO, "[Event] {$event->getData()}");
        });
        if ($this->getProcessId() == 0) {
            sleep(1);
            $this->eventDispatcher->dispatchEvent(new Event("testEvent", "Hello"));
            $this->eventDispatcher->dispatchProcessEvent(new Event("testEvent", "Hello Every Process"), ...$this->getProcessManager()->getProcesses());
        }
    }

    public function onProcessStop()
    {
        $this->log->log(Logger::INFO, "stop");
    }

    public function onPipeMessage(Message $message, Process $fromProcess)
    {
        $this->log->log(Logger::INFO, "[FromProcess:{$fromProcess->getProcessId()}] [{$message->toString()}]");
    }

}