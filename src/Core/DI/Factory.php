<?php
/**
 * Created by PhpStorm.
 * User: administrato
 * Date: 2019/5/24
 * Time: 17:20
 */

namespace ESD\Core\DI;


interface Factory
{
    public function create($params);
}