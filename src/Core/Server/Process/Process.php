<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/16
 * Time: 9:23
 */

namespace ESD\Core\Server\Process;

use ESD\Core\Context\Context;
use ESD\Core\Context\ContextBuilder;
use ESD\Core\Context\ContextManager;
use ESD\Core\Event\EventDispatcher;
use ESD\Core\Event\EventMessageProcessor;
use ESD\Core\Message\Message;
use ESD\Core\Message\MessageProcessor;
use ESD\Core\Server\Server;
use Psr\Log\LoggerInterface;

/**
 * 进程
 * Class Process
 * @package ESD\Core\Server\process
 */
abstract class Process
{
    const DEFAULT_GROUP = "DefaultGroup";
    const WORKER_GROUP = "WorkerGroup";
    const SERVER_GROUP = "ServerGroup";
    const SOCK_DGRAM = 2;
    const PROCESS_TYPE_WORKER = 1;
    const PROCESS_TYPE_CUSTOM = 3;

    /**
     * 进程类型
     * @var int
     */
    protected $processType;


    /**
     * 进程ID
     * @var int
     */
    protected $processId;

    /**
     * 进程PID
     * @var int
     */
    protected $processPid;

    /**
     * 进程名
     * @var string
     */
    protected $processName;

    /**
     * @var Server
     */
    protected $server;

    /**
     * 进程组名
     * @var string
     */
    protected $groupName;

    /**
     * swoole的process类
     * @var \Swoole\Process
     */
    protected $swooleProcess;

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var EventDispatcher
     */
    protected $eventDispatcher;

    /**
     * @var \Swoole\Coroutine\Socket
     */
    private $socket;

    /**
     * @var LoggerInterface
     */
    protected $log;

    /**
     * @var bool
     */
    protected $isReady = false;

    /**
     * @var array
     */
    protected $pipeMessageCache = [];

    /**
     * Process constructor.
     * @param Server $server
     * @param int $processId
     * @param string $name
     * @param string $groupName
     */
    public function __construct(Server $server, int $processId, string $name = null, string $groupName = self::DEFAULT_GROUP)
    {
        $this->server = $server;
        $this->groupName = $groupName;
        $this->processId = $processId;
        if ($groupName == self::WORKER_GROUP) {
            $this->processType = self::PROCESS_TYPE_WORKER;
        } else {
            $this->processType = self::PROCESS_TYPE_CUSTOM;
        }
        $this->processName = $name;
        //注册Process的ContextBuilder
        $contextBuilder = ContextManager::getInstance()->getContextBuilder(ContextBuilder::PROCESS_CONTEXT,
            function () {
                return new ProcessContextBuilder($this);
            });
        $this->context = $contextBuilder->build();
    }

    /**
     * 注册信号
     * @param int $SIG
     * @param callable $param
     */
    public static function signal(int $SIG, callable $param)
    {
        \Swoole\Process::signal($SIG, $param);
    }

    /**
     * 创建一个进程实例,这里都是自定义进程
     * @return Process
     */
    public function createProcess(): Process
    {
        $this->swooleProcess = new \Swoole\Process([$this, "_onProcessStart"], false, self::SOCK_DGRAM, true);
        return $this;
    }

    /**
     * @return \Swoole\Process
     */
    public function getSwooleProcess(): \Swoole\Process
    {
        return $this->swooleProcess;
    }

    /**
     * @return string
     */
    public function getProcessName(): string
    {
        return $this->processName;
    }

    /**
     * @return Server
     */
    public function getServer(): Server
    {
        return $this->server;
    }

