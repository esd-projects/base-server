<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/18
 * Time: 13:52
 */

namespace GoSwoole\BaseServer\Event;


use GoSwoole\BaseServer\Logger\LoggerPlug;
use GoSwoole\BaseServer\Server\Context;
use GoSwoole\BaseServer\Server\Message\MessageProcessor;
use GoSwoole\BaseServer\Server\Plug\BasePlug;

/**
 * Event 插件加载器
 * Class EventPlug
 * @package GoSwoole\BaseServer\Event
 */
class EventPlug extends BasePlug
{
    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;

    public function __construct()
    {
        $this->atAfter(LoggerPlug::class);
    }

    /**
     * 在服务启动前
     * @param Context $context
     */
    public function beforeServerStart(Context $context)
    {

    }

    /**
     * 在进程启动前
     * @param Context $context
     * @throws \GoSwoole\BaseServer\Exception
     */
    public function beforeProcessStart(Context $context)
    {
        //创建事件派发器
        $this->eventDispatcher = new EventDispatcher($context->getServer());
        $context->add("eventDispatcher", $this->eventDispatcher);
        //注册事件派发处理函数
        MessageProcessor::addMessageProcessor(new EventMessageProcessor($this->eventDispatcher));
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