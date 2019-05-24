<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/17
 * Time: 10:58
 */

namespace ESD\Core\Memory\CrossProcess;

/**
 * 原子计数操作类，可以方便整数的无锁原子增减。
 * 使用共享内存，可以在不同的进程之间操作计数
 * Class Atomic
 * @package ESD\Core\Memory\CrossProcess
 */
class Atomic
{
    private $swooleAtomic;

    /**
     * 只能操作32位无符号整数，最大支持42亿，不支持负数
     * Atomic constructor.
     * @param int $initValue 可以指定初始化的数值，默认为0
     */
    public function __construct(int $initValue = 0)
    {
        $this->swooleAtomic = new \Swoole\Atomic($initValue);
    }

    /**
     * 增加计数
     * @param int $addValue
     * @return int
     */
    public function add(int $addValue = 1): int
    {
        return $this->swooleAtomic->add($addValue);
    }

    /**
     * 减少计数
     * @param int $subValue
     * @return int
     */
    public function sub(int $subValue = 1): int
    {
        return $this->swooleAtomic->sub($subValue);
    }

    /**
     * 获取当前计数的值
     * @return int
     */
    public function get(): int
    {
        return $this->swooleAtomic->get();
    }

    /**
     * 将当前值设置为指定的数字。
     * @param int $value
     */
    public function set(int $value): void
    {
        $this->swooleAtomic->set($value);
    }

    /**
     * 如果当前数值等于参数1，则将当前数值设置为参数2
     * @param int $cmp_value
     * @param int $set_value
     * @return mixed  如果不等于返回false
     */
    public function cmpset(int $cmp_value, int $set_value)
    {
        return $this->swooleAtomic->cmpset($cmp_value, $set_value);
    }

    /**
     * 当原子计数的值为0时程序进入等待状态。
     * 使用wait/wakeup特性时，原子计数的值只能为0或1，否则会导致无法正常使用
     * 另外一个进程调用wakeup可以再次唤醒程序。
     * 底层基于Linux Futex实现，使用此特性，可以仅用4字节内存实现一个等待、通知、锁的功能。
     * @param float $timeout 指定超时时间，默认为1秒。设置为-1时表示永不超时，会持续等待直到有其他进程唤醒
     * @return bool 超时返回false，错误码为EAGAIN 成功返回true，表示有其他进程通过wakeup成功唤醒了当前的锁
     * 当然原子计数的值为1时，表示不需要进入等待状态，资源当前就是可用。wait函数会立即返回true
     */
    public function wait(float $timeout = 1.0): bool
    {
        return $this->swooleAtomic->wait($timeout);
    }

    /**
     * 唤醒处于wait状态的其他进程。
     * 当前原子计数如果为0时，表示没有进程正在wait，wakeup会立即返回true
     * 当前原子计数如果为1时，表示当前有进程正在wait，wakeup会唤醒等待的进程，并返回true
     * 如果同时有多个进程处于wait状态，$n参数可以控制唤醒的进程数量
     * 被唤醒的进程返回后，会将原子计数设置为0，这时可以再次调用wakeup唤醒其他正在wait的进程
     * @param int $n
     * @return mixed
     */
    public function wakeup(int $n = 1)
    {
        return $this->swooleAtomic->wakeup($n);
    }
}