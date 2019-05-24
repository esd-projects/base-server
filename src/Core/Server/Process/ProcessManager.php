<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/16
 * Time: 17:46
 */

namespace ESD\Core\Server\Process;


use ESD\Core\Server\Config\ProcessConfig;
use ESD\Core\Server\Server;

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
     */
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
    public function getProcessFromId(int $processId)
    {
        if ($processId == MasterProcess::id) return $this->masterProcess;
        if ($processId == ManagerProcess::id) return $this->managerProcess;
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
     * 合并配置
     */
    public function mergeConfig()
    {
        foreach ($this->customProcessConfigs as $processConfig) {
            $processConfig->merge();
        }
    }

    /**
     * @return ProcessConfig[]
     * @throws Exception\ConfigException
     * @throws \ReflectionException
     */
    public function getCustomProcessConfigs(): array
    {
        //合并配置
        $this->mergeConfig();
        //重新获取配置
        $customProcessConfigs = [];
        $configs = Server::$instance->getConfigContext()->get(ProcessConfig::key, []);
        foreach ($configs as $key => $value) {
            $processConfig = new ProcessConfig();
            $processConfig->setName($key);
            $customProcessConfigs[$key] = $processConfig->buildFromConfig($value);
            if ($processConfig->getClassName() == null) {
                $processConfig->setClassName($this->defaultProcessClass);
            }
        }
        return $customProcessConfigs;
    }

    /**
     * 添加自定义进程
     * @param string $name
     * @param string $processClass
     * @param string $groupName
     * @return ProcessConfig
     * @throws Exception\ConfigException
     * @throws \ReflectionException
     */
    public function addCustomProcessesConfig(string $name, $processClass, string $groupName)
    {
        $processConfig = new ProcessConfig($processClass, $name, $groupName);
        $this->customProcessConfigs[$name] = $processConfig;
        return $processConfig;
    }

    /**
     * 构建进程
     * @throws Exception\ConfigException
     * @throws \ReflectionException
     */
    public function createProcess()
    {
        //配置默认工作进程
        $serverConfig = $this->server->getServerConfig();
        for ($i = 0; $i < $serverConfig->getWorkerNum(); $i++) {
            $defaultProcessClass = $this->getDefaultProcessClass();
            $process = new $defaultProcessClass($this->server, $i, "worker-" . $i, Process::WORKER_GROUP);
            $this->addProcesses($process);
        }
        $startId = $serverConfig->getWorkerNum();
        //重新获取配置
        $this->customProcessConfigs = $this->getCustomProcessConfigs();
        //配置自定义进程
        foreach ($this->customProcessConfigs as $processConfig) {
            $processClass = $processConfig->getClassName();
            $process = new $processClass($this->server, $startId, $processConfig->getName(), $processConfig->getGroupName());
            $this->addProcesses($process);
            $startId++;
        }
    }

    /**
     * 添加进程
     * @param Process $process
     */
    protected function addProcesses(Process $process)
    {
        if ($process->getProcessType() == Process::PROCESS_TYPE_CUSTOM) {
            $process->createProcess();
            $this->server->getServer()->addProcess($process->getSwooleProcess());
        }
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
        $processGroup = new ProcessGroup($this, $groupName, $group);
        $this->groups[$groupName] = $processGroup;
        return $processGroup;
    }


    /**
     * 返回当前服务器主进程的PID。
     * @return int
     */
    public function getMasterPid()
    {
        return $this->server->getServer()->master_pid ?? null;
    }

    /**
     * 返回当前服务器管理进程的PID。
     * @return int
     */
    public function getManagerPid()
    {
        return $this->server->getServer()->manager_pid ?? null;
    }

    /**
     * 得到当前Worker进程的编号
     * @return int
     */
    public function getCurrentProcessId()
    {
        return $this->server->getServer()->worker_id ?? null;
    }

    /**
     * @param $processId
     */
    public function setCurrentProcessId($processId)
    {
        $this->server->getServer()->worker_id = $processId;
    }

    /**
     * 得到当前Worker进程的操作系统进程ID。
     * 与posix_getpid()的返回值相同。
     * @return int
     */
    public function getCurrentProcessPid()
    {
        return $this->server->getServer()->worker_pid;
    }

    /**
     * @param $processPid
     */
    public function setCurrentProcessPid($processPid)
    {
        $this->server->getServer()->worker_pid = $processPid;
    }

    /**
     * 获取当前进程
     * @return Process
     */
    public function getCurrentProcess()
    {
        if ($this->getCurrentProcessId() === null) {
            if ($this->getMasterPid() === null) {
                //说明还没启动
                return $this->masterProcess;
            } else if ($this->getManagerPid() !== null) {
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

    /**
     * @param Process $masterProcess
     */
    public function setMasterProcess(Process $masterProcess): void
    {
        $this->masterProcess = $masterProcess;
    }

    /**
     * @return Process
     */
    public function getManagerProcess(): Process
    {
        return $this->managerProcess;
    }

    /**
     * @return Process
     */
    public function getMasterProcess(): Process
    {
        return $this->masterProcess;
    }
}