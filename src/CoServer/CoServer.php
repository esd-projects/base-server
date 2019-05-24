<?php
/**
 * Created by PhpStorm.
 * User: administrato
 * Date: 2019/5/24
 * Time: 18:09
 */

namespace ESD\CoServer;

use ESD\Core\Server\Config\ServerConfig;
use ESD\Core\Server\Server;
use ESD\Coroutine\Co;

abstract class CoServer extends Server
{
    public function __construct(ServerConfig $serverConfig, string $defaultPortClass, string $defaultProcessClass)
    {
        Co::enableCo();
        parent::__construct($serverConfig, $defaultPortClass, $defaultProcessClass);
    }
}