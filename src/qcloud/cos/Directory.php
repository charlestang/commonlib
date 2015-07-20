<?php

namespace charlestang\commonlib\qcloud\cos;

/**
 * 对目录的抽象封装
 *
 * @author Charles Tang <charlestang@foxmail.com>
 */
class Directory extends Node
{

    protected $children = null;

    /**
     * 返回目录的所有子节点
     * @return Node[]
     */
    public function getChildren()
    {
        if ($this->children === null) {

        }

        return $this->children;
    }

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
}
