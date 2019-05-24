<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/17
 * Time: 13:16
 */

namespace ESD\Coroutine;


use ESD\Coroutine\Pool\CoroutinePoolExecutor;

/**
 * 协程池工厂
 * Class CoPoolFactory
 * @package ESD\Coroutine
 */
class CoPoolFactory
{
    /**
     * @var CoroutinePoolExecutor[]
     */
    private static $factory = [];

    /**
     * 创建一个协程池
     * @param string $name
     * @param int $corePoolSize
     * @param int $maximumPoolSize
     * @param float $keepAliveTime
     * @return CoroutinePoolExecutor
     * @throws \Exception
     */
    public static function createCoPool(string $name, int $corePoolSize, int $maximumPoolSize, float $keepAliveTime): CoroutinePoolExecutor
    {
        $coPool = new CoroutinePoolExecutor($corePoolSize, $maximumPoolSize, $keepAliveTime);
        self::addCoPool($name, $coPool);
        return $coPool;
    }

    /**
     * 添加协程池
     * @param string $name
     * @param CoroutinePoolExecutor $coroutinePoolExecutor
     * @throws \Exception
     */
    public static function addCoPool(string $name, CoroutinePoolExecutor $coroutinePoolExecutor)
    {
        if (isset(self::$factory[$name])) {
            throw new \Exception("协程池命名重复");
        }
        $coroutinePoolExecutor->setName($name);
        self::$factory[$name] = $coroutinePoolExecutor;
    }

    /**
     * 获取协程池
     * @param string $name
     * @return mixed|null
     */
    public static function getCoPool(string $name)
    {
        return self::$factory[$name] ?? null;
    }

    /**
     * 删除销毁协程池
     * @param string $name
     */
    public static function delCoPool(string $name)
    {
        $pool = self::$factory[$name] ?? null;
        if ($pool != null) {
            $pool->destroy();
            unset(self::$factory[$name]);
        }
    }
}