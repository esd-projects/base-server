<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/5/24
 * Time: 13:17
 */

namespace ESD\Core\Context;


class RootContextBuilder implements ContextBuilder
{
    private $context;

    public function __construct()
    {
        $this->context = new Context();
    }

    public function build(): ?Context
    {
        return $this->context;
    }

    public function getDeep(): int
    {
        return ContextBuilder::ROOT_CONTEXT;
    }
}