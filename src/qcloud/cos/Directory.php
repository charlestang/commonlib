<?php

namespace charlestang\commonlib\qcloud\cos;

/**
 * 对目录的抽象封装
 *
 * @author Charles Tang <charlestang@foxmail.com>
 */
class Directory extends Node
{

    /**
     * 递归删除目录
     *
     * 注意：危险方法，使用不当会删除全部文件，有可能非常慢
     *
     * @return boolean
     */
    public function deleteRecursively()
    {

    }

    /**
     * 递归查找全部文件
     *
     * 注意：使用不当会非常缓慢，发出海量网络请求，返回巨量结果集
     *
     * @return Node[]
     */
    public function findRecursively()
    {

    }

    /**
     * 递归创建目录
     *
     * 其功能相当于Linux下的 mkdir -p，自动创建路径中的所有不存在的父目录
     *
     * @return boolean
     */
    public function createRecursively()
    {
        $dirs = explode('/', trim($this->fullPath, '/'));
        $path = '/';
        foreach ($dirs as $dir) {
            $path .= $dir . '/';
            if (!$this->cos->directoryExists($this->bucket, $path)) {
                $this->cos->createDirectory($this->bucket, $path);
            }
        }

        return $this->load();
    }

}
