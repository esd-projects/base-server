<?php
/**
 * Created by PhpStorm.
 * User: administrato
 * Date: 2019/4/17
 * Time: 17:58
 */

const HOOK_TCP = SWOOLE_HOOK_TCP;//TCP Socket类型的stream
const HOOK_UDP = SWOOLE_HOOK_UDP;//UDP Socket类型的stream
const HOOK_UNIX = SWOOLE_HOOK_UNIX;//Unix Stream Socket类型的stream
const HOOK_UDG = SWOOLE_HOOK_UDG;//Unix Dgram Socket类型的stream
const HOOK_SSL = SWOOLE_HOOK_SSL;//SSL Socket类型的stream
const HOOK_TLS = SWOOLE_HOOK_TLS;//TLS Socket类型的stream
const HOOK_SLEEP = SWOOLE_HOOK_SLEEP;//睡眠函数
const HOOK_FILE = SWOOLE_HOOK_FILE;//文件操作
const HOOK_BLOCKING_FUNCTION = SWOOLE_HOOK_BLOCKING_FUNCTION;// 如gethostbyname等阻塞系统调用
const HOOK_ALL = SWOOLE_HOOK_ALL;//打开所有类型

/**
 * 全局打开Runtime协程调度
 * @param bool $enable
 * @param int $flags
 */
function enableRuntimeCoroutine(bool $enable = true, int $flags = HOOK_ALL)
{
    \Swoole\Runtime::enableCoroutine($enable, $flags);
}

/**
 * 序列化
 * @param $data
 * @return string
 */
function serverSerialize($data)
{
    return serialize($data);
}

/**
 * 反序列化
 * @param string $data
 * @return mixed
 */
function serverUnSerialize(string $data)
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
function addTimerTick(int $msec, callable $callback, ... $params)
{
    return \Swoole\Timer::tick($msec, $callback, ...$params);
}

/**
 * 清除一个定时器
 * @param int $timerId
 * @return bool
 */
function clearTimerTick(int $timerId)
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
function addTimerAfter(int $msec, callable $callback, ... $params)
{
    return \Swoole\Timer::after($msec, $callback, ...$params);
}

/**
 * 继承父级的上下文
 * @param callable $run
 */
function goWithContext(callable $run)
{
    $context = null;
    if (\GoSwoole\BaseServer\Coroutine\Co::getCid() > 0) {
        $context = \GoSwoole\BaseServer\Coroutine\Co::getContext();
    }
    go(function () use ($run, $context) {
        \GoSwoole\BaseServer\Coroutine\Context\Context::clone($context);
    });
}