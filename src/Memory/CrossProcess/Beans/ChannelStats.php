<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/17
 * Time: 11:17
 */

namespace GoSwoole\BaseServer\Memory\CrossProcess\Beans;
class ChannelStats
{
    private $queueNum;
    private $queueBytes;

    public function __construct($data)
    {
        $this->queueNum = $data['queue_num'];
        $this->queueBytes = $data['queue_bytes'];
    }

    /**
     * @return mixed
     */
    public function getQueueNum()
    {
        return $this->queueNum;
    }

    /**
     * @return mixed
     */
    public function getQueueBytes()
    {
        return $this->queueBytes;
    }
}