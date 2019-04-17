<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/17
 * Time: 16:54
 */

namespace GoSwoole\BaseServer\Coroutine\Beans;
class Context
{
    /**
     * @var Context
     */
    private $parent;

    /**
     * Context constructor.
     * @param $parent
     */
    public function __construct($parent)
    {
        $this->parent = $parent;
    }

    public static function background(): Context
    {
        return new Context(null);
    }

    public function createChild(): Context
    {
        return new Context($this);
    }

    public function cancelChild()
    {

    }
}