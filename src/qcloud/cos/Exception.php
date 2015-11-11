<?php

namespace charlestang\commonlib\qcloud\cos;

/**
 * Description of newPHPClass
 *
 * @author charles
 */
class Exception extends \Exception
{

    /**
     * @param int $code 错误码
     * @param string $message 错误消息
     */
    public function __construct($code, $message, $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
