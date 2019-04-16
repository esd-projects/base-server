<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/16
 * Time: 17:46
 */

namespace core\server;


class ProcessManager
{
    /**
     * @var Process[]
     */
    private $customProcesses = [];
    /**
     * @var Process[]
     */
    private $processes = [];
    /**
     * @var Server
     */
    private $server;

    /**
     * 默认的class
     * @var string
     */
    private $defaultProcessClass;

    /**
     * 进程组
     * @var array
     */
    private $groups = [];

    public function __construct(Server $server, string $processClass)
    {
        $this->server = $server;
        $this->defaultProcessClass = $processClass;
    }

    /**
     * 通过id获取进程
     * @param int $processId
     * @return Process
     */
    public function getProcessFromId(int $processId): Process
    {
        return $this->processes[$processId] ?? null;
    }

    /**
     * 通过id获取进程
     * @param string $processName
     * @return Process
     */
    public function getProcessFromName(string $processName): Process
    {
        foreach ($this->processes as $process) {
            if ($process->getProcessName() == $processName) {
                return $process;
            }
        }
        return null;
    }

    /**
     * @return Process[]
     */
    public function getCustomProcesses(): array
    {
        return $this->customProcesses;
    }

    /**
     * 添加自定义进程
     * @param string $name
     * @param $processClass
     * @return Process
     */
    public function addCustomProcesses(string $name, $processClass)
    {
        if ($processClass == null) {
            $process = new $this->defaultProcessClass($this->server);
        } else {
            $process = new $processClass($this);
        }
        if ($process instanceof Process) {
            $process->createProcess();
            $process->setName($name);
        }
        $this->customProcesses[] = $process;
        return $process;
    }

    /**
     * 添加进程
     * @param Process $process
     */
    public function addProcesses(Process $process)
    {
        $this->processes[$process->getProcessId()] = $process;
    }

    /**
     * @return string
     */
    public function getDefaultProcessClass(): string
    {
        return $this->defaultProcessClass;
    }

    /**
     * 获取进程组
     * @param string $groupName
     * @return ProcessGroup
     */
    public function getProcessGroup(string $groupName): ProcessGroup
    {
        if (isset($this->groups[$groupName])) {
            return $this->groups[$groupName];
        }
        $group = [];
        foreach ($this->processes as $process) {
            if ($process->getGroupName() == $groupName) {
                $group[] = $process;
            }
        }
        if (count($group) > 0) {
            $processGroup = new ProcessGroup($groupName, $group);
            $this->groups[$groupName] = $processGroup;
        }
        return null;
    }


    /**
     * 返回当前服务器主进程的PID。
     * @return int
     */
    public function getMasterPid(): int
    {
        return $this->server->master_pid;
    }

    /**
     * 返回当前服务器管理进程的PID。
     * @return int
     */
    public function getManagerPid(): int
    {
        return $this->server->manager_pid;
    }

    /**
     * 得到当前Worker进程的编号
     * @return int
     */
    public function getCurrentProcessId(): int
    {
        return $this->server->worker_id;
    }

    /**
     * @param $processId
     */
    public function setCurrentProcessId($processId)
    {
        $this->server->worker_id = $processId;
    }

    /**
     * 得到当前Worker进程的操作系统进程ID。
     * 与posix_getpid()的返回值相同。
     * @return int
     */
    public function getCurrentProcessPid(): int
    {
        return $this->server->worker_pid;
    }

    /**
     * @param $processPid
     */
    public function setCurrentProcessPid($processPid)
    {
        $this->server->worker_pid = $processPid;
    }

    /**
     * true表示当前的进程是Task工作进程
     * @return bool
     */
    public function isTaskworker(): bool
    {
        return $this->server->taskworker;
    }

    /**
     * 获取当前进程
     * @return Process
     */
    public function getCurrentProcess(): Process
    {
        return $this->getProcessFromId($this->getCurrentProcessId());
    }

    /**
     * 发送消息到进程组中，轮询
     * @param $message
     * @param string $groupName
     * @throws \Exception
     */
    public function sendMessageToGroup($message, string $groupName)
    {
        $group = $this->getProcessGroup($groupName);
        if ($group == null) {
            throw new \Exception("没有$groupName 进程组");
        }
        $group->sendMessageToGroup($message);
    }
}