<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/16
 * Time: 13:21
 */

namespace GoSwoole\BaseServer\Utils;


class Utils
{
    const SWOOLE_HOOK_TCP = SWOOLE_HOOK_TCP;//TCP Socket类型的stream
    const SWOOLE_HOOK_UDP = SWOOLE_HOOK_UDP;//UDP Socket类型的stream
    const SWOOLE_HOOK_UNIX = SWOOLE_HOOK_UNIX;//Unix Stream Socket类型的stream
    const SWOOLE_HOOK_UDG = SWOOLE_HOOK_UDG;//Unix Dgram Socket类型的stream
    const SWOOLE_HOOK_SSL = SWOOLE_HOOK_SSL;//SSL Socket类型的stream
    const SWOOLE_HOOK_TLS = SWOOLE_HOOK_TLS;//TLS Socket类型的stream
    const SWOOLE_HOOK_SLEEP = SWOOLE_HOOK_SLEEP;//睡眠函数
    const SWOOLE_HOOK_FILE = SWOOLE_HOOK_FILE;//文件操作
    const SWOOLE_HOOK_STREAM_SELECT = SWOOLE_HOOK_STREAM_SELECT;// stream_select函数
    const SWOOLE_HOOK_BLOCKING_FUNCTION = SWOOLE_HOOK_BLOCKING_FUNCTION;// 如gethostbyname等阻塞系统调用
    const SWOOLE_HOOK_ALL = SWOOLE_HOOK_ALL;//打开所有类型

    /**
     * 全局打开Runtime协程调度
     * @param bool $enable
     * @param int $flags
     */
    public static function enableRuntimeCoroutine(bool $enable = true, int $flags = self::SWOOLE_HOOK_ALL)
    {
        \Swoole\Runtime::enableCoroutine(true, $flags);
    }

    /**
     * 序列化
     * @param $data
     * @return string
     */
    public static function serverSerialize($data)
    {
        return serialize($data);
    }

    /**
     * 反序列化
     * @param string $data
     * @return mixed
     */
    public static function serverUnSerialize(string $data)
    {
        return unserialize($data);
    }

    /**
     * 添加一个定时器
     * @param int $msec
     * @param callable $callback
     * @param array $params
     * @return int
     */
    public static function addTimerTick(int $msec, callable $callback, ... $params)
    {
        return \Swoole\Timer::tick($msec, $callback, ...$params);
    }

    /**
     * 清除一个定时器
     * @param int $timerId
     * @return bool
     */
    public static function clearTimerTick(int $timerId)
    {
        return \Swoole\Timer::clear($timerId);
    }

    /**
     * 添加一个定时器
     * @param int $msec
     * @param callable $callback
     * @param array $params
     * @return int
     */
    public static function addTimerAfter(int $msec, callable $callback, ... $params)
    {
        return \Swoole\Timer::after($msec, $callback, ...$params);
    }
}