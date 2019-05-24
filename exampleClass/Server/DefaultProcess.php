<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2019-04-17
 * Time: 17:39
 */

namespace ESD\ExampleClass\Server;


use ESD\BaseServer\Plugins\Event\Event;
use ESD\BaseServer\Plugins\Logger\GetLogger;
use ESD\BaseServer\Server\Message\Message;
use ESD\BaseServer\Server\Process;

class DefaultProcess extends Process
{
    use GetLogger;

    /**
     * 在onProcessStart之前，用于初始化成员变量
     * @return mixed
     */
    public function init()
    {
        $this->log->info($this->processName);
    }

    public function onProcessStart()
    {
        $this->info("start");
        $message = new Message("message", "test");
        foreach ($this->getProcessManager()->getProcesses() as $process) {
            $this->sendMessage($message, $process);
        }
        $channel = $this->eventDispatcher->listen("testEvent");
        goWithContext(function () use ($channel) {
            $channel->popLoop(function (Event $event) {
                $this->log->info("[Event] {$event->getData()}");
            });
        });
        if ($this->getProcessId() == 0) {
            sleep(1);
            $this->eventDispatcher->dispatchEvent(new Event("testEvent", "Hello"));
            $this->eventDispatcher->dispatchProcessEvent(new Event("testEvent", "Hello Every Process"), ...$this->getProcessManager()->getProcesses());
        }
    }

    public function onProcessStop()
    {
        $this->info("stop");
    }

    public function onPipeMessage(Message $message, Process $fromProcess)
    {
        $this->info("[FromProcess:{$fromProcess->getProcessId()}] [{$message->toString()}]");
    }

}