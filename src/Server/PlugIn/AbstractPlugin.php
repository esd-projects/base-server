<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/18
 * Time: 14:19
 */

namespace GoSwoole\BaseServer\Server\Plugin;


use GoSwoole\BaseServer\Coroutine\Channel;
use GoSwoole\BaseServer\Exception;
use GoSwoole\BaseServer\Server\Server;

/**
 * 基础插件，插件类需要继承
 * Class BasePlug
 * @package GoSwoole\BaseServer\Server\Plug
 */
abstract class AbstractPlugin implements PluginInterface
{
    /**
     * @var PluginInterfaceManager
     */
    private $pluginInterfaceManager;
    /**
     * @var string[]
     */
    private $afterClass = [];
    /**
     * @var PluginInterface[]
     */
    private $afterPlug = [];
    /**
     * @var string[]
     */
    private $beforeClass = [];
    /**
     * @var int
     */
    private $orderIndex = 1;

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
     * 在哪个之后
     * @param $className
     */
    public function atAfter(...$className)
    {
        foreach ($className as $one) {
            $this->afterClass[$one] = $one;
        }
    }

    /**
     * 在哪个之前
     * @param $className
     */
    public function atBefore(...$className)
    {
        foreach ($className as $one) {
            $this->beforeClass[$one] = $one;
        }
    }

    /**
     * @return array
     */
    public function getAfterClass(): array
    {
        return $this->afterClass;
    }


    /**
     * @param PluginInterface $root
     * @param int $layer
     * @return int
     * @throws Exception
     */
    public function getOrderIndex(PluginInterface $root, int $layer): int
    {
        $layer++;
        if ($layer > 255) throw new Exception(get_class($root) . " 插件排序出现了循环依赖，请检查插件");
        $max = $this->orderIndex;
        foreach ($this->afterPlug as $plugin) {
            $vaule = $this->orderIndex + $plugin->getOrderIndex($root, $layer);
            $max = max($max, $vaule);
        }
        return $max;
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
     * @param PluginInterface $afterPlug
     */
    public function addAfterPlug(PluginInterface $afterPlug): void
    {
        $this->afterPlug[] = $afterPlug;
    }

    /**
     * @return string[]
     */
    public function getBeforeClass(): array
    {
        return $this->beforeClass;
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
}