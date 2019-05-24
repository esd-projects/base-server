<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/17
 * Time: 13:56
 */

namespace ESD\Coroutine\Beans;
/**
 * 通道的状态
 * Class ChannelStats
 * @package ESD\Coroutine\Beans
 */
class ChannelStats
{
    /**
     * 消费者数量，表示当前通道为空，有N个协程正在等待其他协程调用push方法生产数据
     * @var int
     */
    private $consumerNum;
    /**
     * 生产者数量，表示当前通道已满，有N个协程正在等待其他协程调用pop方法消费数据
     * @var int
     */
    private $producerNum;
    /**
     * 通道中的元素数量
     * @var int
     */
    private $queueNum;

    public function __construct($data)
    {
        $this->consumerNum = $data['consumer_num'];
        $this->producerNum = $data['producer_num'];
        $this->queueNum = $data['queue_num'];
    }

    /**
     * @return int
     */
    public function getConsumerNum(): int
    {
        return $this->consumerNum;
    }

    /**
     * @return int
     */
    public function getProducerNum(): int
    {
        return $this->producerNum;
    }

    /**
     * @return int
     */
    public function getQueueNum(): int
    {
        return $this->queueNum;
    }
}