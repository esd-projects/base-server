<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/5/24
 * Time: 16:15
 */

namespace ESD\Core\DI;


use DI\ContainerBuilder;
use ESD\Core\Server\Config\ServerConfig;

class DI
{
    public static $definitions = [];
    /**
     * @var DI
     */
    private static $instance;
    /**
     * @var \DI\Container
     */
    private $container;

    /**
     * DI constructor.
     * @param ServerConfig $serverConfig
     * @throws \ESD\Core\Exception
     */
    public function __construct(ServerConfig $serverConfig)
    {
        $cacheProxiesDir = $serverConfig->getCacheDir() . '/proxies';
        if (!file_exists($cacheProxiesDir)) {
            mkdir($cacheProxiesDir, 0777, true);
        }
        $cacheDir = $serverConfig->getCacheDir() . "/di";
        if (!file_exists($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }
        $builder = new ContainerBuilder();
        if (!$serverConfig->isDebug()) {
            $builder->enableCompilation($cacheDir);
            $builder->writeProxiesToFile(true, $cacheProxiesDir);
        }
        $builder->addDefinitions(self::$definitions);
        $builder->useAnnotations(true);
        $this->container = $builder->build();
    }

    /**
     * @param ServerConfig $serverConfig
     * @return DI
     * @throws \ESD\Core\Exception
     */
    public static function getInstance(ServerConfig $serverConfig = null)
    {
        if (self::$instance == null) {
            self::$instance = new DI($serverConfig);
        }
        return self::$instance;
    }

    /**
     * @return \DI\Container
     */
    public function getContainer(): \DI\Container
    {
        return $this->container;
    }
}