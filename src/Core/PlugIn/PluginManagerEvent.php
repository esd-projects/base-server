<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/19
 * Time: 16:12
 */

namespace ESD\Core\PlugIn;

use ESD\Core\Event\Event;

class PluginManagerEvent extends Event
{
    const PlugBeforeServerStartEvent = "PlugBeforeServerStartEvent";
    const PlugAfterServerStartEvent = "PlugAfterServerStartEvent";
    const PlugBeforeProcessStartEvent = "PlugBeforeProcessStartEvent";
    const PlugAfterProcessStartEvent = "PlugAfterProcessStartEvent";
    const PlugAllReadyEvent = "PlugAllReadyEvent";
}