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
class AtomicLong
{
    private $swooleAtomicLong;

    /**
     * 能操作64位有符号整数
     * AtomicLong constructor.
     * @param int $initValue 可以指定初始化的数值，默认为0
     */
    public function __construct($initValue = 0)
    {
        $this->swooleAtomicLong = new \Swoole\Atomic\Long($initValue);
    }

    /**
     * 增加计数
     * @param int $addValue
     * @return int
     */
    public function add(int $addValue = 1)
    {
        return $this->swooleAtomicLong->add($addValue);
    }

    /**
     * 减少计数
     * @param int $subValue
     * @return int
     */
    public function sub(int $subValue = 1)
    {
        return $this->swooleAtomicLong->sub($subValue);
    }

    /**
     * 获取当前计数的值
     * @return int
     */
    public function get()
    {
        return $this->swooleAtomicLong->get();
    }

    /**
     * 将当前值设置为指定的数字。
     * @param int $value
     */
    public function set(int $value): void
    {
        $this->swooleAtomicLong->set($value);
    }

    /**
     * 如果当前数值等于参数1，则将当前数值设置为参数2
     * @param int $cmp_value
     * @param int $set_value
     * @return mixed  如果不等于返回false
     */
    public function cmpset(int $cmp_value, int $set_value)
    {
        return $this->swooleAtomicLong->cmpset($cmp_value, $set_value);
    }
}