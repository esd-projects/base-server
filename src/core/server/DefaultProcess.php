<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/16
 * Time: 12:04
 */

namespace core\server;

/**
 * 默认的进程实例
 * Class DefaultProcess
 * @package core\server
 */
class DefaultProcess extends Process
{

    public function onProcessStart()
    {
        print_r("processId:" . $this->getProcessId() . "\n");
    }

    public function onProcessStop()
    {
        // TODO: Implement onProcessStop() method.
    }

    public function onPipeMessage(string $message, Process $fromProcess)
    {
        // TODO: Implement onPipeMessage() method.
    }
}