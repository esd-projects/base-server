<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/30
 * Time: 9:46
 */

namespace ESD\Core\Config;


use ESD\Core\Server\Server;
use ReflectionClass;

/**
 * 配置的基础类，命名为驼峰
 * Class BaseConfig
 * @package ESD\BaseServer\Plugins\Config
 */
class BaseConfig
{
    protected static $uuid = 1000;
    private $configPrefix;
    private $reflectionClass;
    private $config = [];
    private $isArray;
    private $indexName;

    /**
     * BaseConfig constructor.
     * @param string $prefix
     * @param bool $isArray
     * @param null $indexName
     * @throws \ReflectionException
     */
    public function __construct(string $prefix, bool $isArray = false, $indexName = null)
    {
        $this->configPrefix = $prefix;
        $this->reflectionClass = new ReflectionClass(Static::class);
        $this->isArray = $isArray;
        $this->indexName = $indexName;
    }

    /**
     * 当设置好配置后将合并配置
     * @throws ConfigException
     */
    public function merge()
    {
        $this->config = [];
        $prefix = $this->configPrefix;
        $config = &$this->config;
        //如果是数组那么还要再深入一层
        if ($this->isArray) {
            if ($this->indexName == null) {
                $index = 0;
            } else {
                $indexName = $this->indexName;
                $index = $this->$indexName;
                if (empty($index)) {
                    throw new ConfigException("配置错误无法获取到$indexName");
                }
            }
            $prefix = $prefix . ".$index";
        }
        $prefixs = explode(".", $prefix);
        foreach ($prefixs as $value) {
            $config[$value] = [];
            $config = &$config[$value];
        }
        $config = $this->toConfigArray();
        //添加到配置上下文中
        Server::$instance->getConfigContext()->appendDeepConfig($this->config, ConfigStarter::ConfigDeep);
        //合并回配置
        $this->config = Server::$instance->getConfigContext()->get($prefix);
        $this->buildFromConfig($this->config);
        //注入DI中
        Server::$instance->getContainer()->set(get_class($this), $this);
    }

    /**
     * 驼峰修改为"_"连接
     * @param $var
     * @return mixed
     */
    private function changeConnectStyle($var)
    {
        if (is_numeric($var)) {
            return $var;
        }
        $result = "";
        for ($i = 0; $i < strlen($var); $i++) {
            $str = ord($var[$i]);
            if ($str > 64 && $str < 91) {
                $result .= "_" . strtolower($var[$i]);
            } else {
                $result .= $var[$i];
            }
        }
        return $result;
    }

    /**
     * "_"连接修改为驼峰
     * @param $var
     * @return mixed
     */
    private function changeHumpStyle($var)
    {
        if (is_numeric($var)) {
            return $var;
        }
        $result = "";
        for ($i = 0; $i < strlen($var); $i++) {
            if ($var[$i] == "_") {
                $i = $i + 1;
                $result .= strtoupper($var[$i]);
            } else {
                $result .= $var[$i];
            }
        }
        return $result;
    }

    /**
     * 转换成配置数组
     */
    public function toConfigArray()
    {
        $config = [];
        foreach ($this->reflectionClass->getProperties() as $property) {
            if ($property->getDeclaringClass()->getName() != BaseConfig::class) {
                $varName = $property->getName();
                if ($property->isPrivate()) continue;
                if ($this->$varName !== null) {
                    if (is_array($this->$varName)) {
                        foreach ($this->$varName as $key => $value) {
                            if ($value instanceof BaseConfig) {
                                $config[$this->changeConnectStyle($varName)][$this->changeConnectStyle($key)] = $value->toConfigArray();
                            } else {
                                $config[$this->changeConnectStyle($varName)][$this->changeConnectStyle($key)] = $value;
                            }
                        }
                    } elseif ($this->$varName instanceof BaseConfig) {
                        $config[$this->changeConnectStyle($varName)] = $this->$varName->toConfigArray();
                    } else {
                        $config[$this->changeConnectStyle($varName)] = $this->$varName;
                    }
                }
            }
        }
        return $config;
    }

    /**
     * 从config中获取配置
     * @param $config
     * @return BaseConfig
     */
    public function buildFromConfig($config)
    {
        if ($config == null) return $this;
        foreach ($config as $key => $value) {
            $varName = $this->changeHumpStyle($key);
            $func = "set" . ucfirst($varName);
            if (is_callable([$this, $func])) {
                call_user_func([$this, $func], $value);
            }
        }
        return $this;
    }
}