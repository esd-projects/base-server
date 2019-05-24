<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/18
 * Time: 9:56
 */

namespace ESD\Core\Message;

use ESD\Core\Server\Server;

/**
 * 进程中传递的消息
 * Class Message
 * @package ESD\Core\Server
 */
class Message
{
    /**
     * 类型
     * @var string
     */
    private $type;

    /**
     * 事件内容
     * @var mixed
     */
    private $data;

    /**
     * @var int
     */
    private $fromProcessId;

    public function __construct(string $type, $data)
    {
        $this->type = $type;
        $this->data = $data;
        $this->fromProcessId = Server::$instance->getProcessManager()->getCurrentProcessId();
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     */
    public function setData($data): void
    {
        $this->data = $data;
    }

    public function toString(): string
    {
        $jsonData = json_encode($this->data);
        return "{\"type\":\"$this->type\",\"data\":\"$jsonData\"}";
    }

    /**
     * @return int
     */
    public function getFromProcessId(): int
    {
        return $this->fromProcessId;
    }

    /**
     * @param int $fromProcessId
     */
    public function setFromProcessId(int $fromProcessId): void
    {
        $this->fromProcessId = $fromProcessId;
    }
}