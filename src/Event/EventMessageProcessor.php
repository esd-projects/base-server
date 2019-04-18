<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/18
 * Time: 10:42
 */

namespace GoSwoole\BaseServer\Event;


use GoSwoole\BaseServer\Server\Message\Message;
use GoSwoole\BaseServer\Server\Message\MessageProcessor;

/**
 * 事件派发处理器
 * Class EventMessageProcessor
 * @package GoSwoole\BaseServer\Event
 */
class EventMessageProcessor extends MessageProcessor
{

    public function __construct(EventDispatcher $eventDispatcher)
    {
        parent::__construct($eventDispatcher, EventMessage::type);
    }

    /**
     * 处理消息
     * @param Message $message
     * @return mixed
     */
    public function handler(Message $message): bool
    {
        if ($message instanceof EventMessage) {
            $this->eventDispatcher->dispatchEvent($message->getEvent());
            return true;
        }
        return false;
    }
}