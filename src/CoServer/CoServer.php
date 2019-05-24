<?php
/**
 * Created by PhpStorm.
 * User: administrato
 * Date: 2019/5/24
 * Time: 18:09
 */

namespace ESD\CoServer;

use ESD\Core\Channel\Channel;
use ESD\Core\DI\DI;
use ESD\Core\Event\EventCall;
use ESD\Core\Server\Config\ServerConfig;
use ESD\Core\Server\Server;
use ESD\Coroutine\Channel\ChannelFactory;
use ESD\Coroutine\Co;
use ESD\Coroutine\Event\EventCallFactory;

abstract class CoServer extends Server
{
    public function __construct(ServerConfig $serverConfig, string $defaultPortClass, string $defaultProcessClass)
    {
        Co::enableCo();
        DI::$definitions = [
            EventCall::class => new EventCallFactory(),
            Channel::class => new ChannelFactory()
        ];
        parent::__construct($serverConfig, $defaultPortClass, $defaultProcessClass);
    }
}