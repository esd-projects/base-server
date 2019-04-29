<?php
/**
 * Created by PhpStorm.
 * User: administrato
 * Date: 2019/4/29
 * Time: 14:55
 */

namespace GoSwoole\BaseServer\Plugins\Config;

/**
 * 具有层级关系的配置
 * Class ConfigContext
 * @package GoSwoole\BaseServer\Plugins\Config
 */
class ConfigContext
{
    /**
     * @var array
     */
    protected $contain = [];

    protected $cacheContain = [];

    /**
     * 添加一层配置,按深度逆序排序
     * @param array $config
     * @param $deep
     */
    public function addDeepConfig(array $config, $deep)
    {
        $this->contain[$deep] = $config;
        krsort($this->contain);
        //先合并
        $this->cache();
        $this->conductConfig($this->contain[$deep]);
    }

    /**
     * 多层按顺序进行合并缓存
     */
    protected function cache()
    {
        $this->cacheContain = array_replace_recursive(...$this->contain);
    }

    /**
     * 处理数据
     * @param array $config
     */
    protected function conductConfig(array &$config)
    {
        foreach ($config as &$value) {
            if (is_array($value)) {
                $this->conductConfig($value);
            }
            if (is_string($value)) {
                //处理${}包含的信息
                $result = [];
                preg_match_all("/\\$\{([^\\$]*)\}/i", $value, $result);
                foreach ($result[1] as &$needConduct) {
                    $defaultArray = explode(":", $needConduct);
                    //先获取环境变量
                    $evn = getenv($defaultArray[0]);
                    if ($evn === false) {
                        $evn = $this->get($defaultArray[0]);
                    }
                    if (empty($evn)) {
                        $evn = $defaultArray[1] ?? null;
                    }
                    $needConduct = $evn;
                }
                foreach ($result[0] as $key => $needReplace) {
                    $value = str_replace($needReplace, $result[1][$key], $value);
                }
                $this->cache();
            }
        }
    }

    /**
     * 获取a.b.v这种的值，分隔符默认为"."
     * @param $key
     * @param $separator
     * @return array|mixed|null
     */
    public function get($key, $separator = ".")
    {
        $arr = explode($separator, $key);
        $result = $this->cacheContain;
        foreach ($arr as $value) {
            $result = $result[$value] ?? null;
            if ($result == null) {
                return null;
            }
        }
        return $result;
    }

    /**
     * @param int $deep
     * @return array|null
     */
    public function getContainByDeep(int $deep): ?array
    {
        return $this->contain[$deep] ?? null;
    }

    /**
     * @return array
     */
    public function getCacheContain(): array
    {
        return $this->cacheContain;
    }
}