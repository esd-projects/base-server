<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/15
 * Time: 14:01
 */

namespace ESD\Core\Server\Config;

use ESD\Core\Config\BaseConfig;
use ESD\Core\Config\ConfigException;

/**
 * 端口配置
 * Class PortConfig
 * @package ESD\Core\Server\Config
 */
class PortConfig extends BaseConfig
{
    const SWOOLE_SOCK_TCP = SWOOLE_SOCK_TCP;
    const SWOOLE_SOCK_TCP6 = SWOOLE_SOCK_TCP6;
    const SWOOLE_SOCK_UDP = SWOOLE_SOCK_UDP;
    const SWOOLE_SOCK_UDP6 = SWOOLE_SOCK_UDP6;
    const SWOOLE_SOCK_UNIX_DGRAM = SWOOLE_SOCK_UNIX_DGRAM;
    const SWOOLE_SOCK_UNIX_STREAM = SWOOLE_SOCK_UNIX_STREAM;
    const SWOOLE_SSL = SWOOLE_SSL;

    const SWOOLE_SSLv3_METHOD = SWOOLE_SSLv3_METHOD;
    const SWOOLE_SSLv3_SERVER_METHOD = SWOOLE_SSLv3_SERVER_METHOD;
    const SWOOLE_SSLv3_CLIENT_METHOD = SWOOLE_SSLv3_CLIENT_METHOD;
    const SWOOLE_SSLv23_METHOD = SWOOLE_SSLv23_METHOD;
    const SWOOLE_SSLv23_SERVER_METHOD = SWOOLE_SSLv23_SERVER_METHOD;
    const SWOOLE_SSLv23_CLIENT_METHOD = SWOOLE_SSLv23_CLIENT_METHOD;
    const SWOOLE_TLSv1_METHOD = SWOOLE_TLSv1_METHOD;
    const SWOOLE_TLSv1_SERVER_METHOD = SWOOLE_TLSv1_SERVER_METHOD;
    const SWOOLE_TLSv1_CLIENT_METHOD = SWOOLE_TLSv1_CLIENT_METHOD;
    const SWOOLE_TLSv1_1_METHOD = SWOOLE_TLSv1_1_METHOD;
    const SWOOLE_TLSv1_1_SERVER_METHOD = SWOOLE_TLSv1_1_SERVER_METHOD;
    const SWOOLE_TLSv1_1_CLIENT_METHOD = SWOOLE_TLSv1_1_CLIENT_METHOD;
    const SWOOLE_TLSv1_2_METHOD = SWOOLE_TLSv1_2_METHOD;
    const SWOOLE_TLSv1_2_SERVER_METHOD = SWOOLE_TLSv1_2_SERVER_METHOD;
    const SWOOLE_TLSv1_2_CLIENT_METHOD = SWOOLE_TLSv1_2_CLIENT_METHOD;
    const SWOOLE_DTLSv1_METHOD = SWOOLE_DTLSv1_METHOD;
    const SWOOLE_DTLSv1_SERVER_METHOD = SWOOLE_DTLSv1_SERVER_METHOD;
    const SWOOLE_DTLSv1_CLIENT_METHOD = SWOOLE_DTLSv1_CLIENT_METHOD;

    const WEBSOCKET_OPCODE_TEXT = WEBSOCKET_OPCODE_TEXT;
    const WEBSOCKET_OPCODE_BINARY = WEBSOCKET_OPCODE_BINARY;
    const WEBSOCKET_OPCODE_PING = WEBSOCKET_OPCODE_PING;
    const WEBSOCKET_STATUS_CONNECTION = WEBSOCKET_STATUS_CONNECTION;
    const WEBSOCKET_STATUS_HANDSHAKE = WEBSOCKET_STATUS_HANDSHAKE;
    const WEBSOCKET_STATUS_FRAME = WEBSOCKET_STATUS_FRAME;

