<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/5/24
 * Time: 11:49
 */

namespace ESD\Core\Server;


use ESD\Core\Context\Context;
use ESD\Core\Context\ContextBuilder;

class ServerContextBuilder implements ContextBuilder
{
    protected $context;

    /**
     * ServerContextBuilder constructor.
     * @param Server $server
     * @throws \ESD\Core\Exception
     */
    public function __construct(Server $server)
    {
        $this->context = new Context();
        $this->context->add("server", $server);
    }

    public function build(): ?Context
    {
        return $this->context;
    }

    public function getDeep(): int
    {
        return ContextBuilder::SERVER_CONTEXT;
    }
}