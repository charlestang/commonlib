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

    /**
     * 加载文件的基本信息
     * @return File
     * @throws Exception
     */
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

    /**
     * 使用本地文件替换替换远端的文件
     * @param string $filePath 本地文件的绝对路径
     * @param boolean $createIfNotExists 如果远端文件不存在，就创建，如果为false，远端文件不存在将抛出异常
     * @throws Exception
     */
    public function updateWith($filePath, $createIfNotExists = true)
    {
        if (!file_exists($filePath)) {
            throw new Exception(Error::ERR_FILE_NOT_EXISTS, Error::msg(Error::ERR_FILE_NOT_EXISTS));
        }
    }

}
