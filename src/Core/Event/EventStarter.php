<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/18
 * Time: 13:52
 */

namespace ESD\BaseServer\Plugins\Event;

use ESD\Core\Context\Context;
use ESD\Core\Event\EventDispatcher;
use ESD\Core\Message\MessageProcessor;
use ESD\Core\Server\Server;

/**
 * Event 插件加载器
 * Class EventPlug
 * @package ESD\BaseServer\Plugins\Event
 */
class EventStarter
{
    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;

    /**
     * 在服务启动前
     * @param Context $context
     */
    public function beforeServerStart(Context $context)
    {
        //创建事件派发器
        $this->eventDispatcher = new EventDispatcher(Server::$instance);
        Server::$instance->setEventDispatcher($this->eventDispatcher);
        $context->add("eventDispatcher", $this->eventDispatcher);
    }

    /**
     * 在进程启动前
     * @param Context $context
     * @throws \ESD\Core\Exception
     */
    public function beforeProcessStart(Context $context)
    {
        //创建事件派发器
        $this->eventDispatcher = Server::$instance->getEventDispatcher();
        //注册事件派发处理函数
        MessageProcessor::addMessageProcessor(new EventMessageProcessor($this->eventDispatcher));
        //ready
        $this->ready();
    }
}