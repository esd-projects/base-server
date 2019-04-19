<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2019-04-17
 * Time: 17:38
 */

namespace GoSwoole\BaseServer\ExampleClass\Server;


use GoSwoole\BaseServer\Server\Beans\Request;
use GoSwoole\BaseServer\Server\Beans\Response;
use GoSwoole\BaseServer\Server\Beans\WebSocketFrame;
use GoSwoole\BaseServer\Server\Config\PortConfig;
use GoSwoole\BaseServer\Server\Server;
use GoSwoole\BaseServer\Server\ServerPort;
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
        $this->log = $this->context->getByClassName(Logger::class);
        $this->log->log(Logger::INFO, "[{$this->getPortConfig()->getTypeName()}\t[{$this->getPortConfig()->getHost()}]\t[{$this->getPortConfig()->getPort()}]");
    }

    public function onTcpConnect(int $fd, int $reactorId)
    {
        $this->log->log(Logger::INFO, "");
    }

    public function onTcpClose(int $fd, int $reactorId)
    {
        $this->log->log(Logger::INFO, "");
    }

    public function onTcpReceive(int $fd, int $reactorId, string $data)
    {
        $this->log->log(Logger::INFO, "");
    }

    public function onTcpBufferFull(int $fd)
    {
        $this->log->log(Logger::INFO, "");
    }

    public function onTcpBufferEmpty(int $fd)
    {
        $this->log->log(Logger::INFO, "");
    }

    public function onUdpPacket(string $data, array $client_info)
    {
        $this->log->log(Logger::INFO, "");
    }

    public function onHttpRequest(Request $request, Response $response)
    {
        $response->end("HelloWorld");
        $this->log->log(Logger::INFO, "");
    }

    public function onWsMessage(WebSocketFrame $frame)
    {
        $this->log->log(Logger::INFO, "");
    }

    public function onWsOpen(Request $request)
    {
        $this->log->log(Logger::INFO, "");
    }

    public function onWsPassCustomHandshake(Request $request): bool
    {
        $this->log->log(Logger::INFO, "");
        return true;
    }
}