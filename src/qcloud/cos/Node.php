<?php

namespace charlestang\commonlib\qcloud\cos;

/**
 * 腾讯云COS面向对象接口封装
 *
 * Node，节点，是对目录树上的节点的抽象表示，从COS系统来看，节点有两种类型，一种是目录，另一种是文件。
 * 这两者，在使用过程中，表现出一种共性，将其抽象为节点。
 *
 * @author Charles Tang <charlestang@foxmail.com>
 */
class Node
{

    /**
     * 节点的名称
     * @var string
     */
    public $name;

    /**
     * 节点的完整路径，该节点在某个特定的bucket下面的绝对路径，路径从 / 开始
     * @var string
     */
    public $fullPath;

    /**
     * 节点所属的bucket名字
     * @var string
     */
    public $bucket;

    /**
     * 节点上面附着的自定义属性，由使用系统的业务自行决定
     * @var string
     */
    public $attribute;

    /**
     * 节点创建时间
     * @var int UNIX 格式的时间戳
     */
    public $createTime;

    /**
     * 节点最后更新时间
     * @var int UNIX 格式的时间戳
     */
    public $modifyTime;

    /**
     * 负责访问节点数据Cos对象
     * @var \charlestang\commonlib\qcloud\cos\Cos
     */
    protected $cos;

    public function __construct()
    {

    }

    /**
     * 判定节点是否存在
     * @return boolean
     */
    public function exists()
    {
        return $this->cos->nodeExists($this->bucket, $this->fullPath);
    }

    /**
     * 删除节点
     * @return boolean
     */
    public function delete()
    {
        return $this->cos->deleteNode($this->bucket, $this->fullPath);
    }

}
