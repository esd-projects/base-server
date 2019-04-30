<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/30
 * Time: 9:46
 */

namespace GoSwoole\BaseServer\Plugins\Config;


use GoSwoole\BaseServer\Server\Exception\ConfigException;
use GoSwoole\BaseServer\Server\Server;
use ReflectionClass;

/**
 * 配置的基础类，配置不允许嵌套，不允许有复杂对象,切命名为驼峰
 * Class BaseConfig
 * @package GoSwoole\BaseServer\Plugins\Config
 */
class BaseConfig
{
    protected static $uuid = 1000;
    private $prefix;
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
        $this->prefix = $prefix;
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
        $config = &$this->config;
        //如果是数组那么还要再深入一层
        if ($this->isArray) {
            if ($this->indexName == null) {
                $index = count($config);
            } else {
                $indexName = $this->indexName;
                $index = $this->$indexName;
                if (empty($index)) {
                    throw new ConfigException("配置错误无法获取到$indexName");
                }
            }
            $this->prefix = $this->prefix . ".$index";
        }
        $prefixs = explode(".", $this->prefix);
        foreach ($prefixs as $value) {
            $config[$value] = [];
            $config = &$config[$value];
        }

        foreach ($this->reflectionClass->getProperties() as $property) {
            if ($property->getDeclaringClass()->getName() == Static::class) {
                $varName = $property->getName();
                if ($this->$varName != null) {
                    if ($this->$varName instanceof BaseConfig) {
                        $this->$varName->merge();
                    } else {
                        $config[$this->changeConnectStyle($varName)] = $this->$varName;
                    }
                }
            }
        }
        //添加到配置上下文中
        Server::$instance->getConfigContext()->appendDeepConfig($this->config, ConfigPlugin::ConfigDeep);
        //合并回配置
        $this->config = Server::$instance->getConfigContext()->get($this->prefix);
        foreach ($this->config as $key => $value) {
            $varName = $this->changeHumpStyle($key);
            $func = "set" . ucfirst($varName);
            call_user_func([$this, $func], $value);
        }
    }

    /**
     * 驼峰修改为"_"连接
     * @param $var
     * @return string
     */
    private function changeConnectStyle($var)
    {
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
     * @return string
     */
    private function changeHumpStyle($var)
    {
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
     * 从config中获取配置
     * @param $config
     * @return BaseConfig
     */
    public function buildFromConfig($config)
    {
        foreach ($config as $key => $value) {
            $varName = $this->changeHumpStyle($key);
            $this->$varName = $value;
        }
        return $this;
    }
}