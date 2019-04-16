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

}