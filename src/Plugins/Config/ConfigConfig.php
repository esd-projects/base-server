<?php
/**
 * Created by PhpStorm.
 * User: administrato
 * Date: 2019/4/29
 * Time: 14:19
 */

namespace GoSwoole\BaseServer\Plugins\Config;


class ConfigConfig
{
    /**
     * @var string
     */
    protected $configDir;

    public function __construct(string $configDir)
    {
        $this->configDir = $configDir;
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