<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/17
 * Time: 16:54
 */

namespace GoSwoole\BaseServer\Coroutine\Context;


class Context
{

    const storageKey = "@context";

    /**
     * 将目标上下文拷贝到下面
     * @param Context $sourceContext
     */
    public static function clone(Context $sourceContext)
    {
        if ($sourceContext != null) {
            Co::getContext()[self::storageKey] = $sourceContext;
        }
    }
}