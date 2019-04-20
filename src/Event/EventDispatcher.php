<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/18
 * Time: 9:20
 */

namespace GoSwoole\BaseServer\Event;

use GoSwoole\BaseServer\Server\Process;
use GoSwoole\BaseServer\Server\Server;

/**
 * 事件派发器
 * Class EventDispatcher
 * @package GoSwoole\BaseServer\Event
 */
class EventDispatcher
{
    private $eventListeners = [];

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
     * @param callable $listener
     */
    public function add($type, $listener)
    {
        if (!array_key_exists($type, $this->eventListeners)) {
            $this->eventListeners [$type] = [];
        }
        array_push($this->eventListeners[$type], $listener);
    }

    /**
     * Removes an event listener from the object.
     *
     * @param string $type
     * @param callable $listener
     */
    public function remove($type, $listener)
    {
        if (array_key_exists($type, $this->eventListeners)) {
            $index = array_search($listener, $this->eventListeners [$type]);
            if ($index !== null) {
                unset ($this->eventListeners [$type] [$index]);
            }
            $numListeners = count($this->eventListeners [$type]);
            if ($numListeners == 0) {
                unset ($this->eventListeners [$type]);
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
            unset ($this->eventListeners [$type]);
        } else {
            $this->eventListeners = array();
        }
    }

    /**
     * send event to process
     * @param Event $event
     * @param Process ...$toProcess
     */
    public function dispatchProcessEvent(Event $event, Process ... $toProcess)
    {
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
        if (!array_key_exists($event->getType(), $this->eventListeners)) {
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
        if (array_key_exists($event->getType(), $this->eventListeners)) {
            $listeners = $this->eventListeners [$event->getType()];
        } else {
            return;
        }
        foreach ($listeners as $listener) {
            goWithContext(function () use ($listener, $event) {
                $listener($event);
            });
        }
    }

}