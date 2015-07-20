<?php

namespace charlestang\commonlib\qcloud\cos;

/**
 * 对文件的抽象封装
 *
 * @author Charles Tang <charlestang@foxmail.com>
 */
class File extends Node
{

    /**
     * 文件的大小
     * @var int
     */
    public $size;

    /**
     * 文件的哈希值
     * @var string
     */
    public $sha;

    /**
     * 文件的访问URL
     * @var string
     */
    public $url;

    public function parentDirectory()
    {

    }

}
