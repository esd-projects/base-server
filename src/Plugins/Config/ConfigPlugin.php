<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/29
 * Time: 14:16
 */

namespace GoSwoole\BaseServer\Plugins\Config;

use GoSwoole\BaseServer\Plugins\DI\DIPlugin;
use GoSwoole\BaseServer\Plugins\Event\EventPlugin;
use GoSwoole\BaseServer\Server\Context;
use GoSwoole\BaseServer\Server\PlugIn\AbstractPlugin;
use GoSwoole\BaseServer\Server\Server;
use Symfony\Component\Yaml\Yaml;

class ConfigPlugin extends AbstractPlugin
{
    //手动设置的Config配置
    const ConfigDeep = 10;
    //bootstrap.yml
    const BootstrapDeep = 9;
    //application.yml
    const ApplicationDeep = 8;
    //application-active.yml
    const ApplicationActiveDeep = 7;
    //远程全局Application配置
    const ConfigServerGlobalApplicationDeep = 6;
    //远程Application配置
    const ConfigServerApplicationDeep = 5;
    //远程Application/Active配置
    const ConfigServerApplicationActiveDeep = 4;
    /**
     * @var ConfigConfig
     */
    protected $configConfig;

    /**
     * @var ConfigContext
     */
    protected $configContext;

    /**
     * ConfigPlugin constructor.
     * @param ConfigConfig|null $configConfig
     * @throws \GoSwoole\BaseServer\Exception
     * @throws \DI\DependencyException
     */
    public function __construct(?ConfigConfig $configConfig = null)
    {
        parent::__construct();
        if ($configConfig == null) {
            if (defined("RES_DIR")) {
                $path = RES_DIR;
            } else {
                $path = Server::$instance->getServerConfig()->getRootDir() . "/resources";
            }
            $configConfig = new ConfigConfig($path);
        }
        $this->configConfig = $configConfig;
        $this->configContext = new ConfigContext();
        Server::$instance->setConfigContext($this->configContext);
        $this->atAfter(EventPlugin::class);
        $this->atAfter(DIPlugin::class);
    }

    /**
     * 获取插件名字
     * @return string
     */
    public function getName(): string
    {
        return "Config";
    }

    /**
     * 在服务启动前
     * @param Context $context
     * @return mixed
     * @throws \GoSwoole\BaseServer\Exception
     */
    public function beforeServerStart(Context $context)
    {
        $bootstrapFile = $this->configConfig->getConfigDir() . "/bootstrap.yml";
        if (is_file($bootstrapFile)) {
            $this->configContext->addDeepConfig(Yaml::parseFile($bootstrapFile), self::BootstrapDeep);
        }
        $applicationFile = $this->configConfig->getConfigDir() . "/application.yml";
        if (is_file($applicationFile)) {
            $this->configContext->addDeepConfig(Yaml::parseFile($applicationFile), self::ApplicationDeep);
        }
        $active = $this->configContext->get("goswoole.profiles.active");
        if (!empty($active)) {
            $applicationActiveFile = $this->configConfig->getConfigDir() . "/application-{$active}.yml";
            if (is_file($applicationActiveFile)) {
                $this->configContext->addDeepConfig(Yaml::parseFile($applicationActiveFile), self::ApplicationActiveDeep);
            }
        }
        setContextValue("configContext", $this->configContext);
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
     * @return ConfigContext
     */
    public function getConfigContext(): ConfigContext
    {
        return $this->configContext;
    }

}