<?php

namespace charlestang\commonlib\qcloud\cos;

use \Httpful\Handlers\JsonHandler;
use \Httpful\Httpful;
use \Httpful\Mime;
use \Httpful\Request;
use \Httpful\Response;

defined('COS_APP_ID') or define('COS_APP_ID', 0);
defined('COS_SECRET_ID') or define('COS_SECRET_ID', 0);
defined('COS_SECRET_KEY') or define('COS_SECRET_KEY', 0);

Httpful::register(Mime::JSON, new JsonHandler(['decode_as_array' => true]));

class Cos
{

    const API_SCHEMA             = 'http://';
    const API_DOMAIN             = 'web.file.myqcloud.com';
    const API_BASE_URL           = '/files/v1';
    //是否覆盖
    const OVERWRITE              = 1;
    const NON_OVERWRITE          = 0;
    //签名类型
    const SIGN_TYPE_ONCE         = 1; //一次有效
    const SIGN_TYPE_MULT         = 2; //多次有效
    //签名有效时间
    const SIGN_EXPIRE            = 60; //60秒
    //列表顺序
    const LIST_ORDER_NORMAL      = 0;
    const LIST_ORDER_REVERSE     = 1;
    //列表的方式
    const LIST_PATTERN_BOTH      = 'eListBoth';
    const LIST_PATTERN_FILE_ONLY = 'eListFileOnly';
    const LIST_PATTERN_DIR_ONLY  = 'ListDirOnly';

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
        $request = Request::post($apiUrl, $payload, 'json')->addHeader('authorization',
            $this->getAuthorizationSign($bucketName, $path));
        return $this->parseResponse($request->send());
    }

    /**
     * 删除一个目录
     * @param string $bucketName
     * @param string $dirPath
     * @return
     */
    public function deleteDirectory($bucketName, $dirPath)
    {
        return $this->deleteNode($bucketName, $dirPath);
    }

    /**
     * 更新一个目录的自定义属性
     * @param string $bucketName
     * @param string $dirPath
     * @param string $bizAttr
     * @return
     */
    public function updateDirectory($bucketName, $dirPath, $bizAttr)
    {
        return $this->updateNode($bucketName, $dirPath, $bizAttr);
    }

    /**
     * 底层方法，更新一个节点，可以更新一个目录，也可以更新一个文件的自定义属性
     * @param string $bucketName
     * @param string $nodePath
     * @param string $bizAttr
     * @return
     */
    public function updateNode($bucketName, $nodePath, $bizAttr)
    {
        $path    = DIRECTORY_SEPARATOR . $this->appId . DIRECTORY_SEPARATOR . $bucketName . DIRECTORY_SEPARATOR . $nodePath;
        $apiUrl  = $this->getBaseUrl() . $path;
        $body    = [
            'op'       => 'update',
            'biz_attr' => $bizAttr,
        ];
        $payload = json_encode($body);
        $request = Request::post($apiUrl, $payload, 'json')->addHeader('authorization',
            $this->getAuthorizationSign($bucketName, $path));
        return $this->parseResponse($request->send());
    }

    /**
     * 实现了类似 Linux ls命令的方法
     * @param string $bucketName
     * @param string $nodePath     路径名，以 / 结尾
     * @param string $prefix      按照前缀过滤
     * @param string $offset      起始位置
     * @param int    $pageSize    每页数量
     * @param string $pattern     列表方式，缺省是文件夹和文件都显示
     * @param int    $direction   列表方向
     *
     * @return type
     */
    public function lsNode($bucketName, $nodePath, $prefix = '', $offset = '', $pageSize = 10,
        $pattern = self::LIST_PATTERN_BOTH, $direction = self::LIST_ORDER_NORMAL)
    {
        $path   = DIRECTORY_SEPARATOR . $this->appId . DIRECTORY_SEPARATOR . $bucketName . DIRECTORY_SEPARATOR . $nodePath;
        $apiUrl = $this->getBaseUrl() . $path;
        if (!empty($prefix)) { //如果按照前缀搜索的话
            $apiUrl .= $prefix;
        }
        $query = [
            'op'      => 'list',
            'num'     => $pageSize,
            'pattern' => $pattern,
            'order'   => $direction,
        ];
        if (!empty($offset)) {
            $query['offset'] = $offset;
        }

        $apiUrl .= '?' . http_build_query($query);

        $request = Request::get($apiUrl, 'json')->addHeader('authorization', $this->getAuthorizationSign($bucketName, $path));
        return $this->parseResponse($request->send());
    }

    public function deleteNode($bucketName, $nodePath)
    {
        $path    = DIRECTORY_SEPARATOR . $this->appId . DIRECTORY_SEPARATOR . $bucketName . DIRECTORY_SEPARATOR . $nodePath;
        $apiUrl  = $this->getBaseUrl() . $path;
        $body    = [
            'op' => 'delete',
        ];
        $payload = json_encode($body);
        $request = Request::post($apiUrl, $payload, 'json')->addHeader('authorization',
            $this->getAuthorizationSign($bucketName, $path, self::SIGN_TYPE_ONCE));
        return $this->parseResponse($request->send());
    }

    public function listDirectory($bucketName, $dirPath, $prefix = '', $offset = '', $pageSize = 10,
        $direction = self::LIST_ORDER_NORMAL)
    {
        return $this->lsNode($bucketName, $dirPath, $prefix, $offset, $pageSize, self::LIST_PATTERN_DIR_ONLY, $direction);
    }

    public function listFile($bucketName, $dirPath, $prefix = '', $offset = '', $pageSize = 10,
        $direction = self::LIST_ORDER_NORMAL)
    {
        return $this->lsNode($bucketName, $dirPath, $prefix, $offset, $pageSize, self::LIST_PATTERN_FILE_ONLY, $direction);
    }

    public function directoryExists($bucketName, $dirPath)
    {
        $exists = true;
        try {
            $this->listDirectory($bucketName, $dirPath);
        } catch (Exception $ex) {
            if ($ex->getCode() == Error::ERR_INDEX_NOT_FOUND) {
                $exists = false;
            } else {
                throw $ex;
            }
        }
        return $exists;
    }

    public function uploadFile()
    {
    }

    public function deleteFile($bucketName, $filePath)
    {
        $this->deleteNode($bucketName, $filePath);
    }

    public function updateFileAttribute($bucketName, $filePath, $bizAttr)
    {
        return $this->updateNode($bucketName, $filePath, $bizAttr);
    }

    /**
     * @param Response $response
     */
    private function parseResponse($response)
    {
        if ($response->hasErrors()) {
            if ($response->hasBody()) {
                throw new Exception($response->body['code'], $response->body['message']);
            } else {
                throw new Exception($response->code, 'HTTP Error.');
            }
        }
        if ($response->body['code'] == 0) {
            return isset($response->body['data']) ? $response->body['data'] : true;
        } else {
            throw new Exception($response->body['code'], $response->body['message']);
        }
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
        } else {
            $expire = time() + $expire;
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

    private function encode($string, $key)
    {
        return base64_encode(hash_hmac('sha1', $string, $key, true) . $string);
    }

}