    const key = "esd.port";
    /**
     * 名称
     * @var string
     */
    protected $name;
    /**
     * @var string
     */
    protected $portClass;
    /**
     * 需要监听的ip地址默认为"0.0.0.0"
     * IPv4使用 127.0.0.1表示监听本机，0.0.0.0表示监听所有地址
     * IPv6使用::1表示监听本机，:: (相当于0:0:0:0:0:0:0:0) 表示监听所有地址
     * @var string
     */
    protected $host;
    /**
     * 需要监听的端口
     * 监听小于1024端口需要root权限
     * @var int
     */
    protected $port;
    /**
     * sock类型默认为SWOOLE_SOCK_TCP
     * @var int
     */
    protected $sockType;
    /**
     * 是否启动SSL
     * @var bool
     */
    protected $enableSsl;
    /**
     * Listen队列长度,此参数将决定最多同时有多少个待accept的连接
     * @var int
     */
    protected $backlog;
    /**
     * TCP_NoDelay open_tcp_nodelay => true ,启用tcp_nodelay
     * 启用open_tcp_nodelay，开启后TCP连接发送数据时会关闭Nagle合并算法，立即发往客户端连接。在某些场景下，如http服务器，可以提升响应速度。
     * 默认情况下，发送数据采用Nagle 算法。这样虽然提高了网络吞吐量，但是实时性却降低了，在一些交互性很强的应用程序来说是不允许的，使用TCP_NODELAY选项可以禁止Nagle 算法。
     * @var bool
     */
    protected $openTcpNodelay;
    /**
     * 开启TCP快速握手特性。此项特性，可以提升TCP短连接的响应速度，在客户端完成握手的第三步，发送SYN包时携带数据。
     * @var bool
     */
    protected $tcpFastopen;
    /**
     * 此参数设定一个秒数，当客户端连接连接到服务器时，在约定秒数内并不会触发accept，直到有数据发送，或者超时时才会触发。
     * @var int
     */
    protected $tcpDeferAccept;
    /**
     * 打开EOF检测，此选项将检测客户端连接发来的数据，当数据包结尾是指定的字符串时才会投递给Worker进程。
     * 否则会一直拼接数据包，直到超过缓存区或者超时才会中止。当出错时底层会认为是恶意连接，丢弃数据并强制关闭连接。
     * @var bool
     */
    protected $openEofCheck;
    /**
     * 启用EOF自动分包。
     * 当设置open_eof_check后，底层检测数据是否以特定的字符串结尾来进行数据缓冲,但默认只截取收到数据的末尾部分做对比,这时候可能会产生多条数据合并在一个包内。
     * @var bool
     */
    protected $openEofSplit;
    /**
     * 与 open_eof_check 或者 open_eof_split 配合使用，设置EOF字符串。
     * @var string
     */
    protected $packageEof;
    /**
     * 打开包长检测特性。包长检测提供了固定包头+包体这种格式协议的解析。启用后，可以保证Worker进程onReceive每次都会收到一个完整的数据包。
     * @var bool
     */
    protected $openLengthCheck;
    /**
     * 长度值的类型，接受一个字符参数，与php的 pack 函数一致。目前Swoole支持10种类型：
     * c：有符号、1字节
     * C：无符号、1字节
     * s ：有符号、主机字节序、2字节
     * S：无符号、主机字节序、2字节
     * n：无符号、网络字节序、2字节
     * N：无符号、网络字节序、4字节
     * l：有符号、主机字节序、4字节（小写L）
     * L：无符号、主机字节序、4字节（大写L）
     * v：无符号、小端字节序、2字节
     * V：无符号、小端字节序、4字节
     * @var string
     */
    protected $packageLengthType;
    /**
     * 设置最大数据包尺寸，单位为字节
     * @var int
     */
    protected $packageMaxLength;
    /**
     * 从第几个字节开始计算长度，一般有2种情况：
     * length的值包含了整个包（包头+包体），package_body_offset 为0
     * 包头长度为N字节，length的值不包含包头，仅包含包体，package_body_offset设置为N
     * @var int
     */
    protected $packageBodyOffset;
    /**
     * length长度值在包头的第几个字节。
     * @var int
     */
    protected $packageLengthOffset;
    /**
     * 设置SSL隧道加密，设置值为一个文件名字符串，制定cert证书和key私钥的路径。
     * https应用浏览器必须信任证书才能浏览网页
     * wss应用中，发起WebSocket连接的页面必须使用https
     * 浏览器不信任SSL证书将无法使用wss
     * 文件必须为PEM格式，不支持DER格式，可使用openssl工具进行转换
     * @var string
     */
    protected $sslCertFile;
    /**
     * 启用SSL后，设置ssl_ciphers来改变openssl默认的加密算法。Swoole底层默认使用EECDH+AESGCM:EDH+AESGCM:AES256+EECDH:AES256+EDH
     * @var string
     */
    protected $sslCiphers;
    /**
     * 设置OpenSSL隧道加密的算法。Server与Client使用的算法必须一致，否则SSL/TLS握手会失败，连接会被切断。 默认算法为 SWOOLE_SSLv23_METHOD
     * @var string
     */
    protected $sslMethod;
    /**
     * 启用Http协议处理
     * @var bool
     */
    protected $openHttpProtocol;
    /**
     * 启用websocket协议处理
     * @var bool
     */
    protected $openWebsocketProtocol;
    /**
     * 启用mqtt协议处理，启用后会解析mqtt包头，worker进程onReceive每次会返回一个完整的mqtt数据包。
     * @var bool
     */
    protected $openMqttProtocol;
    /**
     * 启用websocket协议中关闭帧（opcode为0x08的帧）在onMessage回调中接收，默认为false。
     * 开启后，可在WebSocketServer中的onMessage回调中接收到客户端或服务端发送的关闭帧，开发者可自行对其进行处理。
     * @var bool
     */
    protected $openWebsocketCloseFrame;

