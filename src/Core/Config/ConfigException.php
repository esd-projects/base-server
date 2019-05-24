<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/15
 * Time: 15:20
 */

namespace ESD\Core\Config;


use ESD\Core\Exception;

class ConfigException extends Exception
{
    /**
     * @param $object
     * @param $field
     * @param $value
     * @throws ConfigException
     */
    public static function AssertNull($object, $field, $value)
    {
        if ($value === null) {
            $name = get_class($object);
            throw new ConfigException("[{$name}] {$field} cannot be empty");
        }
    }
}