<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/18
 * Time: 13:52
 */

namespace ESD\BaseServer\Plugins\Logger;

use ESD\BaseServer\Plugins\Config\ConfigChangeEvent;
use ESD\BaseServer\Plugins\Config\ConfigPlugin;
use ESD\BaseServer\Plugins\Event\EventDispatcher;
use ESD\BaseServer\Server\Context;
use ESD\BaseServer\Server\PlugIn\AbstractPlugin;
use ESD\BaseServer\Server\Server;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;

/**
 * Log 插件加载器
 * Class EventPlug
 * @package ESD\BaseServer\Plugins\Event
 */
class LoggerPlugin extends AbstractPlugin
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
     * @throws \DI\DependencyException
     * @throws \ReflectionException
     */
    public function __construct(?LoggerConfig $loggerConfig = null)
    {
        parent::__construct();
        $this->atAfter(ConfigPlugin::class);
        if ($loggerConfig == null) {
            $loggerConfig = new LoggerConfig();

        }
        $this->loggerConfig = $loggerConfig;
    }

    /**
     * @param Context $context
     * @throws \ESD\BaseServer\Exception
     * @throws \Exception
     */
    private function buildLogger(Context $context)
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
        $context->add("logger", $this->logger);
        Server::$instance->setLog($this->logger);
    }

    /**
     * 在服务启动前
     * @param Context $context
     * @throws \ESD\BaseServer\Exception
     * @throws \Exception
     */
    public function beforeServerStart(Context $context)
    {
        $this->loggerConfig->merge();
        $this->buildLogger($context);
        $this->handler->setLevel($this->loggerConfig->getLevel());
    }

    /**
     * 在进程启动前
     * @param Context $context
     */
    public function beforeProcessStart(Context $context)
    {
        //监控配置更新
        goWithContext(function () use ($context) {
            $eventDispatcher = $context->getDeepByClassName(EventDispatcher::class);
            $channel = $eventDispatcher->listen(ConfigChangeEvent::ConfigChangeEvent);
            $channel->popLoop(function ($result) {
                $this->loggerConfig->merge();
                $this->handler->setLevel($this->loggerConfig->getLevel());
            });
        });
        $this->ready();
    }

    /**
     * 获取插件名字
     * @return string
     */
    public function getName(): string
    {
        return "Logger";
    }

    /**
     * @return mixed
     */
    public function getLogger()
    {
        return $this->logger;
    }
}