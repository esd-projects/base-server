<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/17
 * Time: 17:58
 */

use GoSwoole\BaseServer\Coroutine\Co;
use GoSwoole\BaseServer\Server\Context;
use GoSwoole\BaseServer\Server\Server;
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
function enableRuntimeCoroutine(bool $enable = true, int $flags = HOOK_ALL ^ HOOK_FILE)
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
    $context = getContext();
    go(function () use ($run, $context) {
        $currentContext = Co::getContext();
        //重新设置他的父类为上级协程
        $currentContext->setParentContext($context);
        $run();
    });
}

/**
 * 获取上下文
 * @return \GoSwoole\BaseServer\Server\Context
 */
function getContext()
{
    $context = null;
    if (Server::$instance != null) {
        $context = Server::$instance->getContext();
    }
    if (Server::$isStart) {
        $context = Server::$instance->getProcessManager()->getCurrentProcess()->getContext();
    }
    if (Co::getCid() > 0) {
        $context = Co::getContext();
    }
    return $context ?? new Context(null);
}

/**
 * 获取上下文值
 * @param $key
 * @return mixed
 */
function getContextValue($key)
{
    return getContext()->get($key);
}

/**
 * 获取上下文值
 * @param $key
 * @return mixed
 */
function getContextValueByClassName($key)
{
    return getContext()->getByClassName($key);
}


/**
 * 获取上下文值
 * @param $key
 * @param $value
 * @return mixed
 * @throws \GoSwoole\BaseServer\Exception
 */
function setContextValue($key, $value)
{
    getContext()->add($key, $value);
}

/**
 * 递归父级获取上下文值
 * @param $key
 * @return mixed
 */
function getDeepContextValue($key)
{
    return getContext()->getDeep($key);
}

/**
 * 递归父级获取上下文值
 * @param $key
 * @return mixed
 */
function getDeepContextValueByClassName($key)
{
    return getContext()->getDeepByClassName($key);
}

