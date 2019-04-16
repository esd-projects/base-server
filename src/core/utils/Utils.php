<?php
/**
 * Created by PhpStorm.
 * User: administrato
 * Date: 2019/4/16
 * Time: 13:21
 */

namespace core\utils;


class Utils
{
    /**
     * 序列化
     * @param $data
     * @return string
     */
    public static function serverSerialize($data){
        return serialize($data);
    }

    /**
     * 反序列化
     * @param string $data
     * @return mixed
     */
    public static function serverUnSerialize(string $data){
        return unserialize($data);
    }
}