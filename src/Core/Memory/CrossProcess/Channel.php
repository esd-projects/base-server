<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/17
 * Time: 11:09
 */

namespace Core\Memory\CrossProcess;

use Core\Memory\CrossProcess\Beans\ChannelStats;

/**
 * 用于实现高性能的进程间通信，底层基于共享内存+Mutex互斥锁实现，可实现用户态的高性能内存队列。
 * Channel可用于多进程环境下，底层在读取写入时会自动加锁，应用层不需要担心数据同步问题
 * Class Channel
 * @package Core\Memory\CrossProcess
 */
class Channel
{
    private $swooleChannel;

    /**
     * 创建通道
     * Channel constructor.
     * @param int $size 通道占用的内存的尺寸，单位为字节。最小值为64K，最大值没有限制
     */
    public function __construct(int $size)
    {
        $this->swooleChannel = new \Swoole\Channel($size);
    }

    /**
     * 向通道写入数据
     * @param $data
     * @return bool
     */
    public function push($data): bool
    {
        return $this->swooleChannel->push($data);
    }

    /**
     * 弹出数据,当通道内有数据时自动将数据弹出并还原为PHP变量
     * 当通道内没有任何数据时pop会失败并返回false
     * @return mixed
     */
    public function pop()
    {
        return $this->swooleChannel->pop();
    }

    /**
     * 获取通道的状态
     * @return ChannelStats
     */
    public function stats(): ChannelStats
    {
        return new ChannelStats($this->swooleChannel->stats());
    }
}
