<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/17
 * Time: 13:21
 */

namespace ESD\Coroutine\Channel;

use ESD\Core\Channel\Channel;
use ESD\Coroutine\Beans\ChannelStats;

/**
 * 通道，类似于go语言的chan，支持多生产者协程和多消费者协程。
 * 底层自动实现了协程的切换和调度。
 * 通道与PHP的Array类似，仅占用内存，没有其他额外的资源申请，所有操作均为内存操作，无IO消耗。
 * Class ChannelImpl
 * @package ESD\Coroutine
 */
class ChannelImpl implements Channel
{
    const CHANNEL_OK = SWOOLE_CHANNEL_OK;
    const CHANNEL_TIMEOUT = SWOOLE_CHANNEL_TIMEOUT;
    const CHANNEL_CLOSED = SWOOLE_CHANNEL_CLOSED;

    private $swooleChannel;

    public function __construct(int $capacity = 1)
    {
        $this->swooleChannel = new \Swoole\Coroutine\Channel($capacity);
    }

    /**
     * 向通道中写入数据。
     * 为避免产生歧义，请勿向通道中写入空数据，如0、false、空字符串、null
     * @param mixed $data 可以是任意类型的PHP变量，包括匿名函数和资源
     * @param float $timeout 设置超时时间，在通道已满的情况下，push会挂起当前协程，在约定的时间内，如果没有任何消费者消费数据，将发生超时，底层会恢复当前协程，push调用立即返回false，写入失败
     * @return bool
     */
    public function push($data, float $timeout = -1): bool
    {
        return $this->swooleChannel->push($data, $timeout);
    }

    /**
     * 从通道中读取数据。
     * 返回值可以是任意类型的PHP变量，包括匿名函数和资源
     * 通道并关闭时，执行失败返回false
     * @param float $timeout 指定超时时间，浮点型，单位为秒，最小粒度为毫秒，在规定时间内没有生产者push数据，将返回false
     * @return mixed
     */
    public function pop(float $timeout = 0)
    {
        return $this->swooleChannel->pop($timeout);
    }

    /**
     * 循环pop
     * @param $callback
     */
    public function popLoop($callback)
    {
        while (true) {
            $result = $this->pop();
            if ($result === false) break;
            $callback($result);
        }
    }

    /**
     * 获取通道的状态
     * @return ChannelStats
     */
    public function getStats(): ChannelStats
    {
        return new ChannelStats($this->swooleChannel->stats());
    }

    /**
     * 关闭通道。并唤醒所有等待读写的协程。
     */
    public function close()
    {
        $this->swooleChannel->close();
    }

    /**
     * 获取通道中的元素数量
     * @return int
     */
    public function length(): int
    {
        return $this->swooleChannel->length();
    }

    /**
     * 判断当前通道是否为空
     * @return bool
     */
    public function isEmpty(): bool
    {
        return $this->swooleChannel->isEmpty();
    }

    /**
     * 判断当前通道是否已满
     * @return bool
     */
    public function isFull(): bool
    {
        return $this->swooleChannel->isFull();
    }

    /**
     * 构造函数中设定的容量会保存在此，不过如果设定的容量小于1则此变量会等于1
     * @return int
     */
    public function getCapacity(): int
    {
        return $this->swooleChannel->capacity;
    }

    /**
     * 构造函数中设定的容量会保存在此，不过如果设定的容量小于1则此变量会等于1
     * @return int
     */
    public function getErrCode(): int
    {
        return $this->swooleChannel->errCode;
    }
}