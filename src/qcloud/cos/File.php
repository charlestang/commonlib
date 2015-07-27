<?php

namespace charlestang\commonlib\qcloud\cos;

/**
 * 对文件的抽象封装
 *
 * @author Charles Tang <charlestang@foxmail.com>
 * @property string $name        文件的名字,不带路径
 * @property string $fullPath    文件在云端bucket内部的绝对路径,从/开始
 * @property string $bucket      文件的bucket名字
 * @property string $attribute   文件的属性,可以随意设置的一个字符串
 * @property int    $createTime  文件创建时间,Unix时间戳
 * @property int    $modifyTime  文件最后更新时间,Unix时间戳
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
     * 如果，云端文件不存在，就创建文件
     * @param string $filePath 本地文件的绝对路径
     * @throws Exception
     */
    public function updateWith($filePath)
    {
        if (!file_exists($filePath)) {
            throw new Exception(Error::ERR_FILE_NOT_EXISTS, Error::msg(Error::ERR_FILE_NOT_EXISTS));
        }

        try {
            $this->delete(); //删除原来的文件
        } catch (Exception $ex) {
            if ($ex->getCode() != Error::ERR_INDEX_NOT_FOUND) {
                throw $ex;
            }
        }

        $result    = $this->cos->uploadFile($this->bucket, $this->fullPath, $filePath);
        $this->url = $result['access_url'];
        return true;
    }

}