    /**
     * 服务SSL设置验证对端证书。默认关闭，即不验证客户端证书。若开启，必须同时设置 ssl_client_cert_file选项
     * @var bool
     */
    protected $sslVerifyPeer;
    /**
     * 服务SSL设置验证对端证书
     * @var string
     */
    protected $sslClientCertFile;
    /**
     * 设置端口重用，此参数用于优化TCP连接的Accept性能，启用端口重用后多个进程可以同时进行Accept操作。
     * @var bool
     */
    protected $enableReusePort;
    /**
     * 设置此选项为true后，accept客户端连接后将不会自动加入EventLoop，仅触发onConnect回调。
     * worker进程可以调用$serv->confirm($fd)对连接进行确认，此时才会将fd加入EventLoop开始进行数据收发，也可以调用$serv->close($fd)关闭此连接。
     * @var bool
     */
    protected $enableDelayReceive;

    /**
     * 是否自定义握手
     * @var bool
     */
    protected $customHandShake = false;

    /**
     * @var int
     */
    protected $wsOpcode = self::WEBSOCKET_OPCODE_TEXT;

    public function __construct()
    {
        parent::__construct(self::key, true, "name");
    }

    /**
     * @return int
     */
    public function getBacklog()
    {
        return $this->backlog ?? 128;
    }

    /**
     * @param int $backlog
     */
    public function setBacklog(int $backlog)
    {
        $this->backlog = $backlog;
    }

    /**
     * @return bool
     */
    public function isOpenTcpNodelay()
    {
        return $this->openTcpNodelay ?? true;
    }

    /**
     * @param bool $openTcpNodelay
     */
    public function setOpenTcpNodelay(bool $openTcpNodelay)
    {
        $this->openTcpNodelay = $openTcpNodelay;
    }

    /**
     * @return bool
     */
    public function isTcpFastopen()
    {
        return $this->tcpFastopen ?? true;
    }

    /**
     * @param bool $tcpFastopen
     */
    public function setTcpFastopen(bool $tcpFastopen)
    {
        $this->tcpFastopen = $tcpFastopen;
    }

    /**
     * @return int
     */
    public function getTcpDeferAccept()
    {
        return $this->tcpDeferAccept ?? null;
    }

    /**
     * @param int $tcpDeferAccept
     */
    public function setTcpDeferAccept(int $tcpDeferAccept)
    {
        $this->tcpDeferAccept = $tcpDeferAccept;
    }

    /**
     * @return bool
     */
    public function isOpenEofCheck()
    {
        return $this->openEofCheck ?? false;
    }

    /**
     * @param bool $openEofCheck
     */
    public function setOpenEofCheck(bool $openEofCheck)
    {
        $this->openEofCheck = $openEofCheck;
    }

    /**
     * @return bool
     */
    public function isOpenEofSplit()
    {
        return $this->openEofSplit ?? false;
    }

    /**
     * @param bool $openEofSplit
     */
    public function setOpenEofSplit(bool $openEofSplit)
    {
        $this->openEofSplit = $openEofSplit;
    }

