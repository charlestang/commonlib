<?php

namespace charlestang\commonlib\qcloud\cos;

use \Httpful\Http;
use \Httpful\Request;

defined('COS_APP_ID') or define('COS_APP_ID', 0);
defined('COS_SECRET_ID') or define('COS_SECRET_ID', 0);
defined('COS_SECRET_KEY') or define('COS_SECRET_KEY', 0);

class Cos
{

    const API_SCHEMA     = 'http://';
    const API_DOMAIN     = 'web.file.myqcloud.com';
    const API_BASE_URL   = '/files/v1';
    //是否覆盖
    const OVERWRITE      = 1;
    const NON_OVERWRITE  = 0;
    //签名类型
    const SIGN_TYPE_ONCE = 1; //一次有效
    const SIGN_TYPE_MULT = 2; //多次有效
    //签名有效时间
    const SIGN_EXPIRE    = 60; //60秒

    //以上常量定义

    private $appId;
    private $secretId;
    private $secretKey;

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
    public function createDirectory($bucketName, $dirPath, $attribute = '', $overwrite = self::NON_OVERWRITE)
    {
        $path   = DIRECTORY_SEPARATOR . $this->appId . DIRECTORY_SEPARATOR . $bucketName . DIRECTORY_SEPARATOR . $dirPath;
        $apiUrl = $this->getBaseUrl() . $path;
        $body   = [
            'op'            => 'create',
            'to_over_write' => $overwrite,
        ];
        if (!empty($attribute)) {
            $body['biz_attr'] = $attribute;
        }
        $payload = json_encode($body);
        $request = new Request([
            'uri'     => $apiUrl,
            'method'  => Http::POST,
            'headers' => [
                'authorization'  => $this->getAuthorizationSign($bucketName, $path),
                'content-type'   => 'application/json',
                'content-length' => strlen($payload),
            ],
            'payload' => $payload,
        ]);

        return $this->parseResponse($request->send());
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

    /**
     * 生成授权签名
     * @param string $bucketName
     * @param string $path
     * @param int $type
     * @param int $expire
     *
     * @return string 签名字符串
     */
    public function getAuthorizationSign($bucketName, $path, $type = self::SIGN_TYPE_MULT, $expire = self::SIGN_EXPIRE)
    {

        if ($type == self::SIGN_TYPE_ONCE) { //一次有效的签名，$expire 必须填 0
            $expire = 0;
        }
        if ($type == self::SIGN_TYPE_MULT) { //多次有效的签名，$path 填空
            $path = '';
        }
        $params = [
            'a' => $this->appId,
            'k' => $this->secretId,
            'e' => $expire,
            't' => time(),
            'r' => rand(1000000000, 9999999999),
            'f' => $path,
            'b' => $bucketName
        ];

        $str = $this->buildString($params);

        return $this->encode($str, $this->secretKey);
    }

    private function getBaseUrl()
    {
        return self::API_SCHEMA . self::API_DOMAIN . self::API_BASE_URL;
    }

    private function buildString($arr)
    {
        $str = '';
        foreach ($arr as $k => $v) {
            $str .= $k . '=' . $v . '&';
        }
        return rtrim($str, '&');
    }

    public function encode($string, $key)
    {
        return base64_encode(hash_hmac('sha1', $string, $key, true) . $string);
    }

}
