<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/18
 * Time: 12:24
 */

namespace GoSwoole\BaseServer\Server\Plugin;

use GoSwoole\BaseServer\Coroutine\Channel;
use GoSwoole\BaseServer\Exception;
use GoSwoole\BaseServer\Plugins\Event\EventDispatcher;
use GoSwoole\BaseServer\Server\Context;
use GoSwoole\BaseServer\Server\Server;
use Monolog\Logger;

/**
 * 插件管理器
 * Class PlugManager
 * @package GoSwoole\BaseServer\Server\Plug
 */
class PluginInterfaceManager implements PluginInterface
{
    /**
     * @var PluginInterface[]
     */
    private $plugs = [];

    /**
     * @var PluginInterface[]
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
        //baseManager是获取不到的，只有用户插件管理才能获取到
        $this->log = $this->server->getContext()->getDeepByClassName(Logger::class);
        $this->eventDispatcher = $this->server->getContext()->getDeepByClassName(EventDispatcher::class);
    }

    /**
     * 添加插件
     * @param PluginInterface $plug
     * @throws Exception
     */
    public function addPlug(PluginInterface $plug)
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
        //发出PlugManagerEvent:PlugBeforeServerStartEvent事件
        if ($this->eventDispatcher != null) {
            $this->eventDispatcher->dispatchEvent(new PluginManagerEvent(PluginManagerEvent::PlugBeforeServerStartEvent, $this));
        }
        foreach ($this->plugs as $plug) {
            if ($this->log != null) {
                $this->log->log(Logger::INFO, "加载[{$plug->getName()}]插件");
            }
            $plug->beforeServerStart($context);
        }
    }

    /**
     * 在进程启动之前
     * @param Context $context
     * @return mixed|void
     */
    public function beforeProcessStart(Context $context)
    {
        //发出PlugManagerEvent:PlugBeforeProcessStartEvent事件
        if ($this->eventDispatcher != null) {
            $this->eventDispatcher->dispatchEvent(new PluginManagerEvent(PluginManagerEvent::PlugBeforeProcessStartEvent, $this));
        }
        foreach ($this->plugs as $plug) {
            $plug->beforeProcessStart($context);
            if (!$plug->getReadyChannel()->pop(5)) {
                $plug->getReadyChannel()->close();
                if ($this->log != null) {
                    $this->log->error("{$plug->getName()}插件加载失败");
                }
                if ($this->eventDispatcher != null) {
                    $this->eventDispatcher->dispatchEvent(new PluginEvent(PluginEvent::PlugFailEvent, $plug));
                }
            } else {
                if ($this->eventDispatcher != null) {
                    $this->eventDispatcher->dispatchEvent(new PluginEvent(PluginEvent::PlugSuccessEvent, $plug));
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
     * @param PluginInterface $plug
     * @return PluginInterface|null
     */
    private function getPlugAfterClass(PluginInterface $plug)
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
        //发出PlugManagerEvent:PlugAllReadyEvent事件
        if ($this->eventDispatcher != null) {
            $this->eventDispatcher->dispatchEvent(new PluginManagerEvent(PluginManagerEvent::PlugAllReadyEvent, $this));
        }
    }
}