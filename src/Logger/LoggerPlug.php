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

    public function __construct()
    {
        parent::__construct();
        $this->atAfter(null);
    }

    /**
     * @param Context $context
     * @throws \GoSwoole\BaseServer\Exception
     * @throws \Exception
     */
    private function buildLogger(Context $context)
    {
        $this->logger = new Logger('log');
        $output = "%datetime% \033[32m%level_name%\033[0m --- %extra.about_process%  %extra.class_and_func% : %message% %context% \n";
        $formatter = new LineFormatter($output, null, false, true);
        $streamHandler = new StreamHandler('php://stderr', Logger::DEBUG);
        $streamHandler->setFormatter($formatter);
        $this->logger->pushProcessor(new GoSwooleProcessor());
        $this->logger->pushProcessor(new IntrospectionProcessor());
        $this->logger->pushHandler($streamHandler);
        $context->add("logger", $this->logger);
        $this->logger->info("加载[Logger]插件");
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
     */
    public function beforeProcessStart(Context $context)
    {
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