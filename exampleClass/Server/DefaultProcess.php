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

    public function __construct(Server $server, int $processId, string $name = null, string $groupName = self::DEFAULT_GROUP)
    {
        parent::__construct($server, $processId, $name, $groupName);
        $this->log = $this->context->getDeepByClassName(Logger::class);
        $this->log->log(Logger::INFO, "$name");
    }

    public function onProcessStart()
    {
        $this->log->log(Logger::INFO, "start");
        $message = new Message("message", "test");
        foreach ($this->getProcessManager()->getProcesses() as $process) {
            $this->sendMessage($message, $process);
        }
        $this->getEventDispatcher()->add("testEvent", function (Event $event) {
            $this->log->log(Logger::INFO, "[Event] {$event->getData()}");
        });
        if ($this->getProcessId() == 0) {
            sleep(1);
            $this->getEventDispatcher()->dispatchEvent(new Event("testEvent", "Hello"));
            $this->getEventDispatcher()->dispatchProcessEvent(new Event("testEvent", "Hello Every Process"), ...$this->getProcessManager()->getProcesses());
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

    public function getEventDispatcher(): EventDispatcher
    {
        return $this->getContext()->getDeepByClassName(EventDispatcher::class);
    }
}