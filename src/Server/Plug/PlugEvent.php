<?php
/**
 * Created by PhpStorm.
 * User: administrato
 * Date: 2019/4/19
 * Time: 16:12
 */

namespace GoSwoole\BaseServer\Server\Plug;


use GoSwoole\BaseServer\Event\Event;

class PlugEvent extends Event
{
    const PlugSuccessEvent = "PlugSuccessEvent";
    const PlugFailEvent = "PlugFailEvent";
    const PlugReady = "PlugReady";
}