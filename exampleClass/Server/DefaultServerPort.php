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

class DefaultServerPort extends ServerPort
{
    private $className;

    public function __construct(Server $server, PortConfig $portConfig)
    {
        parent::__construct($server, $portConfig);
        $this->className = get_class($this);
        print_r("[$this->className]\t{$this->getPortConfig()->getTypeName()}\t[{$this->getPortConfig()->getHost()}]\t[{$this->getPortConfig()->getPort()}]\n");
    }

    public function onTcpConnect(int $fd, int $reactorId)
    {
        print_r("[$this->className:{$this->getPortConfig()->getPort()}]\t[onTcpConnect]\t[$fd]\n");
    }

    public function onTcpClose(int $fd, int $reactorId)
    {
        print_r("[$this->className:{$this->getPortConfig()->getPort()}]\t[onTcpClose]\t[$fd]\n");
    }

    public function onTcpReceive(int $fd, int $reactorId, string $data)
    {
        print_r("[$this->className:{$this->getPortConfig()->getPort()}]\t[onTcpReceive]\t[$fd]\n");
    }

    public function onTcpBufferFull(int $fd)
    {
        print_r("[$this->className:{$this->getPortConfig()->getPort()}]\t[onTcpBufferFull]\t[$fd]\n");
    }

    public function onTcpBufferEmpty(int $fd)
    {
        print_r("[$this->className:{$this->getPortConfig()->getPort()}]\t[onTcpBufferEmpty]\t[$fd]\n");
    }

    public function onUdpPacket(string $data, array $client_info)
    {
        print_r("[$this->className:{$this->getPortConfig()->getPort()}]\t[onUdpPacket]\t[$fd]\n");
    }

    public function onHttpRequest(Request $request, Response $response)
    {
        $response->end("HelloWorld");
        print_r("[$this->className:{$this->getPortConfig()->getPort()}]\t[onHttpRequest]\t[$fd]\n");
    }

    public function onWsMessage(WebSocketFrame $frame)
    {
        print_r("[$this->className:{$this->getPortConfig()->getPort()}]\t[onWsMessage]\t[$fd]\n");
    }

    public function onWsOpen(Request $request)
    {
        print_r("[$this->className:{$this->getPortConfig()->getPort()}]\t[onWsOpen]\t[$fd]\n");
    }

    public function onWsPassCustomHandshake(Request $request): bool
    {
        print_r("[$this->className:{$this->getPortConfig()->getPort()}]\t[onWsPassCustomHandshake]\t[$fd]\n");
        return true;
    }
}