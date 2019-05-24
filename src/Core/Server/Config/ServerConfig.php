<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/15
 * Time: 13:45
 */

namespace ESD\Core\Server\Config;

use ESD\Core\Config\BaseConfig;
use ESD\Core\Config\ConfigException;
use ESD\Core\Exception;

/**
 * 服务配置
 * Class ServerConfig
 * @package ESD\Core\Server\Config
 */
class ServerConfig extends BaseConfig
{
    const key = "esd.server";
    /**
     * 服务器名称
     * @var string
     */
    protected $name = "esd";

    /**
     * 根目录
     * @var string
     */
    protected $rootDir;
    /**
     * reactor线程数，通过此参数来调节Reactor线程的数量，以充分利用多核
     * @var int
     */
    protected $reactorNum;
    /**
     * worker进程数,设置启动的Worker进程数量
     * @var int
     */
    protected $workerNum;
    /**
     * 数据包分发策略。可以选择7种类型，默认为2
     * 1，轮循模式，收到会轮循分配给每一个Worker进程
     * 2，固定模式，根据连接的文件描述符分配Worker。这样可以保证同一个连接发来的数据只会被同一个Worker处理
     * 3，抢占模式，主进程会根据Worker的忙闲状态选择投递，只会投递给处于闲置状态的Worker
     * 4，IP分配，根据客户端IP进行取模hash，分配给一个固定的Worker进程。可以保证同一个来源IP的连接数据总会被分配到同一个Worker进程。算法为 ip2long(ClientIP) % worker_num
     * 5，UID分配，需要用户代码中调用 Server->bind() 将一个连接绑定1个uid。然后底层根据UID的值分配到不同的Worker进程。算法为 UID % worker_num，如果需要使用字符串作为UID，可以使用crc32(UID_STRING)
     * 7，stream模式，空闲的Worker会accept连接，并接受Reactor的新请求
     * @var int
     */
    protected $dispatchMode;
    /**
     * 最大连接
     * @var int
     */
    protected $maxConn;
    /**
     * @var string|null
     */
    protected $proxyServerClass = null;
    /**
     * 守护进程化 daemonize => 1，加入此参数后，将转入后台作为守护进程运行
     * @var bool
     */
    protected $daemonize;
    /**
     * 设置异步重启开关。设置为true时，将启用异步安全重启特性，Worker进程会等待异步事件完成后再退出
     * @var bool
     */
    protected $reloadAsync;
    /**
     * Worker进程收到停止服务通知后最大等待时间，默认为30秒
     * @var int
     */
    protected $maxWaitTime;
    /**
     * CPU亲和设置 open_cpu_affinity => 1 ,启用CPU亲和设置
     * 在多核的硬件平台中，启用此特性会将swoole的reactor线程/worker进程绑定到固定的一个核上。可以避免进程/线程的运行时在多个核之间互相切换，提高CPU Cache的命中率。
     * @var bool
     */
    protected $openCpuAffinity;
    /**
     * 接受一个数组作为参数，array(0, 1) 表示不使用CPU0,CPU1，专门空出来处理网络中断。
     * @var array
     */
    protected $cpuAffinityIgnore;

    /**
     * log_file => '/data/log/swoole.log', 指定swoole错误日志文件。在swoole运行期发生的异常信息会记录到这个文件中。默认会打印到屏幕。
     * @var string
     */
    protected $logFile;

