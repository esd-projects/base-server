<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/29
 * Time: 14:19
 */

namespace GoSwoole\BaseServer\Plugins\Config;


use GoSwoole\BaseServer\Server\Exception\ConfigException;

class ConfigConfig
{
    /**
     * @var string
     */
    protected $configDir;

    /**
     * ConfigConfig constructor.
     * @param string $configDir
     * @throws ConfigException
     */
    public function __construct(string $configDir)
    {
        $this->configDir = $configDir;
        if (!is_dir($configDir)) {
            throw new ConfigException("RES_DIR不合法");
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