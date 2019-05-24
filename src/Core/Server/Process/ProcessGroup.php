<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/16
 * Time: 18:32
 */

namespace ESD\Core\Server\Process;

/**
 * 进程组
 * Class ProcessGroup
 * @package ESD\Core\Server
 */
class ProcessGroup
{
    /**
     * @var Process[]
     */
    private $processes = [];

    private $groupName;

    private $index;

    /**
     * @var ProcessManager
     */
    private $processManager;

    public function __construct(ProcessManager $processManager, string $groupName, array $processes)
    {
        $this->processManager = $processManager;
        $this->processes = $processes;
        $this->groupName = $groupName;
        $this->index = 0;
    }

    /**
     * @return Process[]
     */
    public function getProcesses(): array
    {
        return $this->processes;
    }

    /**
     * @return string
     */
    public function getGroupName(): string
    {
        return $this->groupName;
    }

    /**
     * 发送消息
     * @param $message
     */
    public function sendMessageToGroup($message)
    {
        if ($this->index == count($this->processes)) {
            $this->index = 0;
        }
        $process = $this->processes[$this->index];
        $this->processManager->getCurrentProcess()->sendMessage($message, $process);
        $this->index++;
    }
}