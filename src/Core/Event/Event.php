<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/18
 * Time: 9:28
 */

namespace ESD\Core\Event;

/**
 * 本进程内的事件
 * Class Event
 * @package ESD\BaseServer\Plugins\Event
 */
class Event
{
    /**
     * 事件类型
     * @var string
     */
    private $type;

    /**
     * 事件内容
     * @var mixed
     */
    private $data;

    /**
     * 消息发出的进程id
     * @var int
     */
    private $processId;

    public function __construct(string $type, $data)
    {
        $this->type = $type;
        $this->data = $data;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return int|null
     */
    public function getProcessId(): ?int
    {
        return $this->processId;
    }

    /**
     * @param int $processId |null
     */
    public function setProcessId(?int $processId): void
    {
        $this->processId = $processId;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }
}