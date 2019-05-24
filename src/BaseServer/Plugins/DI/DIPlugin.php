<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/5/6
 * Time: 10:17
 */

namespace ESD\BaseServer\Plugins\DI;


use DI\Container;
use DI\ContainerBuilder;
use ESD\BaseServer\Server\PlugIn\AbstractPlugin;
use ESD\BaseServer\Server\Server;
use ESD\Core\Context\Context;

class DIPlugin extends AbstractPlugin
{
    /**
     * @var Container
     */
    private $container;

    /**
     * 获取插件名字
     * @return string
     */
    public function getName(): string
    {
        return "DI";
    }

    /**
     * 在服务启动前
     * @param Context $context
     * @return mixed
     * @throws \ESD\Core\Exception
     */
    public function beforeServerStart(Context $context)
    {
        //有文件操作必须关闭全局RuntimeCoroutine
        enableRuntimeCoroutine(false);
        $serverConfig = Server::$instance->getServerConfig();
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
        $builder->useAnnotations(true);
        $this->container = $builder->build();
        setContextValue("Container", $this->container);
        Server::$instance->setContainer($this->container);
        $this->container->set(Server::class, Server::$instance);
    }

    /**
     * 在进程启动前
     * @param Context $context
     * @return mixed
     */
    public function beforeProcessStart(Context $context)
    {
        $this->ready();
    }

    /**
     * @return Container
     */
    public function getContainer(): Container
    {
        return $this->container;
    }
}