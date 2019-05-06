<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/18
 * Time: 12:25
 */

namespace GoSwoole\BaseServer\Server\Plugin;


use GoSwoole\BaseServer\Coroutine\Channel;
use GoSwoole\BaseServer\Server\Context;

interface PluginInterface
{
    /**
     * @return Channel
     */
    public function getReadyChannel();

    /**
     * @param PluginInterface $root
     * @param int $layer
     * @return int
     */
    public function getOrderIndex(PluginInterface $root, int $layer): int;

    /**
     * @param mixed $afterPlug
     */
    public function addAfterPlug(PluginInterface $afterPlug);

    /**
     * @param $className
     * @return void
     */
    public function atAfter(...$className);

    /**
     * @param $className
     * @return void
     */
    public function atBefore(...$className);

    /**
     * 获取插件名字
     * @return string
     */
    public function getName(): string;

    /**
     * 在服务启动前
     * @param Context $context
     * @return mixed
     */
    public function beforeServerStart(Context $context);

    /**
     * 在进程启动前
     * @param Context $context
     * @return mixed
     */
    public function beforeProcessStart(Context $context);

    /**
     * @return array
     */
    public function getAfterClass(): array;

    /**
     * @return array
     */
    public function getBeforeClass(): array;

    /**
     * @param PluginInterfaceManager $pluginInterfaceManager
     * @return mixed
     */
    public function onAdded(PluginInterfaceManager $pluginInterfaceManager);

}