<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/18
 * Time: 13:52
 */

namespace ESD\Core\Logger;

use ESD\Core\Server\Server;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;

/**
 * Log 插件加载器
 * Class EventPlug
 */
class LoggerStarter
{
    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var StreamHandler
     */
    private $handler;
    /**
     * @var LoggerConfig
     */
    private $loggerConfig;

    /**
     * LoggerPlugin constructor.
     * @param LoggerConfig|null $loggerConfig
     * @throws \ESD\Core\Config\ConfigException
     * @throws \ESD\Core\Exception
     * @throws \ReflectionException
     */
    public function __construct(?LoggerConfig $loggerConfig = null)
    {
        if ($loggerConfig == null) {
            $loggerConfig = new LoggerConfig();

        }
        $this->loggerConfig = $loggerConfig;
        $this->loggerConfig->merge();
        $this->buildLogger();
        $this->handler->setLevel($this->loggerConfig->getLevel());
    }

    /**
     * @throws \ESD\Core\Exception
     * @throws \Exception
     */
    private function buildLogger()
    {
        $this->logger = new Logger($this->loggerConfig->getName());
        $formatter = new LineFormatter($this->loggerConfig->getOutput(),
            $this->loggerConfig->getDateFormat(),
            $this->loggerConfig->isAllowInlineLineBreaks(),
            $this->loggerConfig->isIgnoreEmptyContextAndExtra());
        $serverConfig = Server::$instance->getServerConfig();
        if ($serverConfig->isDaemonize()) {
            $this->handler = new RotatingFileHandler($serverConfig->getBinDir() . "/logs/" . $this->loggerConfig->getName() . ".log",
                $this->loggerConfig->getMaxFiles(),
                Logger::DEBUG);
        } else {
            $this->handler = new StreamHandler('php://stderr', Logger::DEBUG);
        }
        $this->handler->setFormatter($formatter);
        $this->logger->pushProcessor(new GoSwooleProcessor($this->loggerConfig->isColor()));
        $this->logger->pushProcessor(new GoIntrospectionProcessor());
        $this->logger->pushHandler($this->handler);
    }

    /**
     * @return mixed
     */
    public function getLogger()
    {
        return $this->logger;
    }
}