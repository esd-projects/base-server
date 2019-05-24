<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/5/24
 * Time: 14:21
 */

namespace ESD\Coroutine\Event;


use ESD\Core\DI\Factory;

class EventCallFactory implements Factory
{
    public function create($params)
    {
        return new EventCallImpl($params[0], $params[1], $params[2] ?? false);
    }
}