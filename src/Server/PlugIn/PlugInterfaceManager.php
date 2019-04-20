<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/18
 * Time: 12:24
 */

namespace GoSwoole\BaseServer\Server\PlugIn;

use GoSwoole\BaseServer\Coroutine\Channel;
use GoSwoole\BaseServer\Event\EventDispatcher;
use GoSwoole\BaseServer\Event\EventPlugin;
use GoSwoole\BaseServer\Exception;
use GoSwoole\BaseServer\Logger\LoggerPlug;
use GoSwoole\BaseServer\Server\Context;
use GoSwoole\BaseServer\Server\Server;
use Monolog\Logger;

/**
 * 插件管理器
 * Class PlugManager
 * @package GoSwoole\BaseServer\Server\Plug
 */
class PlugInterfaceManager implements PlugInterface
{
    /**
     * @var PlugInterface[]
     */
    private $plugs = [];

    /**
     * @var PlugInterface[]
     */
    private $plugClasses = [];

    /**
     * @var bool
     */
    private $fixed = false;

    /**
     * @var Server
     */
    private $server;

    /**
     * @var Logger
     */
    private $log;

    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;

    /**
     * @var Channel
     */
    private $readyChannel;

    public function __construct(Server $server)
    {
        $this->server = $server;
        $this->readyChannel = new Channel();
    }

    /**
     * 添加插件
     * @param PlugInterface $plug
     * @throws Exception
     */
    public function addPlug(PlugInterface $plug)
    {
        if ($this->fixed) {
            throw new Exception("已经锁定不能添加插件");
        }
        $this->plugs[] = $plug;
        $this->plugClasses[get_class($plug)] = $plug;
    }

    /**
     * 在服务启动之前
     * @param Context $context
     * @return mixed|void
     */
    public function beforeServerStart(Context $context)
    {
        foreach ($this->plugs as $plug) {
            if ($this->log != null) {
                $this->log->log(Logger::INFO, "加载[{$plug->getName()}]插件");
            }
            $plug->beforeServerStart($context);
            if ($plug instanceof LoggerPlug) {
                //这时可以获取到Log对象了
                $this->log = $this->server->getContext()->getDeepByClassName(Logger::class);
            }
        }
    }

    /**
     * 在进程启动之前
     * @param Context $context
     * @return mixed|void
     */
    public function beforeProcessStart(Context $context)
    {
        foreach ($this->plugs as $plug) {
            $plug->beforeProcessStart($context);
            if (!$plug->getReadyChannel()->pop(5)) {
                $plug->getReadyChannel()->close();
                $this->log->error("{$plug->getName()}插件加载失败");
                if ($this->eventDispatcher != null) {
                    $this->eventDispatcher->dispatchEvent(new PlugEvent(PlugEvent::PlugFailEvent, $plug));
                }
            } else {
                if ($plug instanceof EventPlugin) {
                    //这时可以获取到EventDispatcher对象了
                    $this->eventDispatcher = $this->server->getContext()->getDeepByClassName(EventDispatcher::class);
                }
                if ($this->eventDispatcher != null) {
                    $this->eventDispatcher->dispatchEvent(new PlugEvent(PlugEvent::PlugSuccessEvent, $plug));
                }
            }
        }
        $this->readyChannel->push("ready");
    }

    /**
     * 插件排序
     */
    public function order()
    {
        foreach ($this->plugs as $plug) {
            if ($this->getPlugAfterClass($plug) != null) {
                $plug->setAfterPlug($this->getPlugAfterClass($plug));
            }
        }
        usort($this->plugs, function ($a, $b) {
            if ($a->getOrderIndex() > $b->getOrderIndex()) {
                return 1;
            } else {
                return -1;
            }
        });
        $this->fixed = true;
    }

    /**
     * @param PlugInterface $plug
     * @return PlugInterface|null
     */
    private function getPlugAfterClass(PlugInterface $plug)
    {
        if ($plug->getAfterClass() == null) return null;
        return $this->plugClasses[$plug->getAfterClass()] ?? null;
    }


    /**
     * 获取插件名字
     * @return string
     */
    public function getName(): string
    {
        return "PlugManager";
    }

    /**
     * @param int $index
     * @return mixed
     */
    public function setOrderIndex(int $index)
    {
        return;
    }

    /**
     * @return int
     */
    public function getOrderIndex(): int
    {
        return 0;
    }

    /**
     * @return mixed
     */
    public function getBeforeClass()
    {
        return null;
    }

    /**
     * @return mixed
     */
    public function getAfterClass()
    {
        return null;
    }

    /**
     * @param mixed $afterPlug
     */
    public function setAfterPlug($afterPlug)
    {
        return;
    }

    /**
     * @return Channel
     */
    public function getReadyChannel(): Channel
    {
        return $this->readyChannel;
    }

    /**
     * 等待
     */
    public function waitReady()
    {
        $this->readyChannel->pop();
        $this->readyChannel->close();
    }

}