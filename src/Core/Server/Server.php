<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/15
 * Time: 13:43
 */

namespace ESD\Core\Server;

use DI\Container;
use ESD\Core\Config\ConfigContext;
use ESD\Core\Config\ConfigException;
use ESD\Core\Config\ConfigStarter;
use ESD\Core\Context\Context;
use ESD\Core\Context\ContextBuilder;
use ESD\Core\Context\ContextManager;
use ESD\Core\DI\DI;
use ESD\Core\Event\EventDispatcher;
use ESD\Core\PlugIn\PluginInterfaceManager;
use ESD\Core\Server\Beans\ClientInfo;
use ESD\Core\Server\Beans\Request;
use ESD\Core\Server\Beans\RequestProxy;
use ESD\Core\Server\Beans\Response;
use ESD\Core\Server\Beans\ResponseProxy;
use ESD\Core\Server\Beans\ServerStats;
use ESD\Core\Server\Beans\WebSocketFrame;
use ESD\Core\Server\Config\PortConfig;
use ESD\Core\Server\Config\ServerConfig;
use ESD\Core\Server\Port\PortManager;
use ESD\Core\Server\Port\ServerPort;
use ESD\Core\Server\Process\ManagerProcess;
use ESD\Core\Server\Process\MasterProcess;
use ESD\Core\Server\Process\Process;
use ESD\Core\Server\Process\ProcessManager;
use Psr\Log\LoggerInterface;

/**
 * Class Server
 * 封装了Server对象
 * @package ESD\Core\Server
 */
abstract class Server
{
    /**
     * @var Server
     */
    public static $instance;

    /**
     * 是否启动
     * @var bool
     */
    public static $isStart = false;
    /**
     * 服务器配置
     * @var ServerConfig
     */
    protected $serverConfig;

    /**
     * swoole的server
     * @var \Swoole\WebSocket\Server
     */
    protected $server;

    /**
     * 主要端口
     * @var ServerPort
     */
    private $mainPort;

    /**
     * @var ProcessManager
     */
    protected $processManager;

    /**
     * @var PortManager
     */
    protected $portManager;

    /**
     * @var PluginInterfaceManager
     */
    protected $plugManager;


    /**
     * 是否已配置
     * @var bool
     */
    private $configured = false;

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var EventDispatcher
     */
    protected $eventDispatcher;

    /**
     * @var ConfigContext
     */
    protected $configContext;

    /**
     * @var LoggerInterface
     */
    protected $log;

    /**
     * @var Container
     */
    protected $container;

    /**
     * 这里context获取不到任何插件，因为插件还没有加载
     * Server constructor.
     * @param ServerConfig $serverConfig
     * @param string $defaultPortClass
     * @param string $defaultProcessClass
     * @throws \ESD\Core\Exception
     */
    public function __construct(ServerConfig $serverConfig, string $defaultPortClass, string $defaultProcessClass)
    {
        self::$instance = $this;
        $this->serverConfig = $serverConfig;
        //获取DI容器
        $this->container = DI::getInstance($this->serverConfig)->getContainer();
        date_default_timezone_set('Asia/Shanghai');
        //注册Process的ContextBuilder
        $contextBuilder = ContextManager::getInstance()->getContextBuilder(ContextBuilder::SERVER_CONTEXT,
            function () {
                return new ServerContextBuilder($this);
            });
        $this->context = $contextBuilder->build();
        //初始化Event
        $this->eventDispatcher = new EventDispatcher($this);
        //初始化Config
        $configStarter = new ConfigStarter();
        $this->configContext = $configStarter->getConfigContext();
        $this->serverConfig->merge();
        //初始化Logger
        $this->log = DIGet(LoggerInterface::class);
        //-------------------------------------------------------------------------------------
        $this->portManager = new PortManager($this, $defaultPortClass);
        $this->processManager = new ProcessManager($this, $defaultProcessClass);
        //配置DI容器
        $this->container->set(LoggerInterface::class, $this->log);
        $this->container->set(EventDispatcher::class, $this->eventDispatcher);
        $this->container->set(ConfigContext::class, $this->configContext);
        $this->container->set(PortManager::class, $this->portManager);
        $this->container->set(ProcessManager::class, $this->processManager);
        $this->container->set(Response::class, new ResponseProxy());
        $this->container->set(Request::class, new RequestProxy());
        //设置Context
        $this->context->add("Logger", $this->log);
        $this->context->add("EventDispatcher", $this->eventDispatcher);
        $this->context->add("ConfigContext", $this->configContext);
        set_exception_handler(function ($e) {
            $this->log->error($e);
        });
        print_r($serverConfig->getBanner() . "\n");

        //获取上面这些后才能初始化plugManager
        $this->plugManager = new PluginInterfaceManager($this);
        $this->container->set(PluginInterfaceManager::class, $this->getPlugManager());
    }

