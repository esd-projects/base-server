<?php
/**
 * Created by PhpStorm.
 * User: administrato
 * Date: 2019/5/6
 * Time: 10:17
 */

namespace GoSwoole\BaseServer\Plugins\DI;


use GoSwoole\BaseServer\Server\Context;
use GoSwoole\BaseServer\Server\Plugin\AbstractPlugin;

class DIPlugin extends AbstractPlugin
{

    /**
     * 获取插件名字
     * @return string
     */
    public function getName(): string
    {
        return "DI";
    }

    /**
     * 在服务启动前
     * @param Context $context
     * @return mixed
     */
    public function beforeServerStart(Context $context)
    {
        // TODO: Implement beforeServerStart() method.
    }

    /**
     * 在进程启动前
     * @param Context $context
     * @return mixed
     */
    public function beforeProcessStart(Context $context)
    {
        $this->ready();
    }
}