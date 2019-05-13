<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/24
 * Time: 9:43
 */

namespace ESD\BaseServer\Plugins\Event;


use ESD\BaseServer\Coroutine\Channel;

class EventChannel extends Channel
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var bool
     */
    private $once;

    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;

    public function __construct(EventDispatcher $eventDispatcher, string $type, bool $once = false, int $capacity = 1)
    {
        parent::__construct($capacity);
        $this->type = $type;
        $this->once = $once;
        $this->eventDispatcher = $eventDispatcher;
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
     * @return bool
     */
    public function isOnce(): bool
    {
        return $this->once;
    }

    /**
     * @param bool $once
     */
    public function setOnce(bool $once): void
    {
        $this->once = $once;
    }

    public function pop(float $timeout = 0)
    {
        $result = parent::pop($timeout);
        if ($this->once) {
            $this->eventDispatcher->remove($this->type, $this);
        }
        return $result;
    }

    /**
     * @return EventDispatcher
     */
    public function getEventDispatcher(): EventDispatcher
    {
        return $this->eventDispatcher;
    }
}