    /**
     * @return string
     */
    public function getGroupName(): string
    {
        return $this->groupName;
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
     * @return bool
     */
    public function isReady(): bool
    {
        return $this->isReady;
    }

    /**
     * @param bool $isReady
     */
    public function setIsReady(bool $isReady): void
    {
        $this->isReady = $isReady;
    }

    /**
     * 执行外部命令.
     *
     * @param $path
     * @param $params
     */
    protected function exec($path, $params)
    {
        $this->swooleProcess->exec($path, $params);
    }

    /**
     * 设置进程的名字
     * @param $name
     */
    protected function setName($name)
    {
        $this->processName = $name;
        self::setProcessTitle(Server::$instance->getServerConfig()->getName() . "-" . $name);
    }

    /**
     * 进程启动的回调
     * @throws \ESD\Core\Exception
     */
    public function _onProcessStart()
    {
        Process::signal(SIGINT, function ($signo) {

        });
        $this->log = Server::$instance->getLog();
        $this->eventDispatcher = Server::$instance->getEventDispatcher();
        //注册事件派发处理函数
        MessageProcessor::addMessageProcessor(new EventMessageProcessor($this->eventDispatcher));
        try {
            Server::$isStart = true;
            if ($this->processName != null) {
                $this->setName($this->processName);
            }
            $this->server->getProcessManager()->setCurrentProcessId($this->processId);
            $this->processPid = getmypid();
            $this->server->getProcessManager()->setCurrentProcessPid($this->processPid);
            //用户插件初始化
            $this->server->getPlugManager()->beforeProcessStart($this->context);
            $this->server->getPlugManager()->waitReady();
            $this->setIsReady(true);
            //延迟发送缓存的消息
            foreach ($this->pipeMessageCache as $value) {
                $this->_onPipeMessage($value[0], $value[1]);
            }
            $this->pipeMessageCache = [];
            $this->init();
            $this->log->info("ready");
            if ($this->getProcessType() == self::PROCESS_TYPE_CUSTOM) {
                $this->getProcessManager()->setCurrentProcessId($this->processId);
                Process::signal(SIGTERM, [$this, '_onProcessStop']);
                $this->socket = $this->swooleProcess->exportSocket();
                go(function () {
                    while (true) {
                        $recv = $this->socket->recv();
                        //获取进程id
                        $unpackData = unpack("N", $recv);
                        $processId = $unpackData[1];
                        $fromProcess = $this->server->getProcessManager()->getProcessFromId($processId);
                        go(function () use ($recv, $fromProcess) {
                            $this->_onPipeMessage(serverUnSerialize(substr($recv, 4)), $fromProcess);
                        });
                    }
                });
            }
            enableRuntimeCoroutine();
            //发出事件
            $this->eventDispatcher->dispatchEvent(new ProcessEvent(ProcessEvent::ProcessStartEvent, $this));
            $this->onProcessStart();
        } catch (\Throwable $e) {
            $this->log->error($e);
        }
    }

    /**
     * 在onProcessStart之前，用于初始化成员变量
     * @return mixed
     */
    public abstract function init();

    /**
     * 收到消息
     * @param Message $message
     * @param Process $fromProcess
     */
    public function _onPipeMessage(Message $message, Process $fromProcess)
    {
        if (!$this->isReady()) {
            $this->pipeMessageCache[] = [$message, $fromProcess];
            return;
        }
        try {
            if (!MessageProcessor::dispatch($message)) {
                $this->onPipeMessage($message, $fromProcess);
            }
        } catch (\Throwable $e) {
            $this->log->error($e);
        }
    }

    /**
     * 关闭处理.
     */
    public function _onProcessStop()
    {
        try {
            //发出事件
            $this->eventDispatcher->dispatchEvent(new ProcessEvent(ProcessEvent::ProcessStopEvent, $this));
            $this->onProcessStop();
        } catch (\Throwable $e) {
            $this->log->error($e);
        }
        if ($this->swooleProcess != null) {
            $this->swooleProcess->exit(0);
        }
    }

    /**
     * 向某一个进程发送消息
     * @param Message $message
     * @param Process $toProcess
     */
    public function sendMessage(Message $message, Process $toProcess)
    {
        //如果是自己给自己发，这里处理下
        if ($this->getProcessId() == $toProcess->getProcessId()) {
            $this->_onPipeMessage($message, $this);
            return;
        }
        if ($toProcess->getProcessType() == self::PROCESS_TYPE_CUSTOM) {
            if (!is_string($message)) {
                $message = serverSerialize($message);
            }
            //添加来自哪个进程的ID
            $message = pack("N", $this->getProcessId()) . $message;
            $toProcess->swooleProcess->write($message);
        } else {
            //如果是worker或者task进程通过下面api发送消息
            $this->server->getServer()->sendMessage($message, $toProcess->getProcessId());
        }
    }

    public abstract function onProcessStart();

    public abstract function onProcessStop();

    public abstract function onPipeMessage(Message $message, Process $fromProcess);

    /**
     * @return int
     */
    public function getProcessType(): int
    {
        return $this->processType;
    }

    /**
     * @return int
     */
    public function getProcessId(): int
    {
        return $this->processId;
    }

    /**
     * @return int
     */
    public function getProcessPid(): int
    {
        return $this->processPid;
    }

    /**
     * 获取进程管理器
     * @return ProcessManager
     */
    public function getProcessManager(): ProcessManager
    {
        return $this->server->getProcessManager();
    }

    /**
     * 是否是mac系统
     * @return bool
     */
    public static function isDarwin()
    {
        if (PHP_OS == "Darwin") {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Set process name.
     *
     * @param string $title
     * @return void
     */
    public static function setProcessTitle($title)
    {
        if (self::isDarwin()) {
            return;
        }
        // >=php 5.5
        if (function_exists('cli_set_process_title')) {
            @cli_set_process_title($title);
        } // Need proctitle when php<=5.5 .
        else {
            @swoole_set_process_name($title);
        }
    }
}