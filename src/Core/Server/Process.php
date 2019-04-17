<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/16
 * Time: 9:23
 */

namespace Core\Server;

use Core\Utils\Utils;

/**
 * 进程
 * Class Process
 * @package Core\Server\process
 */
abstract class Process
{
    const DEFAULT_GROUP = "default_group";
    const WORKER_GROUP = "worker_group";
    const SOCK_DGRAM = 2;
    const PROCESS_TYPE_WORKER = 1;
    const PROCESS_TYPE_TASK = 2;
    const PROCESS_TYPE_CUSTOM = 3;

    /**
     * 进程类型
     * @var int
     */
    private $processType;


    /**
     * 进程ID
     * @var int
     */
    private $processId;

    /**
     * 进程PID
     * @var int
     */
    private $processPid;

    /**
     * 进程名
     * @var string
     */
    private $processName;

    /**
     * @var Server
     */
    private $server;

    /**
     * 进程组名
     * @var string
     */
    private $groupName;

    /**
     * swoole的process类
     * @var \Swoole\Process
     */
    private $swooleProcess;

    public function __construct(Server $server, string $groupName = self::DEFAULT_GROUP)
    {
        $this->server = $server;
        $this->groupName = $groupName;
    }

    /**
     * 创建一个进程实例,这里都是自定义进程
     * @return Process
     */
    public function createProcess(): Process
    {
        $this->swooleProcess = new \Swoole\Process([$this, "_onProcessStart"], false, self::SOCK_DGRAM, true);
        $this->setProcessType(self::PROCESS_TYPE_CUSTOM);
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
     * @param int $processId
     */
    public function setProcessId(int $processId): void
    {
        $this->processId = $processId;
    }

    /**
     * @param int $processPid
     */
    public function setProcessPid(int $processPid): void
    {
        $this->processPid = $processPid;
    }

    /**
     * @param int $processType
     */
    public function setProcessType(int $processType): void
    {
        $this->processType = $processType;
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
    public function setName($name)
    {
        $this->processName = $name;
        if ($this->getProcessType() == self::PROCESS_TYPE_CUSTOM) {
            if (!empty($name)) {
                $this->swooleProcess->name($name);
            }
        }
    }

    /**
     * 进程启动的回调
     */
    public function _onProcessStart()
    {
        if ($this->getProcessType() == self::PROCESS_TYPE_CUSTOM) {
            \swoole_process::signal(SIGTERM, [$this, '_onProcessStop']);
            \swoole_event_add($this->swooleProcess->pipe, function ($pipe) {
                $recv = $this->swooleProcess->read();
                //获取进程id
                $unpackData = unpack("N", $recv);
                $processId = $unpackData[1];
                $fromProcess = $this->server->getProcessManager()->getProcessFromId($processId);
                $this->onPipeMessage(substr($recv, 4), $fromProcess);
            });
        }
        $this->server->getProcessManager()->setCurrentProcessId($this->getProcessId());
        $this->setProcessPid(posix_getpid());
        $this->server->getProcessManager()->setCurrentProcessPid($this->getProcessPid());
        $this->onProcessStart();
    }

    /**
     * 关闭处理.
     */
    public function _onProcessStop()
    {
        $this->onProcessStop();
        $this->swooleProcess->exit(0);
    }

    /**
     * 向某一个进程发送消息
     * @param $message
     * @param Process $toProcess
     */
    public function sendMessage($message, Process $toProcess)
    {
        if ($toProcess->getProcessType() == self::PROCESS_TYPE_CUSTOM) {
            if (!is_string($message)) {
                $message = Utils::serverSerialize($message);
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

    public abstract function onPipeMessage(string $message, Process $fromProcess);

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
    public function getProcessManager():ProcessManager
    {
        return $this->server->getProcessManager();
    }
}