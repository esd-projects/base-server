<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/18
 * Time: 13:52
 */

namespace ESD\BaseServer\Plugins\Event;


use ESD\BaseServer\Plugins\DI\DIPlugin;
use ESD\BaseServer\Server\Context;
use ESD\BaseServer\Server\Message\MessageProcessor;
use ESD\BaseServer\Server\PlugIn\AbstractPlugin;
use ESD\BaseServer\Server\Server;

/**
 * Event 插件加载器
 * Class EventPlug
 * @package ESD\BaseServer\Plugins\Event
 */
class EventPlugin extends AbstractPlugin
{
    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;

    public function __construct()
    {
        parent::__construct();
        $this->atAfter(DIPlugin::class);
    }

    /**
     * 在服务启动前
     * @param Context $context
     * @throws \ESD\BaseServer\Exception
     */
    public function beforeServerStart(Context $context)
    {
        //创建事件派发器
        $this->eventDispatcher = new EventDispatcher($context->getServer());
        Server::$instance->setEventDispatcher($this->eventDispatcher);
        $context->add("eventDispatcher", $this->eventDispatcher);
    }

    /**
     * 在进程启动前
     * @param Context $context
     * @throws \ESD\BaseServer\Exception
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

    /**
     * 获取插件名字
     * @return string
     */
    public function getName(): string
    {
        return "Event";
    }
}