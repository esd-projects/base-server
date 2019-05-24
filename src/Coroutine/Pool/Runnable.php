<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/17
 * Time: 13:44
 */

namespace ESD\Coroutine\Pool;


use ESD\Coroutine\ChannelImpl;
use ESD\Coroutine\Co;

abstract class Runnable
{
    /**
     * @var ChannelImpl
     */
    private $channel;

    /**
     * @var mixed
     */
    private $result;

    public function __construct(bool $needResult = false)
    {
        if ($needResult) {
            $this->channel = new ChannelImpl();
        }
    }

    /**
     * 获取结果
     * @param float $timeOut
     * @return mixed
     */
    public function getResult(float $timeOut = 0)
    {
        if ($this->channel == null) return null;
        if ($this->result == null) {
            $this->result = $this->channel->pop($timeOut);
        }
        $this->channel->close();
        return $this->result;
    }

    /**
     * 发送结果
     * @param $result
     */
    public function sendResult($result)
    {
        if ($this->channel == null) {
            return;
        }
        $this->channel->push($result);
    }

    /**
     * 直接执行
     */
    public function justRun()
    {
        Co::runTask($this);
    }

    abstract function run();
}