<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/18
 * Time: 13:52
 */

namespace GoSwoole\BaseServer\Logger;

use GoSwoole\BaseServer\Server\Context;
use GoSwoole\BaseServer\Server\Plug\BasePlug;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\IntrospectionProcessor;

/**
 * Log 插件加载器
 * Class EventPlug
 * @package GoSwoole\BaseServer\Event
 */
class LoggerPlug extends BasePlug
{
    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param Context $context
     * @throws \GoSwoole\BaseServer\Exception
     * @throws \Exception
     */
    private function buildLogger(Context $context)
    {
        $this->logger = new Logger('log');
        $output = "%datetime% \033[32m%level_name%\033[0m --- \033[35m[%extra.processGroup%|%extra.processName%|%extra.cid%]\033[0m  \033[36m%extra.class%\033[0m : %message% %context% \n";
        $formatter = new LineFormatter($output, null, false, true);
        $streamHandler = new StreamHandler('php://stderr', Logger::DEBUG);
        $streamHandler->setFormatter($formatter);
        $this->logger->pushProcessor(new IntrospectionProcessor());
        $this->logger->pushProcessor(new GoSwooleProcessor());
        $this->logger->pushHandler($streamHandler);
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
    }

    /**
     * 在进程启动前
     * @param Context $context
     * @throws \GoSwoole\BaseServer\Exception
     */
    public function beforeProcessStart(Context $context)
    {
        $this->buildLogger($context);
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