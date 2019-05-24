<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2019-04-17
 * Time: 17:30
 */

namespace ESD\Core;


class Exception extends \Exception
{
    protected $trace = true;

    /**
     * @return bool
     */
    public function isTrace(): bool
    {
        return $this->trace;
    }

    /**
     * @param bool $trace
     */
    public function setTrace(bool $trace): void
    {
        $this->trace = $trace;
    }


}