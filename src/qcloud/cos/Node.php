<?php

namespace charlestang\commonlib\qcloud\cos;

/**
 * 腾讯云COS面向对象接口封装
 *
 * @author Charles Tang <charlestang@foxmail.com>
 */
class Node
{

    public $name;
    public $attribute;
    public $createTime;
    public $modifyTime;
    public $url;

    protected $cos;

    public function __construct()
    {

    }

}
