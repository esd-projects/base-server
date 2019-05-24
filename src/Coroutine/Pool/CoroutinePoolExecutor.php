<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/17
 * Time: 13:18
 */

namespace ESD\Coroutine\Pool;

use ESD\Coroutine\ChannelImpl;
use ESD\Coroutine\Co;

/**
 * 协程池
 * Class CoroutinePoolExecutor
 * @package ESD\Coroutine\Pool
 */
class CoroutinePoolExecutor
{
    /**
     * 通道
     * @var ChannelImpl
     */
    private $channel;

    /**
     * 是否销毁了
     * @var bool
     */
    private $isDestroy = false;

    /**
     * cid集合
     * @var array
     */
    private $cids;

    /**
     * 协程池中的核心协程数
     * @var int
     */
    private $corePoolSize;

    /**
     *  协程池中允许的最大协程数
     * @var int
     */
    private $maximumPoolSize;

    /**
     * 单位毫秒，协程空闲时的存活时间，
     * @var int
     */
    private $keepAliveTime;

    /**
     * 名称
     * @var string
     */
    private $name;

    /**
     * 协程池
     * CoroutinePoolExecutor constructor.
     * @param int $corePoolSize 协程池中的核心协程数，当提交一个任务时，协程池创建一个新协程执行任务，直到当前协程数等于corePoolSize；如果当前协程数为corePoolSize，继续提交的任务被保存到阻塞队列中，等待被执行；如果执行了协程池的prestartAllCoreThreads()方法，协程池会提前创建并启动所有核心协程。
     * @param int $maximumPoolSize 协程池中允许的最大协程数。如果当前阻塞队列满了，且继续提交任务，则创建新的协程执行任务，前提是当前协程数小于maximumPoolSize；
     * @param float $keepAliveTime 单位秒，协程空闲时的存活时间，即当协程没有任务执行时，继续存活的时间；默认情况下，该参数只在协程数大于corePoolSize时才有用；
     */
    public function __construct(int $corePoolSize, int $maximumPoolSize, float $keepAliveTime)
    {
        $this->channel = new ChannelImpl($corePoolSize);
        $this->cids = [];
        $this->corePoolSize = $corePoolSize;
        $this->maximumPoolSize = $maximumPoolSize;
        $this->keepAliveTime = $keepAliveTime;
    }

    /**
     * 协程池会提前创建并启动所有核心协程。
     */
    public function preStartAllCoreThreads(): void
    {
        for ($i = 0; $i < $this->corePoolSize; $i++) {
            $this->createNewCoroutine(null, 0);
        }
    }

    /**
     * 创建一个新的协程
     * @param Runnable $runnable
     * @param float $keepAliveTime
     */
    private function createNewCoroutine($runnable, float $keepAliveTime): void
    {
        $cid = goWithContext(function () use ($runnable, $keepAliveTime) {
            defer(function () {
                unset($this->cids[Co::getCid()]);
            });
            if ($runnable != null) {
                if ($runnable instanceof Runnable) {
                    $result = $runnable->run();
                    $runnable->sendResult($result);
                }
                if (is_callable($runnable)) {
                    $runnable();
                }
            }
            while (true) {
                $runnable = $this->channel->pop($keepAliveTime);
                if ($runnable === false) break;
                if ($runnable instanceof Runnable) {
                    $result = $runnable->run();
                    $runnable->sendResult($result);
                } else if (is_callable($runnable)) {
                    $runnable();
                }
            }
        });
        $this->cids[$cid] = $cid;
    }

    /**
     * 销毁
     */
    public function destroy()
    {
        $this->isDestroy = true;
        $this->channel->close();
        $this->cids = null;
        $this->name = null;
        $this->channel = null;
    }

    /**
     * 执行任务
     * @param $runnable
     * @throws \Exception
     */
    public function execute($runnable)
    {
        if ($this->isDestroy()) {
            throw new \Exception("协程池已经销毁，不能执行任务");
        }
        $coCount = count($this->cids);
        //如果小于核心协程数量将继续创建并执行任务
        if ($coCount < $this->corePoolSize) {
            $this->createNewCoroutine($runnable, 0);
            return;
        }
        //当通道的消费者数量为0即代表消费者都在执行任务，这是如果有新的任务来，协程数量小于maximumPoolSize就会继续创建协程
        if ($this->channel->getStats()->getConsumerNum() == 0 && $coCount < $this->maximumPoolSize) {
            $this->createNewCoroutine($runnable, $this->keepAliveTime);
            return;
        }
        //扔到通道中
        $this->channel->push($runnable);
    }

    /**
     * @return bool
     */
    public function isDestroy(): bool
    {
        return $this->isDestroy;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name): void
    {
        $this->name = $name;
    }

    /**
     * @return array
     */
    public function getCids(): array
    {
        return $this->cids;
    }

}