<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/29
 * Time: 14:19
 */

namespace ESD\Core\Config;


class ConfigConfig
{
    /**
     * @var string
     */
    protected $configDir;

    /**
     * ConfigConfig constructor.
     * @param string $configDir
     */
    public function __construct(string $configDir)
    {
        $this->configDir = $configDir;
        if (!is_dir($configDir)) {
            echo "RES_DIR不合法，将不加载配置文件\n";
        }
    }

    /**
     * @return string
     */
    public function getConfigDir(): string
    {
        return $this->configDir;
    }

    /**
     * @param string $configDir
     */
    public function setConfigDir(string $configDir): void
    {
        $this->configDir = $configDir;
    }
}