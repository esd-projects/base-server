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

    private $color;

    public function __construct($level = Logger::DEBUG, array $skipClassesPartials = array(), $skipStackFramesCount = 0, $color = true)
    {
        $this->level = Logger::toMonologLevel($level);
        $this->skipClassesPartials = array_merge(array('Monolog\\'), $skipClassesPartials);
        $this->skipStackFramesCount = $skipStackFramesCount;
        $this->color = $color;
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
        $this->setLength($record);
        return $record;
    }

    /**
     * 设置长度
     * @param $record
     */
    private function setLength(&$record)
    {
        $record['level_name'] = $this->handleLevelName($record['level'], $record['level_name']);
        $record['extra']['class_and_func'] = $this->handleClassName($record['extra']['class'] ?? null, $record['extra']['function'] ?? null);
        $record['extra']['about_process'] = $this->handleProcess($record['extra']['processGroup'] ?? null, $record['extra']['processName'] ?? null, $record['extra']['cid'] ?? null);
    }

    private function handleLevelName($level, $level_name)
    {
        if ($this->color) {
            if ($level >= Logger::ERROR) {
                return "\e[31m" . $level_name . "\e[0m";
            }
            if ($level >= Logger::WARNING) {
                return "\e[33m" . $level_name . "\e[0m";
            } else {
                return "\e[32m" . $level_name . "\e[0m";
            }
        } else {
            return $level_name;
        }

    }

    private function handleProcess($processGroup, $processName, $cid)
    {
        $processName = sprintf('%10s', $processName);
        $cid = sprintf('%4s', $cid);
        $result = "[" . sprintf('%30s', "$processGroup|$processName|$cid") . "]";
        if ($this->color) {
            return "\e[35m" . $result . "\e[0m";
        } else {
            return $result;
        }
    }

    private function handleClassName($class, $func)
    {
        $maxLength = 25;
        if (!empty($class) && strlen($class) > $maxLength) {
            $count = strlen($class);
            $array = explode("\\", $class);
            foreach ($array as &$one) {
                $countOne = strlen($one);
                $one = strtolower($one[0]);
                $count = $count - $countOne + 1;
                if ($count <= $maxLength) break;
            }
            $class = implode(".", $array);
        }
        $class = str_replace("\\", ".", $class);
        if (stristr($func, "{closure}")) {
            $func = "{closure}";
        }
        $result = $class . "::" . $func;
        $result = sprintf('%-40s', $result);
        if ($this->color) {
            return "\e[36m" . $result . "\e[0m";
        } else {
            return $result;
        }
    }
}
