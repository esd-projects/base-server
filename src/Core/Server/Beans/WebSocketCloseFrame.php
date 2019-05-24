<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/16
 * Time: 15:52
 */

namespace ESD\Core\Server\Beans;


class WebSocketCloseFrame extends WebSocketFrame
{
    private $code;
    private $reason;

    public function __construct($frame)
    {
        parent::__construct($frame);
        $this->code = $frame->code;
        $this->reason = $frame->reason;
    }

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return mixed
     */
    public function getReason()
    {
        return $this->reason;
    }
}