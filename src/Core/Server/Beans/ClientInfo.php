<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/16
 * Time: 14:21
 */

namespace ESD\Core\Server\Beans;

/**
 * 连接信息
 * Class ClientInfo
 * @package ESD\Core\Server\Beans
 */
class ClientInfo
{
    /**
     * 来自哪个Reactor线程
     * @var int
     */
    private $reactorId;
    /**
     * 来自哪个监听端口socket，这里不是客户端连接的fd
     * @var int
     */
    private $serverFd;
    /**
     * 来自哪个监听端口
     * @var int
     */
    private $serverPort;
    /**
     * 客户端连接的端口
     * @var int
     */
    private $remotePort;
    /**
     * 客户端连接的IP地址
     * @var int
     */
    private $remoteIp;
    /**
     * 客户端连接到Server的时间，单位秒，由master进程设置
     * @var int
     */
    private $connectTime;
    /**
     * 最后一次收到数据的时间，单位秒，由master进程设置
     * @var int
     */
    private $lastTime;
    /**
     * 连接关闭的错误码，如果连接异常关闭，close_errno的值是非零，可以参考Linux错误信息列表
     * @var int
     */
    private $closeErrno;
    /**
     * [可选项] WebSocket连接状态，当服务器是Swoole\WebSocket\Server时会额外增加此项信息
     * @var int
     */
    private $websocketStatus;
    /**
     * [可选项] 使用SSL隧道加密，并且客户端设置了证书时会额外添加此项信息
     * @var null
     */
    private $sslClientCert;
    /**
     * [可选项] 使用bind绑定了用户ID时会额外增加此项信息
     * @var int
     */
    private $uid;

    public function __construct($data)
    {
        $this->reactorId = $data['reactor_id']??null;
        $this->serverFd = $data['server_fd']??null;
        $this->serverPort = $data['server_port']??null;
        $this->remotePort = $data['remote_port']??null;
        $this->remoteIp = $data['remote_ip']??null;
        $this->connectTime = $data['connect_time']??null;
        $this->lastTime = $data['last_time']??null;
        $this->closeErrno = $data['close_errno']??null;
        $this->websocketStatus = $data['websocket_status']??null;
        $this->sslClientCert = $data['ssl_client_cert']??null;
        $this->uid = $data['uid']??null;
    }

    /**
     * @return null
     */
    public function getReactorId()
    {
        return $this->reactorId;
    }

    /**
     * @return null
     */
    public function getServerFd()
    {
        return $this->serverFd;
    }

    /**
     * @return null
     */
    public function getServerPort()
    {
        return $this->serverPort;
    }

    /**
     * @return null
     */
    public function getRemotePort()
    {
        return $this->remotePort;
    }

    /**
     * @return null
     */
    public function getRemoteIp()
    {
        return $this->remoteIp;
    }

    /**
     * @return null
     */
    public function getConnectTime()
    {
        return $this->connectTime;
    }

    /**
     * @return null
     */
    public function getLastTime()
    {
        return $this->lastTime;
    }

    /**
     * @return null
     */
    public function getCloseErrno()
    {
        return $this->closeErrno;
    }

    /**
     * @return null
     */
    public function getWebsocketStatus()
    {
        return $this->websocketStatus;
    }

    /**
     * @return null
     */
    public function getSslClientCert()
    {
        return $this->sslClientCert;
    }

    /**
     * @return null
     */
    public function getUid()
    {
        return $this->uid;
    }
}