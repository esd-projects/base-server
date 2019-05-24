<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/19
 * Time: 16:12
 */

namespace ESD\Core\Server\Process;


use ESD\Core\Event\Event;

class ProcessEvent extends Event
{
    const ProcessStartEvent = "ProcessStartEvent";
    const ProcessStopEvent = "ProcessStopEvent";
}