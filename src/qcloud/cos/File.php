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

    public function load()
    {
        $data = $this->loadData();
        if (!isset($data['filesize']) || !isset($data['sha'])) {
            throw new Exception(Error::ERR_INVALID_FILE, Error::msg(Error::ERR_INVALID_FILE));
        }
        $this->name       = $data['name'];
        $this->attribute  = $data['biz_attr'];
        $this->createTime = $data['ctime'];
        $this->modifyTime = $data['mtime'];
        $this->size       = $data['filesize'];
        $this->sha        = $data['sha'];
        $this->url        = $data['access_url'];
        $this->loaded     = true;
        return $this;
    }

    public function parentDirectory()
    {
        
    }

}
