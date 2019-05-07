<?php
/**
 * Created by PhpStorm.
 * User: administrato
 * Date: 2019/5/6
 * Time: 10:17
 */

namespace GoSwoole\BaseServer\Plugins\DI;


use DI\Container;
use DI\ContainerBuilder;
use GoSwoole\BaseServer\Server\Context;
use GoSwoole\BaseServer\Server\PlugIn\AbstractPlugin;
use GoSwoole\BaseServer\Server\Server;

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

    private function clear_dir($path = null)
    {
        if (is_dir($path)) {    //判断是否是目录
            $p = scandir($path);     //获取目录下所有文件
            foreach ($p as $value) {
                if ($value != '.' && $value != '..') {    //排除掉当./和../
                    if (is_dir($path . '/' . $value)) {
                        $this->clear_dir($path . '/' . $value);    //递归调用删除方法
                        rmdir($path . '/' . $value);    //删除当前文件夹
                    } else {
                        unlink($path . '/' . $value);    //删除当前文件
                    }
                }
            }
        }
    }

    /**
     * 在服务启动前
     * @param Context $context
     * @return mixed
     * @throws \GoSwoole\BaseServer\Exception
     */
    public function beforeServerStart(Context $context)
    {
        //有文件操作必须关闭全局RuntimeCoroutine
        enableRuntimeCoroutine(false);
        $serverConfig = Server::$instance->getServerConfig();
        $cacheProxiesDir = $serverConfig->getBinDir() . '/cache/proxies';
        if (file_exists($cacheProxiesDir)) {
            $this->clear_dir($cacheProxiesDir);
            rmdir($cacheProxiesDir);
        }
        mkdir($cacheProxiesDir, 0777, true);
        $cacheDir = $serverConfig->getBinDir() . "/cache/di";
        if (file_exists($cacheDir)) {
            $this->clear_dir($cacheDir);
            rmdir($cacheDir);
        }
        mkdir($cacheDir, 0777, true);
        $builder = new ContainerBuilder();
        $builder->enableCompilation($cacheDir);
        $builder->writeProxiesToFile(true, $cacheProxiesDir);
        $builder->useAnnotations(true);
        $this->container = $builder->build();
        setContextValue("Container", $this->container);
        $this->container->set(Server::class, $context->getServer());
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