<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/5/24
 * Time: 14:06
 */

namespace ESD\Core\Event;


interface EventCall
{
    /**
     * EventCall constructor.
     * @param EventDispatcher $eventDispatcher
     * @param string $type
     * @param bool $once
     */
    public function __construct(EventDispatcher $eventDispatcher, string $type, bool $once = false);

    /**
     * @param $data
     * @return mixed
     */
    public function send($data);

    /**
     * @param $fuc
     * @param $timeout
     * @return mixed
     */
    public function call(callable $fuc, $timeout = 5);

    /**
     * @return mixed
     */
    public function destroy();

    /**
     * @return string
     */
    public function getType(): string;

    /**
     * @param string $type
     */
    public function setType(string $type): void;

    /**
     * @return bool
     */
    public function isOnce(): bool;

    /**
     * @param bool $once
     */
    public function setOnce(bool $once): void;

    /**
     * @return EventDispatcher
     */
    public function getEventDispatcher(): EventDispatcher;
}