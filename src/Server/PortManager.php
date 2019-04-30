<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/16
 * Time: 17:58
 */

namespace GoSwoole\BaseServer\Server;


use GoSwoole\BaseServer\Server\Config\PortConfig;
use GoSwoole\BaseServer\Server\Exception\ConfigException;

class PortManager
{
    /**
     * @var PortConfig[]
     */
    private $portConfigs = [];
    /**
     * @var ServerPort[]
     */
    private $ports = [];
    /**
     * @var ServerPort[]
     */
    private $namePorts = [];
    /**
     * @var Server
     */
    private $server;
    /**
     * @var string
     */
    private $defaultPortClass;

    public function __construct(Server $server, string $defaultPortClass)
    {
        $this->server = $server;
        $this->defaultPortClass = $defaultPortClass;
    }

    /**
     * 添加端口配置
     * @param $name
     * @param PortConfig $portConfig
     * @param null $portClass
     */
    public function addPortConfig($name, PortConfig $portConfig, $portClass = null)
    {
        $portConfig->setName($name);
        if ($portClass != null) {
            $portConfig->setPortClass($portClass);
        }
        $this->portConfigs[$name] = $portConfig;
    }

    /**
     * 创建端口实例
     * @throws ConfigException
     * @throws \ReflectionException
     */
    public function createPorts()
    {
        //合并配置
        foreach ($this->portConfigs as $portConfig) {
            $portConfig->merge();
        }
        //重新获取配置
        $this->portConfigs = [];
        $configs = Server::$instance->getConfigContext()->get(PortConfig::key);
        foreach ($configs as $key => $value) {
            $portConfig = new PortConfig();
            $this->portConfigs[$key] = $portConfig->buildFromConfig($value);
        }
        if (count($this->portConfigs) == 0) {
            throw new ConfigException("缺少port配置，无法启动服务");
        }
        foreach ($this->portConfigs as $portConfig) {
            $portClass = $portConfig->getPortClass();
            if ($portClass == null) {
                $serverPort = new $this->defaultPortClass($this->server, $portConfig);
            } else {
                $serverPort = new $portClass($this->server, $portConfig);
            }
            if (isset($this->ports[$portConfig->getPort()])) {
                throw new ConfigException("端口号有重复");
            }
            if (!$serverPort instanceof ServerPort) {
                throw new ConfigException("端口实例必须继承ServerPort");
            }
            $this->ports[$portConfig->getPort()] = $serverPort;
            $this->namePorts[$portConfig->getName()] = $serverPort;
        }
    }

    /**
     * @return ServerPort[]
     */
    public function getPorts(): array
    {
        return $this->ports;
    }

    /**
     * 获取对应端口号的port实例
     * @param $portNo
     * @return ServerPort|null
     */
    public function getPortFromPortNo($portNo)
    {
        return $this->ports[$portNo] ?? null;
    }

    /**
     * 获取对应端口号的port实例
     * @param $name
     * @return ServerPort|null
     */
    public function getPortFromName($name)
    {
        return $this->namePorts[$name] ?? null;
    }

    /**
     * 端口中是否包含WebSocket端口
     * @return bool
     */
    public function hasWebSocketPort(): bool
    {
        foreach ($this->ports as $port) {
            if ($port->getPortConfig()->isOpenWebsocketProtocol()) return true;
        }
        return false;
    }

    /**
     * 端口中是否包含Http端口
     * @return bool
     */
    public function hasHttpPort(): bool
    {
        foreach ($this->ports as $port) {
            if ($port->getPortConfig()->isOpenHttpProtocol()) return true;
        }
        return false;
    }

    /**
     * @return string
     */
    public function getDefaultPortClass(): string
    {
        return $this->defaultPortClass;
    }
}