<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/18
 * Time: 10:42
 */

namespace ESD\Core\Event;


use ESD\Core\Message\Message;
use ESD\Core\Message\MessageProcessor;

/**
 * 事件派发处理器
 * Class EventMessageProcessor
 * @package ESD\BaseServer\Plugins\Event
 */
class EventMessageProcessor extends MessageProcessor
{
    /**
     * 消息派发器
     * @var EventDispatcher
     */
    protected $eventDispatcher;

    public function __construct(EventDispatcher $eventDispatcher)
    {
        parent::__construct(EventMessage::type);
        $this->eventDispatcher = $eventDispatcher;
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