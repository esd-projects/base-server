<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/17
 * Time: 10:29
 */

namespace GoSwoole\BaseServer\Memory\SingleProcess;

/**
 * 让PHP开发者可以像C一样直接读写内存，提升程序的性能，又不用担心内存越界。Buffer会检测offset
 * 无法在多个进程间被共享
 * Class Buffer
 * @package GoSwoole\BaseServer\Memory
 */
class Buffer
{
    private $swooleBuffer;

    public function __construct()
    {
        $this->swooleBuffer = new \swoole_buffer();
    }

    /**
     * 将一个字符串数据追加到缓存区末尾。
     * 执行成功后，会返回新的长度
     * @param string $data 要写入的数据,支持二进制内容
     * @return int
     */
    public function append(string $data)
    {
        return $this->swooleBuffer->append($data);
    }

    /**
     * 从缓冲区中取出内容。
     * 会复制一次内存 (复制指定部分给返回值的字符串) $remove 后实际内存并没有释放，只是底层进行了指针偏移,在某些情况下触发recycle
     * @param int $offset 表示偏移量，如果为负数，表示倒数计算偏移量
     * @param int $length 表示读取数据的长度，默认为从 $offset 到整个缓存区末尾
     * @param bool $remove 表示从缓冲区的头部将此数据移除。只有 $offset = 0 时此参数才有效
     * @return string
     */
    public function substr(int $offset, int $length = -1, bool $remove = false)
    {
        return $this->swooleBuffer->substr($offset, $length, $remove);
    }

    /**
     * 清理缓存区数据。
     * 执行此操作后，缓存区将重置。Buffer对象就可以用来处理新的请求了。
     */
    public function clear()
    {
        $this->swooleBuffer->clear();
    }

    /**
     * 为缓存区扩容。
     * @param int $new_size 指定新的缓冲区尺寸，必须大于当前的尺寸
     */
    public function expand(int $new_size)
    {
        $this->swooleBuffer->clear($new_size);
    }

    /**
     * 向缓存区的任意内存位置写数据。
     * read/write函数可以直接读写内存。
     * 所以使用务必要谨慎，否则可能会破坏现有数据。
     * @param int $offset 偏移量
     * @param string $data 写入的数据
     */
    public function write(int $offset, string $data)
    {
        $this->swooleBuffer->write($offset, $data);
    }

    /**
     * 读取缓存区任意位置的内存。
     * @param int $offset 偏移量
     * @param string $length 要读取的数据长度
     * @return string|bool 如果 $offset 错误或读取的长度超过实际数据的长度，返回 false
     */
    public function read(int $offset, string $length)
    {
        return $this->swooleBuffer->write($offset, $length);
    }

    /**
     * 回收缓冲中已经废弃的内存。
     * 此方法能够在不清空缓冲区和使用 clear() 的情况下，回收通过 substr() 移除但仍存在的部分内存空间。
     */
    public function recycle()
    {
        $this->swooleBuffer->recycle();
    }
}