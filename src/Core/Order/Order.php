<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/5/22
 * Time: 16:36
 */

namespace ESD\Core\Order;


use ESD\Core\Exception;

abstract class Order implements OrderInterface
{
    /**
     * @var string[]
     */
    private $afterClass = [];
    /**
     * @var string[]
     */
    private $beforeClass = [];
    /**
     * @var int
     */
    private $orderIndex = 1;

    /**
     * @var Order[]
     */
    private $afterOrder = [];

    /**
     * 在哪个之后
     * @param $className
     */
    public function atAfter(...$className)
    {
        foreach ($className as $one) {
            $this->afterClass[$one] = $one;
        }
    }

    /**
     * 在哪个之前
     * @param $className
     */
    public function atBefore(...$className)
    {
        foreach ($className as $one) {
            $this->beforeClass[$one] = $one;
        }
    }

    /**
     * @return array
     */
    public function getAfterClass(): array
    {
        return $this->afterClass;
    }

    /**
     * @param Order $root
     * @param int $layer
     * @return int
     * @throws Exception
     */
    public function getOrderIndex(Order $root, int $layer): int
    {
        $layer++;
        if ($layer > 255) throw new Exception(get_class($root) . " 插件排序出现了循环依赖，请检查插件");
        $max = $this->orderIndex;
        foreach ($this->afterOrder as $order) {
            $value = $this->orderIndex + $order->getOrderIndex($root, $layer);
            $max = max($max, $value);
        }
        return $max;
    }

    /**
     * @param Order $afterOrder
     */
    public function addAfterOrder(Order $afterOrder): void
    {
        $this->afterOrder[] = $afterOrder;
    }

    /**
     * @return string[]
     */
    public function getBeforeClass(): array
    {
        return $this->beforeClass;
    }
}