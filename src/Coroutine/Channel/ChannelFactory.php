<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/5/24
 * Time: 16:05
 */

namespace ESD\Coroutine\Channel;


use ESD\Core\DI\Factory;

class ChannelFactory implements Factory
{
    public function create($params)
    {
        return new ChannelImpl($params[0] ?? 1);
    }
}