    /**
     * 通过配置添加一个端口实例和用于初始化实例的class
     * @param string $name
     * @param PortConfig $portConfig
     * @param null $portClass
     * @throws ConfigException
     */
    public function addPort(string $name, PortConfig $portConfig, $portClass = null)
    {
        if ($this->isConfigured()) {
            throw new ConfigException("配置已锁定，请在调用configure前添加");
        }
        $this->portManager->addPortConfig($name, $portConfig, $portClass);
    }

    /**
     * 添加一个进程
     * @param string $name
     * @param null $processClass 不填写将用默认的
     * @param string $groupName
     * @throws ConfigException
     * @throws \ReflectionException
     */
    public function addProcess(string $name, $processClass = null, string $groupName = Process::DEFAULT_GROUP)
    {
        if ($this->isConfigured()) {
            throw new ConfigException("配置已锁定，请在调用configure前添加");
        }
        $this->processManager->addCustomProcessesConfig($name, $processClass, $groupName);
    }

    /**
     * 添加插件和添加配置只能在configure之前
     * 配置服务
     * @throws ConfigException
     * @throws \ESD\Core\Exception
     * @throws \ReflectionException
     */
    public function configure()
    {
        //先生成部分配置
        $this->getPortManager()->mergeConfig();
        $this->getProcessManager()->mergeConfig();
        //插件排序此时不允许添加插件了
        $this->plugManager->order();
        $this->plugManager->init($this->context);
        //调用所有插件的beforeServerStart
        $this->plugManager->beforeServerStart($this->context);
        //锁定配置
        $this->setConfigured(true);
        //设置主要进程
        $managerProcess = new ManagerProcess($this);
        $masterProcess = new MasterProcess($this);
        $this->processManager->setMasterProcess($masterProcess);
        $this->processManager->setManagerProcess($managerProcess);
        //设置进程名称
        Process::setProcessTitle($this->serverConfig->getName());
        //创建端口实例
        $this->getPortManager()->createPorts();
        //主要端口
        if ($this->portManager->hasWebSocketPort()) {
            foreach ($this->portManager->getPorts() as $serverPort) {
                if ($serverPort->isWebSocket()) {
                    $this->mainPort = $serverPort;
                    break;
                }
            }
            if ($this->serverConfig->getProxyServerClass() == null) {
                $this->server = new \Swoole\WebSocket\Server($this->mainPort->getPortConfig()->getHost(),
                    $this->mainPort->getPortConfig()->getPort(),
                    SWOOLE_PROCESS,
                    $this->mainPort->getPortConfig()->getSwooleSockType()
                );
            } else {
                $proxyClass = $this->serverConfig->getProxyServerClass();
                $this->server = new $proxyClass();
            }
        } else if ($this->portManager->hasHttpPort()) {
            foreach ($this->portManager->getPorts() as $serverPort) {
                if ($serverPort->isHttp()) {
                    $this->mainPort = $serverPort;
                    break;
                }
            }
            if ($this->serverConfig->getProxyServerClass() == null) {
                $this->server = new \Swoole\Http\Server($this->mainPort->getPortConfig()->getHost(),
                    $this->mainPort->getPortConfig()->getPort(),
                    SWOOLE_PROCESS,
                    $this->mainPort->getPortConfig()->getSwooleSockType()
                );
            } else {
                $proxyClass = $this->serverConfig->getProxyServerClass();
                $this->server = new $proxyClass();
            }
        } else {
            $this->mainPort = array_values($this->getPortManager()->getPorts())[0];
            if ($this->serverConfig->getProxyServerClass() == null) {
                $this->server = new \Swoole\Server($this->mainPort->getPortConfig()->getHost(),
                    $this->mainPort->getPortConfig()->getPort(),
                    SWOOLE_PROCESS,
                    $this->mainPort->getPortConfig()->getSwooleSockType()
                );
            } else {
                $proxyClass = $this->serverConfig->getProxyServerClass();
                $this->server = new $proxyClass();
            }
        }
        $portConfigData = $this->mainPort->getPortConfig()->buildConfig();
        $serverConfigData = $this->serverConfig->buildConfig();
        $serverConfigData = array_merge($portConfigData, $serverConfigData);
        $this->server->set($serverConfigData);
        //多个端口
        foreach ($this->portManager->getPorts() as $serverPort) {
            $serverPort->create();
        }
        //配置回调
        $this->server->on("start", [$this, "_onStart"]);
        $this->server->on("shutdown", [$this, "_onShutdown"]);
        $this->server->on("workerError", [$this, "_onWorkerError"]);
        $this->server->on("managerStart", [$this, "_onManagerStart"]);
        $this->server->on("managerStop", [$this, "_onManagerStop"]);
        $this->server->on("workerStart", [$this, "_onWorkerStart"]);
        $this->server->on("pipeMessage", [$this, "_onPipeMessage"]);
        $this->server->on("workerStop", [$this, "_onWorkerStop"]);
        //配置进程
        $this->processManager->createProcess();
        $this->configureReady();
        //打印配置
        $this->log->debug("打印配置:\n" . $this->configContext->getCacheContainYaml());
    }

