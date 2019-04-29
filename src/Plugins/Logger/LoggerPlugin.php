<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/18
 * Time: 13:52
 */

namespace GoSwoole\BaseServer\Plugins\Logger;

use GoSwoole\BaseServer\Plugins\Config\ConfigChangeEvent;
use GoSwoole\BaseServer\Plugins\Config\ConfigContext;
use GoSwoole\BaseServer\Plugins\Config\ConfigPlugin;
use GoSwoole\BaseServer\Plugins\Event\EventDispatcher;
use GoSwoole\BaseServer\Server\Context;
use GoSwoole\BaseServer\Server\Plugin\AbstractPlugin;
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

    public function __construct()
    {
        parent::__construct();
        $this->atAfter(ConfigPlugin::class);
    }

    /**
     * @param Context $context
     * @throws \GoSwoole\BaseServer\Exception
     * @throws \Exception
     */
    private function buildLogger(Context $context)
    {
        $this->logger = new Logger('log');
        $output = "%datetime% \033[32m%level_name%\033[0m %extra.about_process% %extra.class_and_func% : %message% %context% \n";
        $formatter = new LineFormatter($output, null, false, true);
        $this->streamHandler = new StreamHandler('php://stderr', Logger::DEBUG);
        $this->streamHandler->setFormatter($formatter);
        $this->logger->pushProcessor(new GoSwooleProcessor());
        $this->logger->pushProcessor(new IntrospectionProcessor());
        $this->logger->pushHandler($this->streamHandler);
        $context->add("logger", $this->logger);
    }

    /**
     * 在服务启动前
     * @param Context $context
     * @throws \GoSwoole\BaseServer\Exception
     * @throws \Exception
     */
    public function beforeServerStart(Context $context)
    {
        $this->buildLogger($context);
        $configContext = $context->getDeepByClassName(ConfigContext::class);
        $this->streamHandler->setLevel($configContext->get("goswoole.logger.level", "debug"));
    }

    /**
     * 在进程启动前
     * @param Context $context
     */
    public function beforeProcessStart(Context $context)
    {
        //监控配置更新
        goWithContext(function () use ($context) {
            $configContext = $context->getDeepByClassName(ConfigContext::class);
            $eventDispatcher = $context->getDeepByClassName(EventDispatcher::class);
            $channel = $eventDispatcher->listen(ConfigChangeEvent::ConfigChangeEvent);
            while (true) {
                $channel->pop();
                $this->streamHandler->setLevel($configContext->get("goswoole.logger.level", "debug"));
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
        return "Log";
    }

    /**
     * @return mixed
     */
    public function getLogger()
    {
        return $this->logger;
    }
}