<?php

namespace charlestang\commonlib\qcloud\cos;

/**
 * Description of newPHPClass
 *
 * @author charles
 */
class Exception extends \Exception
{

    public function __construct($code, $message)
    {
        parent::__construct($message, $code);
    }

}
