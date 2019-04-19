<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/17
 * Time: 14:10
 */

namespace GoSwoole\BaseServer\Coroutine;

use GoSwoole\BaseServer\Coroutine\Pool\Runnable;
use GoSwoole\BaseServer\Server\Context;
use GoSwoole\BaseServer\Server\Server;

class Co
{
    /**
     * 协程设置
     * @param $data
     */
    public static function set($data): void
    {
        \Swoole\Coroutine::set($data);
    }

    /**
     * 获取协程状态
     * @return array
     */
    public static function getStats(): array
    {
        return \Swoole\Coroutine::stats();
    }

    /**
     * 判断指定协程是否存在
     * @return bool
     */
    public static function isExist($coId): bool
    {
        return \Swoole\Coroutine::isExist($coId);
    }

    /**
     * 获取当前协程的唯一ID, 它的别名为getUid, 是一个进程内唯一的正整数
     * @return int
     */
    public static function getCid(): int
    {
        return \Swoole\Coroutine::getCid();
    }

    /**
     * 获取当前协程的父协程ID
     * @return int
     */
    public static function getPcid(): int
    {
        return \Swoole\Coroutine::getPcid();
    }

    /**
     * 获取当前协程的上下文对象
     * @return array
     */
    public static function getSwooleContext()
    {
        return \Swoole\Coroutine::getContext();
    }

    /**
     * 获取当前协程的上下文对象
     * @return Context
     * @throws \GoSwoole\BaseServer\Exception
     */
    public static function getContext(): Context
    {
        $result = self::getSwooleContext()[Context::storageKey] ?? null;
        if ($result == null) {
            //每次获取的父类都先默认为进程的Context
            $parentContext = null;
            if (Server::$isStart) {
                $parentContext = Server::$instance->getProcessManager()->getCurrentProcess()->getContext();
            }
            self::getSwooleContext()[Context::storageKey] = new Context(Server::$instance, $parentContext);
        }
        return self::getSwooleContext()[Context::storageKey];
    }

    /**
     * 获取当前协程的父级上下文
     * @return Context|null
     */
    public static function getParentContext()
    {
        return self::getSwooleContext()[Context::parentStorageKey] ?? null;
    }

    /**
     * 遍历当前进程内的所有协程。
     * @return \Iterator
     */
    public static function getListCoroutines()
    {
        return \Swoole\Coroutine::getListCoroutines();
    }

    /**
     * 获取协程函数调用栈。
     * @param int $cid
     * @param int $options
     * @param int $limit
     * @return array
     */
    public static function getBackTrace(int $cid = 0, int $options = DEBUG_BACKTRACE_PROVIDE_OBJECT, int $limit = 0): array
    {
        return \Swoole\Coroutine::getBackTrace($cid, $options, $limit);
    }

    /**
     * 让出当前协程的执行权。
     */
    public static function yield()
    {
        \Swoole\Coroutine::yield();
    }

    /**
     * 让出当前协程的执行权。
     * @param int $coroutineId
     */
    public static function resume(int $coroutineId)
    {
        \Swoole\Coroutine::resume($coroutineId);
    }

    /**
     * 执行任务
     * @param $runnable
     * @return int|bool
     */
    public static function runTask($runnable)
    {
        $cid = goWithContext(function () use ($runnable) {
            if ($runnable != null) {
                if ($runnable instanceof Runnable) {
                    $result = $runnable->run();
                    $runnable->sendResult($result);
                }
                if (is_callable($runnable)) {
                    $runnable();
                }
            }
        });
        return $cid;
    }

}