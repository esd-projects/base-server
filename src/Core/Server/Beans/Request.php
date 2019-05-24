<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/16
 * Time: 14:53
 */

namespace ESD\Core\Server\Beans;

use ESD\Core\Exception;

/**
 * HTTP请求对象
 * Class Request
 * @package ESD\Core\Server\Beans
 */
class Request
{
    const HEADER_HOST = "host";
    const HEADER_CONNECTION = "connection";
    const HEADER_PRAGMA = "pragma";
    const HEADER_CACHE_CONTROL = "cache-control";
    const HEADER_USER_AGENT = "user-agent";
    const HEADER_ACCEPT = "accept";
    const HEADER_REFERER = "referer";
    const HEADER_ACCEPT_ENCODING = "accept-encoding";
    const HEADER_ACCEPT_LANGUAGE = "accept-language";

    const SERVER_REQUEST_METHOD = "request_method";
    const SERVER_REQUEST_URI = "request_uri";
    const SERVER_PATH_INFO = "path_info";
    const SERVER_REQUEST_TIME = "request_time";
    const SERVER_REQUEST_TIME_FLOAT = "request_time_float";
    const SERVER_SERVER_PORT = "server_port";
    const SERVER_REMOTE_ADDR = "remote_addr";
    const SERVER_MASTER_TIME = "master_time";
    const SERVER_SERVER_PROTOCOL = "server_protocol";

    /**
     * swoole的原始对象
     * @var Swoole\Http\Request
     */
    private $swooleRequest;
    public $header;
    public $server;
    public $get;
    public $post;
    public $cookie;
    public $files;
    public $fd;
    public $streamId;

    public function __construct($swooleRequest)
    {
        $this->swooleRequest = $swooleRequest;
        $this->header = $this->swooleRequest->header;
        $this->server = $this->swooleRequest->server;
        $this->get = $this->swooleRequest->get;
        $this->post = $this->swooleRequest->post;
        $this->cookie = $this->swooleRequest->cookie;
        $this->files = $this->swooleRequest->files;
        $this->fd = $this->swooleRequest->fd;
        $this->streamId = $this->swooleRequest->streamId;
    }

    /**
     * @return mixed
     */
    public function getSwooleRequest()
    {
        return $this->swooleRequest;
    }

    /**
     * 获取原始的POST包体，用于非application/x-www-form-urlencoded格式的Http POST请求。
     * @return string
     */
    public function getRawContent(): string
    {
        return $this->swooleRequest->rawContent();
    }

    /**
     * 获取完整的原始Http请求报文。包括Http Header和Http Body
     * @return string
     */
    public function getData(): string
    {
        return $this->swooleRequest->getData();
    }

    /**
     * @param string $key
     * @return string|null
     */
    public function getHeader(string $key)
    {
        return $this->header[$key] ?? null;
    }

    /**
     * @param string $key
     * @return string|null
     */
    public function getServer(string $key)
    {
        return $this->server[$key] ?? null;
    }

    /**
     * @param string $key
     * @return string|null
     */
    public function getCookie(string $key, $default = null)
    {
        return $this->cookie[$key] ?? $default;
    }


    /**
     * @param string $key
     * @param null $default
     * @return string|null
     */
    public function getGet(string $key, $default = null)
    {
        return $this->get[$key] ?? $default;
    }


    /**
     * @param string $key
     * @return string|null
     * @throws Exception
     */
    public function getGetRequire(string $key)
    {
        $result = $this->get[$key] ?? null;
        if ($result == null) {
            throw new Exception("缺少参数$key");
        }
        return $result;
    }

    /**
     * @return array
     */
    public function get()
    {
        return $this->get;
    }

    /**
     * @return array
     */
    public function post()
    {
        return $this->post;
    }


    /**
     * @param string $key
     * @param null $default
     * @return string|null
     */
    public function getPost(string $key, $default = null)
    {
        return $this->post[$key] ?? $default;
    }

    /**
     * @param string $key
     * @return string|null
     * @throws Exception
     */
    public function getPostRequire(string $key)
    {
        $result = $this->post[$key] ?? null;
        if ($result == null) {
            throw new Exception("缺少参数$key");
        }
        return $result;
    }


    /**
     * @param string $key
     * @param null $default
     * @return string|null
     */
    public function getGetPost(string $key, $default = null)
    {
        return $this->get[$key] ?? $this->post[$key] ?? $default;
    }


    /**
     * @param string $key
     * @param null $default
     * @return string|null
     */
    public function getPostGet(string $key, $default = null)
    {
        return $this->post[$key] ?? $this->get[$key] ?? $default;
    }

    /**
     * @param string $key
     * @return string|null
     * @throws Exception
     */
    public function getGetPostRequire(string $key)
    {
        $result = $this->get[$key] ?? $this->post[$key] ?? null;
        if ($result == null) {
            throw new Exception("缺少参数$key");
        }
        return $result;
    }

    /**
     * @param string $key
     * @return string|null
     * @throws Exception
     */
    public function getPostGetRequire(string $key)
    {
        $result = $this->post[$key] ?? $this->get[$key] ?? null;
        if ($result == null) {
            throw new Exception("缺少参数$key");
        }
        return $result;
    }

    /**
     * 获取requestBody
     * @return mixed|null
     */
    public function getJsonBody()
    {
        return json_decode($this->getRawContent(), true);
    }
}