<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/29
 * Time: 14:16
 */

namespace ESD\Core\Config;

use ESD\Core\Server\Server;
use Symfony\Component\Yaml\Yaml;

class ConfigStarter
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
     * @throws \ESD\Core\Exception
     */
    public function __construct(?ConfigConfig $configConfig = null)
    {
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
        $bootstrapFile = $this->configConfig->getConfigDir() . "/bootstrap.yml";
        if (is_file($bootstrapFile)) {
            $this->configContext->addDeepConfig(Yaml::parseFile($bootstrapFile), self::BootstrapDeep);
        }
        $applicationFile = $this->configConfig->getConfigDir() . "/application.yml";
        if (is_file($applicationFile)) {
            $this->configContext->addDeepConfig(Yaml::parseFile($applicationFile), self::ApplicationDeep);
        }
        $active = $this->configContext->get("esd.profiles.active");
        if (!empty($active)) {
            $applicationActiveFile = $this->configConfig->getConfigDir() . "/application-{$active}.yml";
            if (is_file($applicationActiveFile)) {
                $this->configContext->addDeepConfig(Yaml::parseFile($applicationActiveFile), self::ApplicationActiveDeep);
            }
        }
    }

    /**
     * @return ConfigContext
     */
    public function getConfigContext(): ConfigContext
    {
        return $this->configContext;
    }
}