    /**
     * 设置Server错误日志打印的等级，范围是0-5。低于log_level设置的日志信息不会抛出。
     * 0 => SWOOLE_LOG_DEBUG
     * 1 => SWOOLE_LOG_TRACE
     * 2 => SWOOLE_LOG_INFO
     * 3 => SWOOLE_LOG_NOTICE
     * 4 => SWOOLE_LOG_WARNING
     * 5 => SWOOLE_LOG_ERROR
     * SWOOLE_LOG_DEBUG和SWOOLE_LOG_TRACE仅在编译为--enable-debug-log和--enable-trace-log版本时可用
     * 默认为SWOOLE_LOG_DEBUG也就是所有级别都打印
     * @var string
     */
    protected $logLevel;
    /**
     * 心跳检测机制 每隔多少秒检测一次，单位秒，Swoole会轮询所有TCP连接，将超过心跳时间的连接关闭掉
     * @var int
     */
    protected $heartbeatCheckInterval;
    /**
     * 心跳检测机制 TCP连接的最大闲置时间，单位s , 如果某fd最后一次发包距离现在的时间超过heartbeat_idle_time会把这个连接关闭。
     * @var int
     */
    protected $heartbeatIdleTime;

    /**
     * 设置Worker/TaskWorker子进程的所属用户。
     * 服务器如果需要监听1024以下的端口，必须有root权限。
     * 但程序运行在root用户下，代码中一旦有漏洞，攻击者就可以以root的方式执行远程指令，风险很大。
     * 配置了user项之后，可以让主进程运行在root权限下，子进程运行在普通用户权限下。
     * @var string
     */
    protected $user;
    /**
     * 设置worker子进程的进程用户组。与user配置相同，此配置是修改进程所属用户组，提升服务器程序的安全性。
     * @var string
     */
    protected $group;
    /**
     * 重定向Worker进程的文件系统根目录。此设置可以使进程对文件系统的读写与实际的操作系统文件系统隔离。提升安全性。
     * @var string
     */
    protected $chroot;

    /**
     * 在Server启动时自动将master进程的PID写入到文件，在Server关闭时自动删除PID文件。
     * @var string
     */
    protected $pidFile;

    /**
     * 配置发送输出缓存区内存尺寸。
     * 注意此函数不应当调整过大，避免拥塞的数据过多，导致吃光机器内存
     * 开启大量Worker进程时，将会占用worker_num * buffer_output_size字节的内存
     * @var int
     */
    protected $bufferOutputSize;
    /**
     * 数据发送缓存区
     * 参数buffer_output_size用于设置单次最大发送长度。socket_buffer_size用于设置客户端连接最大允许占用内存数量。
     * 调整连接发送缓存区的大小。TCP通信有拥塞控制机制，服务器向客户端发送大量数据时，并不能立即发出。这时发送的数据会存放在服务器端的内存缓存区内。此参数可以调整内存缓存区的大小。
     * 如果发送数据过多，客户端阻塞，数据占满缓存区后Server会报如下错误信息：swFactoryProcess_finish: send failed, session#1 output buffer has been overflowed.
     * 发送缓冲区塞满导致send失败，只会影响当前的客户端，其他客户端不受影响
     * 服务器有大量TCP连接时，最差的情况下将会占用serv->max_connection * socket_buffer_size字节的内存
     * @var int
     */
    protected $socketBufferSize;

    /**
     * 设置当前工作进程最大协程数量。超过max_coroutine底层将无法创建新的协程，底层会抛出错误，并直接关闭连接。
     * @var int
     */
    protected $maxCoroutine;

    /**
     * 设置上传文件的临时目录。
     * @var string
     */
    protected $uploadTmpDir;

    /**
     * 设置POST消息解析开关，选项为true时自动将Content-Type为x-www-form-urlencoded的请求包体解析到POST数组。设置为false时将关闭POST解析。
     * @var bool
     */
    protected $httpParsePost = true;

    /**
     * 配置静态文件根目录，与$enableStaticHandler配合使用。
     * @var string
     */
    protected $documentRoot;

    /**
     * $enableStaticHandler为true后，底层收到Http请求会先判断document_root路径下是否存在此文件，如果存在会直接发送文件内容给客户端，不再触发onRequest回调。
     * @var bool
     */
    protected $enableStaticHandler = false;

    /**
     * 启用压缩。默认为开启。
     * @var bool
     */
    protected $httpCompression = true;

