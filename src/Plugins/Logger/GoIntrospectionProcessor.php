<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/5/15
 * Time: 14:55
 */

namespace ESD\BaseServer\Plugins\Logger;


use Monolog\Processor\IntrospectionProcessor;

class GoIntrospectionProcessor extends IntrospectionProcessor
{
    public function __construct($level = Logger::DEBUG, array $skipClassesPartials = array(), $skipStackFramesCount = 0)
    {
        $skipClassesPartials = array_merge(array('ESD\\BaseServer\\Plugins\\Logger\\'), $skipClassesPartials);
        parent::__construct($level, $skipClassesPartials, $skipStackFramesCount);
    }
}