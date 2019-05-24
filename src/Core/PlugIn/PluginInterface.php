<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/18
 * Time: 12:25
 */

namespace ESD\Core\PlugIn;


use ESD\Core\Channel\Channel;
use ESD\Core\Context\Context;

interface PluginInterface
{
    /**
     * @return Channel
     */
    public function getReadyChannel();

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
    public function init(Context $context);

    /**
     * 初始化
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
     * @param PluginInterfaceManager $pluginInterfaceManager
     * @return mixed
     */
    public function onAdded(PluginInterfaceManager $pluginInterfaceManager);

}