    /**
     * 设置WebSocket子协议。设置后握手响应的Http头会增加Sec-WebSocket-Protocol: {$websocket_subprotocol}。具体使用方法请参考WebSocket协议相关RFC文档。
     * @var string
     */
    protected $websocketSubprotocol;

    /**
     * 启用websocket协议中关闭帧（opcode为0x08的帧）在onMessage回调中接收，默认为false
     * 开启后，可在WebSocketServer中的onMessage回调中接收到客户端或服务端发送的关闭帧，开发者可自行对其进行处理。
     * @var bool
     */
    protected $openWebsocketCloseFrame = false;

    /**
     * 默认为debug模式，代表重启缓存无效
     * @var bool
     */
    protected $debug = true;

    /**
     * Banner
     * @var string
     */
    protected $banner = "
             ________      ______      ______    
            |_   __  |   .' ____ \    |_   _ `.  
              | |_ \_|   | (___ \_|     | | `. \ 
              |  _| _     _.____`.      | |  | | 
             _| |__/ |   | \____) |    _| |_.' / 
            |________|    \______.'   |______.'  
                                                 ";

    public function __construct()
    {
        parent::__construct(self::key);
    }

    /**
     * @return int
     */
    public function getReactorNum()
    {
        return $this->reactorNum;
    }

    /**
     * @param int $reactorNum
     */
    public function setReactorNum(int $reactorNum)
    {
        $this->reactorNum = $reactorNum;
    }

    /**
     * @return int
     */
    public function getWorkerNum()
    {
        return $this->workerNum ?? 1;
    }

    /**
     * @param int $workerNum
     */
    public function setWorkerNum(int $workerNum)
    {
        $this->workerNum = $workerNum;
    }

    /**
     * @return int
     */
    public function getDispatchMode()
    {
        return $this->dispatchMode ?? 2;
    }

    /**
     * @param int $dispatchMode
     */
    public function setDispatchMode(int $dispatchMode)
    {
        $this->dispatchMode = $dispatchMode;
    }

    /**
     * @return int
     */
    public function getMaxConn()
    {
        return $this->maxConn ?? 100000;
    }

    /**
     * @param int $maxConn
     */
    public function setMaxConn(int $maxConn)
    {
        $this->maxConn = $maxConn;
    }

    /**
     * @return bool
     */
    public function isDaemonize()
    {
        return $this->daemonize ?? false;
    }

    /**
     * @param bool $daemonize
     */
    public function setDaemonize(bool $daemonize)
    {
        $this->daemonize = $daemonize;
    }

    /**
     * @return bool
     */
    public function isReloadAsync()
    {
        return $this->reloadAsync ?? false;
    }

    /**
     * @param bool $reloadAsync
     */
    public function setReloadAsync(bool $reloadAsync)
    {
        $this->reloadAsync = $reloadAsync;
    }

    /**
     * @return int
     */
    public function getMaxWaitTime()
    {
        return $this->maxWaitTime ?? 30;
    }

    /**
     * @param int $maxWaitTime
     */
    public function setMaxWaitTime(int $maxWaitTime)
    {
        $this->maxWaitTime = $maxWaitTime;
    }

    /**
     * @return bool
     */
    public function isOpenCpuAffinity()
    {
        return $this->openCpuAffinity ?? true;
    }

    /**
     * @param bool $openCpuAffinity
     */
    public function setOpenCpuAffinity(bool $openCpuAffinity)
    {
        $this->openCpuAffinity = $openCpuAffinity;
    }

    /**
     * @return array
     */
    public function getCpuAffinityIgnore()
    {
        return $this->cpuAffinityIgnore;
    }

    /**
     * @param array $cpuAffinityIgnore
     */
    public function setCpuAffinityIgnore(array $cpuAffinityIgnore)
    {
        $this->cpuAffinityIgnore = $cpuAffinityIgnore;
    }