    /**
     * @return string
     */
    public function getPackageEof()
    {
        return $this->packageEof;
    }

    /**
     * @param string $packageEof
     */
    public function setPackageEof(string $packageEof)
    {
        $this->packageEof = $packageEof;
    }

    /**
     * @return bool
     */
    public function isOpenLengthCheck()
    {
        return $this->openLengthCheck ?? false;
    }

    /**
     * @param bool $openLengthCheck
     */
    public function setOpenLengthCheck(bool $openLengthCheck)
    {
        $this->openLengthCheck = $openLengthCheck;
    }

    /**
     * @return string
     */
    public function getPackageLengthType()
    {
        return $this->packageLengthType;
    }

    /**
     * @param string $packageLengthType
     */
    public function setPackageLengthType(string $packageLengthType)
    {
        $this->packageLengthType = $packageLengthType;
    }

    /**
     * @return int
     */
    public function getPackageMaxLength()
    {
        return $this->packageMaxLength;
    }

    /**
     * @param int $packageMaxLength
     */
    public function setPackageMaxLength(int $packageMaxLength)
    {
        $this->packageMaxLength = $packageMaxLength;
    }

    /**
     * @return int
     */
    public function getPackageBodyOffset()
    {
        return $this->packageBodyOffset;
    }

    /**
     * @param int $packageBodyOffset
     */
    public function setPackageBodyOffset(int $packageBodyOffset)
    {
        $this->packageBodyOffset = $packageBodyOffset;
    }

    /**
     * @return int
     */
    public function getPackageLengthOffset()
    {
        return $this->packageLengthOffset;
    }

    /**
     * @param int $packageLengthOffset
     */
    public function setPackageLengthOffset(int $packageLengthOffset)
    {
        $this->packageLengthOffset = $packageLengthOffset;
    }

    /**
     * @return string
     */
    public function getSslCertFile()
    {
        return $this->sslCertFile;
    }

    /**
     * @param string $sslCertFile
     */
    public function setSslCertFile(string $sslCertFile)
    {
        $this->sslCertFile = $sslCertFile;
    }

    /**
     * @return string
     */
    public function getSslCiphers()
    {
        return $this->sslCiphers;
    }

    /**
     * @param string $sslCiphers
     */
    public function setSslCiphers(string $sslCiphers)
    {
        $this->sslCiphers = $sslCiphers;
    }

    /**
     * @return bool
     */
    public function isOpenHttpProtocol()
    {
        return $this->openHttpProtocol ?? false;
    }

    /**
     * @param bool $openHttpProtocol
     */
    public function setOpenHttpProtocol(bool $openHttpProtocol)
    {
        $this->openHttpProtocol = $openHttpProtocol;
    }

    /**
     * @return bool
     */
    public function isOpenWebsocketProtocol()
    {
        return $this->openWebsocketProtocol ?? false;
    }

    /**
     * @param bool $openWebsocketProtocol
     */
    public function setOpenWebsocketProtocol(bool $openWebsocketProtocol)
    {
        $this->openWebsocketProtocol = $openWebsocketProtocol;
    }

    /**
     * @return bool
     */
    public function isOpenMqttProtocol()
    {
        return $this->openMqttProtocol ?? false;
    }

    /**
     * @param bool $openMqttProtocol
     */
    public function setOpenMqttProtocol(bool $openMqttProtocol)
    {
        $this->openMqttProtocol = $openMqttProtocol;
    }

    /**
     * @return bool
     */
    public function isOpenWebsocketCloseFrame()
    {
        return $this->openWebsocketCloseFrame ?? false;
    }

    /**
     * @param bool $openWebsocketCloseFrame
     */
    public function setOpenWebsocketCloseFrame(bool $openWebsocketCloseFrame)
    {
        $this->openWebsocketCloseFrame = $openWebsocketCloseFrame;
    }

    /**
     * @return bool
     */
    public function isSslVerifyPeer()
    {
        return $this->sslVerifyPeer ?? false;
    }

    /**
     * @param bool $sslVerifyPeer
     */
    public function setSslVerifyPeer(bool $sslVerifyPeer)
    {
        $this->sslVerifyPeer = $sslVerifyPeer;
    }

    /**
     * @return bool
     */
    public function isEnableReusePort()
    {
        return $this->enableReusePort ?? false;
    }