    /**
     * 所有的配置插件已初始化好
     * @return mixed
     */
    abstract public function configureReady();

    /**
     * 启动
     */
    public function _onStart()
    {
        Server::$isStart = true;
        //发送ApplicationStartingEvent事件
        $this->eventDispatcher->dispatchEvent(new ApplicationEvent(ApplicationEvent::ApplicationStartingEvent, $this));
        $this->processManager->getMasterProcess()->onProcessStart();
        try {
            $this->onStart();
        } catch (\Throwable $e) {
            $this->log->error($e);
        }
    }

    /**
     * 关闭
     */
    public function _onShutdown()
    {
        //发送ApplicationShutdownEvent事件
        $this->eventDispatcher->dispatchEvent(new ApplicationEvent(ApplicationEvent::ApplicationShutdownEvent, $this));
        try {
            $this->onShutdown();
        } catch (\Throwable $e) {
            $this->log->error($e);
        }
    }

    public function _onWorkerError($serv, int $worker_id, int $worker_pid, int $exit_code, int $signal)
    {
        $process = $this->processManager->getProcessFromId($worker_id);
        $this->log->alert("workerId:$worker_id exitCode:$exit_code signal:$signal");
        try {
            $this->onWorkerError($process, $exit_code, $signal);
        } catch (\Throwable $e) {
            $this->log->error($e);
        }
    }

    public function _onManagerStart()
    {
        Server::$isStart = true;
        $this->processManager->getManagerProcess()->onProcessStart();
        try {
            $this->onManagerStart();
        } catch (\Throwable $e) {
            $this->log->error($e);
        }
    }

    public function _onManagerStop()
    {
        $this->processManager->getManagerProcess()->onProcessStop();
        try {
            $this->onManagerStop();
        } catch (\Throwable $e) {
            $this->log->error($e);
        }
    }

    public function _onWorkerStart($server, int $worker_id)
    {
        Server::$isStart = true;
        $process = $this->processManager->getProcessFromId($worker_id);
        $process->_onProcessStart();
    }

    public function _onPipeMessage($server, int $srcWorkerId, $message)
    {
        $this->processManager->getCurrentProcess()->_onPipeMessage($message, $this->processManager->getProcessFromId($srcWorkerId));
    }

    public function _onWorkerStop($server, int $worker_id)
    {
        $process = $this->processManager->getProcessFromId($worker_id);
        $process->_onProcessStop();
    }

    public abstract function onStart();

    public abstract function onShutdown();

    public abstract function onWorkerError(Process $process, int $exit_code, int $signal);

    public abstract function onManagerStart();

    public abstract function onManagerStop();

    /**
     * 启动服务
     */
    public function start()
    {
        if ($this->server == null) {
            throw new \Exception("请先调用configure");
        }
        $this->server->start();
    }


    /**
     * 获取swoole的server类
     * @return \Swoole\WebSocket\Server
     */
    public function getServer()
    {
        return $this->server;
    }

    /**
     * 获取主端口
     * @return mixed
     */
    public function getMainPort()
    {
        return $this->mainPort;
    }


    /**
     * TCP连接迭代器
     * @return \Iterator
     */
    public function getConnections(): \Iterator
    {
        return $this->server->connections;
    }

    /**
     * @return bool
     */
    public function isConfigured(): bool
    {
        return $this->configured;
    }

    /**
     * @param bool $configured
     */
    public function setConfigured(bool $configured): void
    {
        $this->configured = $configured;
    }

    /**
     * 获取连接的信息
     * @param int $fd
     * @return ClientInfo
     */
    public function getClientInfo(int $fd): ClientInfo
    {
        return new ClientInfo($this->server->getClientInfo($fd));
    }

