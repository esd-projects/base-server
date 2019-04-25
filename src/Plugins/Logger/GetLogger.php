<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/25
 * Time: 10:21
 */

namespace GoSwoole\BaseServer\Plugins\Logger;

use GoSwoole\BaseServer\Server\Server;
use Monolog\Logger;

/**
 * 帮助获取logger实例
 * Class GetLogger
 * @package GoSwoole\BaseServer\Plugins\Logger
 */
trait GetLogger
{
    public function log($level, $message, array $context = array())
    {
        Server::$instance->getLog()->log($level, $message, $context);
    }

    /**
     * Adds a log record at the DEBUG level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  string $message The log message
     * @param  array $context The log context
     * @return bool   Whether the record has been processed
     */
    public function debug($message, array $context = array())
    {
        return $this->addRecord(Logger::DEBUG, $message, $context);
    }

    /**
     * Adds a log record.
     *
     * @param  int $level The logging level
     * @param  string $message The log message
     * @param  array $context The log context
     * @return bool Whether the record has been processed
     */
    public function addRecord($level, $message, array $context = array())
    {
        return Server::$instance->getLog()->addRecord($level, $message, $context);
    }

    /**
     * Adds a log record at the INFO level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  string $message The log message
     * @param  array $context The log context
     * @return bool   Whether the record has been processed
     */
    public function info($message, array $context = array())
    {
        return $this->addRecord(Logger::INFO, $message, $context);
    }

    /**
     * Adds a log record at the NOTICE level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  string $message The log message
     * @param  array $context The log context
     * @return bool   Whether the record has been processed
     */
    public function notice($message, array $context = array())
    {
        return $this->addRecord(Logger::NOTICE, $message, $context);
    }

    /**
     * Adds a log record at the WARNING level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  string $message The log message
     * @param  array $context The log context
     * @return bool   Whether the record has been processed
     */
    public function warn($message, array $context = array())
    {
        return $this->addRecord(Logger::WARNING, $message, $context);
    }

    /**
     * Adds a log record at the WARNING level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  string $message The log message
     * @param  array $context The log context
     * @return bool   Whether the record has been processed
     */
    public function warning($message, array $context = array())
    {
        return $this->addRecord(Logger::WARNING, $message, $context);
    }

    /**
     * Adds a log record at the ERROR level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  string $message The log message
     * @param  array $context The log context
     * @return bool   Whether the record has been processed
     */
    public function err($message, array $context = array())
    {
        return $this->addRecord(Logger::ERROR, $message, $context);
    }

    /**
     * Adds a log record at the ERROR level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  string $message The log message
     * @param  array $context The log context
     * @return bool   Whether the record has been processed
     */
    public function error($message, array $context = array())
    {
        return $this->addRecord(Logger::ERROR, $message, $context);
    }

    /**
     * Adds a log record at the CRITICAL level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  string $message The log message
     * @param  array $context The log context
     * @return bool   Whether the record has been processed
     */
    public function crit($message, array $context = array())
    {
        return $this->addRecord(Logger::CRITICAL, $message, $context);
    }

    /**
     * Adds a log record at the CRITICAL level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  string $message The log message
     * @param  array $context The log context
     * @return bool   Whether the record has been processed
     */
    public function critical($message, array $context = array())
    {
        return $this->addRecord(Logger::CRITICAL, $message, $context);
    }

    /**
     * Adds a log record at the ALERT level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  string $message The log message
     * @param  array $context The log context
     * @return bool   Whether the record has been processed
     */
    public function alert($message, array $context = array())
    {
        return $this->addRecord(Logger::ALERT, $message, $context);
    }

    /**
     * Adds a log record at the EMERGENCY level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  string $message The log message
     * @param  array $context The log context
     * @return bool   Whether the record has been processed
     */
    public function emerg($message, array $context = array())
    {
        return $this->addRecord(Logger::EMERGENCY, $message, $context);
    }

    /**
     * Adds a log record at the EMERGENCY level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  string $message The log message
     * @param  array $context The log context
     * @return bool   Whether the record has been processed
     */
    public function emergency($message, array $context = array())
    {
        return $this->addRecord(Logger::EMERGENCY, $message, $context);
    }
}