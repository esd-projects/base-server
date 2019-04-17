<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/17
 * Time: 13:44
 */

namespace Core\Coroutine\Pool;


interface Runnable
{
    function run();
}