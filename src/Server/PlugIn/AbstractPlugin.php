<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/18
 * Time: 14:19
 */

namespace ESD\BaseServer\Server\PlugIn;


use ESD\BaseServer\Coroutine\Channel;
use ESD\BaseServer\Order\Order;
use ESD\BaseServer\Server\Context;
use ESD\BaseServer\Server\Server;

/**
 * 基础插件，插件类需要继承
 * Class BasePlug
 * @package ESD\BaseServer\Server\Plug
 */
abstract class AbstractPlugin extends Order implements PluginInterface
{
    /**
     * @var PluginInterfaceManager
     */
    protected $pluginInterfaceManager;
    /**
     * @var Channel
     */
    private $readyChannel;

    /**
     * AbstractPlugin constructor.
     * @throws \DI\DependencyException
     */
    public function __construct()
    {
        $this->readyChannel = new Channel();
        if (Server::$instance->getContainer() != null) {
            //注入DI
            Server::$instance->getContainer()->injectOn($this);
        }
    }

    /**
     * 配置到DI容器
     * @param $name
     * @param $value
     */
    public function setToDIContainer($name, $value)
    {
        if (Server::$instance->getContainer() != null) {
            Server::$instance->getContainer()->set($name, $value);
        }
    }

    /**
     * @return Channel
     */
    public function getReadyChannel(): Channel
    {
        return $this->readyChannel;
    }

    public function ready()
    {
        $this->readyChannel->push("ready");
    }

    /**
     * 被加入时，这里其实可以添加依赖的插件
     * @param PluginInterfaceManager $pluginInterfaceManager
     * @return mixed|void
     */
    public function onAdded(PluginInterfaceManager $pluginInterfaceManager)
    {
        $this->pluginInterfaceManager = $pluginInterfaceManager;
    }

    /**
     * 初始化
     * @param Context $context
     * @return mixed|void
     */
    public function init(Context $context)
    {
        return;
    }
}