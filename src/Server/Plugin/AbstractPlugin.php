<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/18
 * Time: 14:19
 */

namespace GoSwoole\BaseServer\Server\Plugin;


use GoSwoole\BaseServer\Coroutine\Channel;

/**
 * 基础插件，插件类需要继承
 * Class BasePlug
 * @package GoSwoole\BaseServer\Server\Plug
 */
abstract class AbstractPlugin implements PluginInterface
{
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

    public function __construct()
    {
        $this->readyChannel = new Channel();
    }

    /**
     * 在哪个之后
     * @param $className
     */
    public function atAfter($className)
    {
        $this->afterClass[$className] = $className;
    }

    /**
     * 在哪个之前
     * @param $className
     */
    public function atBefore($className)
    {
        $this->beforeClass[$className] = $className;
    }

    /**
     * @return array
     */
    public function getAfterClass(): array
    {
        return $this->afterClass;
    }


    /**
     * @return int
     */
    public function getOrderIndex(): int
    {
        $max = $this->orderIndex;
        foreach ($this->afterPlug as $plugin) {
            $vaule = $this->orderIndex + $plugin->getOrderIndex();
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
     * @param PluginInterface[] $afterPlug
     */
    public function setAfterPlug(array $afterPlug): void
    {
        $this->afterPlug = $afterPlug;
    }

    /**
     * @return string[]
     */
    public function getBeforeClass(): array
    {
        return $this->beforeClass;
    }
}