    /**
     * @return string
     */
    public function getLogFile()
    {
        return $this->logFile;
    }

    /**
     * @param string $logFile
     */
    public function setLogFile(string $logFile)
    {
        $this->logFile = $logFile;
    }

    /**
     * @return string
     */
    public function getLogLevel()
    {
        return $this->logLevel ?? 0;
    }

    /**
     * @param string $logLevel
     */
    public function setLogLevel(string $logLevel)
    {
        $this->logLevel = $logLevel;
    }

    /**
     * @return int
     */
    public function getHeartbeatCheckInterval()
    {
        return $this->heartbeatCheckInterval;
    }

    /**
     * @param int $heartbeatCheckInterval
     */
    public function setHeartbeatCheckInterval(int $heartbeatCheckInterval)
    {
        $this->heartbeatCheckInterval = $heartbeatCheckInterval;
    }

    /**
     * @return int
     */
    public function getHeartbeatIdleTime()
    {
        return $this->heartbeatIdleTime;
    }

    /**
     * @param int $heartbeatIdleTime
     */
    public function setHeartbeatIdleTime(int $heartbeatIdleTime)
    {
        $this->heartbeatIdleTime = $heartbeatIdleTime;
    }


    /**
     * @return string
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param string $user
     */
    public function setUser(string $user)
    {
        $this->user = $user;
    }

    /**
     * @return string
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @param string $group
     */
    public function setGroup(string $group)
    {
        $this->group = $group;
    }

    /**
     * @return string
     */
    public function getChroot()
    {
        return $this->chroot;
    }

    /**
     * @param string $chroot
     */
    public function setChroot(string $chroot)
    {
        $this->chroot = $chroot;
    }

    /**
     * @return string
     */
    public function getPidFile()
    {
        return $this->pidFile;
    }

    /**
     * @param string $pidFile
     */
    public function setPidFile(string $pidFile)
    {
        $this->pidFile = $pidFile;
    }

    /**
     * @return int
     */
    public function getBufferOutputSize()
    {
        return $this->bufferOutputSize ?? 8 * 1024 * 1024;
    }

    /**
     * @param int $bufferOutputSize
     */
    public function setBufferOutputSize(int $bufferOutputSize)
    {
        $this->bufferOutputSize = $bufferOutputSize;
    }

    /**
     * @return int
     */
    public function getSocketBufferSize()
    {
        return $this->socketBufferSize;
    }

    /**
     * @param int $socketBufferSize
     */
    public function setSocketBufferSize(int $socketBufferSize)
    {
        $this->socketBufferSize = $socketBufferSize;
    }

    /**
     * @return int
     */
    public function getMaxCoroutine()
    {
        return $this->maxCoroutine ?? 3000;
    }

    /**
     * @param int $maxCoroutine
     */
    public function setMaxCoroutine(int $maxCoroutine)
    {
        $this->maxCoroutine = $maxCoroutine;
    }

    /**
     * @return string
     */
    public function getUploadTmpDir()
    {
        return $this->uploadTmpDir;
    }

    /**
     * @param string $uploadTmpDir
     */
    public function setUploadTmpDir(string $uploadTmpDir)
    {
        $this->uploadTmpDir = $uploadTmpDir;
    }

    /**
     * @return bool
     */
    public function isHttpParsePost()
    {
        return $this->httpParsePost;
    }

    /**
     * @param bool $httpParsePost
     */
    public function setHttpParsePost(bool $httpParsePost)
    {
        $this->httpParsePost = $httpParsePost;
    }

    /**
     * @return string
     */
    public function getDocumentRoot()
    {
        return $this->documentRoot;
    }

    /**
     * @param string $documentRoot
     */
    public function setDocumentRoot(string $documentRoot)
    {
        $this->documentRoot = $documentRoot;
    }

    /**
     * @return bool
     */
    public function isEnableStaticHandler()
    {
        return $this->enableStaticHandler;
    }

