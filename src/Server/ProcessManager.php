<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/16
 * Time: 17:46
 */

namespace GoSwoole\BaseServer\Server;


use GoSwoole\BaseServer\Server\Config\ProcessConfig;
use GoSwoole\BaseServer\Server\ServerProcess\MasterProcess;

class ProcessManager
{
    /**
     * @var ProcessConfig[]
     */
    private $customProcessConfigs = [];
    /**
     * @var Process[]
     */
    private $processes = [];
    /**
     * @var Server
     */
    private $server;
    /**
     * @var Process
     */
    private $masterProcess;
    /**
     * @var Process
     */
    private $managerProcess;
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

    /**
     * ProcessManager constructor.
     * @param Server $server
     * @param string $processClass
     * @throws \GoSwoole\BaseServer\Exception
     */
    public function __construct(Server $server, string $processClass)
    {
        $this->server = $server;
        $this->defaultProcessClass = $processClass;
        $this->masterProcess = new MasterProcess($server);
    }

    /**
     * 通过id获取进程
     * @param int $processId
     * @return Process
     */
    public function getProcessFromId(int $processId)
    {
        return $this->processes[$processId] ?? null;
    }

    /**
     * 通过id获取进程
     * @param string $processName
     * @return Process
     */
    public function getProcessFromName(string $processName)
    {
        foreach ($this->processes as $process) {
            if ($process->getProcessName() == $processName) {
                return $process;
            }
        }
        return null;
    }

    /**
     * @return ProcessConfig[]
     */
    public function getCustomProcessConfigs(): array
    {
        return $this->customProcessConfigs;
    }

    /**
     * 添加自定义进程
     * @param string $name
     * @param string $processClass
     * @param string $groupName
     * @return ProcessConfig
     * @throws Exception\ConfigException
     */
    public function addCustomProcesses(string $name, $processClass, string $groupName)
    {
        if ($processClass != null) {
            $processConfig = new ProcessConfig($processClass, $name, $groupName);
        } else {
            $processConfig = new ProcessConfig($this->defaultProcessClass, $name, $groupName);
        }
        $this->customProcessConfigs[] = $processConfig;
        return $processConfig;
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
            $processGroup = new ProcessGroup($this, $groupName, $group);
            $this->groups[$groupName] = $processGroup;
        }
        return null;
    }


    /**
     * 返回当前服务器主进程的PID。
     * @return int
     */
    public function getMasterPid()
    {
        return $this->server->master_pid ?? null;
    }

    /**
     * 返回当前服务器管理进程的PID。
     * @return int
     */
    public function getManagerPid()
    {
        return $this->server->manager_pid ?? null;
    }

    /**
     * 得到当前Worker进程的编号
     * @return int
     */
    public function getCurrentProcessId()
    {
        return $this->server->worker_id ?? null;
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
    public function getCurrentProcessPid()
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
     * 获取当前进程
     * @return Process
     */
    public function getCurrentProcess(): Process
    {
        if ($this->getCurrentProcessId() == null) {
            if ($this->getMasterPid() == null) {
                //说明还没启动
                return $this->masterProcess;
            } else if ($this->getManagerPid() != null) {
                return $this->managerProcess;
            } else {
                return null;
            }
        }
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

    /**
     * @return Process[]
     */
    public function getProcesses(): array
    {
        return $this->processes;
    }

    /**
     * @param Process $managerProcess
     */
    public function setManagerProcess(Process $managerProcess): void
    {
        $this->managerProcess = $managerProcess;
    }
}