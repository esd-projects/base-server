<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2019-04-17
 * Time: 17:38
 */

namespace ESD\BaseServer\ExampleClass\Server;


use ESD\BaseServer\Server\Beans\Request;
use ESD\BaseServer\Server\Beans\Response;
use ESD\BaseServer\Server\Beans\WebSocketFrame;
use ESD\BaseServer\Server\Config\PortConfig;
use ESD\BaseServer\Server\Server;
use ESD\BaseServer\Server\ServerPort;
use Monolog\Logger;

class DefaultServerPort extends ServerPort
{
    /**
     * @var Logger
     */
    private $log;

    public function __construct(Server $server, PortConfig $portConfig)
    {
        parent::__construct($server, $portConfig);
        $this->log = getDeepContextValueByClassName(Logger::class);
        $this->log->info("{$this->getPortConfig()->getTypeName()}\t[{$this->getPortConfig()->getHost()}]\t[{$this->getPortConfig()->getPort()}]");
    }

    public function onTcpConnect(int $fd, int $reactorId)
    {
        $this->log->info("");
    }

    public function onTcpClose(int $fd, int $reactorId)
    {
        $this->log->info("");
    }

    public function onTcpReceive(int $fd, int $reactorId, string $data)
    {
        $this->log->info("");
    }

    public function onUdpPacket(string $data, array $client_info)
    {
        $this->log->info("");
    }

    public function onHttpRequest(Request $request, Response $response)
    {
        $response->end("HelloWorld");
        $this->log->info("");
    }

    public function onWsMessage(WebSocketFrame $frame)
    {
        $this->log->info("");
    }

    public function onWsOpen(Request $request)
    {
        $this->log->info("");
    }

    public function onWsPassCustomHandshake(Request $request): bool
    {
        $this->log->info("");
        return true;
    }
}