<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/19
 * Time: 16:12
 */

namespace ESD\Core\Server;

use ESD\Core\Event\Event;

class ApplicationEvent extends Event
{
    const ApplicationStartingEvent = "ApplicationStartingEvent";
    const ApplicationShutdownEvent = "ApplicationShutdownEvent";
}