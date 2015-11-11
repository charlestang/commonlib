<?php

namespace charlestang\commonlib\qcloud\cos;

/**
 * 腾讯云COS面向对象接口封装
 *
 * Node，节点，是对目录树上的节点的抽象表示，从COS系统来看，节点有两种类型，一种是目录，另一种是文件。
 * 这两者，在使用过程中，表现出一种共性，将其抽象为节点。
 *
 * @author Charles Tang <charlestang@foxmail.com>
 * @property string $name        节点的名字
 * @property string $fullPath    节点在云端bucket内部的绝对路径,从/开始
 * @property string $bucket      节点的bucket名字
 * @property string $attribute   节点的属性,可以随意设置的一个字符串
 * @property int    $createTime  节点创建时间,Unix时间戳
 * @property int    $modifyTime  节点最后更新时间,Unix时间戳
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

    /**
     * 节点是否加载数据
     *
     * 如果节点已经与腾讯云服务进行过通讯，并载入所有数据，则该值为true，否则为false
     * @var boolean
     */
    protected $loaded = false;

    /**
     * @param string $fullPath 该节点的绝对路径
     * @param \charlestang\commonlib\qcloud\cos\Cos $cos 与腾讯云连接的Cos对象
     */
    public function __construct($bucket, $fullPath, $cos = null)
    {
        $this->bucket   = $bucket;
        $this->fullPath = $fullPath;
        if ($cos === null) {
            $this->cos = new Cos();
        } else {
            $this->cos = $cos;
        }
    }

    /**
     * 连接腾讯云，加载数据
     * @return boolean
     */
    public function load()
    {
        $data             = $this->loadData();
        $this->name       = $data['name'];
        $this->attribute  = $data['biz_attr'];
        $this->createTime = $data['ctime'];
        $this->modifyTime = $data['mtime'];
        $this->loaded     = true;
        return $this;
    }

    /**
     * 装载数据
     * @return array
     */
    protected function loadData()
    {
        return $this->cos->statNode($this->bucket, $this->fullPath);
    }

    /**
     * 节点是否加载数据
     * @return boolean
     */
    public function isLoaded()
    {
        return $this->loaded;
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

    /**
     * 读取节点的自定义属性
     * @return string
     */
    public function getAttribute()
    {
        $this->load();
        return $this->attribute;
    }

    /**
     * 设置节点的自定义属性
     * @param string $attribute
     * @return boolean
     */
    public function setAttribute($attribute)
    {
        return $this->cos->updateNode($this->bucket, $this->fullPath, $attribute);
    }
}
