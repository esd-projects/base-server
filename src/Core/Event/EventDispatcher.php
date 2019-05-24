<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/18
 * Time: 9:20
 */

namespace ESD\Core\Event;

use ESD\Core\Server\Process\Process;
use ESD\Core\Server\Server;

/**
 * 事件派发器
 * Class EventDispatcher
 * @package ESD\BaseServer\Plugins\Event
 */
class EventDispatcher
{
    private $eventCalls = [];

    /**
     * @var Server
     */
    private $server;

    public function __construct(Server $server)
    {
        $this->server = $server;
    }

    /**
     * Registers an event listener at a certain object.
     *
     * @param string $type
     * @param EventCall|null $eventCall
     * @param bool $once 是否仅仅一次
     * @return EventCall
     */
    public function listen($type, ?EventCall $eventCall = null, $once = false): EventCall
    {
        if (!array_key_exists($type, $this->eventCalls)) {
            $this->eventCalls [$type] = [];
        }
        if ($eventCall == null) {
            $eventCall = DIGet(EventCall::class, [$this, $type, $once]);
        }
        array_push($this->eventCalls[$type], $eventCall);
        return $eventCall;
    }

    /**
     * Removes an event listener from the object.
     *
     * @param string $type
     * @param EventCall $eventCall
     */
    public function remove($type, EventCall $eventCall)
    {
        if ($eventCall != null) $eventCall->destroy();
        if (array_key_exists($type, $this->eventCalls)) {
            $index = array_search($eventCall, $this->eventCalls [$type]);
            if ($index !== null) {
                unset ($this->eventCalls [$type] [$index]);
            }
            $numListeners = count($this->eventCalls [$type]);
            if ($numListeners == 0) {
                unset ($this->eventCalls [$type]);
            }
        }
    }

    /**
     * Removes all event listeners with a certain type, or all of them if type is null.
     * Be careful when removing all event listeners: you never know who else was listening.
     *
     * @param string $type
     */
    public function removeAll($type = null)
    {
        if ($type) {
            unset ($this->eventCalls [$type]);
        } else {
            $this->eventCalls = array();
        }
    }

    /**
     * send event to process
     * @param Event $event
     * @param Process ...$toProcess
     */
    public function dispatchProcessEvent(Event $event, Process ... $toProcess)
    {
        $event->setProcessId(Server::$instance->getProcessManager()->getCurrentProcessId());
        if ($toProcess == null) {
            $this->dispatchEvent($event);
        }
        foreach ($toProcess as $process) {
            $this->server->getProcessManager()->getCurrentProcess()->sendMessage(new EventMessage($event), $process);
        }
    }

    /**
     * Dispatches an event to all objects that have registered listeners for its type.
     * If an event with enabled 'bubble' property is dispatched to a display object, it will
     * travel up along the line of parents, until it either hits the root object or someone
     * stops its propagation manually.
     *
     * @param Event $event
     */
    public function dispatchEvent(Event $event)
    {
        if (Server::$instance->getProcessManager() != null) {
            $event->setProcessId(Server::$instance->getProcessManager()->getCurrentProcessId());
        }
        if (!array_key_exists($event->getType(), $this->eventCalls)) {
            return; // no need to do anything
        }
        $this->invokeEvent($event);
    }

    /**
     * @private
     * Invokes an event on the current object.
     * This method does not do any bubbling, nor
     * does it back-up and restore the previous target on the event. The 'dispatchEvent'
     * method uses this method internally.
     *
     * @param Event $event
     */
    private function invokeEvent($event)
    {
        if (array_key_exists($event->getType(), $this->eventCalls)) {
            $calls = $this->eventCalls [$event->getType()];
        } else {
            return;
        }
        foreach ($calls as $call) {
            if ($call instanceof EventCall) {
                goWithContext(function () use ($call, $event) {
                    $call->send($event);
                });
            }
        }
    }

}