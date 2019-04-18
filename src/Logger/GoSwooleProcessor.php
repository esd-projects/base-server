<?php
/**
 * Created by PhpStorm.
 * User: administrato
 * Date: 2019/4/18
 * Time: 17:54
 */

namespace GoSwoole\BaseServer\Logger;

use GoSwoole\BaseServer\Coroutine\Co;
use GoSwoole\BaseServer\Server\Server;
use Monolog\Logger;
use Monolog\Processor\ProcessorInterface;

class GoSwooleProcessor implements ProcessorInterface
{
    private $level;

    private $skipClassesPartials;

    private $skipStackFramesCount;

    private $skipFunctions = array(
        'call_user_func',
        'call_user_func_array',
    );

    public function __construct($level = Logger::DEBUG, array $skipClassesPartials = array(), $skipStackFramesCount = 0)
    {
        $this->level = Logger::toMonologLevel($level);
        $this->skipClassesPartials = array_merge(array('Monolog\\'), $skipClassesPartials);
        $this->skipStackFramesCount = $skipStackFramesCount;
    }

    /**
     * @param  array $record
     * @return array
     */
    public function __invoke(array $record)
    {
        // return if the level is not high enough
        if ($record['level'] < $this->level) {
            return $record;
        }
        $process = Server::$instance->getProcessManager()->getCurrentProcess();
        // we should have the call source now
        $record['extra'] = array_merge(
            $record['extra'],
            array(
                'processId' => $process->getProcessId(),
                'processName' => $process->getProcessName(),
                'processGroup' => $process->getGroupName(),
                'cid' => Co::getCid()
            )
        );

        return $record;
    }
}
