<?php
/**
 * Created by PhpStorm.
 * User: administrato
 * Date: 2019/4/19
 * Time: 16:12
 */

namespace GoSwoole\BaseServer\Server\Plugin;


use GoSwoole\BaseServer\Event\Event;

class PluginManagerEvent extends Event
{
    const PlugBeforeServerStartEvent = "PlugBeforeServerStartEvent";
    const PlugBeforeProcessStartEvent = "PlugBeforeProcessStartEvent";
    const PlugAllReadyEvent = "PlugAllReadyEvent";
}