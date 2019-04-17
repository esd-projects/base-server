<?php
/**
 * Created by PhpStorm.
 * User: administrato
 * Date: 2019/4/16
 * Time: 17:58
 */

namespace Core\Server;


use Core\Server\Config\PortConfig;
use Core\Server\Exception\ConfigException;

class PortManager
{
    /**
     * @var ServerPort[]
     */
    private $ports = [];
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
     * 通过配置添加一个端口实例和用于初始化实例的class
     * @param PortConfig $portConfig
     * @param null $portClass
     * @return ServerPort
     * @throws ConfigException
     */
    public function addPort(PortConfig $portConfig, $portClass = null)
    {
        if ($portClass == null) {
            $serverPort = new $this->defaultPortClass($portConfig);
        } else {
            $serverPort = new $portClass($portConfig);
        }
        if (isset($this->ports[$portConfig->getPort()])) {
            throw new ConfigException("端口号有重复");
        }
        $this->ports[$portConfig->getPort()] = $serverPort;
        return $serverPort;
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