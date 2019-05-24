<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/18
 * Time: 12:24
 */

namespace ESD\Core\PlugIn;

use ESD\Core\Channel\Channel;
use ESD\Core\Context\Context;
use ESD\Core\Event\EventDispatcher;
use ESD\Core\Exception;
use ESD\Core\Order\OrderOwnerTrait;
use ESD\Core\Server\Server;
use Psr\Log\LoggerInterface;

/**
 * 插件管理器
 * Class PlugManager
 * @package ESD\Core\Server\Plug
 */
class PluginInterfaceManager implements PluginInterface
{
    use OrderOwnerTrait;

    /**
     * @var Server
     */
    private $server;

    /**
     * @var LoggerInterface
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


    /**
     * PluginInterfaceManager constructor.
     * @param Server $server
     */
    public function __construct(Server $server)
    {
        $this->server = $server;
        $this->readyChannel = DIGet(Channel::class);
        $this->log = $this->server->getLog();
        $this->eventDispatcher = $this->server->getEventDispatcher();
    }

    /**
     * 添加插件
     * @param AbstractPlugin $plug
     * @throws Exception
     */
    public function addPlug(AbstractPlugin $plug)
    {
        $this->addOrder($plug);
        $plug->onAdded($this);
    }

    /**
     * 获取插件
     * @param String $className
     * @return PluginInterface|null
     */
    public function getPlug(String $className)
    {
        $plug = $this->orderClassList[$className] ?? null;
        if ($plug instanceof PluginInterface) {
            return $plug;
        } else {
            return null;
        }
    }

    /**
     * 初始化
     * @param Context $context
     * @return mixed|void
     */
    public function init(Context $context)
    {
        foreach ($this->orderList as $plug) {
            if ($plug instanceof PluginInterface) {
                if ($this->log != null) {
                    $this->log->debug("加载[{$plug->getName()}]插件");
                }
                $plug->init($context);
            }
        }
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
        foreach ($this->orderList as $plug) {
            if ($plug instanceof PluginInterface) {
                $plug->beforeServerStart($context);
            }
        }
        //发出PlugManagerEvent:PlugAfterServerStartEvent事件
        if ($this->eventDispatcher != null) {
            $this->eventDispatcher->dispatchEvent(new PluginManagerEvent(PluginManagerEvent::PlugAfterServerStartEvent, $this));
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
        foreach ($this->orderList as $plug) {
            if ($plug instanceof PluginInterface) {
                try {
                    $plug->beforeProcessStart($context);
                } catch (\Throwable $e) {
                    $this->log->error($e);
                    $this->log->error("{$plug->getName()}插件加载失败");
                    continue;
                }
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
        }
        //发出PlugManagerEvent:PlugAfterProcessStartEvent事件
        if ($this->eventDispatcher != null) {
            $this->eventDispatcher->dispatchEvent(new PluginManagerEvent(PluginManagerEvent::PlugAfterProcessStartEvent, $this));
        }
        $this->readyChannel->push("ready");
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

    /**
     * @param PluginInterfaceManager $pluginInterfaceManager
     * @return mixed
     */
    public function onAdded(PluginInterfaceManager $pluginInterfaceManager)
    {
        return;
    }

    /**
     * @return Server
     */
    public function getServer(): Server
    {
        return $this->server;
    }
}