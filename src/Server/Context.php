<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/18
 * Time: 12:37
 */

namespace GoSwoole\BaseServer\Server;

use GoSwoole\BaseServer\Exception;

/**
 * Server的上下文
 * Class Context
 * @package GoSwoole\BaseServer\Server
 */
class Context
{
    const storageKey = "@context";
    /**
     * @var array
     */
    private $contain = [];
    /**
     * @var array
     */
    private $classContain = [];

    /**
     * @var Context
     */
    private $parentContext;

    /**
     * @var Server
     */
    private $server;

    /**
     * Context constructor.
     * @param Server $server
     * @param Context|null $parentContext
     * @throws Exception
     */
    public function __construct($server, Context $parentContext = null)
    {
        $this->server = $server;
        if ($server != null) {
            $this->add("server", $server);
        }
        $this->parentContext = $parentContext;
    }

    /**
     * 添加
     * @param $name
     * @param $value
     * @param $overwrite
     * @throws Exception
     */
    public function add($name, $value, $overwrite = false)
    {
        if (isset($this->contain[$name]) && !$overwrite) {
            throw new Exception("已经存在相同名字的上下文");
        }
        $this->contain[$name] = $value;
        if ($value instanceof \stdClass) {
            $this->classContain[get_class($value)] = $value;
        }
    }

    /**
     * 通过类名获取
     * @param $className
     * @return mixed|null
     */
    public function getByClassName($className)
    {
        return $this->classContain[$className] ?? null;
    }

    /**
     * 通过类名递归获取
     * @param $className
     * @return mixed|null
     */
    public function getDeepByClassName($className)
    {
        $result = $this->classContain[$className] ?? null;
        if ($result == null && $this->parentContext != null) {
            return $this->parentContext->getDeepByClassName($className);
        }
        return $result;
    }

    /**
     * 获取Server
     * @return Server|null
     */
    public function getServer()
    {
        return $this->server;
    }

    /**
     * 获取
     * @param $name
     * @return mixed|null
     */
    public function get($name)
    {
        return $this->contain[$name] ?? null;
    }

    /**
     * 递归父级获取
     * @param $name
     * @return null
     */
    public function getDeep($name)
    {
        $result = $this->contain[$name] ?? null;
        if ($result == null && $this->parentContext != null) {
            return $this->parentContext->getDeep($name);
        }
        return $result;
    }

    /**
     * @param Context $parentContext
     */
    public function setParentContext(Context $parentContext): void
    {
        $this->parentContext = $parentContext;
    }
}