<?php
/**
 * Created by PhpStorm.
 * User: administrato
 * Date: 2019/5/9
 * Time: 13:38
 */

namespace GoSwoole\BaseServer\Server;


class EmptyServer
{
    public function __call($name, $arguments)
    {
        var_dump("__call:" . $name);
    }

    public function __get($name)
    {
        var_dump("__get:" . $name);
    }

    public function __set($name, $value)
    {
        var_dump("__set:" . $name);
    }
}