<?php
/**
 * Created by PhpStorm.
 * User: administrato
 * Date: 2019/4/19
 * Time: 16:12
 */

namespace GoSwoole\BaseServer\Plugins\Event;


class ApplicationEvent extends Event
{
    const ApplicationStartingEvent = "ApplicationStartingEvent";
    const ApplicationShutdownEvent = "ApplicationShutdownEvent";
}