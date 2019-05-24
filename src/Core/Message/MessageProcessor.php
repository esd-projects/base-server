<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/18
 * Time: 9:59
 */

namespace ESD\Core\Message;

use ESD\Core\Exception;

/**
 * 消息处理器
 * Class MessageProcessor
 * @package ESD\Core\Message
 */
abstract class MessageProcessor
{
    /**
     * @var MessageProcessor[]
     */
    private static $messageProcessorMap = [];

    /**
     * 消息类型
     * @var string
     */
    protected $type;


    public function __construct(string $type)
    {
        $this->type = $type;
    }

    /**
     * 添加处理程序
     * @param MessageProcessor $messageProcessor
     * @param bool $overwrite
     * @throws Exception
     */
    public static function addMessageProcessor(MessageProcessor $messageProcessor, bool $overwrite = false)
    {
        if (isset(self::$messageProcessorMap[$messageProcessor->type]) && !$overwrite) {
            throw new Exception("拥有类型相同的消息处理程序");
        }
        self::$messageProcessorMap[$messageProcessor->type] = $messageProcessor;
    }

    /**
     * 分配消息
     * @param Message $message
     * @return bool
     */
    public static function dispatch(Message $message): bool
    {
        $processor = self::$messageProcessorMap[$message->getType()] ?? null;
        if ($processor != null) {
            return $processor->handler($message);
        }
        return false;
    }

    /**
     * 处理消息
     * @param Message $message
     * @return mixed
     */
    abstract public function handler(Message $message): bool;
}