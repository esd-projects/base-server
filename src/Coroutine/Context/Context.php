<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/17
 * Time: 16:54
 */

namespace GoSwoole\BaseServer\Coroutine\Context;


use GoSwoole\BaseServer\Coroutine\Co;

class Context implements \ArrayAccess
{

    const storageKey = "@context";
    const parentStorageKey = "@parent_context";
    protected $container = [];

    /**
     * 将目标上下文拷贝到下面
     * @param Context $sourceContext
     */
    public static function createWithParent($sourceContext)
    {
        if ($sourceContext != null) {
            Co::getSwooleContext()[self::parentStorageKey] = $sourceContext;
        }
    }

    /**
     * Whether a offset exists
     * @link https://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     * @since 5.0.0
     */
    public function offsetExists($offset)
    {
        return isset($this->container[$offset]);
    }

    /**
     * Offset to retrieve
     * @link https://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        return $this->container[$offset];
    }

    /**
     * Offset to set
     * @link https://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetSet($offset, $value)
    {
        $this->container[$offset] = $value;
    }

    /**
     * Offset to unset
     * @link https://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset($offset)
    {
        unset($this->container[$offset]);
    }

    /**
     * 获取父辈的上下文
     * @return mixed|null
     */
    public function getParentContext()
    {
        return Co::getSwooleContext()[self::parentStorageKey] ?? null;
    }
}