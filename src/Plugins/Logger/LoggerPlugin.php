<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/18
 * Time: 13:52
 */

namespace GoSwoole\BaseServer\Plugins\Logger;

use GoSwoole\BaseServer\Plugins\Config\ConfigChangeEvent;
use GoSwoole\BaseServer\Plugins\Config\ConfigPlugin;
use GoSwoole\BaseServer\Plugins\Event\EventDispatcher;
use GoSwoole\BaseServer\Server\Context;
use GoSwoole\BaseServer\Server\PlugIn\AbstractPlugin;
use GoSwoole\BaseServer\Server\Server;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\IntrospectionProcessor;

/**
 * Log 插件加载器
 * Class EventPlug
 * @package GoSwoole\BaseServer\Plugins\Event
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
    private $streamHandler;
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
     * @throws \GoSwoole\BaseServer\Exception
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

        } else {
            $this->streamHandler = new StreamHandler('php://stderr', Logger::DEBUG);
            $this->streamHandler->setFormatter($formatter);
        }
        $this->logger->pushProcessor(new GoSwooleProcessor($this->loggerConfig->isColor()));
        $this->logger->pushProcessor(new IntrospectionProcessor());
        $this->logger->pushHandler($this->streamHandler);
        $context->add("logger", $this->logger);
        Server::$instance->setLog($this->logger);
    }

    /**
     * 在服务启动前
     * @param Context $context
     * @throws \GoSwoole\BaseServer\Exception
     * @throws \Exception
     */
    public function beforeServerStart(Context $context)
    {
        $this->loggerConfig->merge();
        $this->buildLogger($context);
        $this->streamHandler->setLevel($this->loggerConfig->getLevel());
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
            while (true) {
                $channel->pop();
                $this->loggerConfig->merge();
                $this->streamHandler->setLevel($this->loggerConfig->getLevel());
            }
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