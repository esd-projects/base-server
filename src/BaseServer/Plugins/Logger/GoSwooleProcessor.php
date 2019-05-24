<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/18
 * Time: 17:54
 */

namespace ESD\BaseServer\Plugins\Logger;

use ESD\BaseServer\Server\Server;
use ESD\Coroutine\Co;
use Monolog\Logger;
use Monolog\Processor\ProcessorInterface;

class GoSwooleProcessor implements ProcessorInterface
{
    private $level;

    private $skipClassesPartials;

    private $skipStackFramesCount;

    private $color;

    public function __construct($color = true, $level = Logger::DEBUG, array $skipClassesPartials = array(), $skipStackFramesCount = 0)
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
        if ($process != null) {
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
        }
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
        $level_name = sprintf('%-7s', $level_name);
        if ($this->color) {
            if ($level >= Logger::ERROR) {
                $level_name = "\e[31m" . $level_name . "\e[0m";
            } elseif ($level >= Logger::WARNING) {
                $level_name = "\e[33m" . $level_name . "\e[0m";
            } elseif ($level >= Logger::INFO) {
                $level_name = "\e[32m" . $level_name . "\e[0m";
            } else {
                $level_name = "\e[34m" . $level_name . "\e[0m";
            }
        }
        return $level_name;
    }

    private function handleProcess($processGroup, $processName, $cid)
    {
        $processName = sprintf('%10s', $processName);
        $cid = sprintf('%4s', $cid);
        $result = "[" . sprintf("%30s", "$processGroup|$processName|$cid") . "]";
        if ($this->color) {
            return "\e[35m" . $result . "\e[0m";
        } else {
            return $result;
        }
    }

    private $classNameMax = 40;

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
        $this->classNameMax = max($this->classNameMax, strlen($result));
        $result = sprintf("%-{$this->classNameMax}s", $result);
        if ($this->color) {
            return "\e[36m" . $result . "\e[0m";
        } else {
            return $result;
        }
    }
}