    /**
     * @param bool $enableReusePort
     */
    public function setEnableReusePort(bool $enableReusePort)
    {
        $this->enableReusePort = $enableReusePort;
    }

    /**
     * @return bool
     */
    public function isEnableDelayReceive()
    {
        return $this->enableDelayReceive ?? false;
    }

    /**
     * @param bool $enableDelayReceive
     */
    public function setEnableDelayReceive(bool $enableDelayReceive)
    {
        $this->enableDelayReceive = $enableDelayReceive;
    }

    /**
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @param int $port
     */
    public function setPort(int $port)
    {
        $this->port = $port;
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @param string $host
     */
    public function setHost(string $host)
    {
        $this->host = $host;
    }

    /**
     * @return int
     */
    public function getSockType()
    {
        return $this->sockType;
    }

    /**
     * @param int $sockType
     */
    public function setSockType(int $sockType)
    {
        $this->sockType = $sockType;
    }

    /**
     * @return bool
     */
    public function isEnableSsl()
    {
        return $this->enableSsl ?? false;
    }

    /**
     * @param bool $enableSsl
     */
    public function setEnableSsl(bool $enableSsl)
    {
        $this->enableSsl = $enableSsl;
    }

    /**
     * @return string
     */
    public function getSslMethod()
    {
        return $this->sslMethod;
    }

    /**
     * @param string $sslMethod
     */
    public function setSslMethod(string $sslMethod)
    {
        $this->sslMethod = $sslMethod;
    }

    /**
     * @return string
     */
    public function getSslClientCertFile()
    {
        return $this->sslClientCertFile;
    }

    /**
     * @param string $sslClientCertFile
     */
    public function setSslClientCertFile(string $sslClientCertFile)
    {
        $this->sslClientCertFile = $sslClientCertFile;
    }

    /**
     * 获取swoole使用的SockType配置
     * @return int
     * @throws ConfigException
     */
    public function getSwooleSockType()
    {
        ConfigException::AssertNull($this, "sockType", $this->getSockType());
        if ($this->isEnableSsl()) {
            return $this->getSockType() | self::SWOOLE_SSL;
        } else {
            return $this->getSockType();
        }
    }

    /**
     * @return bool
     */
    public function isCustomHandShake()
    {
        return $this->customHandShake;
    }

    /**
     * @param bool $customHandShake
     */
    public function setCustomHandShake(bool $customHandShake)
    {
        $this->customHandShake = $customHandShake;
    }


    /**
     * 构建配置
     * @return array
     * @throws ConfigException
     */
    public function buildConfig(): array
    {
        $build = [];
        ConfigException::AssertNull($this, "host", $this->getHost());
        ConfigException::AssertNull($this, "port", $this->getPort());
        ConfigException::AssertNull($this, "sockType", $this->getSockType());
        if ($this->isEnableSsl()) {
            ConfigException::AssertNull($this, "sslCertFile", $this->getSslCertFile());
            $build['ssl_cert_file'] = $this->getSslCertFile();
            if ($this->getSslCiphers() != null) {
                $build['ssl_ciphers'] = $this->getSslCiphers();
            }
            if ($this->getSslMethod() != null) {
                $build['ssl_method'] = $this->getSslMethod();
            }
        }
        $build['backlog'] = $this->getBacklog();
        $build['open_tcp_nodelay'] = $this->isOpenTcpNodelay();
        $build['tcp_fastopen'] = $this->isTcpFastopen();
        $build['enable_delay_receive'] = $this->isEnableDelayReceive();
        if ($this->getTcpDeferAccept() != null) {
            $build['tcp_defer_accept'] = $this->getTcpDeferAccept();
        }
        if ($this->isSslVerifyPeer()) {
            ConfigException::AssertNull($this, "sslClientCertFile", $this->getSSlClientCertFile());
            $build['ssl_verify_peer'] = $this->isSslVerifyPeer();
            $build['ssl_client_cert_file'] = $this->getSSlClientCertFile();
        }
        if (!$this->isOpenHttpProtocol() &&
            !$this->isOpenWebsocketProtocol() &&
            !$this->isOpenMqttProtocol() &&
            !$this->isOpenEofCheck() &&
            !$this->isOpenEofSplit() &&
            !$this->isOpenLengthCheck()) {
            throw new ConfigException("PortConfig中没有指定端口协议");
        }
        $count = 0;
        if ($this->isOpenHttpProtocol()) {
            $count++;
            $build['open_http_protocol'] = $this->isOpenHttpProtocol();
        }
        if ($this->isOpenWebsocketProtocol()) {
            $count++;
            $build['open_websocket_protocol'] = $this->isOpenWebsocketProtocol();
            $build['open_websocket_close_frame'] = $this->isOpenWebsocketCloseFrame();
        }
        if ($this->isOpenMqttProtocol()) {
            $count++;
            $build['open_mqtt_protocol'] = $this->isOpenMqttProtocol();
        }
        if ($this->isOpenEofCheck()) {
            $count++;
            $build['open_eof_check'] = $this->isOpenEofCheck();
            ConfigException::AssertNull($this, "packageEof", $this->getPackageEof());
            $build['package_eof'] = $this->getPackageEof();
        }
        if ($this->isOpenEofSplit()) {
            $count++;
            $build['open_eof_split'] = $this->isOpenEofSplit();
            ConfigException::AssertNull($this, "packageEof", $this->getPackageEof());
            $build['package_eof'] = $this->getPackageEof();
        }
        if ($this->isOpenLengthCheck()) {
            $count++;
            $build['open_length_check'] = $this->isOpenLengthCheck();
            ConfigException::AssertNull($this, "packageLengthOffset", $this->getPackageLengthOffset());
            $build['package_length_offset'] = $this->getPackageLengthOffset();
            ConfigException::AssertNull($this, "packageLengthType", $this->getPackageLengthType());
            $build['package_length_type'] = $this->getPackageLengthType();
            ConfigException::AssertNull($this, "packageBodyOffset", $this->getPackageBodyOffset());
            $build['package_body_offset'] = $this->getPackageBodyOffset();
            if ($this->getPackageMaxLength() != null && $this->getPackageMaxLength() > 0) {
                $build['package_max_length'] = $this->getPackageMaxLength();
            }
        }
        if ($count > 1) {
            throw new ConfigException("PortConfig中只能指定一种协议");
        }
        if ($this->isEnableReusePort()) {
            $build['enable_reuse_port'] = $this->isEnableReusePort();
        }
        return $build;
    }

    /**
     * 获取类型名称
     */
    public function getTypeName()
    {
        if ($this->isOpenWebsocketProtocol()) {
            return "WebSocket";
        }
        if ($this->isOpenHttpProtocol()) {
            return "HTTP";
        }
        if ($this->isOpenMqttProtocol()) {
            return "MQTT";
        }
        if ($this->getSwooleSockType() == self::SWOOLE_SOCK_UDP || $this->getSwooleSockType() == self::SWOOLE_SOCK_UDP6) {
            return "UDP";
        } else {
            if ($this->isOpenEofSplit() || $this->isOpenEofCheck()) {
                return "TCP-EOF";
            } else if ($this->isOpenLengthCheck()) {
                return "TCP-Length";
            } else {
                return "TCP";
            }
        }
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getPortClass()
    {
        return $this->portClass;
    }

    /**
     * @param string $portClass
     */
    public function setPortClass(string $portClass): void
    {
        $this->portClass = $portClass;
    }

    /**
     * @return int
     */
    public function getWsOpcode(): int
    {
        return $this->wsOpcode;
    }

    /**
     * @param int $wsOpcode
     */
    public function setWsOpcode(int $wsOpcode): void
    {
        $this->wsOpcode = $wsOpcode;
    }

    /**
     * 获取基础类型
     * @return string
     * @throws ConfigException
     */
    public function getBaseType(): string
    {
        if ($this->isOpenHttpProtocol()) {
            return "http";
        }
        if ($this->isOpenWebsocketProtocol()) {
            return "ws";
        }
        if ($this->getSwooleSockType() == self::SWOOLE_SOCK_TCP || $this->getSwooleSockType() == self::SWOOLE_SOCK_TCP6) {
            return "tcp";
        }
        if ($this->getSwooleSockType() == self::SWOOLE_SOCK_UDP || $this->getSwooleSockType() == self::SWOOLE_SOCK_UDP6) {
            return "udp";
        }
        return "unknown";
    }
}