<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/19
 * Time: 16:12
 */

namespace ESD\BaseServer\Server\PlugIn;


use ESD\BaseServer\Plugins\Event\Event;

class PluginManagerEvent extends Event
{
    const PlugBeforeServerStartEvent = "PlugBeforeServerStartEvent";
    const PlugAfterServerStartEvent = "PlugAfterServerStartEvent";
    const PlugBeforeProcessStartEvent = "PlugBeforeProcessStartEvent";
    const PlugAfterProcessStartEvent = "PlugAfterProcessStartEvent";
    const PlugAllReadyEvent = "PlugAllReadyEvent";
}