    /**
     * 关闭客户端连接
     * $reset设置为true会强制关闭连接，丢弃发送队列中的数据
     * @param int $fd
     * @param bool $reset
     */
    public function closeFd(int $fd, bool $reset = false)
    {
        $this->server->close($fd, $reset);
    }

    /**
     * 自动判断是ws还是tcp
     * @param int $fd
     * @param string $data
     */
    public function autoSend(int $fd, string $data)
    {
        $clientInfo = $this->getClientInfo($fd);
        $port = $this->getPortManager()->getPortFromPortNo($clientInfo->getServerPort());
        if ($this->isEstablished($fd)) {
            $this->wsPush($fd, $data, $port->getPortConfig()->getWsOpcode());
        } else {
            $this->send($fd, $data);
        }
    }

    /**
     * 向客户端发送数据
     * @param int $fd 客户端的文件描述符
     * @param string $data 发送的数据
     * @param int $serverSocket 向Unix Socket DGRAM对端发送数据时需要此项参数，TCP客户端不需要填写
     * @return bool 发送成功会返回true
     */
    public function send(int $fd, string $data, int $serverSocket = -1): bool
    {
        return $this->server->send($fd, $data, $serverSocket);
    }

    /**
     * 发送文件到TCP客户端连接
     * @param int $fd
     * @param string $filename 要发送的文件路径，如果文件不存在会返回false
     * @param int $offset 指定文件偏移量，可以从文件的某个位置起发送数据。默认为0，表示从文件头部开始发送
     * @param int $length 指定发送的长度，默认为文件尺寸。
     * @return bool 操作成功返回true，失败返回false
     */
    public function sendFile(int $fd, string $filename, int $offset = 0, int $length = 0): bool
    {
        return $this->server->sendfile($fd, $filename, $offset, $length);
    }

    /**
     * 向任意的客户端IP:PORT发送UDP数据包。
     * 必须监听了UDP的端口，才可以使用向IPv4地址发送数据
     * 必须监听了UDP6的端口，才可以使用向IPv6地址发送数据
     * @param string $ip 为IPv4或IPv6字符串，如192.168.1.102。如果IP不合法会返回错误
     * @param int $port 为 1-65535的网络端口号，如果端口错误发送会失败
     * @param string $data 要发送的数据内容，可以是文本或者二进制内容
     * @param int $server_socket 服务器可能会同时监听多个UDP端口，此参数可以指定使用哪个端口发送数据包
     * @return bool
     */
    public function sendToUpd(string $ip, int $port, string $data, int $server_socket = -1): bool
    {
        return $this->server->sendto($ip, $port, $data, $server_socket);
    }

    /**
     * 检测fd对应的连接是否存在。
     * @param $fd
     * @return bool
     */
    public function existFd($fd): bool
    {
        return $this->server->exist($fd);
    }

    /**
     * 将连接绑定一个用户定义的UID，可以设置dispatch_mode=5设置以此值进行hash固定分配。可以保证某一个UID的连接全部会分配到同一个Worker进程。
     * @param int $fd
     * @param int $uid
     */
    public function bindUid(int $fd, int $uid)
    {
        $this->server->bind($fd, $uid);
    }

    /**
     * 得到当前Server的活动TCP连接数，启动时间，accpet/close的总次数等信息。
     * @return ServerStats
     */
    public function stats(): ServerStats
    {
        return new ServerStats($this->server->stats());
    }

    /**
     * 检测服务器所有连接，并找出已经超过约定时间的连接。如果指定if_close_connection，则自动关闭超时的连接。未指定仅返回连接的fd数组。
     * 调用成功将返回一个连续数组，元素是已关闭的$fd
     * 调用失败返回false
     * @param bool $if_close_connection
     * @return array
     */
    public function heartbeat(bool $if_close_connection = true): array
    {
        return $this->server->heartbeat($if_close_connection);
    }

    /**
     * 获取最近一次操作错误的错误码。业务代码中可以根据错误码类型执行不同的逻辑。
     * 1001 连接已经被Server端关闭了，出现这个错误一般是代码中已经执行了$serv->close()关闭了某个连接，但仍然调用$serv->send()向这个连接发送数据
     * 1002 连接已被Client端关闭了，Socket已关闭无法发送数据到对端
     * 1003 正在执行close，onClose回调函数中不得使用$serv->send()
     * 1004 连接已关闭
     * 1005 连接不存在，传入$fd 可能是错误的
     * 1007 接收到了超时的数据，TCP关闭连接后，可能会有部分数据残留在管道缓存区内，这部分数据会被丢弃
     * 1008 发送缓存区已满无法执行send操作，出现这个错误表示这个连接的对端无法及时收数据导致发送缓存区已塞满
     * 1202 发送的数据超过了 Server->buffer_output_size 设置
     * @return int
     */
    public function getLastError(): int
    {
        return $this->server->getLastError();
    }