    /**
     * @param bool $enableStaticHandler
     */
    public function setEnableStaticHandler(bool $enableStaticHandler)
    {
        $this->enableStaticHandler = $enableStaticHandler;
    }

    /**
     * @return bool
     */
    public function isHttpCompression()
    {
        return $this->httpCompression;
    }

    /**
     * @param bool $httpCompression
     */
    public function setHttpCompression(bool $httpCompression)
    {
        $this->httpCompression = $httpCompression;
    }

    /**
     * @return string
     */
    public function getWebsocketSubprotocol()
    {
        return $this->websocketSubprotocol;
    }

    /**
     * @param string $websocketSubprotocol
     */
    public function setWebsocketSubprotocol(string $websocketSubprotocol)
    {
        $this->websocketSubprotocol = $websocketSubprotocol;
    }

    /**
     * @return bool
     */
    public function isOpenWebsocketCloseFrame()
    {
        return $this->openWebsocketCloseFrame;
    }

    /**
     * @param bool $openWebsocketCloseFrame
     */
    public function setOpenWebsocketCloseFrame(bool $openWebsocketCloseFrame)
    {
        $this->openWebsocketCloseFrame = $openWebsocketCloseFrame;
    }

    /**
     * 构建配置
     * @return array
     * @throws ConfigException
     * @throws Exception
     */
    public function buildConfig(): array
    {
        $this->merge();
        $build = [];
        if (empty($this->getRootDir())) {
            throw new ConfigException("RootDir不能为空");
        } else {
            //格式化rootDir
            $this->rootDir = realpath($this->getRootDir());
            if ($this->rootDir === false) {
                throw new ConfigException("RootDir不存在");
            }
        }
        if ($this->getReactorNum() != null && $this->getReactorNum() > 0) {
            $build['reactor_num'] = $this->getReactorNum();
        }
        if ($this->getWorkerNum() != null && $this->getWorkerNum() > 0) {
            $build['worker_num'] = $this->getWorkerNum();
        } else {
            throw new ConfigException("ServerConfig中workerNum不能为空或者小于1");
        }
        if ($this->getDispatchMode() != null && $this->getDispatchMode() > 0) {
            $build['dispatch_mode'] = $this->getDispatchMode();
        } else {
            throw new ConfigException("ServerConfig中dispatchMode不能为空或者小于1");
        }
        if ($this->getMaxConn() != null && $this->getMaxConn() > 0) {
            $build['max_conn'] = $this->getMaxConn();
        } else {
            throw new ConfigException("ServerConfig中maxConn不能为空或者小于1");
        }
        $build['daemonize'] = $this->isDaemonize();
        $build['reload_async'] = $this->isReloadAsync();
        $build['max_wait_time'] = $this->getMaxWaitTime();
        $build['open_cpu_affinity'] = $this->isOpenCpuAffinity();
        if (!empty($this->getCpuAffinityIgnore())) {
            $build['cpu_affinity_ignore'] = $this->getCpuAffinityIgnore();
        }
        if (empty($this->getLogFile())) {
            $path = $this->rootDir . DIRECTORY_SEPARATOR . "bin" . DIRECTORY_SEPARATOR . "logs";
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }
            $this->logFile = $path . DIRECTORY_SEPARATOR . "swoole.log";
        }
        $build['log_file'] = $this->getLogFile();
        $build['log_level'] = $this->getLogLevel();
        if ($this->getHeartbeatCheckInterval() != null) {
            $build['heartbeat_check_interval'] = $this->getHeartbeatCheckInterval();
            if ($this->getHeartbeatIdleTime() != null) {
                $build['heartbeat_idle_time'] = $this->getHeartbeatIdleTime();
            }
        }
        if (!empty($this->getUser())) {
            $build['user'] = $this->getUser();
        }
        if (!empty($this->getGroup())) {
            $build['group'] = $this->getGroup();
        }
        if (!empty($this->getChroot())) {
            $build['chroot'] = $this->getChroot();
        }
        if (empty($this->getPidFile())) {
            $path = $this->rootDir . DIRECTORY_SEPARATOR . "bin";
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }
            $this->pidFile = $path . DIRECTORY_SEPARATOR . "pid";
        }
        $build['pid_file'] = $this->getPidFile();
        if (!empty($this->getBufferOutputSize())) {
            $build['buffer_output_size'] = $this->getBufferOutputSize();
        }
        if (!empty($this->getSocketBufferSize())) {
            $build['socket_buffer_size'] = $this->getSocketBufferSize();
        }
        if (!empty($this->getMaxCoroutine())) {
            $build['max_coroutine'] = $this->getMaxCoroutine();
        }
        if (!empty($this->getUploadTmpDir())) {
            $build['upload_tmp_dir'] = $this->getUploadTmpDir();
        }
        $build['http_parse_post'] = $this->isHttpParsePost();
        if ($this->isEnableStaticHandler()) {
            $build['enable_static_handler'] = $this->isEnableStaticHandler();
            ConfigException::AssertNull($this, "documentRoot", $this->getDocumentRoot());
            $build['document_root'] = $this->getDocumentRoot();
        }
        $build['http_compression'] = $this->isHttpCompression();
        if (!empty($this->getWebsocketSubprotocol())) {
            $build['websocket_subprotocol'] = $this->getWebsocketSubprotocol();
        }
        $build['open_websocket_close_frame'] = $this->isOpenWebsocketCloseFrame();
        return $build;
    }

    /**
     * @return string
     */
    public function getBanner(): string
    {
        return $this->banner;
    }

    /**
     * @param string $banner
     */
    public function setBanner(string $banner): void
    {
        $this->banner = $banner;
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
     * @throws Exception
     */
    public function getRootDir()
    {
        if (empty($this->rootDir) && !defined("ROOT_DIR")) {
            throw new Exception("没有设置ROOT_DIR常量，请定义");
        }
        if (defined("ROOT_DIR")) {
            return ROOT_DIR;
        }
        return $this->rootDir;
    }

    /**
     * @param string $rootDir
     */
    public function setRootDir(string $rootDir): void
    {
        if (!defined("ROOT_DIR")) {
            $this->rootDir = $rootDir;
            define("ROOT_DIR", $rootDir);
        } else {
            $this->rootDir = ROOT_DIR;
        }
    }

    /**
     * 获取bin目录
     * @return string
     * @throws Exception
     */
    public function getBinDir()
    {
        return realpath($this->getRootDir()) . DIRECTORY_SEPARATOR . "bin";
    }

    /**
     * 获取bin目录
     * @return string
     * @throws Exception
     */
    public function getCacheDir()
    {
        return $this->getBinDir() . DIRECTORY_SEPARATOR . "cache";
    }

    /**
     * 获取src目录
     * @return string
     * @throws Exception
     */
    public function getSrcDir()
    {
        return realpath($this->getRootDir()) . DIRECTORY_SEPARATOR . "src";
    }

    /**
     * 获取vendor目录
     * @return string
     * @throws Exception
     */
    public function getVendorDir()
    {
        return realpath($this->getRootDir()) . DIRECTORY_SEPARATOR . "vendor";
    }

    /**
     * @return string|null
     */
    public function getProxyServerClass(): ?string
    {
        return $this->proxyServerClass;
    }

    /**
     * @param string|null $proxyServerClass
     */
    public function setProxyServerClass(?string $proxyServerClass): void
    {
        $this->proxyServerClass = $proxyServerClass;
    }

    /**
     * @return bool
     */
    public function isDebug(): bool
    {
        return $this->debug;
    }

    /**
     * @param bool $debug
     */
    public function setDebug(bool $debug): void
    {
        $this->debug = $debug;
    }
}