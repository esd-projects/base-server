<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/17
 * Time: 10:40
 */

namespace ESD\Core\Memory\CrossProcess;

/**
 * 一个基于共享内存和锁实现的超高性能，并发数据结构。用于解决多进程/多线程数据共享和同步加锁问题。
 * 支持多进程
 * 使用共享内存来保存数据，在创建子进程前，务必要执行Table->create()
 * Table->create() 必须在Server->start()前执行
 * Class Table
 * @package ESD\BaseServer\Memory
 */
class Table implements \Iterator, \Countable
{
    /**
     * 默认为4个字节，可以设置1，2，4，8一共4种长度
     */
    const TYPE_INT = \Swoole\Table::TYPE_INT;
    /**
     * 会占用8个字节的内存
     */
    const TYPE_FLOAT = \Swoole\Table::TYPE_FLOAT;
    /**
     * 设置后，设置的字符串不能超过此长度
     */
    const TYPE_STRING = \Swoole\Table::TYPE_STRING;


    private $swooleTable;

    /**
     * 创建内存表。
     * Table constructor.
     * @param int $size 参数指定表格的最大行数，如果$size不是为2的N次方，如1024、8192,65536等，底层会自动调整为接近的一个数字，如果小于1024则默认成1024，即1024是最小值
     * @param float $conflictProportion
     */
    public function __construct(int $size, float $conflictProportion = 0.2)
    {
        $this->swooleTable = new \Swoole\Table($size, $conflictProportion);
    }

    /**
     * 内存表增加一列
     * @param string $name 字段的名称
     * @param int $type 字段类型，支持3种类型，TYPE_INT, TYPE_FLOAT, TYPE_STRING
     * Table::TYPE_INT默认为4个字节，可以设置1，2，4，8一共4种长度
     * Table::TYPE_STRING设置后，设置的字符串不能超过此长度
     * Table::TYPE_FLOAT会占用8个字节的内存
     * @param int $size 字符串字段的最大长度，单位为字节。字符串类型的字段必须指定$size
     */
    public function column(string $name, int $type, int $size = 0)
    {
        $this->swooleTable->column($name, $type, $size);
    }

    /**
     * 创建内存表。
     * 定义好表的结构后，执行create向操作系统申请内存，创建表
     * 调用create之前不能使用set、get等数据读写操作方法
     * 调用create之后不能使用column方法添加新字段
     * 系统内存不足，申请失败，create返回false
     * 申请内存成功，create返回true
     * @return bool
     */
    public function create(): bool
    {
        return $this->swooleTable->create();
    }

    /**
     * 设置行的数据，Table使用key-value的方式来访问数据。
     * 可以设置全部字段的值，也可以只修改部分字段
     * 未设置前，该行数据的所有字段均为空
     * 如果传入字符串长度超过了列定义时设定的最大尺寸，底层会自动截断。
     * @param string $key 数据的key，相同的$key对应同一行数据，如果set同一个key，会覆盖上一次的数据,Key非二进制安全，必须为字符串类型，不得传入二进制数据
     * @param array $value 必须是一个数组，必须与字段定义的$name完全相同
     */
    public function set(string $key, array $value): void
    {
        $this->swooleTable->set($key, $value);
    }

    /**
     * 原子自增操作。
     * @param string $key 指定数据的key，如果$key对应的行不存在，默认列的值为0
     * @param string $column 指定列名，仅支持浮点型和整型字段
     * @param mixed $incrby 增量，默认为1。如果列为整形，$incrby必须为int型，如果列为浮点型，$incrby必须为float类型
     * @return int|float 返回最终的结果数值
     */
    public function incr(string $key, string $column, $incrby = 1)
    {
        return $this->swooleTable->incr($key, $column, $incrby);
    }

    /**
     * 原子自减操作。
     * @param string $key 指定数据的key，如果$key对应的行不存在，默认列的值为0
     * @param string $column 指定列名，仅支持浮点型和整型字段
     * @param mixed $incrby 增量，默认为1。如果列为整形，$incrby必须为int型，如果列为浮点型，$incrby必须为float类型
     * @return int|float 返回最终的结果数值
     */
    public function decr(string $key, string $column, $incrby = 1)
    {
        return $this->swooleTable->decr($key, $column, $incrby);
    }

    /**
     * 获取一行数据
     * @param string $key 指定查询数据行的KEY，必须为字符串类型
     * @param string|null $field 当指定了$field时仅返回该字段的值，而不是整个记录
     * @return mixed 返回最终的结果数值 $key不存在，将返回false,成功返回结果数组
     */
    public function get(string $key, string $field = null)
    {
        return $this->swooleTable->get($key, $field);
    }

    /**
     * 检查table中是否存在某一个key。
     * @param string $key
     * @return bool
     */
    public function exist(string $key): bool
    {
        return $this->swooleTable->exist($key);
    }

    /**
     * 返回table中存在的条目数
     * @return int
     */
    public function count(): int
    {
        return $this->swooleTable->count();
    }

    /**
     * 删除数据
     * @param $key $key对应的数据不存在，将返回false
     * @return bool 成功删除返回true
     */
    public function del($key): bool
    {
        return $this->swooleTable->del($key);
    }

    /**
     * Return the current element
     * @link https://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     * @since 5.0.0
     */
    public function current()
    {
        return $this->swooleTable->current();
    }

    /**
     * Move forward to next element
     * @link https://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function next()
    {
        return $this->swooleTable->next();
    }

    /**
     * Return the key of the current element
     * @link https://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     * @since 5.0.0
     */
    public function key()
    {
        return $this->swooleTable->key();
    }

    /**
     * Checks if current position is valid
     * @link https://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     * @since 5.0.0
     */
    public function valid()
    {
        return $this->swooleTable->valid();
    }

    /**
     * Rewind the Iterator to the first element
     * @link https://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function rewind()
    {
        return $this->swooleTable->rewind();
    }
}