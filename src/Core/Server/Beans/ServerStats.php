<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/16
 * Time: 14:40
 */

namespace ESD\Core\Server\Beans;

/**
 * 服务器状态
 * Class ServerStats
 * @package ESD\Core\Server\Beans
 */
class ServerStats
{
    /**
     * 服务器启动的时间
     * @var int
     */
    private $startTime;
    /**
     * 当前连接的数量
     * @var int
     */
    private $connectionNum;
    /**
     * 接受了多少个连接
     * @var int
     */
    private $acceptCount;
    /**
     * 关闭的连接数量
     * @var int
     */
    private $closeCount;
    /**
     * 当前正在排队的任务数
     * @var int
     */
    private $taskingNum;
    /**
     * Server收到的请求次数
     * @var int
     */
    private $requestCount;
    /**
     * 当前Worker进程收到的请求次数
     * @var int
     */
    private $workerRequestCount;
    /**
     * master进程向当前Worker进程投递任务的计数，在master进程进行dispatch时增加计数
     * @var int
     */
    private $workerDispatchCount;
    /**
     * 消息队列中的task数量
     * @var int
     */
    private $taskQueueNum;
    /**
     * 消息队列的内存占用字节数
     * @var int
     */
    private $taskQueueBytes;
    /**
     * 当前协程数量
     * @var int
     */
    private $coroutineNum;

    public function __construct($data)
    {
        $this->startTime = $data['start_time']??null;
        $this->connectionNum = $data['connection_num']??null;
        $this->acceptCount = $data['accept_count']??null;
        $this->closeCount = $data['close_count']??null;
        $this->taskingNum = $data['tasking_num']??null;
        $this->requestCount = $data['request_count']??null;
        $this->workerRequestCount = $data['worker_request_count']??null;
        $this->workerDispatchCount = $data['worker_dispatch_count']??null;
        $this->taskQueueNum = $data['task_queue_num']??null;
        $this->taskQueueBytes = $data['task_queue_bytes']??null;
        $this->coroutineNum = $data['coroutine_num']??null;
    }

    /**
     * @return int
     */
    public function getStartTime()
    {
        return $this->startTime;
    }

    /**
     * @return int
     */
    public function getConnectionNum()
    {
        return $this->connectionNum;
    }

    /**
     * @return int
     */
    public function getAcceptCount()
    {
        return $this->acceptCount;
    }

    /**
     * @return int
     */
    public function getCloseCount()
    {
        return $this->closeCount;
    }

    /**
     * @return int
     */
    public function getTaskingNum()
    {
        return $this->taskingNum;
    }

    /**
     * @return int
     */
    public function getRequestCount()
    {
        return $this->requestCount;
    }

    /**
     * @return int
     */
    public function getWorkerRequestCount()
    {
        return $this->workerRequestCount;
    }

    /**
     * @return int
     */
    public function getWorkerDispatchCount()
    {
        return $this->workerDispatchCount;
    }

    /**
     * @return int
     */
    public function getTaskQueueNum()
    {
        return $this->taskQueueNum;
    }

    /**
     * @return int
     */
    public function getTaskQueueBytes()
    {
        return $this->taskQueueBytes;
    }

    /**
     * @return int
     */
    public function getCoroutineNum()
    {
        return $this->coroutineNum;
    }
}