<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/17
 * Time: 10:21
 */

namespace Core\Memory\CrossProcess;

/**
 * 锁
 * Class Lock
 * @package Core\Memory
 */
class Lock
{
    /**
     * 文件锁
     */
    const FILELOCK = SWOOLE_FILELOCK;
    /**
     * 读写锁
     */
    const RWLOCK = SWOOLE_RWLOCK;
    /**
     * 信号量
     */
    const SEM = SWOOLE_SEM;
    /**
     * 互斥锁
     */
    const MUTEX = SWOOLE_MUTEX;
    /**
     * 自旋锁
     */
    const SPINLOCK = SWOOLE_SPINLOCK;

    private $swooleLock;

    public function __construct($type)
    {
        $this->swooleLock = new \swoole_lock($type);
    }

    /**
     * 加锁操作。如果有其他进程持有锁，那这里将进入阻塞，直到持有锁的进程unlock。
     * @return bool
     */
    public function lock()
    {
        return $this->swooleLock->lock();
    }

    /**
     * 加锁操作。与lock方法不同的是，trylock()不会阻塞，它会立即返回。
     * 加锁成功返回true，此时可以修改共享变量。
     * 加锁失败返回false，表示有其他进程持有锁。
     * @return bool
     */
    public function trylock()
    {
        return $this->swooleLock->trylock();
    }

    /**
     * 释放锁
     * @return bool
     */
    public function unlock()
    {
        return $this->swooleLock->unlock();
    }

    /**
     * 只读加锁。
     * 只有RWLOCK和FILELOCK类型的锁支持只读加锁
     * 在持有读锁的过程中，其他进程依然可以获得读锁，可以继续发生读操作
     * @return bool
     */
    public function lockRead()
    {
        return $this->swooleLock->lock_read();
    }

    /**
     * 加锁。此方法与lockRead相同，但是非阻塞的。
     * 调用会立即返回，必须检测返回值以确定是否拿到了锁。
     * @return bool
     */
    public function tryLockRead()
    {
        return $this->swooleLock->trylock_read();
    }

    /**
     * 加锁操作，与lock一致，但lockWait可以设置超时时间。
     * $timeout传入超时时间，默认为1秒
     * 在规定的时间内未获得锁，返回false
     * 加锁成功返回true
     * 只有MUTEX类型的锁支持lockWait
     * @param float $timeout
     * @return bool
     */
    public function lockWait(float $timeout = 1.0)
    {
        return $this->swooleLock->lockwait($timeout);
    }
}