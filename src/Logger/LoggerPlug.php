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

    private $logger;

    /**
     * 在服务启动前
     * @param Context $context
     * @throws \GoSwoole\BaseServer\Exception
     * @throws \Exception
     */
    public function beforeServerStart(Context $context)
    {
        $this->logger = new Logger('log');
        $output = "%datetime% %level_name% [%extra.processGroup%:%extra.processName%] [cid:%extra.cid%] %extra.class% : %message% %context% \n";
        $formatter = new LineFormatter($output, null, false, true);
        $streamHandler = new StreamHandler('php://stderr', Logger::DEBUG);
        $streamHandler->setFormatter($formatter);
        $this->logger->pushProcessor(new IntrospectionProcessor());
        $this->logger->pushProcessor(new GoSwooleProcessor());
        $this->logger->pushHandler($streamHandler);
        $context->add("logger", $this->logger);
    }

    /**
     * 在进程启动前
     * @param Context $context
     */
    public function beforeProcessStart(Context $context)
    {

    }

    /**
     * 获取插件名字
     * @return string
     */
    public function getName(): string
    {
        return "Log";
    }
}