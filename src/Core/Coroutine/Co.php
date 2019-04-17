<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/17
 * Time: 14:10
 */

namespace Core\Coroutine;


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
    public static function getContext()
    {
        return \Swoole\Coroutine::getContext();
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
}