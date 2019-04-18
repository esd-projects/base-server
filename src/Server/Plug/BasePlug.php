<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/18
 * Time: 14:19
 */

namespace GoSwoole\BaseServer\Server\Plug;


/**
 * 基础插件，插件类需要继承
 * Class BasePlug
 * @package GoSwoole\BaseServer\Server\Plug
 */
abstract class BasePlug implements Plug
{
    /**
     * @var string
     */
    private $afterClass;
    /**
     * @var Plug
     */
    private $afterPlug;
    /**
     * @var int
     */
    private $orderIndex = 1;

    /**
     * 在哪个之后
     * @param $className
     */
    public function atAfter($className)
    {
        $this->afterClass = $className;
    }

    /**
     * 发送准备好的信号，插件全部准备好，服务才能接受访问
     */
    public function ready()
    {

    }

    /**
     * @return mixed
     */
    public function getAfterClass()
    {
        return $this->afterClass;
    }


    /**
     * @return int
     */
    public function getOrderIndex(): int
    {
        if ($this->afterClass != null) {
            return $this->orderIndex + $this->afterPlug->getOrderIndex();
        }
        return $this->orderIndex;
    }

    /**
     * @param mixed $afterPlug
     */
    public function setAfterPlug($afterPlug): void
    {
        $this->afterPlug = $afterPlug;
    }
}