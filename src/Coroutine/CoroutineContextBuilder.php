<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/5/24
 * Time: 11:38
 */

namespace ESD\Coroutine;


use ESD\Core\Context\Context;
use ESD\Core\Context\ContextBuilder;

class CoroutineContextBuilder implements ContextBuilder
{

    public function build(): ?Context
    {
        if (Co::getCid() > 0) {
            return Co::getContext();
        } else {
            return null;
        }
    }

    public function getDeep(): int
    {
        return ContextBuilder::CO_CONTEXT;
    }
}