<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/5/15
 * Time: 11:56
 */

namespace ESD\BaseServer\Plugins\Logger;


use ESD\BaseServer\Exception;

class Logger extends \Monolog\Logger
{
    public function addRecord($level, $message, array $context = array())
    {
        if ($message instanceof Exception) {
            if (!$message->isTrace()) {
                return parent::addRecord(\Monolog\Logger::DEBUG, $message, $context);
            }
        }
        return parent::addRecord($level, $message, $context);
    }
}