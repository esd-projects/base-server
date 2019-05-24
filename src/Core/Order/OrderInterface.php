<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/5/22
 * Time: 16:41
 */

namespace ESD\Core\Order;


interface OrderInterface
{
    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @param Order $root
     * @param int $layer
     * @return int
     */
    public function getOrderIndex(Order $root, int $layer): int;

    /**
     * @param mixed $afterPlug
     */
    public function addAfterOrder(Order $afterPlug);

    /**
     * @param $className
     * @return void
     */
    public function atAfter(...$className);

    /**
     * @param $className
     * @return void
     */
    public function atBefore(...$className);

    /**
     * @return array
     */
    public function getAfterClass(): array;

    /**
     * @return array
     */
    public function getBeforeClass(): array;

}