    /**
     * 设置客户端连接为保护状态，不被心跳线程切断。
     * $value 设置的状态，true表示保护状态，false表示不保护
     * @param int $fd
     * @param bool $value
     */
    public function protect(int $fd, bool $value = true)
    {
        $this->server->protect($fd, $value);
    }

    /**
     * 确认连接，与enable_delay_receive配合使用。
     * 当客户端建立连接后，并不监听可读事件。
     * 仅触发onConnect事件回调，在onConnect回调中执行confirm确认连接，这时服务器才会监听可读事件，接收来自客户端连接的数据。
     * @param int $fd
     */
    public function confirm(int $fd)
    {
        $this->server->confirm($fd);
    }

    /**
     * 重启所有Worker/Task进程。
     */
    public function reload()
    {
        $this->server->reload();
    }

    /**
     * 关闭服务器
     */
    public function shutdown()
    {
        $this->server->shutdown();
    }

    /**
     * 延后执行一个PHP函数
     * @param callable $callback
     */
    public function defer(callable $callback)
    {
        $this->server->defer($callback);
    }

    /**
     * 向websocket客户端连接推送数据，长度最大不得超过2M。
     * @param int $fd
     * @param $data
     * @param int $opcode
     * @param bool $finish
     * @return bool
     */
    public function wsPush(int $fd, $data, int $opcode = 1, bool $finish = true): bool
    {
        return $this->server->push($fd, $data, $opcode, $finish);
    }

    /**
     * 主动向websocket客户端发送关闭帧并关闭该连接
     * @param int $fd
     * @param int $code 关闭连接的状态码，根据RFC6455，对于应用程序关闭连接状态码，取值范围为1000或4000-4999之间
     * @param string $reason 关闭连接的原因，utf-8格式字符串，字节长度不超过125
     * @return bool
     */
    public function wsDisconnect(int $fd, int $code = 1000, string $reason = ""): bool
    {
        return $this->server->disconnect($fd, $code, $reason);
    }

    /**
     * 检查连接是否为有效的WebSocket客户端连接。
     * 此函数与exist方法不同，exist方法仅判断是否为TCP连接，无法判断是否为已完成握手的WebSocket客户端。
     * @param int $fd
     * @return bool
     */
    public function isEstablished(int $fd): bool
    {
        return $this->server->isEstablished($fd);
    }

    /**
     * 打包WebSocket消息
     * 返回打包好的WebSocket数据包，可通过Socket发送给对端
     * @param WebSocketFrame $webSocketFrame 消息内容
     * @param bool $mask 是否设置掩码
     * @return string
     */
    public function wsPack(WebSocketFrame $webSocketFrame, $mask = false): string
    {
        return $this->server->pack($webSocketFrame->getData(), $webSocketFrame->getOpcode(), $webSocketFrame->getFinish(), $mask);
    }

    /**
     * 解析WebSocket数据帧
     * 解析失败返回false
     * @param string $data
     * @return WebSocketFrame
     */
    public function wsUnPack(string $data): WebSocketFrame
    {
        return new WebSocketFrame($this->server->unpack($data));
    }

    /**
     * @return ProcessManager
     */
    public function getProcessManager(): ProcessManager
    {
        return $this->processManager;
    }

    /**
     * @return PortManager
     */
    public function getPortManager(): PortManager
    {
        return $this->portManager;
    }

    /**
     * @return PluginInterfaceManager
     */
    public function getPlugManager(): PluginInterfaceManager
    {
        return $this->plugManager;
    }

    /**
     * @return Context
     */
    public function getContext(): Context
    {
        return $this->context;
    }

    /**
     * @return EventDispatcher
     */
    public function getEventDispatcher(): EventDispatcher
    {
        return $this->eventDispatcher;
    }

    /**
     * @return ServerConfig
     */
    public function getServerConfig(): ServerConfig
    {
        return $this->serverConfig;
    }

    /**
     * @return LoggerInterface
     */
    public function getLog(): LoggerInterface
    {
        return $this->log;
    }

    /**
     * @return ConfigContext
     */
    public function getConfigContext(): ConfigContext
    {
        return $this->configContext;
    }

    /**
     * @return Container|null
     */
    public function getContainer(): ?Container
    {
        return $this->container;
    }
}