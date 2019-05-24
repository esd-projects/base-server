<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/5/24
 * Time: 10:41
 */

namespace ESD\Core\Context;


interface ContextBuilder
{
    const ROOT_CONTEXT = 0;

    const SERVER_CONTEXT = 1;

    const PROCESS_CONTEXT = 2;

    const CO_CONTEXT = 3;

    public function build(): ?Context;

    public function getDeep(): int;
}