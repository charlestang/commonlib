<?php

namespace charlestang\commonlib\qcloud\cos;

/**
 * Description of newPHPClass
 *
 * @author charles
 */
class Error
{

    const ERR_INVALID_PARAM       = -100001;
    const ERR_INDEX_NOT_FOUND     = -166;
    const ERR_DIRECTORY_NOT_EMPTY = -173;
    const ERR_PATH_CONFLICT       = -178;
    const ERR_FILE_ALREADY_EXISTS = -4018;

    protected static $msgs = [
        self::ERR_INVALID_PARAM => '参数格式错误',
    ];

    public static function msg($code)
    {
        $msg = '系统错误，请稍后重试';
        if (isset(self::$msgs[$code])) {
            $msg = self::$msgs[$code];
        }
        return $msg;
    }

}
