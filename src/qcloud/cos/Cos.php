<?php

defined('COS_APP_ID') or define('COS_APP_ID', 0);
defined('COS_SECRET_ID') or define('COS_SECRET_ID', 0);
defined('COS_SECRET_KEY') or define('COS_SECRET_KEY', 0);

namespace charlestang\commonlib\qcloud\cos;

class Cos
{

    private $appId;
    private $secretId;
    private $secretKey;

    const OVER_WRITE      = 1;
    const DONT_OVER_WRITE = 0;

    public function __construct($appId = COS_APP_ID, $secretId = COS_SECRET_ID, $secretKey = COS_SECRET_KEY)
    {
        $this->appId     = $appId;
        $this->secretId  = $secretId;
        $this->secretKey = $secretKey;
    }

    /**
     * 在一个指定的Bucket中，创建一个目录
     * @param string $bucket_name bucket的名字
     * @param string $dir_path    要创建的目录的路径，要从根目录写起，以 / 结尾
     */
    public function createDirectory($bucketName, $dirPath, $overWrite= self::DONT_OVER_WRITE, $bizAttr= '')
    {
        
    }

    public function deleteDirectory($bucketName, $dirPath)
    {
        
    }

    public function updateDirectory($bucketName, $dirPath, $bizAttr)
    {
        
    }

    public function listDirectory()
    {
        
    }

    public function uploadFile()
    {
        
    }

    public function deleteFile()
    {
        
    }

    public function updateFileAttribute()
    {
        
    }

    public function listFile()
    {
        
    }

}
