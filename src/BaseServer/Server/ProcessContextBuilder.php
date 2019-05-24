<?php
/**
 * Created by PhpStorm.
 * User: administrato
 * Date: 2019/5/24
 * Time: 11:57
 */

namespace ESD\BaseServer\Server;


use ESD\Core\Context\Context;
use ESD\Core\Context\ContextBuilder;

class ProcessContextBuilder implements ContextBuilder
{
    /**
     * @var Context
     */
    private $context;

    public function __construct(Process $process)
    {
        $this->context = new Context();
        $this->context->add("process", $process);
    }

    public function build(): ?Context
    {
        return $this->context;
    }

    public function getDeep(): int
    {
        return ContextBuilder::PROCESS_CONTEXT;
    }
}