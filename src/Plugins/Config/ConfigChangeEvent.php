<?php
/**
 * Created by PhpStorm.
 * User: administrato
 * Date: 2019/4/29
 * Time: 18:35
 */

namespace GoSwoole\BaseServer\Plugins\Config;


use GoSwoole\BaseServer\Plugins\Event\Event;

class ConfigChangeEvent extends Event
{
    const ConfigChangeEvent = "ConfigChangeEvent";

    public function __construct($data)
    {
        parent::__construct(self::ConfigChangeEvent, $data);
    }
}