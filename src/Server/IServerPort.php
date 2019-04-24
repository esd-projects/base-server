<?php
/**
 * Created by PhpStorm.
 * User: administrato
 * Date: 2019/4/24
 * Time: 13:55
 */

namespace GoSwoole\BaseServer\Server;


use GoSwoole\BaseServer\Server\Beans\Request;
use GoSwoole\BaseServer\Server\Beans\Response;
use GoSwoole\BaseServer\Server\Beans\WebSocketFrame;

interface IServerPort
{
    public function onTcpConnect(int $fd, int $reactorId);

    public function onTcpClose(int $fd, int $reactorId);

    public function onTcpReceive(int $fd, int $reactorId, string $data);

    public function onUdpPacket(string $data, array $client_info);

    public function onHttpRequest(Request $request, Response $response);

    public function onWsMessage(WebSocketFrame $frame);

    public function onWsOpen(Request $request);

    public function onWsPassCustomHandshake(Request $request): bool;
}