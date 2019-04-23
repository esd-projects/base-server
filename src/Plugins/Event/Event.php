<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/18
 * Time: 9:28
 */

namespace GoSwoole\BaseServer\Plugins\Event;

/**
 * 本进程内的事件
 * Class Event
 * @package GoSwoole\BaseServer\Plugins\Event
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
}