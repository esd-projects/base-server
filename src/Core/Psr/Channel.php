<?php
/**
 * Created by PhpStorm.
 * User: administrato
 * Date: 2019/5/24
 * Time: 13:44
 */

namespace ESD\Core\Psr;


interface Channel
{
    public function push($data, float $timeout = -1): bool;

    public function pop(float $timeout = 0);

    public function length(): int;

    public function isEmpty(): bool;

    public function isFull(): bool;

    public function getCapacity(): int;
}